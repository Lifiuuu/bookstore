<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HospitalQueueController extends Controller
{
    /**
     * Available polyclinics — static configuration, no DB needed.
     */
    protected array $polyclinics = [
        ['id' => 'umum',          'name' => 'Poli Umum'],
        ['id' => 'anak',          'name' => 'Poli Anak'],
        ['id' => 'gigi',          'name' => 'Poli Gigi'],
        ['id' => 'kandungan',     'name' => 'Poli Kandungan'],
        ['id' => 'penyakit-dalam','name' => 'Poli Penyakit Dalam'],
        ['id' => 'mata',          'name' => 'Poli Mata'],
    ];

    /**
     * Priority value map — lower integer = higher priority.
     */
    protected array $priorityValues = [
        'darurat'    => 1,
        'lansia'     => 2,
        'disabilitas'=> 2,
        'normal'     => 3,
    ];

    // -------------------------------------------------------------------------
    // VIEWS
    // -------------------------------------------------------------------------

    public function registerForm()
    {
        return view('hospital-queue.register', [
            'polyclinics' => $this->polyclinics,
        ]);
    }

    public function nursePanel(Request $request)
    {
        $poliId = $this->resolvePoliId($request->get('poli', 'umum'));
        $state  = $this->getState($poliId);

        return view('hospital-queue.nurse', [
            'polyclinics'   => $this->polyclinics,
            'currentPoliId' => $poliId,
            'state'         => $state,
        ]);
    }

    public function boardDisplay()
    {
        return view('hospital-queue.board', [
            'polyclinics' => $this->polyclinics,
        ]);
    }

    // -------------------------------------------------------------------------
    // MUTATIONS (JSON responses)
    // -------------------------------------------------------------------------

    public function registerSubmit(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'poli'     => 'required|string',
            'priority' => 'required|in:normal,lansia,disabilitas,darurat',
        ]);

        $poliId = $this->resolvePoliId($request->poli);
        $state  = $this->getState($poliId);

        $priorityValue = $this->priorityValues[$request->priority] ?? 3;

        $entry = [
            'number'         => $state['next_number'],
            'name'           => $request->name,
            'priority'       => $request->priority,
            'priority_value' => $priorityValue,
            'registered_at'  => now()->toISOString(),
            'status'         => 'waiting',
        ];

        $state['waiting'][]  = $entry;
        $state['next_number']++;
        $this->saveState($poliId, $state);

        // Compute position in queue
        $position = count($state['waiting']);

        return redirect()->route('hq.register')->with('queued', [
            'number'    => $entry['number'],
            'poli_name' => $state['poli_name'],
            'poli_id'   => $poliId,
            'position'  => $position,
            'estimated' => $position * 10,
        ]);
    }

    public function nurseCall(Request $request)
    {
        $poliId = $this->resolvePoliId($request->get('poli', 'umum'));
        $state  = $this->getState($poliId);

        if (empty($state['waiting'])) {
            return response()->json(['error' => 'Antrian kosong'], 422);
        }

        $next = $this->getNextPatient($state['waiting']);

        // Remove from waiting
        $state['waiting'] = array_values(array_filter(
            $state['waiting'],
            fn($p) => $p['number'] !== $next['number']
        ));

        $next['status'] = 'called';
        $state['current'] = $next;

        $this->saveState($poliId, $state);

        return response()->json(['success' => true, 'state' => $state]);
    }

    public function nurseSkip(Request $request)
    {
        $poliId = $this->resolvePoliId($request->get('poli', 'umum'));
        $state  = $this->getState($poliId);

        if (is_null($state['current'])) {
            return response()->json(['error' => 'Tidak ada pasien yang sedang dipanggil'], 422);
        }

        $absent = $state['current'];
        $absent['status'] = 'absent';
        $state['absent'][]  = $absent;
        $state['current']   = null;

        $this->saveState($poliId, $state);

        return response()->json(['success' => true]);
    }

    public function nurseRecall(Request $request)
    {
        $request->validate(['number' => 'required|integer', 'poli' => 'required|string']);

        $poliId = $this->resolvePoliId($request->poli);
        $state  = $this->getState($poliId);

        $patient = null;
        foreach ($state['absent'] as $p) {
            if ((int)$p['number'] === (int)$request->number) {
                $patient = $p;
                break;
            }
        }

        if (is_null($patient)) {
            return response()->json(['error' => 'Pasien tidak ditemukan di daftar tidak hadir'], 404);
        }

        // Remove from absent
        $state['absent'] = array_values(array_filter(
            $state['absent'],
            fn($p) => (int)$p['number'] !== (int)$request->number
        ));

        // Prepend to waiting
        $patient['status'] = 'waiting';
        array_unshift($state['waiting'], $patient);

        $this->saveState($poliId, $state);

        return response()->json(['success' => true]);
    }

    public function nurseReset(Request $request)
    {
        $request->validate(['poli' => 'required|string']);

        $poliId = $this->resolvePoliId($request->poli);
        Cache::forget("hospital_queue:{$poliId}");

        return response()->json(['success' => true]);
    }

    // -------------------------------------------------------------------------
    // API STATE (AJAX Polling fallback)
    // -------------------------------------------------------------------------

    public function stateApi(Request $request)
    {
        $poliParam = $request->get('poli', 'umum');
        $isAll = $poliParam === 'all';
        $poliId = $isAll ? 'all' : $this->resolvePoliId($poliParam);

        if ($isAll) {
            $states = [];
            foreach ($this->polyclinics as $poli) {
                $states[] = $this->getState($poli['id']);
            }
            return response()->json(['type' => 'all', 'states' => $states]);
        }

        $state = $this->getState($poliId);
        return response()->json(['type' => 'single', 'state' => $state]);
    }

    // -------------------------------------------------------------------------
    // PRIVATE HELPERS
    // -------------------------------------------------------------------------

    public function getState(string $poliId): array
    {
        $poliName = $this->getPoliName($poliId);
        $cached   = Cache::get("hospital_queue:{$poliId}");

        if ($cached) {
            return $cached;
        }

        return [
            'poli_id'     => $poliId,
            'poli_name'   => $poliName,
            'version'     => 0,
            'next_number' => 1,
            'current'     => null,
            'waiting'     => [],
            'absent'      => [],
        ];
    }

    public function saveState(string $poliId, array $state): void
    {
        $state['version']++;

        $lock = Cache::lock("hospital_queue:write:{$poliId}", 5);
        try {
            $lock->block(5);
            Cache::put("hospital_queue:{$poliId}", $state, 7200);
        } finally {
            optional($lock)->release();
        }
    }

    protected function getNextPatient(array $waiting): ?array
    {
        if (empty($waiting)) {
            return null;
        }

        usort($waiting, function ($a, $b) {
            $pa = $a['priority_value'] ?? 3;
            $pb = $b['priority_value'] ?? 3;

            if ($pa !== $pb) {
                return $pa <=> $pb;
            }

            return strcmp($a['registered_at'] ?? '', $b['registered_at'] ?? '');
        });

        return $waiting[0];
    }

    protected function resolvePoliId(string $poliId): string
    {
        $ids = array_column($this->polyclinics, 'id');
        return in_array($poliId, $ids) ? $poliId : 'umum';
    }

    protected function getPoliName(string $poliId): string
    {
        foreach ($this->polyclinics as $poli) {
            if ($poli['id'] === $poliId) {
                return $poli['name'];
            }
        }
        return 'Poli Umum';
    }
}
