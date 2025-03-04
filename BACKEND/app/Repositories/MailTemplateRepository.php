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

    /**
     * @OA\Delete(
     *     path="/mail-templates/{id}",
     *     summary="Delete a Mail Template by ID",
     *     description="Delete a specific mail template by its ID. If the template is not found or cannot be deleted, an appropriate error is returned.",
     *     operationId="deleteMailTemplate",
     *     tags={"MailTemplates"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the mail template to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mail template deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="message", type="string", example="Mail template deleted successfully"),
     *                 @OA\Property(property="data", type="object", description="Mail template that was deleted")
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Mail Template not found",
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="message", type="string", example="Mail template not found")
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Mail Template deletion not allowed",
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="message", type="string", example="Mail template cannot be deleted")
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */

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
