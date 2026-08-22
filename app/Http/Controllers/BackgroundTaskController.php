<?php

namespace App\Http\Controllers;

use App\Services\BackgroundTaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BackgroundTaskController extends Controller
{
    public function __construct(
        protected BackgroundTaskService $backgroundTasks,
    ) {}

    /**
     * Poll task status (JSON). Same-origin; requires authentication.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $data = $this->backgroundTasks->getForUser($id, (int) $request->user()->id);

        if ($data === null) {
            return response()->json([
                'error' => 'Task not found or access denied.',
            ], 404);
        }

        return response()->json($data);
    }
}
