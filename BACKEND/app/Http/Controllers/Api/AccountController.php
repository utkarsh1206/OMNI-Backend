<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountRequest;
use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\AccountResource;
use App\Repositories\AccountRepository;

class AccountController extends Controller
{


  private $accountRepository;
  public function __construct(AccountRepository $accountRepository)
  {
      $this->accountRepository = $accountRepository;
  }


  /**
 * @OA\Get(
 *     path="/accounts",
 *     summary="List Accounts",
 *     description="Fetch a list of accounts with optional filters like search, product_id, campaign_id, and owner_id.",
 *     tags={"Accounts"},
 *     @OA\Parameter(
 *         name="per_page",
 *         in="query",
 *         description="The number of items per page",
 *         required=false,
 *         @OA\Schema(
 *             type="integer",
 *             default=10
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         description="The page number, use 'all' to fetch all records",
 *         required=false,
 *         @OA\Schema(
 *             type="string",
 *             enum={"all", "1", "2", "3"},
 *             default="1"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="search",
 *         in="query",
 *         description="Search term to filter accounts by name, mobile, or owner username",
 *         required=false,
 *         @OA\Schema(
 *             type="string"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="product_id",
 *         in="query",
 *         description="Filter by product ID",
 *         required=false,
 *         @OA\Schema(
 *             type="string"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="campaign_id",
 *         in="query",
 *         description="Filter by campaign ID",
 *         required=false,
 *         @OA\Schema(
 *             type="string"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="owner_id",
 *         in="query",
 *         description="Filter by owner ID",
 *         required=false,
 *         @OA\Schema(
 *             type="string"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successfully fetched accounts",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="status",
 *                 type="string",
 *                 example="success"
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Data fetched successfully"
 *             ),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer"),
 *                     @OA\Property(property="name", type="string"),
 *                     @OA\Property(property="mobile", type="string"),
 *                     @OA\Property(property="product_id", type="string"),
 *                     @OA\Property(property="campaign_id", type="string"),
 *                     @OA\Property(property="owner_id", type="integer")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad Request",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="status",
 *                 type="string",
 *                 example="error"
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Invalid request"
 *             )
 *         )
 *     )
 * )
 */
  public function index(Request $request)
  {
    return $this->accountRepository->list($request);
  }


