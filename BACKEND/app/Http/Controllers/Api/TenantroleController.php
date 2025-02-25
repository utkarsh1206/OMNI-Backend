<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TenantRole;
use App\Http\Resources\TenantsResource;
use App\Http\Requests\TenantRoleRequest;
use App\Repositories\TenantRoleRepository;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use Exception;
class TenantRoleController extends Controller
{

    private $tenantRoleRepository;

    public function __construct(TenantRoleRepository $tenantRoleRepository)
    {
        $this->tenantRoleRepository = $tenantRoleRepository;
    }

    public function index(Request $request)
    {
        try {
            return $this->tenantRoleRepository->list($request);
        } catch (Exception $e) {
            return response()->json(['error' => trans('message.wrong_with_system')], ResponseHTTP::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(TenantRoleRequest $request)
    {
        try {
            return $this->tenantRoleRepository->create($request);
        } catch (Exception $e) {
            return response()->json(['error' => trans('message.wrong_with_system')], ResponseHTTP::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(TenantRole $tenantrole)
    {
        try {
            return new TenantsResource($tenantrole);
        } catch (Exception $e) {
            return response()->json(['error' => trans('message.wrong_with_system')], ResponseHTTP::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(TenantRoleRequest $request, TenantRole $tenantrole)
    {
        try {
            return $this->tenantRoleRepository->update($request, $tenantrole);
        } catch (Exception $e) {
            return response()->json(['error' => trans('message.wrong_with_system')], ResponseHTTP::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(TenantRole $tenantrole)
    {
        try {
            return $this->tenantRoleRepository->delete($tenantrole);
        } catch (Exception $e) {
            return response()->json(['error' => trans('message.wrong_with_system')], ResponseHTTP::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function list()
    {
        try {
            return $this->tenantRoleRepository->menuListPermissions();
        } catch (Exception $e) {
            return response()->json(['error' => trans('message.wrong_with_system')], ResponseHTTP::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
  
}
