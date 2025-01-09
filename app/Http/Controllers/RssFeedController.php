<?php

namespace App\Http\Controllers;

use App\Contracts\RssFeedContract;
use Illuminate\Http\Request;

class RssFeedController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private readonly RssFeedContract $guardian)
    {
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $section)
    {
        $feedItems = $this->guardian->get('/sections', ['q' => $section]);

        return response()
            ->view('rss-feeds', compact('feedItems'))
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
