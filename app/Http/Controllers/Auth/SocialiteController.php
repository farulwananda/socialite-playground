<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
        $user = Socialite::driver($provider)->user();

        $existingUser = User::where('email', $user->getEmail())->first();

        if ($existingUser) {
            if ($existingUser->name !== $user->getName() || $existingUser->image !== $user->getAvatar()) {
                $existingUser->name = $user->getName();
                $existingUser->image = $user->getAvatar();
                $existingUser->save();
            }

            Auth::login($existingUser, true);
        } else {
            $newUser = User::create([
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'password' => bcrypt('password'),
                'image' => $user->getAvatar(),
            ]);

            Auth::login($newUser, true);
        }

        return redirect()->route('dashboard');
    }
}
