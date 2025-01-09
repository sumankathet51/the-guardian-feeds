<?php

namespace App\Services;

use App\Contracts\RssFeedContract;
use App\Exceptions\GuardianApiException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GuardianService implements RssFeedContract
{
    /**
     * @throws GuardianApiException
     */
    public function get(string $endpoint, array $queryParams = []): array
    {
        Log::debug('Fetching sections from Guardian API', ['endpoint' => $endpoint]);

        $this->validateQueryParams($queryParams);
        $cacheKey = Str::slug($endpoint).':'.$queryParams['q'];

        if (Cache::has($cacheKey)) {
            Log::debug('Fetching sections from cache', ['endpoint' => $endpoint, 'cache_key' => $cacheKey]);

            return Cache::get($cacheKey);
        }

        $this->validateApiKey();

        $queryParams['api-key'] = config('the-guardian.api_key');
        $queryParams['format']  = 'json';
        $endpointWithParams     = $endpoint.'?'.http_build_query($queryParams);

        $response = Http::get(config('the-guardian.resource_url').$endpointWithParams);
        Log::debug('API Response', ['response' => $response->json()]);

        if (! $response->successful()) {
            throw new GuardianApiException($response);
        }

        Log::info('Successfully fetched sections from Guardian API');
        $results = $response->json()['response']['results'];

        Cache::set($cacheKey, $results, config('cache.ttl'));

        return $results;
    }

    /**
     * @throws GuardianApiException
     */
    private function validateQueryParams(array $queryParams): void
    {
        if (! isset($queryParams['q'])) {
            throw new GuardianApiException('Missing query parameter "q"');
        }
    }

    /**
     * @throws GuardianApiException
     */
    private function validateApiKey(): void
    {
        if (! config('the-guardian.api_key')) {
            throw new GuardianApiException('Missing API key');
        }
    }
}
