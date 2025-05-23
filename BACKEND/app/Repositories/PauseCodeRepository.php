<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use App\Models\PauseCode;
use Exception;

class PauseCodeRepository
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
            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));
            $code = $request->input('code');
            $name = $request->input('name');

            $query = PauseCode::query();

            if ($code) {
                $query->where('code', 'like', "%$code%");
            }
            if ($name) {
                $query->where('name', 'like', "%$name%");
            }

            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('code', 'like', "%$searchTerm%")
                        ->orWhere('name', 'like', "%$searchTerm%");
                });
            }

            $pauseCodes = $page == config('omni.ALL_PAGE') ? $query->get() : $query->paginate($perPage);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $pauseCodes, trans('message.fetch_data'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating Pause Code Listing: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function store(Request $request)
    {
        try {
            $pauseCode = PauseCode::create([
                'code' => $request->code,
                'name' => $request->name,
                'description' => $request->description,
            ]);

            return $this->apiResponse->getResponseStructure(trans('message.success'), $pauseCode, trans('message.pause_code_created_msg'), ResponseHTTP::HTTP_CREATED);
        } catch (Exception $ex) {
            generateLogs('error', 'Error in Storing Pause Code: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $pauseCode = PauseCode::find($id);

            if (!$pauseCode) {
                return response()->json([
                    'message' => trans("message.pause_code_not_found"),
                ], ResponseHTTP::HTTP_NOT_FOUND);
            }

            $pauseCode->update([
                'code' => $request->code,
                'name' => $request->name,
                'description' => $request->description,
            ]);

            return $this->apiResponse->getResponseStructure(trans('message.success'), $pauseCode, trans('message.pause_code_updated_msg'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in Updating Pause Code: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function delete($id)
    {
        try {
            $pauseCode = PauseCode::find($id);

            if (!$pauseCode) {
                return response()->json([
                    'message' => trans("message.pause_code_not_found"),
                ], ResponseHTTP::HTTP_NOT_FOUND);
            }

            $pauseCode->delete();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $pauseCode, trans('message.pause_code_deleted_msg'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in Deleting Pause Code: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function show($id)
    {
        try {
            $pauseCode = PauseCode::find($id);

            if (!$pauseCode) {
                return response()->json([
                    'message' => trans("message.pause_code_not_found"),
                ], ResponseHTTP::HTTP_NOT_FOUND);
            }
            return $this->apiResponse->getResponseStructure(trans('message.success'), $pauseCode, trans('message.fetch_data'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in Fetching Pause Code: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
