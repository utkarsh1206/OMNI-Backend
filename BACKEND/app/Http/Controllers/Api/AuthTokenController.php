<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\AuthTokenRepository;

class AuthTokenController extends Controller
{
    private $authTokenRepository;
    public function __construct(AuthTokenRepository $authTokenRepository)
    {
        $this->authTokenRepository = $authTokenRepository;
    }

    public function index(Request $request)
    {
        return $this->authTokenRepository->list($request);
    }

}
