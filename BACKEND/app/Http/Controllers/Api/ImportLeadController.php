<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\ImportExportLeadRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Http\Response\CustomApiResponse;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;

class ImportLeadController extends Controller
{
    protected $importExportLeadRepository;
    private $apiResponse;

    public function __construct(ImportExportLeadRepository $importExportLeadRepository, CustomApiResponse $customApiResponse)
    {
        $this->importExportLeadRepository = $importExportLeadRepository;

        $this->apiResponse = $customApiResponse;
    }

    public function downloadSampleCsv($list_id)
    {
        try {
            return $this->importExportLeadRepository->downloadSampleCsv($list_id);
        } catch (\Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function uploadLeadCsv(Request $request, $list_id)
    {
        try {
            $request->validate(['file' => 'required|mimes:csv']);
            $file = $request->file('file');

            $csvData = $this->importExportLeadRepository->processCsvFile($file, $list_id);

            return $csvData;
        } catch (\Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

}
