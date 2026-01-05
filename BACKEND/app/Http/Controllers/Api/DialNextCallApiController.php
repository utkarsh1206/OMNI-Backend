<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DialNextCallApiRequest;
use App\Repositories\DialNextCallApiRepository;
use Illuminate\Http\Request;

class DialNextCallApiController extends Controller
{
    private DialNextCallApiRepository $repository;

    public function __construct(DialNextCallApiRepository $repository)
    {
        $this->repository = $repository;
    }

    public function dialNextCallApi(Request $request)
    {
        return $this->repository->dialNextCall($request);
    }
}
