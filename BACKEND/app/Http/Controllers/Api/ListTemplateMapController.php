<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListTemplateMapRequest;
use App\Repositories\ListTemplateMapRepository;
use Illuminate\Http\Request;

class ListTemplateMapController extends Controller
{
    private $listTemplateMapRepository;

    public function __construct(ListTemplateMapRepository $listTemplateMapRepository)
    {
        $this->listTemplateMapRepository = $listTemplateMapRepository;
    }

    public function store(ListTemplateMapRequest $request)
    {
        return $this->listTemplateMapRepository->store($request);
    }

    public function show(int $listId)
    {
        return $this->listTemplateMapRepository->getByList($listId);
    }
}
