<?php

namespace App\Http\Controllers\Auth\Integrations;

use Illuminate\Http\Request;
use Laravel\Socialite\Socialite;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class YoutubeAuthController extends Controller
{
    /**
     * @return RedirectResponse
     */
    public function redirect(): Response
    {
        $url = Socialite::driver('google')
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