  /**
 * @OA\Post(
 *     path="/accounts",
 *     summary="Create a new account",
 *     description="Creates a new account after validating that the account with the same product_id and name does not exist.",
 *     tags={"Accounts"},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Account data to be created",
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 type="object",
 *                 required={"product_id", "name", "owner_id"},
 *                 @OA\Property(
 *                     property="product_id",
 *                     type="string",
 *                     description="The product ID associated with the account"
 *                 ),
 *                 @OA\Property(
 *                     property="campaign_id",
 *                     type="string",
 *                     description="The campaign ID associated with the account"
 *                 ),
 *                 @OA\Property(
 *                     property="name",
 *                     type="string",
 *                     description="The name of the account"
 *                 ),
 *                 @OA\Property(
 *                     property="mobile",
 *                     type="string",
 *                     description="The mobile number of the account"
 *                 ),
 *                 @OA\Property(
 *                     property="website",
 *                     type="string",
 *                     description="The website URL of the account"
 *                 ),
 *                 @OA\Property(
 *                     property="industry_id",
 *                     type="string",
 *                     description="Industry ID of the account"
 *                 ),
 *                 @OA\Property(
 *                     property="business_type_id",
 *                     type="string",
 *                     description="Business type ID of the account"
 *                 ),
 *                 @OA\Property(
 *                     property="tax_number",
 *                     type="string",
 *                     description="The tax number associated with the account"
 *                 ),
 *                 @OA\Property(
 *                     property="country_id",
 *                     type="string",
 *                     description="Country ID of the account"
 *                 ),
 *                 @OA\Property(
 *                     property="state_id",
 *                     type="string",
 *                     description="State ID of the account"
 *                 ),
 *                 @OA\Property(
 *                     property="city_id",
 *                     type="string",
 *                     description="City ID of the account"
 *                 ),
 *                 @OA\Property(
 *                     property="pincode",
 *                     type="string",
 *                     description="Pincode of the account"
 *                 ),
 *                 @OA\Property(
 *                     property="address",
 *                     type="string",
 *                     description="The address of the account"
 *                 ),
 *                 @OA\Property(
 *                     property="owner_id",
 *                     type="string",
 *                     description="Owner ID of the account"
 *                 ),
 *                 @OA\Property(
 *                     property="description",
 *                     type="string",
 *                     description="Description of the account"
 *                 ),
 *                 @OA\Property(
 *                     property="company_name",
 *                     type="string",
 *                     description="The company name associated with the account"
 *                 ),
 *                 @OA\Property(
 *                     property="contact_person_first_name",
 *                     type="string",
 *                     description="First name of the contact person"
 *                 ),
 *                 @OA\Property(
 *                     property="contact_person_last_name",
 *                     type="string",
 *                     description="Last name of the contact person"
 *                 ),
 *                 @OA\Property(
 *                     property="contact_no",
 *                     type="string",
 *                     description="Contact number of the contact person"
 *                 ),
 *                 @OA\Property(
 *                     property="landline_no",
 *                     type="string",
 *                     description="Landline number of the account"
 *                 ),
 *                 @OA\Property(
 *                     property="company_email",
 *                     type="string",
 *                     description="The email address of the company"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Account successfully created",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="status",
 *                 type="string",
 *                 example="success"
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Account successfully created"
 *             ),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer"),
 *                 @OA\Property(property="product_id", type="string"),
 *                 @OA\Property(property="name", type="string"),
 *                 @OA\Property(property="mobile", type="string"),
 *                 @OA\Property(property="website", type="string"),
 *                 @OA\Property(property="industry_id", type="string"),
 *                 @OA\Property(property="business_type_id", type="string"),
 *                 @OA\Property(property="tax_number", type="string"),
 *                 @OA\Property(property="country_id", type="string"),
 *                 @OA\Property(property="state_id", type="string"),
 *                 @OA\Property(property="city_id", type="string"),
 *                 @OA\Property(property="pincode", type="string"),
 *                 @OA\Property(property="address", type="string"),
 *                 @OA\Property(property="owner_id", type="string"),
 *                 @OA\Property(property="description", type="string"),
 *                 @OA\Property(property="company_name", type="string"),
 *                 @OA\Property(property="contact_person_first_name", type="string"),
 *                 @OA\Property(property="contact_person_last_name", type="string"),
 *                 @OA\Property(property="contact_no", type="string"),
 *                 @OA\Property(property="landline_no", type="string"),
 *                 @OA\Property(property="company_email", type="string")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Unprocessable Entity - Account with the same product_id and name already exists",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="The account with the same product_id and name already exists."
 *             )
 *         )
 *     )
 * )
 */
  public function store(AccountRequest $request)
  {
    return $this->accountRepository->store($request);
  }


