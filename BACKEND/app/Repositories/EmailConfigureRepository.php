<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use App\Models\EmailConfigure;
use Illuminate\Database\QueryException;


class EmailConfigureRepository
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

            // Start the query for tenants
            $query = EmailConfigure::query();

            // If there's a search term, apply it to the query
            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('type', 'like', '%' . $searchTerm . '%')
                        ->orWhere('smtp_host', 'like', '%' . $searchTerm . '%')
                        ->orWhere('smtp_port', 'like', '%' . $searchTerm . '%')
                        ->orWhere('smtp_user', 'like', '%' . $searchTerm . '%')
                        ->orWhere('smtp_password', 'like', '%' . $searchTerm . '%')
                        ->orWhere('default_type', 'like', '%' . $searchTerm . '%')
                        ->orWhere('default_status', 'like', '%' . $searchTerm . '%')
                        ->orWhere('imap_host', 'like', '%' . $searchTerm . '%')
                        ->orWhere('imap_port', 'like', '%' . $searchTerm . '%')
                        ->orWhere('imap_user', 'like', '%' . $searchTerm . '%')
                        ->orWhere('imap_password', 'like', '%' . $searchTerm . '%')
                        ->orWhere('sender_name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('sender_email', 'like', '%' . $searchTerm . '%')
                        ->orWhere('assign_method', 'like', '%' . $searchTerm . '%')
                        ->orWhere('signature', 'like', '%' . $searchTerm . '%');
                });
            }
            // Paginate the results
            $perPage = $request->input('per_page', 10);
            $email_configure = $query->paginate($perPage);
            // Return the response
            return $this->apiResponse->getResponseStructure(trans('message.success'), $email_configure, trans('message.fetch_data'));
        } catch (QueryException $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function store(Request $request)
    {
        
        try {

            $emailConfig = EmailConfigure::create([
                'type' => $request->type,
                'product_id' => $request->product_id,
                'campaign_id' => $request->campaign_id,
                'inbound_id' => $request->inbound_id,
                'smtp_host' => $request->smtp_host,
                'smtp_port' => $request->smtp_port,
                'smtp_user' => $request->smtp_user,
                'smtp_password' => $request->smtp_password,
                'default_type' => $request->default_type,
                'default_status' => $request->default_status,
                'imap_host' => $request->imap_host,
                'imap_port' => $request->imap_port,
                'imap_user' => $request->imap_user,
                'imap_password' => $request->imap_password,
                'sender_name' => $request->sender_name,
                'sender_email' => $request->sender_email,
                'assign_method' => $request->assign_method,
                'signature' => $request->signature,
            ]);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $emailConfig, trans('message.email_configure_created'), ResponseHTTP::HTTP_CREATED);
        } catch (QueryException $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }


    public function update(Request $request, $email_configure)
    {
        try {
            $email_configure->update([
                'type' => $request->type,
                'product_id' => $request->product_id,
                'campaign_id' => $request->campaign_id,
                'inbound_id' => $request->inbound_id,
                'smtp_host' => $request->smtp_host,
                'smtp_port' => $request->smtp_port,
                'smtp_user' => $request->smtp_user,
                'smtp_password' => $request->smtp_password,
                'default_type' => $request->default_type,
                'default_status' => $request->default_status,
                'imap_host' => $request->imap_host,
                'imap_port' => $request->imap_port,
                'imap_user' => $request->imap_user,
                'imap_password' => $request->imap_password,
                'sender_name' => $request->sender_name,
                'sender_email' => $request->sender_email,
                'assign_method' => $request->assign_method,
                'signature' => $request->signature,
            ]);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $email_configure, trans('message.email_configure_updated'));
        } catch (QueryException $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }


    public function delete($email_configure)
    {
        try {
            $email_configure->delete();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $email_configure, trans('message.email_configure_deleted'));
        } catch (QueryException $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function show($email_configure)
    {
        try {
            return $this->apiResponse->getResponseStructure(trans('message.success'), $email_configure, trans('message.fetch_data'));
        } catch (QueryException $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
