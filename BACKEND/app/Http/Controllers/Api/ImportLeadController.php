<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\ImportExportLeadRepository;
use App\Http\Response\CustomApiResponse;
use App\Services\ListResolverService;

class ImportLeadController extends Controller
{
    protected $importExportLeadRepository;
    protected $listResolverService;

    private $apiResponse;

    public function __construct(
        ImportExportLeadRepository $importExportLeadRepository,
        CustomApiResponse $customApiResponse,
        ListResolverService $listResolverService
    ) {
        $this->importExportLeadRepository = $importExportLeadRepository;
        $this->apiResponse = $customApiResponse;
        $this->listResolverService = $listResolverService;
    }

    public function downloadSampleCsv(Request $request)
    {
        $resolved = $this->listResolverService->resolveListId($request);

        if ($resolved instanceof \Illuminate\Http\JsonResponse) {
            return $resolved;
        }

        $listId = $resolved['list_id'];

        return $this->importExportLeadRepository->downloadSampleCsv($listId);
    }

    public function uploadLeadCsv(Request $request)
    {
        try {
            $request->validate(['file' => 'required|mimes:csv,txt']);

            $file = $request->file('file');

            $resolved = $this->listResolverService->resolveListId($request);

            if ($resolved instanceof \Illuminate\Http\JsonResponse) {
                return $resolved;
            }

            $listId = $resolved['list_id'];

            $omniLeadSource = $request->route('campaign_id')
                ? 'bulk_lead_import_third_party'
                : 'csv';


            $csvData = $this->importExportLeadRepository->processCsvFile(
                $file,
                $listId,
                $request,
                $omniLeadSource
            );

            return $csvData;
        } catch (\Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
