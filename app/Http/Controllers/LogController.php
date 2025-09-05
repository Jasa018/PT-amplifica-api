<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Logs",
 *     description="API Endpoints for Application Logs"
 * )
 */
class LogController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/logs",
     *     summary="Get application logs",
     *     tags={"Logs"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="level",
     *         in="query",
     *         description="Filter logs by level (info, warning, error)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="store_id",
     *         in="query",
     *         description="Filter logs by store ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search logs by message or context",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Log")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $logs = Log::query();

        if ($request->filled('level')) {
            $logs->where('level', $request->input('level'));
        }

        if ($request->filled('store_id')) {
            $logs->where('store_id', $request->input('store_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $logs->where(function ($query) use ($search) {
                $query->where('message', 'like', '%' . $search . '%')
                      ->orWhere('context', 'like', '%' . $search . '%');
            });
        }

        return $logs->latest()->paginate(20);
    }
}
