<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\DynamicListFieldRequest;
use App\Repositories\DynamicListFieldsRepository;
use App\Http\Resources\DynamicListFieldResource;
use App\Models\DynamicListField;
use Exception;
use App\Http\Response\CustomApiResponse;

/**
 * @OA\Tag(
 *     name="Dynamic List Fields",
 *     description="Manage dynamic list fields for custom lead forms"
 * )
 */
class DynamicListFieldController extends Controller
{
    protected $dynamicListFieldRepository;
    private $apiResponse;

    public function __construct(DynamicListFieldsRepository $dynamicListFieldRepository, CustomApiResponse $customApiResponse)
    {
        $this->dynamicListFieldRepository = $dynamicListFieldRepository;
        $this->apiResponse = $customApiResponse;
    }

    /**
     * @OA\Get(
     *     path="/api/dynamic-list-fields",
     *     tags={"Dynamic List Fields"},
     *     security={{"bearerAuth": {}}},
     *     summary="List dynamic list fields",
     *     description="Returns a list of dynamic fields based on filters",
     *     @OA\Parameter(name="search", in="query", description="Search keyword", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="list_id", in="query", description="List ID", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="field_type", in="query", description="Field type", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", description="Status (active/inactive)", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", description="Items per page", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Successful fetch"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function index(Request $request)
    {
        return $this->dynamicListFieldRepository->list($request);
    }

    /**
     * @OA\Post(
     *     path="/api/dynamic-list-fields",
     *     tags={"Dynamic List Fields"},
     *     security={{"bearerAuth": {}}},
     *     summary="Create dynamic list fields",
     *     description="Creates fields and adds schema to respective table",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="list_id", type="inteer"),
     *             @OA\Property(property="form_items", type="object",
     *                 @OA\Property(property="A", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="B", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="C", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="D", type="array", @OA\Items(type="object")),
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Created successfully"),
     *     @OA\Response(response=422, description="Validation failed"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function store(DynamicListFieldRequest $request)
    {
        return $this->dynamicListFieldRepository->create($request);
    }

    /**
     * @OA\Get(
     *     path="/api/dynamic-list-fields/{id}",
     *     tags={"Dynamic List Fields"},
     *     security={{"bearerAuth": {}}},
     *     summary="Get a dynamic list field by ID",
     *     @OA\Parameter(name="id", in="path", required=true, description="Field ID", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Field found"),
     *     @OA\Response(response=404, description="Field not found"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function show($ecdialListField)
    {
        try {
            $listField = DynamicListField::findOrFail($ecdialListField);

            if (config('omni.FIELD_API_ENABLED')) {
                $listField->load(['apiConfig', 'dependencyMappings' => function ($q) use ($listField) {
                    $q->where('ecdial_list_field_dependencies.list_id', $listField->list_id);
                }]);
            }

            return new DynamicListFieldResource($listField);
        } catch (Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/dynamic-list-fields/{id}",
     *     tags={"Dynamic List Fields"},
     *     security={{"bearerAuth": {}}},
     *     summary="Update a dynamic list field",
     *     @OA\Parameter(name="id", in="path", required=true, description="Field ID", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *               @OA\Property(property="field_name", type="string"),
     *               @OA\Property(property="field_label", type="string"),
     *               @OA\Property(property="field_type", type="string", enum={"text", "select", "multi", "checkbox", "radio", "textarea", "date", "number"}),
     *               @OA\Property(property="description", type="string"),
     *               @OA\Property(property="options", type="string"),
     *               @OA\Property(property="mandatory", type="string", enum={"Y", "N"}),
     *               @OA\Property(property="calling_authority", type="string", enum={"Y", "N"}),
     *               @OA\Property(property="show_authority", type="string", enum={"Y", "N"}),
     *               @OA\Property(property="agent_view", type="string", enum={"Y", "N"}),
     *               @OA\Property(property="field_order", type="integer"),
     *               @OA\Property(property="field_help", type="string"),
     *               @OA\Property(property="field_size", type="integer"),
     *               @OA\Property(property="field_max_length", type="integer"),
     *               @OA\Property(property="search_filter", type="string", enum={"Y", "N"}),
     *               @OA\Property(property="editable", type="string", enum={"Y", "N"}),
     *               @OA\Property(property="default_value", type="string"),
     *               @OA\Property(property="value_masked", type="string", enum={"Y", "N"}),
     *               @OA\Property(property="duplicate_check", type="string", enum={"Y", "N"}),
     *               @OA\Property(property="status", type="string", enum={"active", "inactive"})
     *           )
     *     ),
     *     @OA\Response(response=200, description="Updated successfully"),
     *     @OA\Response(response=404, description="Field not found"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    // public function update(DynamicListFieldRequest $request, $id)
    // {
    //     return $this->dynamicListFieldRepository->update($request, $id);
    // }
    public function update(Request $request, $id)
    {
        return $this->dynamicListFieldRepository->update($request, $id);
    }

    /**
     * @OA\Delete(
     *     path="/api/dynamic-list-fields/{id}",
     *     tags={"Dynamic List Fields"},
     *     security={{"bearerAuth": {}}},
     *     summary="Delete a dynamic list field",
     *     @OA\Parameter(name="id", in="path", required=true, description="Field ID", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Deleted successfully"),
     *     @OA\Response(response=404, description="Field not found"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function destroy($id)
    {
        return $this->dynamicListFieldRepository->delete($id);
    }

    /**
     * Trigger the API configured for the given dynamic field and return the options response.
     *
     * Pass dependency_values as a key-value map for multi-parameter dependencies.
     */
    public function fetchOptions(Request $request, $id)
    {
        return $this->dynamicListFieldRepository->fetchOptions((int) $id, $request);
    }
}
