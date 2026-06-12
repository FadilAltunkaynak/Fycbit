<?php

use App\Services\ParallelService\ParallelServiceContract;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

function responseJsonData(bool $success, string $message = '', array|object|null $data = []): JsonResponse
{
    $message = ! $success && empty($message) ? __('Something went wrong! Please try again later') : $message;

    return response()->json(['success' => $success, 'message' => $message, 'data' => $data]);
}

if (! function_exists('success')) {
    /**
     * Generate Success Response Array
     *
     * @return array{success:bool,message:string,data:mixed}
     */
    function success(mixed $messageOrData = null, mixed $data = [], array $topLevelData = []): array
    {
        if ($messageOrData === null) {
            $messageOrData = __('Success');
        }

        if (gettype($messageOrData) !== 'string') {
            $data = $messageOrData;
            $messageOrData = __('Success');
        }

        $response = [
            'success' => true,
            'message' => $messageOrData,
            'data' => $data,
        ];

        foreach ($topLevelData as $key => $value) {
            $response[$key] = $value;
        }

        return $response;
    }
}

if (! function_exists('failed')) {
    /**
     * Generate Failed Response Array
     *
     * @return array{success:bool,message:string,data:mixed}
     */
    function failed(mixed $messageOrData = null, mixed $data = [], array $topLevelData = []): array
    {
        if ($messageOrData === null) {
            $messageOrData = __('Failed');
        }

        if (gettype($messageOrData) !== 'string') {
            $data = $messageOrData;
            $messageOrData = __('Failed');
        }

        $response = success($messageOrData, $data, $topLevelData);
        $response['success'] = false;

        return $response;
    }
}

if (! function_exists('is_success')) {
    /**
     * Check Response Array
     *
     * @param  array{success:bool,message:string,data:mixed}  $response
     */
    function is_success(array $response): bool
    {
        return (bool) ($response['success'] ?? false);
    }
}

if (! function_exists('async')) {
    /**
     * Run a closure parallel process async
     */
    function async(Closure $closure): mixed
    {
        return app(ParallelServiceContract::class)->async($closure);
    }
}

if (! function_exists('fireAsync')) {
    /**
     * Run a closure parallel process and return async id
     */
    function fireAsync(Closure $closure): mixed
    {
        $parl = app(ParallelServiceContract::class);
        $parl->add('value', $closure);

        return $parl->fire();
    }
}

if (! function_exists('waitAsync')) {
    /**
     * Run a closure parallel process and return async id
     *
     * @param  Closure  $closure
     */
    function waitAsync(int|array $asyncId): array
    {
        if (is_array($asyncId)) {
            return $asyncId;
        }

        $parl = app(ParallelServiceContract::class);

        return $parl->wait($asyncId);
    }
}
if (! function_exists('adminGoogleAuthEnabled')) {
    /**
     * Check Admin Google Auth Is Enabled
     */
    function adminGoogleAuthEnabled(): bool
    {
        return Auth::user()?->g2f_enabled ?? false;
    }
}

if (! function_exists('authId')) {
    /**
     * Return Auth Id
     */
    function authId(): int
    {
        return Auth::id() ?? Auth::guard('api')->id() ?? 0;
    }
}

if (! function_exists('authUser')) {
    /**
     * Return Auth User|Null
     */
    function authUser(): ?User
    {
        return Auth::user() ?? Auth::guard('api')->user() ?? null;
    }
}
