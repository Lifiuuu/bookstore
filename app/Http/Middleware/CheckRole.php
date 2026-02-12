<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     * Middleware untuk mengecek akses user setelah login
     * Menyimpan user data ke session untuk proses verifikasi
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Jika user sudah authenticate
        if (!$request->user()) {
            return redirect('login');
        }

        $user = $request->user();

        // Simpan user data ke session
        $request->session()->put('user_id', $user->id);
        $request->session()->put('user_name', $user->name);
        $request->session()->put('user_email', $user->email);
        $request->session()->put('user_role', $user->role);
        $request->session()->put('logged_in_at', now());

        // Check apakah user role adalah 'user'
        if ($user->role === 'user') {
            return $next($request);
        }

        abort(403, 'Akses ditolak. Anda tidak memiliki otoitasi untuk mengakses halaman ini.');
    }
}
