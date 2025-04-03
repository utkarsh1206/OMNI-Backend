<?php

namespace App\Repositories;

use App\Models\Notes;
use App\Http\Response\CustomApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;

class NoteRepository
{


    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function index(Request $request)
    {
        try {
            $supportticketId = $request->input('supportticket_id');
            $notedetail = Notes::where('supportticket_id', $supportticketId)->orderBy('id', 'desc')->get();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $notedetail, trans('message.data_fetch'));
        } catch (ValidationException $e) {
            generateLogs('error', 'Error in Note List : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $notedetail = Notes::create([
                'supportticket_id' => $request->supportticket_id,
                'date' => gmdate('Y-m-d H:i:s'),
                'note' => $request->note,
                'created_by' => isset($request->user_id) ? $request->user_id : 0,
            ]);

            return $this->apiResponse->getResponseStructure(trans('message.success'), $notedetail, trans('message.note_created_msg'), ResponseHTTP::HTTP_CREATED);
        } catch (ValidationException $e) {
            generateLogs('error', 'Error in Note Store : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
