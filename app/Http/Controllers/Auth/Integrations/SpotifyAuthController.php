<?php

namespace App\Http\Controllers\Auth\Integrations;

use Illuminate\Http\Request;
use Laravel\Socialite\Socialite;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

class SpotifyAuthController extends Controller
{
    /**
     * @return Response
     */
    public function redirect(): Response
    {
        $url = Socialite::driver('spotify')
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        return response()->json(
            ['url' => $url],
            Response::HTTP_OK
        );
    }

    public function callback()
    {

    }
}
