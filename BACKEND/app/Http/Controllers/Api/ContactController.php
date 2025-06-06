<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use Illuminate\Http\Request;
use App\Models\Contact;
use App\Repositories\ContactRepository;

class ContactController extends Controller
{
    private $contactRepository;

    public function __construct(ContactRepository $contactRepository)
    {
        $this->contactRepository = $contactRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/contacts",
     *     summary="Get list of contacts",
     *     description="Retrieve a paginated list of contacts, with optional search and filter parameters.",
     *     tags={"Contacts"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for filtering contacts by first name, last name, email, mobile, etc.",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="product_id",
     *         in="query",
     *         description="Filter by product ID.",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="account_id",
     *         in="query",
     *         description="Filter by account ID.",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="owner_id",
     *         in="query",
     *         description="Filter by owner ID.",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="contact_number",
     *         in="query",
     *         description="Filter by Contact Number.",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination.",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of results per page.",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of contacts successfully retrieved",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        return $this->contactRepository->list($request);
    }

    /**
     * @OA\Post(
     *     path="/api/contacts",
     *     summary="Create a new contact",
     *     description="Create a new contact with the provided data.",
     *     tags={"Contacts"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id", "account_id", "first_name", "last_name", "mobile", "gender"},
     *             @OA\Property(property="product_id", type="string"),
     *             @OA\Property(property="account_id", type="string"),
     *             @OA\Property(property="name_prefix", type="string", nullable=true),
     *             @OA\Property(property="first_name", type="string"),
     *             @OA\Property(property="last_name", type="string"),
     *             @OA\Property(property="email", type="string", nullable=true),
     *             @OA\Property(property="mobile", type="integer"),
     *             @OA\Property(property="owner_id", type="string", nullable=true),
     *             @OA\Property(property="country_id", type="string", nullable=true),
     *             @OA\Property(property="state_id", type="string", nullable=true),
     *             @OA\Property(property="city_id", type="string", nullable=true),
     *             @OA\Property(property="pincode", type="string", nullable=true),
     *             @OA\Property(property="address", type="string", nullable=true),
     *             @OA\Property(property="gender", type="string"),
     *             @OA\Property(property="description", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Contact created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Contact created successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(ContactRequest $request)
    {
        return $this->contactRepository->store($request);
    }

    /**
     * @OA\Get(
     *     path="/api/contacts/{id}",
     *     summary="Get a specific contact",
     *     description="Retrieve details of a single contact by ID.",
     *     tags={"Contacts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the contact to retrieve",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contact details successfully retrieved"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Contact not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Contact not found")
     *         )
     *     )
     * )
     */
    public function show(Contact $contact)
    {
        return $this->contactRepository->show($contact);
    }

    /**
     * @OA\Put(
     *     path="/api/contacts/{id}",
     *     summary="Update an existing contact",
     *     description="Update the details of an existing contact by ID.",
     *     tags={"Contacts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the contact to update",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id", "account_id", "first_name", "last_name", "mobile", "gender"},
     *             @OA\Property(property="product_id", type="string"),
     *             @OA\Property(property="account_id", type="string"),
     *             @OA\Property(property="name_prefix", type="string", nullable=true),
     *             @OA\Property(property="first_name", type="string"),
     *             @OA\Property(property="last_name", type="string"),
     *             @OA\Property(property="email", type="string", nullable=true),
     *             @OA\Property(property="mobile", type="integer"),
     *             @OA\Property(property="owner_id", type="string", nullable=true),
     *             @OA\Property(property="country_id", type="string", nullable=true),
     *             @OA\Property(property="state_id", type="string", nullable=true),
     *             @OA\Property(property="city_id", type="string", nullable=true),
     *             @OA\Property(property="pincode", type="string", nullable=true),
     *             @OA\Property(property="address", type="string", nullable=true),
     *             @OA\Property(property="gender", type="string"),
     *             @OA\Property(property="description", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contact updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Contact updated successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Contact not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Contact not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function update(ContactRequest $request, string $id)
    {
        return $this->contactRepository->update($request, $id);
    }

    /**
     * @OA\Delete(
     *     path="/api/contacts/{id}",
     *     summary="Delete a contact",
     *     description="Delete a contact by ID.",
     *     tags={"Contacts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the contact to delete",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contact successfully deleted",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Contact deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Contact not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Contact not found")
     *         )
     *     )
     * )
     */
    public function destroy(string $id)
    {
        return $this->contactRepository->delete($id);
    }
}
