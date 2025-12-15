<?php

namespace App\Http\Controllers\Auth\Integrations;

use App\Http\Controllers\Controller;
use App\Models\OauthAccount;
use App\Models\User;
use Laravel\Socialite\Socialite;
use Symfony\Component\HttpFoundation\Response;

class YoutubeAuthController extends Controller
{
    public function redirect(): Response
    {
        return response()->json(
            data: [
                'url' => Socialite::driver('google')
                    ->scopes([
                        'https://www.googleapis.com/auth/youtube.readonly',
                    ])
                    ->with([
                        'access_type' => 'offline',
                        'prompt' => 'consent',
                    ])
                    ->stateless()
                    ->redirect()
                    ->getTargetUrl(),
            ],
            status: Response::HTTP_OK
        );
    }

    public function callback(): Response
    {
        try {
            $socialiteUser = Socialite::driver('google')
                ->stateless()
                ->user();

            $user = User::updateOrCreate([
                'email' => $socialiteUser->email,
            ], [
                'name' => $socialiteUser->name,
                'email_verified_at' => now(),
            ]);

            OauthAccount::updateOrCreate([
                'provider_user_id' => $socialiteUser->id,
                'user_id' => $user->id,
            ], [
                'provider' => 'google',
                'access_token' => $socialiteUser->token,
                'refresh_token' => $socialiteUser->refreshToken,
                'expires_at' => now()->addSeconds($socialiteUser->expiresIn),
            ]);

            return response()->json(
                data: ['token' => $user->createToken('spotify-auth-token')->plainTextToken],
                status: Response::HTTP_OK
            );
        } catch (\Exception $e) {
            logger()->info($e->getMessage());

            return response()->json(
                data: [],
                status: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
