<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use App\Models\Account;
use App\Models\Department;
use App\Models\Product;
use App\Models\Users;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use Exception;

class AccountRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list(Request $request)
    {
        try {
            $query = Account::query();
            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

            // Search and filter parameters
            $searchTerm = $request->input('search', '');
            $filters = [
                'name' => $request->input('name'),
                'mobile' => $request->input('mobile'),
                // 'company_email' => $request->input('company_email'),
                'website' => $request->input('website'),
                'product_id' => $request->input('product_id'),
                'owner_id' => $request->input('owner_id'),
                'department_id' => $request->input('department_id'),
            ];

            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                        ->orWhere('mobile', 'like', "%{$searchTerm}%")
                        // ->orWhere('company_email', 'like', "%{$searchTerm}%")
                        ->orWhere('website', 'like', "%{$searchTerm}%");

                    // Lookup owner by user name (first or last)
                    $owner = Users::where('first_name', 'like', "%{$searchTerm}%")
                        ->orWhere('last_name', 'like', "%{$searchTerm}%")
                        ->first();
                    if ($owner) {
                        $q->orWhere('owner_id', $owner->id);
                    }

                    // Lookup department by name
                    $departmentId = Department::where('name', 'like', "%{$searchTerm}%")->value('id');
                    if ($departmentId) {
                        $q->orWhere('department_id', $departmentId);
                    }

                    // Lookup product by name
                    $productId = Product::where('name', 'like', "%{$searchTerm}%")->value('id');
                    if ($productId) {
                        $q->orWhere('product_id', $productId);
                    }
                });
            }

            if (!empty($filters['name'])) {
                $query->where('name', 'like', "%{$filters['name']}%");
            }

            if (!empty($filters['mobile'])) {
                $query->where('mobile', 'like', "%{$filters['mobile']}%");
            }

            // if (!empty($filters['company_email'])) {
            //     $query->where('company_email', 'like', "%{$filters['company_email']}%");
            // }

            if (!empty($filters['website'])) {
                $query->where('website', 'like', "%{$filters['website']}%");
            }

            if (!empty($filters['owner_id'])) {
                $query->where('owner_id', $filters['owner_id']);
            }

            if (!empty($filters['product_id'])) {
                $query->where('product_id', $filters['product_id']);
            }

            if (!empty($filters['department_id'])) {
                $query->where('department_id', $filters['department_id']);
            }

            $accounts = $page == config('omni.ALL_PAGE')
                ? $query->get()
                : $query->paginate($perPage);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $accounts,
                trans('message.data_fetch')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating Account Listing: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function store(Request $request)
    {
        try {
            // Validate if the account with the same product_id and name exists
            $existingAccount = Account::where('product_id', $request->product_id)
                ->where('name', $request->name)
                ->exists();

            if ($existingAccount) {
                return response()->json([
                    'message' => trans("message.account_product_error"),
                ], ResponseHTTP::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Prepare data for account creation
            $accountData = $request->only([
                'product_id',
                'department_id',
                'name',
                'mobile',
                'website',
                'industry_id',
                'business_type_id',
                'tax_number',
                'country_id',
                'state_id',
                'city_id',
                'pincode',
                'address',
                'owner_id',
                'description',
                'company_name',
                'contact_person_first_name',
                'contact_person_last_name',
                'contact_no',
                'landline_no',
                'company_email'
            ]);

            // Create the new account
            $account = Account::create($accountData);

            // Return the response with the created account data
            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $account,
                trans('message.account_created')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in creating Accounts: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function show($account)
    {
        try {
            return $this->apiResponse->getResponseStructure(trans('message.success'), $account, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in fetching Account: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function delete($id)
    {
        try {
            $account = Account::find($id);
            if (!$account) {
                return response()->json(['message' => trans("message.account_not_found")], ResponseHTTP::HTTP_NOT_FOUND);
            }
            $account->delete();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $account, trans('message.account_deleted'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in deleting Account: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Retrieve the account or throw an exception if not found
            $account = Account::findOrFail($id);

            // Check if another account with the same product_id and name exists (excluding the current account)
            $existingAccount = Account::where('product_id', $request->product_id)
                ->where('name', $request->name)
                ->where('id', '!=', $account->id)
                ->exists(); // Using exists() for efficiency

            if ($existingAccount) {
                return $this->apiResponse->getResponseStructure(
                    trans("message.account_product_error"),
                    null,
                    trans("message.account_product_error")
                );
            }

            // Prepare the data to be updated
            $accountData = $request->only([
                'product_id',
                'department_id',
                'name',
                'mobile',
                'website',
                'industry_id',
                'business_type_id',
                'tax_number',
                'country_id',
                'state_id',
                'city_id',
                'pincode',
                'address',
                'owner_id',
                'description',
                'company_name',
                'contact_person_first_name',
                'contact_person_last_name',
                'contact_no',
                'landline_no',
                'company_email'
            ]);

            // Update only the fields that are changed (if any)
            $account->update($accountData);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $account,
                trans('message.account_updated')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in updating Account: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
