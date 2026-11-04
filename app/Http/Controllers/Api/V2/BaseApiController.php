<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class BaseApiController extends Controller
{
    /**
     * Return standardized success JSON response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @param array $meta
     * @return JsonResponse
     */
    public function sendResponse($data, string $message = 'Success', int $code = 200, array $meta = []): JsonResponse
    {
        $response = [
            'status' => true,
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ];

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $code);
    }

    /**
     * Return standardized paginated JSON response with resources.
     *
     * @param LengthAwarePaginator $paginator
     * @param string $resourceClass
     * @param string $message
     * @return JsonResponse
     */
    public function sendPaginated(LengthAwarePaginator $paginator, string $resourceClass, string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => $message,
            'data' => $resourceClass::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'has_more' => $paginator->hasMorePages(),
            ],
        ], 200);
    }

    /**
     * Return standardized error JSON response.
     *
     * @param string $error
     * @param array $messages
     * @param int $code
     * @return JsonResponse
     */
    public function sendError(string $error, array $messages = [], int $code = 400): JsonResponse
    {
        $response = [
            'status' => false,
            'code' => $code,
            'message' => $error,
        ];

        if (!empty($messages)) {
            $response['errors'] = $messages;
        }

        return response()->json($response, $code);
    }
}

