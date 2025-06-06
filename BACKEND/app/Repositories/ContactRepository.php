<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\Account;
use App\Models\User;
use App\Http\Response\CustomApiResponse;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use Exception;

class ContactRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list(Request $request)
    {
        try {
            $searchTerm = $request->input('search', '');
            $productId = $request->input('product_id', '');
            $accountId = $request->input('account_id', '');
            $ownerId = $request->input('owner_id', '');
            $contactNumber = $request->input('contact_number', '');

            $query = Contact::query();
            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('first_name', 'like', "%$searchTerm%")
                        ->orWhere('last_name', 'like', "%$searchTerm%")
                        ->orWhere('email', 'like', "%$searchTerm%")
                        ->orWhere('mobile', 'like', "%$searchTerm%")
                        ->orWhere('product_id', 'like', "%$searchTerm%")
                        ->orWhere('account_id', 'like', "%$searchTerm%")
                        ->orWhere('owner_id', 'like', "%$searchTerm%")
                        ->orWhere('mobile', 'like', "%$searchTerm%");
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

            if (!empty($contactNumber)) {
                $query->where('mobile', 'like', "$contactNumber%");
            }

            $contact = $page == config('omni.ALL_PAGE') ? $query->get() : $query->paginate($perPage);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $contact, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating Contact Listing: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function store(Request $request)
    {
        try {
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

            return $this->apiResponse->getResponseStructure(trans('message.success'), $contact, trans('message.contact_created'), ResponseHTTP::HTTP_CREATED);
        } catch (Exception $ex) {
            generateLogs('error', 'Error in creating Contact: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $contact = Contact::find($id);

            if (!$contact) {
                return response()->json(['message' => trans("message.contact_not_found")], ResponseHTTP::HTTP_NOT_FOUND);
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
            return $this->apiResponse->getResponseStructure(trans('message.success'), $contact, trans('message.contact_updated'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in updating Contact: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function delete($id)
    {
        try {
            $contact = Contact::find($id);
            if (!$contact) {
                return response()->json(['message' => trans("message.contact_not_found")], ResponseHTTP::HTTP_NOT_FOUND);
            }
            $contact->delete();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $contact, trans('message.contact_deleted'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in deleting Contact: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function show($contact)
    {
        try {
            return $this->apiResponse->getResponseStructure(trans('message.success'), $contact, trans('message.fetch_data'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in fetching Contact: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
