<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WilayahController extends Controller
{
    // Try to read CSV from a URL and return rows (each row is an array of fields)
    private function readCsvFromUrl(string $url): array
    {
        $rows = [];

        // try fopen on URL first (requires allow_url_fopen)
        $handle = @fopen($url, 'r');
        $tmp = null;
        if ($handle === false) {
            // fallback: file_get_contents then tmpfile
            $content = @file_get_contents($url);
            if ($content === false) {
                return $rows;
            }
            $tmp = tmpfile();
            fwrite($tmp, $content);
            $meta = stream_get_meta_data($tmp);
            $tmpPath = $meta['uri'];
            $handle = @fopen($tmpPath, 'r');
            if ($handle === false) {
                if (is_resource($tmp)) fclose($tmp);
                return $rows;
            }
        }

        while (($data = fgetcsv($handle, 0, ';')) !== false) {
            // fgetcsv already handles quotes/enclosures; trim fields
            $rows[] = array_map(function ($v) {
                return is_string($v) ? trim($v) : $v;
            }, $data);
        }

        fclose($handle);
        if (isset($tmp) && is_resource($tmp)) {
            fclose($tmp);
        }

        return $rows;
    }

    // Map CSV rows with header (first row) to associative arrays
    private function mapCsv(string $url): array
    {
        $rows = $this->readCsvFromUrl($url);
        if (empty($rows) || count($rows) < 2) return [];

        $header = $rows[0];
        $mapped = [];
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            // pad row if shorter than header
            if (count($row) < count($header)) {
                $row = array_pad($row, count($header), '');
            }
            try {
                $assoc = array_combine($header, $row);
            } catch (\Throwable $e) {
                // skip malformed row
                continue;
            }
            $mapped[] = $assoc;
        }

        return $mapped;
    }
    public function index()
    {
        return view('tm5.wilayah');
    }

    public function provinces()
    {
        $url = 'https://raw.githubusercontent.com/guzfirdaus/Wilayah-Administrasi-Indonesia/master/csv/provinces.csv';
        $data = $this->mapCsv($url);
        $out = array_map(function ($r) {
            return ['id' => $r['id'], 'name' => $r['name']];
        }, $data);
        return response()->json(array_values($out));
    }

    public function regencies(Request $request)
    {
        $provinceId = $request->query('province_id') ?? $request->query('prov') ?? $request->query('province');
        $url = 'https://raw.githubusercontent.com/guzfirdaus/Wilayah-Administrasi-Indonesia/master/csv/regencies.csv';
        $data = $this->mapCsv($url);
        $filtered = array_filter($data, function ($r) use ($provinceId) {
            if ($provinceId === null || $provinceId === '') return true;
            return (string) $r['province_id'] === (string) $provinceId;
        });
        $out = array_map(function ($r) {
            return ['id' => $r['id'], 'name' => $r['name'], 'province_id' => $r['province_id']];
        }, array_values($filtered));
        return response()->json($out);
    }

    public function districts(Request $request)
    {
        $regencyId = $request->query('regency_id') ?? $request->query('regency');
        $url = 'https://raw.githubusercontent.com/guzfirdaus/Wilayah-Administrasi-Indonesia/master/csv/districts.csv';
        $data = $this->mapCsv($url);
        $filtered = array_filter($data, function ($r) use ($regencyId) {
            if ($regencyId === null || $regencyId === '') return true;
            return (string) $r['regency_id'] === (string) $regencyId;
        });
        $out = array_map(function ($r) {
            return ['id' => $r['id'], 'name' => $r['name'], 'regency_id' => $r['regency_id']];
        }, array_values($filtered));
        return response()->json($out);
    }

    public function villages(Request $request)
    {
        $districtId = $request->query('district_id') ?? $request->query('district');
        $url = 'https://raw.githubusercontent.com/guzfirdaus/Wilayah-Administrasi-Indonesia/master/csv/villages.csv';
        $data = $this->mapCsv($url);
        $filtered = array_filter($data, function ($r) use ($districtId) {
            if ($districtId === null || $districtId === '') return true;
            return (string) $r['district_id'] === (string) $districtId;
        });
        $out = array_map(function ($r) {
            return ['id' => $r['id'], 'name' => $r['name'], 'district_id' => $r['district_id']];
        }, array_values($filtered));
        return response()->json($out);
    }
}
