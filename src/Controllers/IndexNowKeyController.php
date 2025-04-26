<?php

namespace Ymigval\LaravelIndexnow\Controllers;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class IndexNowKeyController
{
    /**
     * Handle the request to display the IndexNow API key.
     *
     * @return Response
     */
    public function show(): Response
    {
        $apiKey = config('indexnow.current_api_key');
        
        return new Response($apiKey, ResponseAlias::HTTP_OK, [
            'Content-Type' => 'text/plain; charset=utf-8',
        ]);
    }
}
