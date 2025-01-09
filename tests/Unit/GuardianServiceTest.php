<?php

use App\Services\GuardianService;
use App\Exceptions\GuardianApiException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

// Mock the config function
beforeEach(function () {
    config()->set('the-guardian.resource_url', 'https://api.theguardian.com/');
    config()->set('the-guardian.api_key', 'dummy-api-key');
    config()->set('cache.ttl', 3600);
});

it('fetches results from the cache if available', function () {
    $endpoint = 'sections';
    $queryParams = ['q' => 'technology'];
    $cacheKey = 'sections:technology';
    $cachedData = [['id' => '1', 'name' => 'Technology']];

    Cache::shouldReceive('has')->with($cacheKey)->andReturn(true);
    Cache::shouldReceive('get')->with($cacheKey)->andReturn($cachedData);

    $service = new GuardianService();
    $results = $service->get($endpoint, $queryParams);

    expect($results)->toBe($cachedData);
});

it('fetches results from the API and caches them', function () {
    $endpoint = 'sections';
    $queryParams = ['q' => 'technology'];
    $cacheKey = 'sections:technology';
    $apiResponse = [
        'response' => [
            'results' => [['id' => '1', 'name' => 'Technology']],
        ],
    ];

    Cache::shouldReceive('has')->with($cacheKey)->andReturn(false);
    Cache::shouldReceive('set')->with($cacheKey, $apiResponse['response']['results'], 3600)->once();

    Http::fake([
        'https://api.theguardian.com/sections*' => Http::response($apiResponse, 200),
    ]);

    $service = new GuardianService();
    $results = $service->get($endpoint, $queryParams);

    expect($results)->toBe($apiResponse['response']['results']);
});

it('throws an exception if the query parameter "q" is missing', function () {
    $this->expectException(GuardianApiException::class);
    $this->expectExceptionMessage('Missing query parameter "q"');

    $service = new GuardianService();
    $service->get('sections', []);
});

it('throws an exception if the API key is missing', function () {
    config()->set('the-guardian.api_key', null);

    $this->expectException(GuardianApiException::class);
    $this->expectExceptionMessage('Missing API key');

    $service = new GuardianService();
    $service->get('sections', ['q' => 'technology']);
});

it('throws an exception if the API response is unsuccessful', function () {
    $endpoint = 'sections';
    $queryParams = ['q' => 'technology'];

    Cache::shouldReceive('has')->with('sections:technology')->andReturn(false);

    Http::fake([
        'https://api.theguardian.com/sections*' => Http::response([], 500),
    ]);

    $this->expectException(GuardianApiException::class);

    $service = new GuardianService();
    $service->get($endpoint, $queryParams);
});
