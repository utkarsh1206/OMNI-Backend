<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\TenantRoleRequest;
use App\Models\Role;
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

    public function show(Role $tenantrole)
    {
        try {
             return $this->tenantRoleRepository->show($tenantrole);
        } catch (Exception $e) {
            return response()->json(['error' => trans('message.wrong_with_system')], ResponseHTTP::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(TenantRoleRequest $request, Role $tenantrole)
    {
        try {
            return $this->tenantRoleRepository->update($request, $tenantrole);
        } catch (Exception $e) {
            return response()->json(['error' => trans('message.wrong_with_system')], ResponseHTTP::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy( $tenantrole)
    {
        try {
            return $this->tenantRoleRepository->delete($tenantrole);
        } catch (Exception $e) {
            return response()->json(['error' => trans('message.wrong_with_system')], ResponseHTTP::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function list()
    {
        return $this->tenantRoleRepository->menuListPermissions();
    }

    public function fetchPermission()
    {
        return $this->tenantRoleRepository->fetchPermissions();
    }
}
