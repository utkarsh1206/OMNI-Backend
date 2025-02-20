<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\AccountResource;

class AccountController extends Controller
{
  public function index(Request $request)
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
              ->orWhere('mobile', 'like', "%$searchTerm%" );
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
      return response()->json([
        'data' => $accounts,
      ]);
    } else {
      $accounts = $query->paginate($perPage);
      return response()->json([
        'meta' => [
          'current_page' => $accounts->currentPage(),
          'last_page' => $accounts->lastPage(),
          'per_page' => $accounts->perPage(),
          'total' => $accounts->total(),
        ],
        'data' => $accounts->items(),

      ]);
    }
  }

  public function store(Request $request)
  {
    $existingAccount = Account::where('product_id', $request->product_id)
      ->where('name', $request->name)
      ->first();

    if ($existingAccount) {
      return response()->json([
        'message' => 'Product and Name combination already exists!',
      ], 422);
    }

    $validator = Validator::make($request->all(), [
      'name' => 'required|string|max:255',
      'campaign_id' => 'required|string',
      // 'product_deployed_type' => 'required|string',
      // 'neox_integration' => 'required|string',
      // 'phone_type' => 'required|string',
      // 'pstn_line' => '',
      // 'pstn_ports_qty' => '',
      // 'pstn_ports_qty' => '',
      // 'did_number_details' => '',
      // 'pilot_number_detail' => '',
      // 'extension_range_detail' => '',
      // 'server_serialnumber' => '',
      // 'server_ip' => '',
      // 'server_mac' => '',
      // 'license_key' => '',
      // 'no_of_users ' => '',
      // 'account_expiry_date' => '',
      // 'license_detail' => '',
      'company_name' => '',
      'contact_person_first_name' => '',
      'contact_person_last_name' => '',
      'contact_no' => '',
      'company_email' => '',
    ]);

    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
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
      // 'product_deployed_type' => $request->product_deployed_type,
      // 'neox_integration' => $request->neox_integration,
      // 'phone_type' => $request->phone_type,
      // 'phone_type_comment' => $request->phone_type_comment,
      // 'pstn_line' => $request->pstn_line,
      // 'pstn_ports_qty' => $request->pstn_ports_qty,
      // 'isp_details' => $request->isp_details,
      // 'did_number_details' => $request->did_number_details,
      // 'pilot_number_detail' => $request->pilot_number_detail,
      // 'extension_range_detail' => $request->extension_range_detail,
      // 'server_serialnumber' => $request->server_serialnumber,
      // 'server_ip' => $request->server_ip,
      // 'server_mac' => $request->server_mac,
      // 'license_key' => $request->license_key,
      // 'no_of_users' => $request->no_of_users,
      // 'account_expiry_date' => $request->account_expiry_date,
      // 'license_detail' => $request->license_detail,
      'company_name' => $request->company_name,
      'contact_person_first_name' => $request->contact_person_first_name,
      'contact_person_last_name' => $request->contact_person_last_name,
      'contact_no' => $request->contact_no,
      'landline_no' => $request->landline_no,
      'company_email' => $request->company_email
    ]);

    return response()->json([
      'message' => 'Account created successfully!',
      'data' => new AccountResource($account)
    ], 201);
  }

  public function show(Account $account)
  {
    return new AccountResource($account);
  }

  public function update(Request $request, string $id)
  {
    $account = Account::find($id);

    if (!$account) {
      return response()->json(['message' => 'Account not found'], 404);
    }
    
    $existingAccount = Account::where('product_id', $request->product_id)
      ->where('name', $request->name)
      ->where('id', '!=', $account->id)
      ->first();

    if ($existingAccount) {
      return response()->json([
        'message' => 'Product and Name combination already exists!',
      ], 422);
    }

    $validator = Validator::make($request->all(), [
      'name' => 'required|string|max:255',
      'campaign_id' => 'required',
      // 'product_deployed_type' => 'required|string',
      // 'neox_integration' => 'required|string',
      // 'phone_type' => 'required|string',
      // 'pstn_line' => '',
      // 'pstn_ports_qty' => '',
      // 'pstn_ports_qty' => '',
      // 'did_number_details' => '',
      // 'pilot_number_detail' => '',
      // 'extension_range_detail' => '',
      // 'server_serialnumber' => '',
      // 'server_ip' => '',
      // 'server_mac' => '',
      // 'license_key' => '',
      // 'no_of_users ' => '',
      // 'account_expiry_date' => '',
      // 'license_detail' => '',
      'company_name' => '',
      'contact_person_first_name' => '',
      'contact_person_last_name' => '',
      'contact_no' => '',
      'company_email' => '',
    ]);

    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
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
      // 'product_deployed_type' => $request->product_deployed_type,
      // 'neox_integration' => $request->neox_integration,
      // 'phone_type' => $request->phone_type,
      // 'phone_type_comment' => $request->phone_type_comment,
      // 'pstn_line' => $request->pstn_line,
      // 'pstn_ports_qty' => $request->pstn_ports_qty,
      // 'isp_details' => $request->isp_details,
      // 'did_number_details' => $request->did_number_details,
      // 'pilot_number_detail' => $request->pilot_number_detail,
      // 'extension_range_detail' => $request->extension_range_detail,
      // 'server_serialnumber' => $request->server_serialnumber,
      // 'server_ip' => $request->server_ip,
      // 'server_mac' => $request->server_mac,
      // 'license_key' => $request->license_key,
      // 'no_of_users' => $request->no_of_users,
      // 'account_expiry_date' => $request->account_expiry_date,
      // 'license_detail' => $request->license_detail,
      'company_name' => $request->company_name,
      'contact_person_first_name' => $request->contact_person_first_name,
      'contact_person_last_name' => $request->contact_person_last_name,
      'contact_no' => $request->contact_no,
      'landline_no' => $request->landline_no,
      'company_email' => $request->company_email
    ]);

    return response()->json([
      'message' => 'Account updated successfully!',
      'data' => new AccountResource($account)
    ], 201);
  }

  public function destroy(string $id)
  {
    $account = Account::find($id);

    if (!$account) {
      return response()->json(['message' => 'Account not found'], 404);
    }

    $account->delete();
    return response()->json([
      'message' => 'Account deleted successfully',
    ]);
  }
}
