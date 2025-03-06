<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PhoneRequest;
use Illuminate\Http\Request;
use App\Repositories\PhoneRepository;

class PhoneController extends Controller
{

    private $phoneRepository;

    public function __construct(PhoneRepository $phoneRepository)
    {
        $this->phoneRepository = $phoneRepository;
    }
    /**
     * @OA\Get(
     *     path="/phones",
     *     summary="Get list of phones",
     *     description="Retrieve a list of phones with search and pagination support.",
     *     operationId="indexPhones",
     *     tags={"Phones"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term to filter by extension, login, or status",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="extension",
     *         in="query",
     *         description="Filter by phone extension",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="login",
     *         in="query",
     *         description="Filter by phone login",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination, use 'all' to fetch all records",
     *         required=false,
     *         @OA\Schema(type="string", example="1")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of phones",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="data")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid parameters",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Invalid parameters")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
       return $this->phoneRepository->list($request);
    }

      /**
     * @OA\Post(
     *     path="/phones",
     *     summary="Create a new phone",
     *     description="Create a new phone record.",
     *     operationId="storePhone",
     *     tags={"Phones"},
     *     @OA\RequestBody(
     *         required=true
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Phone created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Phone created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input data",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Invalid input data")
     *         )
     *     )
     * )
     */
    public function store(PhoneRequest $request)
    {
        $validatedData = $request->validated();
        return $this->phoneRepository->store($validatedData);
    }


    /**
     * @OA\Get(
     *     path="/phones/{id}",
     *     summary="Get a specific phone",
     *     description="Retrieve details of a phone by its ID.",
     *     operationId="showPhone",
     *     tags={"Phones"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the phone to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Phone details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Phone not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Phone not found")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        return $this->phoneRepository->show($id);
    }

    /**
     * @OA\Put(
     *     path="/phones/{extension}",
     *     summary="Update a phone",
     *     description="Update the details of an existing phone.",
     *     operationId="updatePhone",
     *     tags={"Phones"},
     *     @OA\Parameter(
     *         name="extension",
     *         in="path",
     *         description="Extension of the phone to update",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Phone updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Phone updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Phone not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Phone not found")
     *         )
     *     )
     * )
     */
    public function update(PhoneRequest $request, $extension)
    {
        $validatedData = $request->validated();
        return $this->phoneRepository->update($validatedData,$extension);
    }


     /**
     * @OA\Delete(
     *     path="/phones/{extension}",
     *     summary="Delete a phone",
     *     description="Delete a phone record by its extension.",
     *     operationId="destroyPhone",
     *     tags={"Phones"},
     *     @OA\Parameter(
     *         name="extension",
     *         in="path",
     *         description="Extension of the phone to delete",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Phone deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Phone deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Phone not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Phone not found")
     *         )
     *     )
     * )
     */
    public function destroy($extension)
    {
        return $this->phoneRepository->delete($extension);
    }
}
