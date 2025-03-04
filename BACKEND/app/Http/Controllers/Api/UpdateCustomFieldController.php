<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\UpdateCustomFieldRepository;
use Illuminate\Http\Request;


class UpdateCustomFieldController extends Controller
{

    private $updateCustomFieldRepository;


    public function __construct(UpdateCustomFieldRepository $updateCustomFieldRepository)
    {
        $this->updateCustomFieldRepository = $updateCustomFieldRepository;
    }

    /**
     * @OA\Put(
     *     path="/api/update-custom-field",
     *     summary="Update or Insert Custom Field Record",
     *     description="This endpoint either updates an existing record or inserts a new one based on the provided data.",
     *     tags={"Custom Fields"},
     *     @OA\Parameter(
     *         name="list_id",
     *         in="query",
     *         required=true,
     *         description="The list ID for the custom field record",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="uniqueid",
     *         in="query",
     *         required=true,
     *         description="The unique identifier for the record to update or insert.",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Custom query data to update or insert into the table",
     *         @OA\JsonContent(
     *             type="object",
     *             additionalProperties=true,
     *             example={"column1": "value1", "column2": "value2"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Record has been modified or inserted successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Record has been modified successfully."
     *             ),
     *             @OA\Property(
     *                 property="affected_rows",
     *                 type="integer",
     *                 example=1
     *             ),
     *             @OA\Property(
     *                 property="inserted_id",
     *                 type="integer",
     *                 example=123
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request. The custom_query should be an associative array.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="The custom_query should be an associative array."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error. Error inserting the record.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Error inserting the record."
     *             ),
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Database error or another error message."
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        return $this->updateCustomFieldRepository->index($request);
    }
}
