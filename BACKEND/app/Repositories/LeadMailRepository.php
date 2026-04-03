<?php

namespace App\Repositories;

use App\Models\MailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Response\CustomApiResponse;
use App\Mail\SendEmail;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use Exception;

class LeadMailRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function sendMail(Request $request, $templateId)
    {
        try {
            // Fetch mail template
            $template = MailTemplate::find($templateId);

            if (!$template) {
                return response()->json([
                    'message' => 'Mail template not found.'
                ], ResponseHTTP::HTTP_NOT_FOUND);
            }

            try {
                setEmailConfig();
            } catch (Exception $e) {
                generateLogs("error", "Failed to set email config: " . $e->getMessage());
                return false;
            }

            sendProcessedEmail(
                $request->email,
                $template->subject,
                $template->body,
                $template->id,
                [],     // attachement empty array
                null,
                config('mail.from.address'),
                auth()->user()->id,
                "Agent to Lead Mail"
            );

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                null,
                'Mail sent successfully'
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in sending lead mail ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
