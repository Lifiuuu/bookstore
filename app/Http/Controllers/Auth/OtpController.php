<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Verified;

class OtpController extends Controller
{
    public function show(Request $request)
    {
        // show OTP input; require otp_user_id in session
        if (! $request->session()->has('otp_user_id')) {
            return redirect()->route('login');
        }

        // use the verify view as the single OTP entry point
        return view('auth.verify');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $userId = $request->session()->get('otp_user_id');
        if (! $userId) {
            return redirect()->route('login')->withErrors(['otp' => 'Session expired, please login again.']);
        }

        $user = User::find($userId);
        if (! $user) {
            return redirect()->route('login')->withErrors(['otp' => 'User not found.']);
        }

        if (! hash_equals($user->otp ?? '', $request->input('otp'))) {
            return back()->withErrors(['otp' => 'Invalid OTP code.']);
        }

        // clear otp
        $user->otp = null;

        // mark email verified if not already
        if (! $user->hasVerifiedEmail()) {
            $user->email_verified_at = now();
            $user->save();
            event(new Verified($user));
        } else {
            $user->save();
        }

        // log in user and forget session marker
        Auth::loginUsingId($user->id);
        $request->session()->forget('otp_user_id');

        return redirect()->intended('/dashboard');
    }
}
