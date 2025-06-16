<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\RecordingFileRepository;
use Illuminate\Http\Request;

class RecordingFileController extends Controller
{
    protected $recordingRepo;

    public function __construct(RecordingFileRepository $recordingRepo)
    {
        $this->recordingRepo = $recordingRepo;
    }


    /**
     * @OA\Get(
     *     path="/api/recording-file",
     *     operationId="getRecordingFile",
     *     tags={"RecordingsFile"},
     *     summary="Fetch recording file by unique ID",
     *     description="Returns the file name and URL of the matching recording based on unique_id in the filename.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="unique_id",
     *         in="query",
     *         required=true,
     *         description="Unique ID that appears in the filename",
     *         @OA\Schema(type="string", example="CI1722255087397")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Recording file found",
     *         @OA\JsonContent(
     *             @OA\Property(property="filename", type="string", example="2024_07_29_17_41_31_9725958612_8001_CI1722255087397.wav"),
     *             @OA\Property(property="url", type="string", example="https://your-domain.com/storage/recordings/recording-file.wav")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Missing unique_id parameter",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="unique_id is required")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="File not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="File not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Recording directory not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Recording directory not found")
     *         )
     *     )
     * )
     */

    public function getRecordingFile(Request $request)
    {
        return $this->recordingRepo->findRecordingFile($request);
    }
}
