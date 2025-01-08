<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GuardianApiException extends Exception
{
    private Response $response;

    /**
     * Create a new exception instance.
     */
    public function __construct(Response $response, int $code = 0, ?Exception $previous = null)
    {
        $this->response = $response;
        $message        = $response->json()['message'] ?? 'Guardian API Error';

        parent::__construct($message, $code, $previous);
    }

    /**
     * Report the exception.
     */
    public function report(): void
    {
        if ($this->response->movedPermanently() || $this->response->tooManyRequests()) {
            Log::critical('Guardian API Server Error', [
                'message'     => $this->getMessage(),
                'response'    => $this->response->json(),
                'status'      => $this->response->getStatusCode(),
                'stack_trace' => $this->getTraceAsString(),
            ]);
        } elseif ($this->response->clientError()) {
            Log::warning('Guardian API Client Error', [
                'message'     => $this->getMessage(),
                'response'    => $this->response->json(),
                'status'      => $this->response->getStatusCode(),
                'stack_trace' => $this->getTraceAsString(),
            ]);
        } elseif ($this->response->unauthorized()) {
            Log::alert('Unauthorized access attempt to Guardian API');
        } else {
            Log::error('Guardian API Error', [
                'message'     => $this->getMessage(),
                'response'    => $this->response->json(),
                'status'      => $this->response->getStatusCode(),
                'stack_trace' => $this->getTraceAsString(),
            ]);
        }
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => 'Internal server error!',
        ], 500);
    }
}
