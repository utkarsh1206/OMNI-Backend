<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use App\Models\Phone;
use Exception;

class PhoneRepository
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
            $extension = $request->input('extension', '');
            $login = $request->input('login', '');
            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

            $query = Phone::query();

            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('extension', 'like', "%$searchTerm%")
                        ->orWhere('login', 'like', "%$searchTerm%")
                        ->orWhere('status', 'like', "%$searchTerm%");
                });
            }

            if (!empty($extension)) {
                $query->where('extension', $extension);
            }
            if (!empty($login)) {
                $query->where('login', $login);
            }
            $phones = $page == config('omni.ALL_PAGE') ? $query->get() : $query->paginate($perPage);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $phones, trans('message.fetch_data'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in generating Phone List : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function store($validatedData)
    {
        try {
            $validatedData['local_gmt'] = $validatedData['local_gmt'] ?? '5.50';
            $validatedData['dialplan_number'] = $validatedData['extension'];
            $validatedData['voicemail_id'] = $validatedData['extension'];
            $validatedData['active'] = "Y";

            $phone = Phone::create($validatedData);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $phone, trans('message.phone_created'), ResponseHTTP::HTTP_ACCEPTED);
        } catch (Exception $e) {
            generateLogs('error', 'Error in generating Phone Store : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function update($validatedData, $extension)
    {
        try {
            $phone = Phone::where('extension', $extension)->first();

            if (!$phone) {
                return response()->json(['message' => trans("message.phone_not_found")], ResponseHTTP::HTTP_NOT_FOUND);
            }

            $validatedData['local_gmt'] = $validatedData['local_gmt'] ?? '5.50';
            $phone->update($validatedData);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $phone, trans('message.phone_updated'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in generating Phone Update : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function delete($extension)
    {
        try {
            $phone = Phone::where('extension', $extension)->first();

            if (!$phone) {
                return response()->json(['message' => trans("message.phone_not_found")], ResponseHTTP::HTTP_NOT_FOUND);
            }

            $phone->delete();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $phone, trans('message.phone_deleted'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in generating Phone Delete : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function show($id)
    {
        try {
            $phone = Phone::findOrFail($id);

            if (!$phone) {
                return response()->json([
                    'message' => trans("message.phone_not_found")
                ], ResponseHTTP::HTTP_NOT_FOUND);
            }
            return $this->apiResponse->getResponseStructure(trans('message.success'), $phone, trans('message.fetch_data'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in generating Phone Show : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
