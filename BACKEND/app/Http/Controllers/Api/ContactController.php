<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ContactResource;

class ContactController extends Controller
{
  public function index(Request $request)
  {
    $searchTerm = $request->input('search', '');
    $productId = $request->input('product_id', '');
    $accountId = $request->input('account_id', '');
    $ownerId = $request->input('owner_id', '');

    $query = Contact::query();
    $perPage = $request->input('per_page', 10);
    $page = $request->input('page', 1);

    if (!empty($searchTerm)) {
      $query->where(function ($q) use ($searchTerm) {
        $q->where('first_name', 'like', "%$searchTerm%")
          ->orWhere('last_name', 'like', "%$searchTerm%")
          ->orWhere('email', 'like', "%$searchTerm%")
          ->orWhere('mobile', 'like', "%$searchTerm%")
          ->orWhere('product_id', 'like', "%$searchTerm%")
          ->orWhere('account_id', 'like', "%$searchTerm%")
          ->orWhere('owner_id', 'like', "%$searchTerm%");
      });

      $account = Account::where('name', 'like', "%$searchTerm%")->first();
      if ($account) {
        $query->orWhere('account_id', $account->id);
      }

      $owner = User::where('username', 'like', "%$searchTerm%")->first();
      if ($owner) {
        $query->orWhere('owner_id', $owner->id);
      }
    }

    if (!empty($productId)) {
      $query->where('product_id', $productId);
    }

    if (!empty($accountId)) {
      $query->where('account_id', $accountId);
    }

    if (!empty($ownerId)) {
      $query->where('owner_id', $ownerId);
    }

    if ($page === 'all') {
      $contact = $query->get();
      return response()->json([
        'data' => $contact,
      ]);
    } else {
      $contact = $query->paginate($perPage);
      return response()->json([
        'meta' => [
          'current_page' => $contact->currentPage(),
          'last_page' => $contact->lastPage(),
          'per_page' => $contact->perPage(),
          'total' => $contact->total(),
        ],
        'data' => $contact->items(),

      ]);
    }
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'product_id' => 'required',
      'account_id' => 'required',
      'first_name' => 'required|string',
      'last_name' => 'required|string',
      'mobile' => 'required|integer',
      'gender' => 'required|string',
    ]);

    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $contact = Contact::create([
      'product_id' => $request->product_id,
      'account_id' => $request->account_id,
      'name_prefix' => $request->name_prefix,
      'first_name' => $request->first_name,
      'last_name' => $request->last_name,
      'email' => $request->email,
      'mobile' => $request->mobile,
      'owner_id' => $request->owner_id,
      'country_id' => $request->country_id,
      'state_id' => $request->state_id,
      'city_id' => $request->state_id,
      'pincode' => $request->pincode,
      'address' => $request->address,
      'gender' => $request->gender,
      'description' => $request->description
    ]);

    return response()->json([
      'message' => 'Contact created successfully!',
      'data' => new ContactResource($contact)
    ], 201);
  }

  public function show(Contact $contact)
  {
    return new ContactResource($contact);
  }

  public function update(Request $request, string $id)
  {
    $contact = Contact::find($id);

    if (!$contact) {
      return response()->json(['message' => 'Contact not found'], 404);
    }

    $validator = Validator::make($request->all(), [
      'product_id' => 'required',
      'account_id' => 'required',
      'first_name' => 'required|string',
      'last_name' => 'required|string',
      'mobile' => 'required|integer',
      'gender' => 'required|string',
    ]);

    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $contact->update([
      'product_id' => $request->product_id,
      'account_id' => $request->account_id,
      'name_prefix' => $request->name_prefix,
      'first_name' => $request->first_name,
      'last_name' => $request->last_name,
      'email' => $request->email,
      'mobile' => $request->mobile,
      'owner_id' => $request->owner_id,
      'country_id' => $request->country_id,
      'state_id' => $request->state_id,
      'city_id' => $request->state_id,
      'pincode' => $request->pincode,
      'address' => $request->address,
      'gender' => $request->gender,
      'description' => $request->description
    ]);

    return response()->json([
      'message' => 'Contact updated successfully!',
      'data' => new ContactResource($contact)
    ], 201);
  }

  public function destroy(string $id)
  {
    $contact = Contact::find($id);

    if (!$contact) {
      return response()->json(['message' => 'Contact not found'], 404);
    }

    $contact->delete();
    return response()->json([
      'message' => 'Contact deleted successfully',
    ]);
  }
}
