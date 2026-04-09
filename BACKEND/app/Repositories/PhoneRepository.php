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
                $query->where('extension', 'like', "%$extension%");
            }
            if (!empty($login)) {
                $query->where('login', 'like', "%$login%");
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

            // NOC-995 To-Do Hosted MultiTenant START
            if (config('omni.IS_DC_MULTITENANT') == 1) {
                $user = auth()->user();
                $token = getTenantUin($user);
                $domain = strToLower($user->domain);
                $ip = rtrim(config('omni.HOSTED_URL'), '/');

                $url = "{$ip}/api/admin/actions/v2/phone_create.php?domain={$domain}";

                $payload = [
                    'token'     => $token,
                    'extension' => $validatedData['extension'],
                    'loginId'   => $validatedData['login'],
                    'password'  => $validatedData['pass'],
                    'active'    => $validatedData['active'],
                    'local_gmt' => $validatedData['local_gmt']
                ];

                $response = sendHttpRequest($url, 'POST', $payload);

                if ($response instanceof \Illuminate\Http\JsonResponse) {
                    // Extract array directly
                    $resData = $response->getData(true);
                } elseif (is_array($response)) {
                    // Already an array, no need to decode
                    $resData = $response;
                } elseif (is_string($response)) {
                    // Decode JSON string
                    $resData = json_decode($response, true);
                } else {
                    $resData = ['message' => 'Unexpected response type'];
                }
                generateLogs("info", "response: " . json_encode($resData));

                if (!isset($resData['code']) || $resData['code'] !== 0) {
                    throw new Exception("External Multi-tenant API failed to create Phone: Code: " . ($response['code'] ?? 'Unknown Code') . " Message :" . ($response['message'] ?? 'Unknown response'));
                }
            }   // NOC-995 To-Do Hosted MultiTenant END
            else {
                // Retrieve configuration
                $config = getNeoxConfig();

                $sipServerUrl = $config['sip_server_url'] ?? null;
                $sipUaUriDomain = $config['sip_ua_uri_domain'] ?? null;

                if (empty($sipServerUrl) || empty($sipUaUriDomain)) {
                    return $this->apiResponse->getResponseStructure(
                        trans('message.error'),
                        null,
                        'Please Set the SIP Configuration first',
                        ResponseHTTP::HTTP_BAD_REQUEST
                    );
                }

                $ip = $config['neox_local_ip'];

                $url = "https://{$ip}/api/admin/actions/v2/phone_create.php";

                sendHttpRequest($url, 'POST', $validatedData);
            }

            $phone = Phone::create($validatedData);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $phone,
                trans('message.phone_created'),
                ResponseHTTP::HTTP_ACCEPTED
            );
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

            // NOC-995 To-Do Hosted MultiTenant START
            if (config('omni.IS_DC_MULTITENANT') == 1) {
                $user = auth()->user();
                $token = getTenantUin($user);
                $domain = strToLower($user->domain);
                $ip = rtrim(config('omni.HOSTED_URL'), '/');

                $url = "{$ip}/api/admin/actions/v2/phone_delete.php?domain={$domain}";

                $payload = [
                    'token'     => $token,
                    'extension' => $extension
                ];

                $response = sendHttpRequest($url, 'POST', $payload);

                if ($response instanceof \Illuminate\Http\JsonResponse) {
                    // Extract array directly
                    $resData = $response->getData(true);
                } elseif (is_array($response)) {
                    // Already an array, no need to decode
                    $resData = $response;
                } elseif (is_string($response)) {
                    // Decode JSON string
                    $resData = json_decode($response, true);
                } else {
                    $resData = ['message' => 'Unexpected response type'];
                }
                generateLogs("info", "response: " . json_encode($resData));

                // Verify if the API response is successful (code 0)
                if (!isset($resData['code']) || $resData['code'] !== 0) {
                    throw new Exception("External Multi-tenant API failed to delete Phone: Code: " . ($response['code'] ?? 'Unknown Code') . " Message: " . ($response['message'] ?? 'Unknown response'));
                }
            }   // NOC-995 To-Do Hosted MultiTenant END
            else {
                $config = getNeoxConfig();

                $ip = $config['neox_local_ip'];

                $url = "https://{$ip}/api/admin/actions/v2/phone_delete.php";

                $data = ['extension' => $extension];
                sendHttpRequest($url, 'POST', $data);
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