  /**
 * @OA\Get(
 *     path="/accounts/{account}",
 *     summary="Get Account by ID",
 *     description="Fetches the details of a specific account by its ID.",
 *     tags={"Accounts"},
 *     @OA\Parameter(
 *         name="account",
 *         in="path",
 *         description="The ID of the account to fetch",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             example=1
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successfully fetched the account details",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="status",
 *                 type="string",
 *                 example="success"
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Data fetched successfully"
 *             ),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer"),
 *                 @OA\Property(property="product_id", type="string"),
 *                 @OA\Property(property="name", type="string"),
 *                 @OA\Property(property="mobile", type="string"),
 *                 @OA\Property(property="website", type="string"),
 *                 @OA\Property(property="industry_id", type="string"),
 *                 @OA\Property(property="business_type_id", type="string"),
 *                 @OA\Property(property="tax_number", type="string"),
 *                 @OA\Property(property="country_id", type="string"),
 *                 @OA\Property(property="state_id", type="string"),
 *                 @OA\Property(property="city_id", type="string"),
 *                 @OA\Property(property="pincode", type="string"),
 *                 @OA\Property(property="address", type="string"),
 *                 @OA\Property(property="owner_id", type="string"),
 *                 @OA\Property(property="description", type="string"),
 *                 @OA\Property(property="company_name", type="string"),
 *                 @OA\Property(property="contact_person_first_name", type="string"),
 *                 @OA\Property(property="contact_person_last_name", type="string"),
 *                 @OA\Property(property="contact_no", type="string"),
 *                 @OA\Property(property="landline_no", type="string"),
 *                 @OA\Property(property="company_email", type="string")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Account not found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Account not found"
 *             )
 *         )
 *     )
 * )
 */
  public function show(Account $account)
  {
    return $this->accountRepository->show($account);
  }


  /**
 * @OA\Put(
 *     path="/accounts/{id}",
 *     summary="Update an existing account",
 *     description="Updates an account's details by its ID. The request will check if another account with the same product_id and name exists, excluding the current account.",
 *     tags={"Accounts"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="The ID of the account to update",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             example=1
 *         )
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         description="Account data to update",
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 type="object",
 *                 required={"product_id", "name", "owner_id"},
 *                 @OA\Property(
 *                     property="product_id",
 *                     type="string",
 *                     description="The product ID associated with the account"
 *                 ),
 *                 @OA\Property(
 *                     property="campaign_id",
 *                     type="string",
 *                     description="The campaign ID associated with the account"
 *                 ),
 *                 @OA\Property(
 *                     property="name",
 *                     type="string",
 *                     description="The name of the account"
 *                 ),
 *                 @OA\Property(
 *                     property="mobile",
 *                     type="string",
 *                     description="The mobile number of the account"
 *                 ),
 *                 @OA\Property(
 *                     property="website",
 *                     type="string",
 *                     description="The website URL of the account"
 *                 ),
 *                 @OA\Property(
 *                     property="industry_id",
 *                     type="string",
 *                     description="Industry ID of the account"
 *                 ),
 *                 @OA\Property(
 *                     property="business_type_id",
 *                     type="string",
 *                     description="Business type ID of the account"
 *                 ),
 *                 @OA\Property(
 *                     property="tax_number",
 *                     type="string",
 *                     description="The tax number associated with the account"
 *                 ),
 *                 @OA\Property(
 *                     property="country_id",
 *                     type="string",
 *                     description="Country ID of the account"
 *                 ),
 *                 @OA\Property(
 *                     property="state_id",
 *                     type="string",
 *                     description="State ID of the account"
 *                 ),
 *                 @OA\Property(
 *                     property="city_id",
 *                     type="string",
 *                     description="City ID of the account"
 *                 ),
 *                 @OA\Property(
 *                     property="pincode",
 *                     type="string",
 *                     description="Pincode of the account"
 *                 ),
 *                 @OA\Property(
 *                     property="address",
 *                     type="string",
 *                     description="The address of the account"
 *                 ),
 *                 @OA\Property(
 *                     property="owner_id",
 *                     type="string",
 *                     description="Owner ID of the account"
 *                 ),
 *                 @OA\Property(
 *                     property="description",
 *                     type="string",
 *                     description="Description of the account"
 *                 ),
 *                 @OA\Property(
 *                     property="company_name",
 *                     type="string",
 *                     description="The company name associated with the account"
 *                 ),
 *                 @OA\Property(
 *                     property="contact_person_first_name",
 *                     type="string",
 *                     description="First name of the contact person"
 *                 ),
 *                 @OA\Property(
 *                     property="contact_person_last_name",
 *                     type="string",
 *                     description="Last name of the contact person"
 *                 ),
 *                 @OA\Property(
 *                     property="contact_no",
 *                     type="string",
 *                     description="Contact number of the contact person"
 *                 ),
 *                 @OA\Property(
 *                     property="landline_no",
 *                     type="string",
 *                     description="Landline number of the account"
 *                 ),
 *                 @OA\Property(
 *                     property="company_email",
 *                     type="string",
 *                     description="The email address of the company"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successfully updated the account",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="status",
 *                 type="string",
 *                 example="success"
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Account successfully updated"
 *             ),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer"),
 *                 @OA\Property(property="product_id", type="string"),
 *                 @OA\Property(property="name", type="string"),
 *                 @OA\Property(property="mobile", type="string"),
 *                 @OA\Property(property="website", type="string"),
 *                 @OA\Property(property="industry_id", type="string"),
 *                 @OA\Property(property="business_type_id", type="string"),
 *                 @OA\Property(property="tax_number", type="string"),
 *                 @OA\Property(property="country_id", type="string"),
 *                 @OA\Property(property="state_id", type="string"),
 *                 @OA\Property(property="city_id", type="string"),
 *                 @OA\Property(property="pincode", type="string"),
 *                 @OA\Property(property="address", type="string"),
 *                 @OA\Property(property="owner_id", type="string"),
 *                 @OA\Property(property="description", type="string"),
 *                 @OA\Property(property="company_name", type="string"),
 *                 @OA\Property(property="contact_person_first_name", type="string"),
 *                 @OA\Property(property="contact_person_last_name", type="string"),
 *                 @OA\Property(property="contact_no", type="string"),
 *                 @OA\Property(property="landline_no", type="string"),
 *                 @OA\Property(property="company_email", type="string")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Unprocessable Entity - Account with the same product_id and name already exists",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="The account with the same product_id and name already exists."
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Account not found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Account not found"
 *             )
 *         )
 *     )
 * )
 */
  public function update(AccountRequest $request, string $id)
  {
    return $this->accountRepository->update($request,$id);
  }

