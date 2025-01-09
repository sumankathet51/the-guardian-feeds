<?php

use App\Contracts\RssFeedContract;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    // Define the route for testing
    Route::get('/feeds/{section}', \App\Http\Controllers\RssFeedController::class)
        ->name('rss-feed.show');
});

it('returns RSS feed with correct structure and content', function () {
    $section = 'technology';
    $mockFeedItems = [
        [
            'id'       => 'technology-1',
            'webTitle' => 'Tech News 1',
            'webUrl'   => 'https://example.com/tech1',
        ],
        [
            'id'       => 'technology-2',
            'webTitle' => 'Tech News 2',
            'webUrl'   => 'https://example.com/tech2',
        ],
    ];

    // Mock the RssFeedContract
    $rssFeedMock = Mockery::mock(RssFeedContract::class);
    $rssFeedMock->shouldReceive('get')
        ->with('/sections', ['q' => $section])
        ->once()
        ->andReturn($mockFeedItems);

    // Bind the mock to the container
    app()->instance(RssFeedContract::class, $rssFeedMock);

    // Make the request
    $response = $this->get("/feeds/{$section}");

    // Assert response headers and status
    $response->assertStatus(200)
        ->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');

    // Assert response content contains expected RSS XML
    $responseContent = $response->getContent();

    // Assert the RSS structure
    expect($responseContent)
        ->toContain('<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">')
        ->toContain('<channel>')
        ->toContain('<title>The Guardian RSS Feed</title>')
        ->toContain('<link>' . route('rss-feed.show', $section) . '</link>')
        ->toContain('<description>Latest updates from The Guardian</description>')
        ->toContain('<language>en-us</language>');

    // Assert each feed item is present
    foreach ($mockFeedItems as $feedItem) {
        expect($responseContent)
            ->toContain('<item>')
            ->toContain('<title>' . $feedItem['webTitle'] . '</title>')
            ->toContain('<link>' . $feedItem['webUrl'] . '</link>')
            ->toContain('<description>Updates in ' . $feedItem['webTitle'] . '</description>')
            ->toContain('<guid isPermaLink="false">' . $feedItem['id'] . '</guid>');
    }
});
