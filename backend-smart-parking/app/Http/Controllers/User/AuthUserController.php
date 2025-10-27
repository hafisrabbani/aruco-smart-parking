<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AuthUserController extends Controller
{
    public function index()
    {
        return view('user.auth');
    }

    public function login()
    {
        $googleUser = Socialite::driver('google')->user();
        $email = $googleUser->getEmail(); // TOOD: Validate Domain Email
        $allowedDomain = ['gmail.com'];
        $mail = $this->mailPrefix($email, $allowedDomain);
        if (!$mail) {
            return redirect(route('user.login.form'))
                ->withErrors(['email' => 'Email domain is not allowed.'])
                ->withInput();
        }
        $user = User::where('email', $email)->first();
        if (!$user) {
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $email,
                'password' => bcrypt(str()->random(16)),
            ]);
        }

        Auth::login($user);

        return redirect(route('user.dashboard'));
    }

    public function mailPrefix($mail = '', $allowedDomains = [])
    {
        if (!$mail || empty($allowedDomains)) {
            return null;
        }

        $mailParts = explode('@', $mail);
        if (count($mailParts) !== 2) {
            return null;
        }

        [$localPart, $domainPart] = $mailParts;
        $domainPart = strtolower(trim($domainPart));

        foreach ($allowedDomains as $allowedDomain) {
            if ($domainPart === strtolower(trim($allowedDomain))) {
                return $mail;
            }
        }

        return null;
    }

    public function logout()
    {
        Auth::logout();
        return redirect(route('user.login.form'));
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

}
