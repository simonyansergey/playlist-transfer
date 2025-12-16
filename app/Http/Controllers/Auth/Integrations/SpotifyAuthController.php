<?php

namespace App\Http\Controllers\Auth\Integrations;

use App\Models\User;
use App\Models\OauthAccount;
use Illuminate\Http\Request;
use Laravel\Socialite\Socialite;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

class SpotifyAuthController extends Controller
{
    public function redirect(): Response
    {
        return response()->json(
            data: [
                'url' => Socialite::driver('spotify')
                    ->scopes([
                        'playlist-modify-private',
                        'playlist-modify-public',
                    ])
                    ->stateless()
                    ->redirect()
                    ->getTargetUrl(),
            ],
            status: Response::HTTP_OK
        );
    }

    public function callback(Request $request): Response
    {
        try {
            $user = $request->user('sanctum');
            $socialiteUser = Socialite::driver('spotify')
                ->stateless()
                ->user();

            OauthAccount::updateOrCreate([
                'provider_user_id' => $socialiteUser->id,
                'user_id' => $user->id,
            ], [
                'provider' => 'spotify',
                'access_token' => $socialiteUser->token,
                'refresh_token' => $socialiteUser->refreshToken,
                'expires_at' => now()->addSeconds($socialiteUser->expiresIn),
            ]);

            return response()->json(
                data: ['message' => 'Spotify account linked successfully!'],
                status: Response::HTTP_OK
            );
        } catch (\Exception $e) {
            logger()->info($e->getMessage());

            return response()->json(
                data: ['message' => 'Something went wrong! Please try again later'],
                status: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
