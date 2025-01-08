<?php

namespace App\Services;

use App\Contracts\RssFeedContract;
use App\Exceptions\GuardianApiException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GuardianService implements RssFeedContract
{
    /**
     * @throws GuardianApiException
     */
    public function get(string $endpoint, array $queryParams = []): array
    {
        Log::debug('Fetching sections from Guardian API', ['endpoint' => $endpoint]);

        $queryParams['api-key'] = 'sadasd';
        $queryParams['format']  = 'json';
        $endpoint .= '?'.http_build_query($queryParams);

        $response = Http::get(config('the-guardian.resource_url').$endpoint);
        Log::debug('API Response', ['response' => $response->json()]);

        if (! $response->successful()) {
            throw new GuardianApiException($response);
        }

        Log::info('Successfully fetched sections from Guardian API');

        return $response->json()['response']['results'];
    }
}