  /**
 * @OA\Delete(
 *     path="/accounts/{id}",
 *     summary="Delete an Account by ID",
 *     description="Deletes the specified account by its ID. Returns an error if the account is not found.",
 *     tags={"Accounts"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="The ID of the account to delete",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             example=1
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successfully deleted the account",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="status",
 *                 type="string",
 *                 example="success"
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Account successfully deleted"
 *             ),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer"),
 *                 @OA\Property(property="product_id", type="string"),
 *                 @OA\Property(property="name", type="string"),
 *                 @OA\Property(property="mobile", type="string"),
 *                 @OA\Property(property="website", type="string"),
 *                 @OA\Property(property="industry_id", type="string"),
 *                 @OA\Property(property="business_type_id", type="string"),
 *                 @OA\Property(property="tax_number", type="string"),
 *                 @OA\Property(property="country_id", type="string"),
 *                 @OA\Property(property="state_id", type="string"),
 *                 @OA\Property(property="city_id", type="string"),
 *                 @OA\Property(property="pincode", type="string"),
 *                 @OA\Property(property="address", type="string"),
 *                 @OA\Property(property="owner_id", type="string"),
 *                 @OA\Property(property="description", type="string"),
 *                 @OA\Property(property="company_name", type="string"),
 *                 @OA\Property(property="contact_person_first_name", type="string"),
 *                 @OA\Property(property="contact_person_last_name", type="string"),
 *                 @OA\Property(property="contact_no", type="string"),
 *                 @OA\Property(property="landline_no", type="string"),
 *                 @OA\Property(property="company_email", type="string")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Account not found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Account not found"
 *             )
 *         )
 *     )
 * )
 */
  public function destroy(string $id)
  {
    return $this->accountRepository->delete($id);
  }
}
