<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Resources\TenantsResource;
use App\Http\Response\CustomApiResponse;
use App\Models\SupportTicket;
use App\Models\MailDetail;
use App\Models\EmailConfigure;
use App\Models\SupportTicketNotification;
use Illuminate\Support\Facades\Mail;
use Exception;
use App\Mail\SendEmail;
use App\Models\SmsTemplate;
use App\Services\SmsService;
use App\Models\MailTemplate;
use App\Models\SupportTicketCustomFieldValue;

class SupportTicketRepository
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

            // Advanced filters
            $filters = [
                'support_ticket_id' => $request->input('ticket_id'),
                'product_id' => $request->input('product_id'),
                'subject' => $request->input('subject'),
                'type' => $request->input('default_type'),
                'status' => $request->input('default_status'),
                'priority' => $request->input('default_priority'),
                'assign_to_id' => $request->input('assign_to_id'),
                'contact_number' => $request->input('contact_number'),
                // 'usergroup_id' => $request->input('usergroup_id'),
                'department_id' => $request->input('department_id'),
                'account_id' => $request->input('account_id'),
                'incident_id' => $request->input('incident_id'),
            ];

            $token = $request->bearerToken();
            if ($token != "") {
                $payload = extractPayload($token);
                $roleId = $payload['role_id'];
                $userId = $payload['id'];
            }

            // $query = SupportTicket::query()
            //     ->with([
            //         // 'product:id,name',
            //         'priority:id,name',
            //         'ticketType:id,type',
            //         'department:id,name',
            //         'contact:id,first_name,last_name',
            //         'assignedAgent:id,username',
            //         // 'account:id,name',
            //         'incidentType:id,name',
            //         // 'usergroup:id,name'
            //     ]);

            $query = SupportTicket::query()->with(
                [
                    'department',
                    'priority:id,name',
                    'ticketType:id,type',
                    'ticketStatus:id,name',
                    'product:id,name',
                    'supportTicketCustomFieldValues.productCustomField',
                ]
            );

            if (isset($roleId) && $roleId == 3) {
                $query->where('assign_to_id', $userId);
            }

            // ✅ LIVE SEARCH
            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('support_ticket_id', 'like', "%{$searchTerm}%")
                        ->orWhere('subject', 'like', "%{$searchTerm}%")
                        ->orWhere('description', 'like', "%{$searchTerm}%")
                        // ->orWhereHas('product', function ($subQ) use ($searchTerm) {
                        //     $subQ->where('name', 'like', "%{$searchTerm}%");
                        // })
                        // ->orWhereHas('priority', function ($subQ) use ($searchTerm) {
                        //     $subQ->where('name', 'like', "%{$searchTerm}%");
                        // })
                        // ->orWhereHas('ticketType', function ($subQ) use ($searchTerm) {
                        //     $subQ->where('type', 'like', "%{$searchTerm}%");
                        // })
                        ->orWhereHas('assignedAgent', function ($subQ) use ($searchTerm) {
                            $subQ->where('first_name', 'like', "%{$searchTerm}%")
                                ->orWhere('last_name', 'like', "%{$searchTerm}%")
                                ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$searchTerm}%"]);
                        })
                        ->orWhereHas('contact', function ($subQ) use ($searchTerm) {
                            $subQ->where('first_name', 'like', "%{$searchTerm}%")
                                ->orWhere('last_name', 'like', "%{$searchTerm}%")
                                ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$searchTerm}%"]);
                        })
                        // --- START: Search in Custom Fields ---
                        ->orWhereHas('supportTicketCustomFieldValues', function ($subQ) use ($searchTerm) {
                            $subQ->where('field_value', 'like', "%{$searchTerm}%")
                                ->whereHas('productCustomField', function ($fieldQ) {
                                    $fieldQ->where('search_filter', 'Y');
                                });
                        });
                });
            }

            // ✅ ADVANCED FILTERS
            // if (!empty($filters['support_ticket_id'])) {
            //     $query->where('ticket_id', 'like', "%{$filters['support_ticket_id']}%");
            // }

            // if (!empty($filters['product_id'])) {
            //     $query->where('product_id', $filters['product_id']);
            // }

            // if (!empty($filters['subject'])) {
            //     $query->where('subject', 'like', "%{$filters['subject']}%");
            // }

            // if (!empty($filters['type'])) {
            //     $query->where('default_type', $filters['type']);
            // }

            // if (!empty($filters['status'])) {
            //     $query->where('default_status', $filters['status']);
            // }

            // if (!empty($filters['priority'])) {
            //     $query->where('default_priority', $filters['priority']);
            // }

            // if (!empty($filters['assign_to_id'])) {
            //     $query->where('assign_to_id', $filters['assign_to_id']);
            // }

            // if (!empty($filters['contact_number'])) {
            //     $query->where('contact_number', 'like', "%{$filters['contact_number']}%");
            // }

            // // if (!empty($filters['usergroup_id'])) {
            // //     $query->where('usergroup_id', $filters['usergroup_id']);
            // // }

            // if (!empty($filters['department_id'])) {
            //     $query->where('department_id', $filters['department_id']);
            // }

            // if (!empty($filters['account_id'])) {
            //     $query->where('account_id', $filters['account_id']);
            // }

            // if (!empty($filters['incident_id'])) {
            //     $query->where('incident_id', $filters['incident_id']);
            // }

            foreach ($filters as $key => $value) {
                if (!empty($value)) {
                    // Handle specific column names if different from filter key
                    $columnName = $key;
                    if ($key === 'type') {
                        $columnName = 'default_type';
                    } elseif ($key === 'status') {
                        $columnName = 'default_status';
                    } elseif ($key === 'priority') {
                        $columnName = 'default_priority';
                    } elseif ($key === 'ticket_id') {
                        $columnName = 'support_ticket_id';
                    }

                    if (in_array($key, ['subject', 'contact_number', 'ticket_id'])) {
                        $query->where($columnName, 'like', "%{$value}%");
                    } else {
                        $query->where($columnName, $value);
                    }
                }
            }


            // DO NOT REMOVE, WILL BE USED IN FUTURE - UTKARSH
            // ------------------------------------------

            // --- START: ADVANCED FILTERS FOR CUSTOM FIELDS ---
            // Assume custom filters are sent as: ?custom_filters[field_name_1]=value1&custom_filters[field_name_2]=value2
            // $customFilters = $request->input('custom_filters', []);
            // if (!empty($customFilters)) {
            //     foreach ($customFilters as $fieldName => $filterValue) {
            //         if (!empty($filterValue) && is_string($fieldName)) {
            //             $query->whereHas('supportTicketCustomFieldValues', function ($q) use ($fieldName, $filterValue) {
            //                 $q->where('field_name', $fieldName)
            //                     ->where('field_value', 'like', "%{$filterValue}%") // Assuming partial match for text fields
            //                     ->whereHas('productCustomField', function ($subQ) {
            //                         // Only filter on custom fields explicitly marked as searchable
            //                         $subQ->where('search_filter', 'Y');
            //                     });
            //             });
            //         }
            //     }
            // }
            // --- END: ADVANCED FILTERS FOR CUSTOM FIELDS ---

            // ------------------------------------------
            // DO NOT REMOVE, WILL BE USED IN FUTURE - UTKARSH

            // ✅ PAGINATION
            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

            $query->orderBy('id', 'desc');

            $supportTickets = $page == config('omni.ALL_PAGE')
                ? $query->get()
                : $query->paginate($perPage);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $supportTickets,
                trans('message.data_fetch')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error fetching Support Tickets List : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function store(Request $request)
    {
        /** Format in which the data should be sent
         * {
         *   "product_id": 123,
         *   "subject": "Issue with product X",
         *   "description": "...",
         *   "custom_fields": {
         *           "order_number": "XYZ789",
         *           "customer_feedback": "Very satisfied with the quick r*esponse.",
         *           "delivery_date": "2025-07-15"
         *           ...
         *       }
         *   }
         */
        try {
            $apiUser = auth()->user();

            // Handle file attachments
            [$attachment_urls, $mail_attachment_urls] = $this->processAttachments($request);

            // Create support ticket
            $builtTicketData = $this->buildTicketData($request, $attachment_urls);
            $supportTicket = SupportTicket::create($builtTicketData);
            $supportTicketId = $builtTicketData['support_ticket_id'];

            if ($request->has('custom_fields') && is_array($request->custom_fields)) {
                $customFieldValuesToStore = [];
                $productId = $supportTicket->product_id;

                foreach ($request->custom_fields as $fieldName => $fieldValue) {
                    if (is_string($fieldName)) {
                        // $preparedFieldValue = is_array($fieldValue) ? json_encode($fieldValue) : $fieldValue;

                        $customFieldValuesToStore[] = [
                            'support_ticket_id' => $supportTicket->id,
                            'product_id' => $productId,
                            'field_name' => $fieldName,
                            'field_value' => $fieldValue,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }

                if (!empty($customFieldValuesToStore)) {
                    // Insert all custom field values in a single batch for efficiency
                    SupportTicketCustomFieldValue::insert($customFieldValuesToStore);
                }
            }

            $mailTemplate = MailTemplate::where('type', 'created_ticket')->first();
            if ($mailTemplate) {
                $templateData = [
                    '{customer_name}' => $apiUser->first_name . ' ' . $apiUser->last_name,
                    '{ticket_number}' => $supportTicketId,
                    '{company_email}' => 'support@ezeedesk.com',
                    '{signature}' => 'The Ezeedesk Team',
                ];

                $mailTemplate->subject = strtr($mailTemplate->subject, $templateData);
                $mailTemplate->body = strtr($mailTemplate->body, $templateData);
            }

            // Create mail details
            MailDetail::create($this->buildMailData($request, $apiUser, $attachment_urls, $supportTicketId, $mailTemplate));

            // Log ticket creation
            $this->createTicket($supportTicket, "Ticket Created", $apiUser->first_name . " " . $apiUser->last_name . " has created the ticket", $apiUser);
            $this->sendSms($apiUser, "Ticket Created", $apiUser->mobile, $supportTicket);
            $this->sendSupportEmail($request, $mail_attachment_urls, $supportTicket->id, $request->assign_to_id ?? 0);

            return $this->apiResponse->getResponseStructure(trans('message.success'), $supportTicket, trans('message.support_ticket_create_msg'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error occurred while creating Support Ticket: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function update(Request $request, SupportTicket $supportTicket)
    {
        try {
            $apiUser = auth()->user();

            // Handle file attachments
            [$attachment_urls, $mail_attachment_urls] = $this->processAttachments($request);

            $updateData = $this->buildTicketData($request, $attachment_urls);
            $updateData['support_ticket_id'] = $request->support_ticket_id;
            if (empty($attachment_urls)) {
                unset($updateData['attachment']);
            }

            $supportTicket->update($updateData);

            SupportTicketCustomFieldValue::where('support_ticket_id', $supportTicket->id)->delete();

            if ($request->has('custom_fields') && is_array($request->custom_fields)) {
                $customFieldValuesToStore = [];
                // Get the current product_id from the *updated* support ticket
                $productId = $supportTicket->product_id;

                foreach ($request->custom_fields as $fieldName => $fieldValue) {
                    if (is_string($fieldName)) {
                        $customFieldValuesToStore[] = [
                            'support_ticket_id' => $supportTicket->id,
                            'product_id' => $productId,
                            'field_name' => $fieldName,
                            'field_value' => $fieldValue, // Model's mutator handles serialization if needed
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }

                if (!empty($customFieldValuesToStore)) {
                    SupportTicketCustomFieldValue::insert($customFieldValuesToStore);
                }
            }

            $this->createTicket($supportTicket, $request->default_status ?? "", "Edit Support Ticket", $apiUser);
            $this->sendSms($apiUser, "Ticket Created", $apiUser->mobile, $supportTicket);

            $this->sendSupportEmail($request, $mail_attachment_urls, $supportTicket->id, $request->assign_to_id ?? 0);

            return $this->apiResponse->getResponseStructure(trans('message.success'), $supportTicket, trans('message.support_ticket_update_msg'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error occurred while updating Support Ticket: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function delete($supportTicket)
    {
        try {
            $supportTicket->delete();
            // Return the response
            return $this->apiResponse->getResponseStructure(trans('message.success'), $supportTicket, trans('message.support_ticket_delete_msg'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error occurred while deleting  Support Tickets : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function show(SupportTicket $supportTicket)
    {
        try {
            // $response = new TenantsResource($supportTicket);
            $supportTicket->load('supportTicketCustomFieldValues.productCustomField');

            return $this->apiResponse->getResponseStructure(trans('message.success'), $supportTicket, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error fetching SLA setting : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function statusUpdate(Request $request, $id)
    {
        try {
            $supportTicket = Supportticket::find($id);

            $apiUser = auth()->user();
            $type = "";
            $comment = "";
            $statusInfo = "";

            if (isset($request->default_status)) {
                $type = $request->default_status;
                $comment = "Change ticket status to: " . $request->default_status;
                $updateData = [
                    'default_status' => isset($request->default_status) ? $request->default_status : "",
                ];
                $statusInfo = "Status Changed";
            }
            if (isset($request->default_priority)) {
                $type = $request->default_priority;
                $comment = "Change ticket priority to: " . $request->default_priority;
                $updateData = [
                    'default_priority' => isset($request->default_priority) ? $request->default_priority : "",
                ];
                $statusInfo = "Priority Changed";
            }
            if (isset($request->default_type)) {
                $type = $request->default_type;
                $comment = "Change ticket type to: " . $request->default_type;
                $updateData = [
                    'default_type' => isset($request->default_type) ? $request->default_type : "",
                ];
                $statusInfo = "Type Changed";
            }

            $supportTicket->update($updateData);

            $ticketResponse = $this->createTicket($supportTicket, $type, $comment, $apiUser, $statusInfo);

            if ($request->default_status == "Resolved") {
                // $this->sendSms($apiUser, "Ticket Resolved", $apiUser->mobile, $supportticket);
                $toEmails = $supportTicket->assignedUser->email ?? '';
                setEmailConfig();

                $mailTemplate = MailTemplate::where("type", "agent_solve_the_ticket")->first();
                $subject =  $mailTemplate->subject ?? '';
                $body =  $mailTemplate->body ?? '';
                $body = str_replace("{ticket_number}", $supportTicket->support_ticket_id, $body);
                $body = str_replace("{{customer_name}}", $supportTicket->assignedUser->first_name, $body);
                $subject = str_replace("{ticket_number}", $supportTicket->support_ticket_id, $subject);

                Mail::to($toEmails)->send(new SendEmail($subject, $body));
            }

            $statusUpdateData = array_merge(
                (new TenantsResource($supportTicket))->toArray($request),
                $ticketResponse
            );

            // Return the response
            return $this->apiResponse->getResponseStructure(trans('message.success'), $statusUpdateData, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error fetching Support Tickets : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    private function createTicket($supportticket, $type, $comment, $apiUser, $statusChange = null)
    {
        $notification = SupportTicketNotification::create([
            'type' => $statusChange ?? $type,
            'status' => $supportticket->default_status,
            'comment' => $comment,
            'support_ticket_id' => $supportticket->id,
            'date' => gmdate('Y-m-d H:i:s'),
            'created_by' => $apiUser->id ?? 0,
            'icon' =>  $statusChange ? getIconforNotification($statusChange) : getIconforNotification($type),
            'color' =>  $statusChange ? getColorforNotification($statusChange) : getColorforNotification($type),
        ]);

        return [
            'type' => $statusChange,
            'status' => $notification->status,
            'comment' => $notification->comment,
            'support_ticket_id' => $notification->support_ticket_id,
            'created_by' => $notification->created_by,
            'icon' => $notification->icon,
            'color' => $notification->color,
            'date' => $notification->date,
            'created_at' => $notification->created_at,
            'updated_at' => $notification->updated_at,
        ];
    }

    private function sendSms($apiUser, $type, $phone, $supportticket)
    {
        $smsInfo = new SmsService;
        $templateInfo = SmsTemplate::where("name", $type)->first();
        $templateName = $templateInfo->name ?? '';
        $template = $templateInfo->content ?? '';
        $template = str_replace("{#ticket ##}", $supportticket->support_ticket_id, $template);
        $template = str_replace("{#customer name#}", $apiUser->first_name . ' ' . $apiUser->last_name, $template);
        // $smsInfo->sendSMS($apiUser->id, $templateName, $template, $phone);
    }

    private function processAttachments(Request $request): array
    {
        $attachment_urls = [];
        $mail_attachment_urls = [];
        if ($request->hasFile('attachment')) {

            $files = uploadAttachment($request, "attachment", "support_ticket");

            foreach ($files as $attachment) {
                $attachment_urls[] = url('storage/' . $attachment['path']);
                $mail_attachment_urls[] = [
                    'path' => storage_path('app/public/' . $attachment['path']),
                    'name' => $attachment['fileName'],
                    'mime' => $attachment['mimeType']
                ];
            }
        }

        return [$attachment_urls, $mail_attachment_urls];
    }

    private function buildTicketData(Request $request, array $attachment_urls): array
    {
        return [
            'support_ticket_id' => gmdate('ymdhis'),
            'product_id' => $request->product_id ?? 0,
            'campaign_id' => $request->campaign_id ?? 0,
            'inbound_group_id' => $request->inbound_group_id ?? 0,
            // 'contact_id' => $request->contact_id ?? 0,
            'contact_number' => $request->contact_number ?? 0,
            'assign_to_id' => $request->assign_to_id ?? 0,
            'default_type' => $request->default_type ?? 0,
            'default_priority' => $request->default_priority ?? 0,
            'default_status' => $request->default_status ?? '',
            'subject' => $request->subject ?? '',
            'department_id' => $request->department_id ?? '',
            'to_emails' => $request->to_emails ?? '',
            'cc' => $request->cc ?? '',
            'bcc' => $request->bcc ?? '',
            'description' => $request->description ?? '',
            'attachment' => json_encode($attachment_urls),
            'incident_id' => $request->incident_id ?? 0,
            'account_id' => $request->account_id ?? 0,
            'category_id' => $request->category_id ?? 0
        ];
    }

    private function buildMailData(Request $request, $apiUser, array $attachment_urls, string $supportTicketId): array
    {
        return [
            'default_type' => $request->default_type ?? '',
            'user_id' => $apiUser->id ?? '',
            'priority' => $request->default_priority ?? '',
            'status' => $request->default_status ?? '',
            'to' => $request->to_emails ?? '',
            'cc' => $request->cc ?? '',
            'bcc' => $request->bcc ?? '',
            'body' => $request->description ?? '',
            'attachment' => json_encode($attachment_urls),
            'supportticket_id' => $supportTicketId,
            'date' => gmdate('Y-m-d H:i:s'),
        ];
    }

    private function sendSupportEmail(Request $request, array $mail_attachment_urls, int $ticketId, int $assignToId, array $mailTemplate = null)
    {
        $emailConfig = EmailConfigure::where('type', 'gmail')->first();
        if (isset($emailConfig) && !empty($emailConfig)) {
            setEmailConfig();
            $sender_email = $emailConfig->sender_email ?? config('mail.from.address');
            establishMasterConnection(auth()->user()->domain, "omnichannel", "mysql");
            $subject = $mailTemplate['subject'] ?? $request->subject;
            $body = $mailTemplate['body'] ?? $request->description;
            $result = Mail::to($request->to_emails)->send(new SendEmail($subject, $body, $mail_attachment_urls));
            getDbConnection(auth()->user()->domain);
            logEmail([
                "user_id" => $assignToId,
                "email_template_id" => 0,
                "from_email" => $sender_email,
                "module_name" => "Support Ticket",
                "entity_id" => $ticketId,
                "status" => "Sent",
            ]);
        }
    }
}
