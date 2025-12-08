<?php

return [
    'client_id' => env('SPOTIFY_CLIENT_ID'),
    'client_secret' => env('SPOTIFY_CLIENT_SECRET'),
    'redirect_uri' => env('SPOTIFY_REDIRECT_URI'),
    'token_url' => 'https://accounts.spotify.com/api/token',
    'api_base' => 'https://api.spotify.com/v1',
];
