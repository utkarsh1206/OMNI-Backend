<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller; 
use App\Repositories\UtilityRepository;
use Illuminate\Http\Request;

class UtilityController extends Controller
{
    
    protected $utilityRepository;

    public function __construct(UtilityRepository $utilityRepository)
    {
        $this->utilityRepository = $utilityRepository;
    }

    public function export()
    {
        return $this->utilityRepository->export();
    }

    public function exportUtility(Request $request)
    {
        return $this->utilityRepository->exportUtility($request);
    }

    public function import()
    {
        return $this->utilityRepository->import();
    }

    public function importUtility(Request $request)
    {
        return $this->utilityRepository->importUtility($request);
    }
}
