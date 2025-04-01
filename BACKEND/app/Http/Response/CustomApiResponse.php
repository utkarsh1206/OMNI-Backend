<?php

namespace App\Http\Response;

use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use Exception;
class CustomApiResponse
{
    public function getResponseStructure($status, $payload = null, $message = '', $statusCode = 200)
    {
        if (! empty($status) && ! empty($payload)) {
            $data = [
                'message' => $message,
                'data' => $payload,
            ];
        } elseif ($status) {
            $data = [
                'message' => $message,
            ];
        } else {
            $data = [
                'error' => [
                    $message,
                ],
            ];
        }

        return response()->json($data, $statusCode);
    }

    /**
     * Generate API Response
     *
     * @param  integer  $status
     * @param  string  $key
     * @param  string  $value
     * @param  string  $message
     * @return array
     */
    public function getCustomResponse($status, $key, $value, $message = '')
    {
        return  [
            "status" => $status,
            'message' => $message,
            $key => $value,
        ];
    }

    /**
     * handle all type of exceptions
     *
     * @return mixed|string
     */
    public function handleAndResponseException(Exception $ex)
    {
        $response = '';
        switch ($ex) {
            case $ex instanceof \Illuminate\Database\Eloquent\ModelNotFoundException:
                $response = response()->json(['message' => trans('message.record_not_found')], ResponseHTTP::HTTP_NOT_FOUND);
                break;
            case $ex instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException:
                $response = response()->json(['message' => trans('message.not_found')], ResponseHTTP::HTTP_NOT_FOUND);
                break;
            case $ex instanceof \Illuminate\Database\QueryException:
                $response = response()->json(['message' => trans('message.wrong_with_query')], ResponseHTTP::HTTP_BAD_REQUEST);
                break;
            case $ex instanceof \Illuminate\Http\Exceptions\HttpResponseException:
                $response = response()->json(['error' => trans('message.wrong_with_system')], ResponseHTTP::HTTP_INTERNAL_SERVER_ERROR);
                break;
            case $ex instanceof \Illuminate\Validation\ValidationException:
                $response = response()->json(['message' => trans('message.invalid_data')], ResponseHTTP::HTTP_BAD_REQUEST);
                break;

            case $ex instanceof \Exception:
                $response = response()->json(['message' => trans('message.wrong_with_system')], ResponseHTTP::HTTP_INTERNAL_SERVER_ERROR);
                break;
        }

        return $response;
    }
}
