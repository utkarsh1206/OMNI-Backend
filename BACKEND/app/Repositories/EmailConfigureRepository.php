<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use App\Models\EmailConfigure;
use App\Models\Product;
use Exception;

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
            $productName = $request->input('name', '');
            $smtpUser = $request->input('user', '');
            $imapUser = $request->input('iuser', '');

            // Start the query for tenants
            $query = EmailConfigure::select(
                'id',
                'type',
                'product_id',
                'department_id',
                'assign_method',
                'sender_name',
                'sender_email',
                'smtp_user',
                'imap_user'
            )->with(['product:id,name', 'department:id,name']);

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

            if (!empty($productName)) {
                $productId = Product::where('name', 'like', "%$productName%")->value('id');
                if ($productId !== null) {
                    $query->where('product_id', $productId);
                } else {
                    $query->where('product_id', $productName);
                }
            }

            if ($smtpUser) {
                $query->where('smtp_user', 'like', "%$smtpUser%");
            }

            if ($imapUser) {
                $query->where('imap_user', 'like', "%$imapUser%");
            }

            // Paginate the results
            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $email_configure = $query->paginate($perPage);
            // Return the response
            return $this->apiResponse->getResponseStructure(trans('message.success'), $email_configure, trans('message.fetch_data'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in generating Email Configure List: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function store(Request $request)
    {

        try {
            $input = $request->all();
            $emailConfig = EmailConfigure::create($input);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $emailConfig, trans('message.email_configure_created'), ResponseHTTP::HTTP_CREATED);
        } catch (Exception $e) {
            generateLogs('error', 'Error in generating Email Configure Store: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }


    public function update(Request $request, $email_configure)
    {
        try {
            $update = $request->all();
            $email_configure->update($update);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $email_configure, trans('message.email_configure_updated'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in generating Email Configure Update: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }


    public function delete($email_configure)
    {
        try {
            $email_configure->delete();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $email_configure, trans('message.email_configure_deleted'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in generating Email Configure Delete: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function show($email_configure)
    {
        try {
            return $this->apiResponse->getResponseStructure(trans('message.success'), $email_configure, trans('message.fetch_data'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in generating Email Configure Show: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
