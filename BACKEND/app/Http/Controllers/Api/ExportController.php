<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller; 
use App\Repositories\ExportRepository;

class ExportController extends Controller
{
    
    protected $exportRepository;

    public function __construct(ExportRepository $exportRepository)
    {
        $this->exportRepository = $exportRepository;
    }

    public function export()
    {
        return $this->exportRepository->export();
    }
}
