<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\SlaSettingRepository;
use App\Http\Requests\SlaSettingRequest;
use App\Models\SlaSetting;

class SlaSettingController extends Controller
{
    private $slaSettingRepository;

    public function __construct(SlaSettingRepository $slaSettingRepository)
    {
        $this->slaSettingRepository = $slaSettingRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/sla-settings",
     *     tags={"SLA Settings"},
     *     summary="List SLA Settings",
     *     description="Fetch a list of SLA settings with optional search and pagination",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for filtering SLA settings",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="start_time", type="string"),
     *                     @OA\Property(property="end_time", type="string"),
     *                     @OA\Property(property="off_days", type="array",
     *                         @OA\Items(type="string")
     *                     ),
     *                     @OA\Property(property="holidays", type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="holiday_date", type="string"),
     *                             @OA\Property(property="description", type="string")
     *                         )
     *                     ),
     *                     @OA\Property(property="targets", type="object",
     *                         @OA\Property(
     *                             property="level",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="priority_type", type="string"),
     *                                 @OA\Property(property="response_time_value", type="integer"),
     *                                 @OA\Property(property="alert_before_response_value", type="integer"),
     *                                 @OA\Property(property="resolve_time_value", type="integer"),
     *                                 @OA\Property(property="alert_before_resolve_value", type="integer")
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function index(Request $request)
    {
        return $this->slaSettingRepository->list($request);
    }

    /**
     * @OA\Post(
     *     path="/api/sla-settings",
     *     tags={"SLA Settings"},
     *     summary="Create SLA Setting",
     *     description="Create a new SLA setting",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"sla"},
     *             @OA\Property(property="sla", type="object",
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="is_24_hours", type="boolean"),
     *                 @OA\Property(property="start_time", type="string"),
     *                 @OA\Property(property="end_time", type="string"),
     *                 @OA\Property(property="description", type="string")
     *             ),
     *             @OA\Property(property="off_days", type="array",
     *                 @OA\Items(type="string")
     *             ),
     *             @OA\Property(property="holidays", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="holiday_date", type="string"),
     *                     @OA\Property(property="description", type="string")
     *                 )
     *             ),
     *             @OA\Property(property="targets", type="object",
     *                 @OA\Property(
     *                     property="level",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="priority_type", type="integer"),
     *                         @OA\Property(property="response_time_value", type="integer"),
     *                         @OA\Property(property="alert_before_response_value", type="integer"),
     *                         @OA\Property(property="resolve_time_value", type="integer"),
     *                         @OA\Property(property="alert_before_resolve_value", type="integer")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="SLA setting created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function store(SlaSettingRequest $request)
    {
        return $this->slaSettingRepository->store($request);
    }

    /**
     * @OA\Get(
     *     path="/api/sla-settings/{id}",
     *     tags={"SLA Settings"},
     *     summary="Get SLA Setting by ID",
     *     description="Retrieve an SLA setting by its ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the SLA setting",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="SLA Setting not found"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function show(SlaSetting $slaSetting)
    {
        return $this->slaSettingRepository->show($slaSetting);
    }

    /**
     * @OA\Put(
     *     path="/api/sla-settings/{id}",
     *     tags={"SLA Settings"},
     *     summary="Update SLA Setting",
     *     description="Update an existing SLA setting",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the SLA setting",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="sla", type="object",
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="is_24_hours", type="boolean"),
     *                 @OA\Property(property="start_time", type="string"),
     *                 @OA\Property(property="end_time", type="string"),
     *                 @OA\Property(property="description", type="string")
     *             ),
     *             @OA\Property(property="off_days", type="array",
     *                 @OA\Items(type="string")
     *             ),
     *             @OA\Property(property="holidays", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="holiday_date", type="string"),
     *                     @OA\Property(property="description", type="string")
     *                 )
     *             ),
     *             @OA\Property(property="targets", type="object",
     *                 @OA\Property(
     *                     property="level",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="priority_type", type="integer"),
     *                         @OA\Property(property="response_time_value", type="integer"),
     *                         @OA\Property(property="alert_before_response_value", type="integer"),
     *                         @OA\Property(property="resolve_time_value", type="integer"),
     *                         @OA\Property(property="alert_before_resolve_value", type="integer")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SLA setting updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="SLA Setting not found"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function update(SlaSettingRequest $request, SlaSetting $slaSetting)
    {
        return $this->slaSettingRepository->update($request, $slaSetting);
    }

    /**
     * @OA\Delete(
     *     path="/api/sla-settings/{id}",
     *     tags={"SLA Settings"},
     *     summary="Delete SLA Setting",
     *     description="Delete an SLA setting by its ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the SLA setting to be deleted",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SLA setting deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="message", type="string", example="SLA setting deleted successfully")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="SLA Setting not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="SLA Setting not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred.")
     *         )
     *     )
     * )
     */
    public function destroy(SlaSetting $slaSetting)
    {
        return $this->slaSettingRepository->delete($slaSetting);
    }
}
