<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;

class SocialAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        $googleUser = Socialite::driver('google')->user();

        $user = User::where('id_google', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if (! $user) {
            $user = User::create([
                'name' => $googleUser->getName() ?? $googleUser->getNickname() ?? 'Google User',
                'email' => $googleUser->getEmail(),
                'password' => Hash::make(Str::random(24)),
                'id_google' => $googleUser->getId(),
            ]);
        } else {
            // ensure id_google is stored
            if (! $user->id_google) {
                $user->id_google = $googleUser->getId();
            }
        }

        // generate OTP, save and email
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->otp = $otp;

        // mark email as verified (trusted Google email) if not already
        if (! $user->email_verified_at) {
            $user->email_verified_at = now();
        }

        $user->save();

        if ($user->email) {
            Mail::to($user->email)->send(new OtpMail($otp));
        }

        // ensure user is not logged in yet; store user id in session for OTP verification
        Auth::logout();
        $request->session()->put('otp_user_id', $user->id);

        return redirect()->route('otp.show');
    }
}
