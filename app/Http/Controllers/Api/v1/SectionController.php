<?php

namespace App\Http\Controllers\Api\v1;

use App\Contracts\RssFeedContract;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SectionController extends Controller
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
        return $this->guardian->get('/sections', ['q' => $section]);
    }
}
