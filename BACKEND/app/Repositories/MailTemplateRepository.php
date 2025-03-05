<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\MailTemplate;
use Illuminate\Support\Facades\Log;
use App\Http\Response\CustomApiResponse;
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
        $searchTerm = $request->input('search', '');

        $query = MailTemplate::query();

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%$searchTerm%")
                    ->orWhere('type', 'like', "%$searchTerm%")
                    ->orWhere('subject', 'like', "%$searchTerm%")
                    ->orWhere('mail_for', 'like', "%$searchTerm%");
            });
        }

        if ($page === 'all') {
            $mailTemplates = $query->get();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $mailTemplates, trans('message.fetch_data'));
        } else {
            $mailTemplates = $query->paginate($perPage);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $mailTemplates, trans('message.fetch_data'));
        }
    }

    public function store(Request $request)
    {
        try {
            $mailTemplate = MailTemplate::create($request->all());
            return $this->apiResponse->getResponseStructure(trans('message.success'), $mailTemplate, trans('message.mail_template_created'), ResponseHTTP::HTTP_CREATED);
        } catch (ValidationException $e) {
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
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

   
    public function delete($id)
    {
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
    }

    public function show($mailTemplate)
    {
        return $this->apiResponse->getResponseStructure(trans('message.success'), $mailTemplate, trans('message.fetch_data'));
    }
}
