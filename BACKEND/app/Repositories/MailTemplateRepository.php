<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\MailTemplate;
use App\Http\Response\CustomApiResponse;
use Exception;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use Illuminate\Validation\ValidationException;

class MailTemplateRepository
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
            $query = MailTemplate::query();
            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'like', "%$searchTerm%")
                        ->orWhere('type', 'like', "%$searchTerm%")
                        ->orWhere('subject', 'like', "%$searchTerm%")
                        ->orWhere('mail_for', 'like', "%$searchTerm%");
                });
            }
            $incidentType = $page == config('omni.ALL_PAGE') ?  $query->get() : $query->paginate($perPage);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $incidentType, trans('message.fetch_data'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in generating failed MailDetail List: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $mailTemplate = MailTemplate::create($request->all());
            return $this->apiResponse->getResponseStructure(trans('message.success'), $mailTemplate, trans('message.mail_template_created'), ResponseHTTP::HTTP_CREATED);
        } catch (ValidationException $e) {
            generateLogs('error', 'Error in generating failed Store MailTemplate : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $mailTemplate = MailTemplate::find($id);

            // If the template doesn't exist, return a 404 error
            if (!$mailTemplate) {
                return response()->json([
                    'message' => trans("message.mail_template_not_found")
                ], ResponseHTTP::HTTP_NOT_FOUND);
            }

            $mailTemplate->update($request->all());
            return $this->apiResponse->getResponseStructure(trans('message.success'), $mailTemplate, trans('message.mail_template_updated'), ResponseHTTP::HTTP_CREATED);
        } catch (ValidationException $e) {
            generateLogs('error', 'Error in generating failed Update MailTemplate : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }


    public function delete($id)
    {
        try {
            // Find the mail template by ID
            $mailTemplate = MailTemplate::find($id);

            // If the template doesn't exist, return a 404 error
            if (!$mailTemplate) {
                return response()->json([
                    'message' => trans("message.mail_template_not_found"),
                ], ResponseHTTP::HTTP_NOT_FOUND);
            }

            if ($mailTemplate->is_allow !== 'Yes') {
                return response()->json([
                    'message' => trans("message.mail_template_delete_not_allow"),
                ], ResponseHTTP::HTTP_FORBIDDEN);
            }
            $mailTemplate->delete();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $mailTemplate, trans('message.mail_template_deleted'));
        } catch (ValidationException $e) {
            generateLogs('error', 'Error in generating failed Delete MailTemplate : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function show($mailTemplate)
    {
        return $this->apiResponse->getResponseStructure(trans('message.success'), $mailTemplate, trans('message.fetch_data'));
    }
}
