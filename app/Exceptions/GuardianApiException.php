<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class GuardianApiException extends Exception
{
    /**
     * The response instance.
     */
    protected ?Response $response;

    /**
     * The redirect path for redirect responses.
     */
    protected ?string $redirectTo = null;

    /**
     * Create a new exception instance.
     */
    public function __construct(string|Response $messageOrResponse, int $code = 0, ?Throwable $previous = null)
    {
        if ($messageOrResponse instanceof Response) {
            $this->response = $messageOrResponse;
            $message        = $messageOrResponse->json()['message'] ?? 'Guardian API Error';
        } else {
            $this->response = null;
            $message        = $messageOrResponse;
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the response instance.
     */
    public function getResponse(): ?Response
    {
        return $this->response;
    }

    /**
     * Get the redirect path for redirect responses.
     */
    public function withRedirectTo(string $redirectTo): void
    {
        $this->redirectTo = $redirectTo;
    }

    /**
     * Get response data as array.
     *
     * @return array<string, mixed>
     */
    protected function getResponseData(): array
    {
        if (! $this->response) {
            return [];
        }

        return $this->response->json();
    }

    /**
     * Get status code from response.
     */
    protected function getStatusCode(): int
    {
        if (! $this->response) {
            return 500;
        }

        return $this->response->getStatusCode();
    }

    /**
     * Report the exception.
     */
    public function report(): void
    {
        $context = [
            'message'     => $this->getMessage(),
            'stack_trace' => $this->getTraceAsString(),
            'status'      => $this->getStatusCode(),
            'response'    => $this->getResponseData(),
        ];

        if (! $this->response) {
            Log::error('Guardian API Error', $context);

            return;
        }

        $context = [
            'message'     => $this->getMessage(),
            'response'    => $this->response->json(),
            'status'      => $this->response->status(),
            'stack_trace' => $this->getTraceAsString(),
        ];

        match (true) {
            $this->response->movedPermanently(),
            $this->response->tooManyRequests() => Log::critical('Guardian API Server Error', $context),

            $this->response->unauthorized() => Log::alert('Unauthorized access attempt to Guardian API', $context),

            $this->response->clientError() => Log::warning('Guardian API Client Error', $context),

            default => Log::error('Guardian API Error', $context)
        };
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render(Request $request): Response|JsonResponse|RedirectResponse
    {
        $data = [
            'message' => 'Internal Server Error!',
            'status'  => 500,
            'errors'  => $this->context['errors'] ?? null,
        ];

        // Handle redirect if specified
        if ($this->redirectTo) {
            return redirect($this->redirectTo)
                ->withInput()
                ->with('error', $this->getMessage());
        }

        // Handle API requests
        if ($request->expectsJson()) {
            return response()->json($data, 500);
        }

        // Handle other requests
        abort($data['status'], $data['message']);
    }
}
