<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use App\Models\Account;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;

class AccountRepository
{


    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list(Request $request)
    {
        $searchTerm = $request->input('search', '');
        $productId = $request->input('product_id', '');
        $campaignId = $request->input('campaign_id', '');
        $ownerId = $request->input('owner_id', '');
        $query = Account::query();
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%$searchTerm%")
                    ->orWhere('mobile', 'like', "%$searchTerm%");
            });

            $owner = User::where('username', 'like', "%$searchTerm%")->first();
            if ($owner) {
                $query->orWhere('owner_id', $owner->id);
            }
        }

        if (!empty($productId)) {
            $query->where('product_id', $productId);
        }

        if (!empty($campaignId)) {
            $query->where('campaign_id', $campaignId);
        }

        if (!empty($ownerId)) {
            $query->where('owner_id', $ownerId);
        }

        if ($page === 'all') {
            $accounts = $query->get();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $accounts, trans('message.data_fetch'));
        } else {
            $accounts = $query->paginate($perPage);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $accounts, trans('message.data_fetch'));
        }
    }


    public function store(Request $request)
    {
        $existingAccount = Account::where('product_id', $request->product_id)
            ->where('name', $request->name)
            ->first();

        if ($existingAccount) {
            return response()->json([
                'message' => trans("message.account_product_error"),
            ], ResponseHTTP::HTTP_UNPROCESSABLE_ENTITY);
        }

        $account = Account::create([
            'product_id' => $request->product_id,
            'campaign_id' => $request->campaign_id,
            'name' => $request->name,
            'mobile' => $request->mobile,
            'website' => $request->website,
            'industry_id' => $request->industry_id,
            'business_type_id' => $request->business_type_id,
            'tax_number' => $request->tax_number,
            'country_id' => $request->country_id,
            'state_id' => $request->state_id,
            'city_id' => $request->city_id,
            'pincode' => $request->pincode,
            'address' => $request->address,
            'owner_id' => $request->owner_id,
            'description' => $request->description,
            'company_name' => $request->company_name,
            'contact_person_first_name' => $request->contact_person_first_name,
            'contact_person_last_name' => $request->contact_person_last_name,
            'contact_no' => $request->contact_no,
            'landline_no' => $request->landline_no,
            'company_email' => $request->company_email
        ]);
        return $this->apiResponse->getResponseStructure(trans('message.success'), $account, trans('message.account_created'));
    }

    public function show($account)
    {
        return $this->apiResponse->getResponseStructure(trans('message.success'), $account, trans('message.data_fetch'));
    }

    public function delete($id)
    {
        $account = Account::find($id);
        if (!$account) {
            return response()->json(['message' => trans("message.account_not_found")], ResponseHTTP::HTTP_NOT_FOUND);
        }
        $account->delete();
        return $this->apiResponse->getResponseStructure(trans('message.success'), $account, trans('message.account_deleted'));
    }

    public function update($request, $id)
    {
        $account = Account::find($id);

        if (!$account) {
            return response()->json(['message' => trans("message.account_not_found")], ResponseHTTP::HTTP_NOT_FOUND);
        }

        $existingAccount = Account::where('product_id', $request->product_id)
            ->where('name', $request->name)
            ->where('id', '!=', $account->id)
            ->first();

        if ($existingAccount) {
            return response()->json([
                'message' => trans("message.account_product_error"),
            ], ResponseHTTP::HTTP_UNPROCESSABLE_ENTITY);
        }
        $account->update([
            'product_id' => $request->product_id,
            'campaign_id' => $request->campaign_id,
            'name' => $request->name,
            'mobile' => $request->mobile,
            'website' => $request->website,
            'industry_id' => $request->industry_id,
            'business_type_id' => $request->business_type_id,
            'tax_number' => $request->tax_number,
            'country_id' => $request->country_id,
            'state_id' => $request->state_id,
            'city_id' => $request->city_id,
            'pincode' => $request->pincode,
            'address' => $request->address,
            'owner_id' => $request->owner_id,
            'description' => $request->description,
            'company_name' => $request->company_name,
            'contact_person_first_name' => $request->contact_person_first_name,
            'contact_person_last_name' => $request->contact_person_last_name,
            'contact_no' => $request->contact_no,
            'landline_no' => $request->landline_no,
            'company_email' => $request->company_email
        ]);
        return $this->apiResponse->getResponseStructure(trans('message.success'), $account, trans('message.account_updated'));
    }
}
