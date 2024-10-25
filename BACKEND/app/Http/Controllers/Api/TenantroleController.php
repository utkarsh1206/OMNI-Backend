<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenantrole;
use App\Http\Resources\TenantsResource;
use Illuminate\Support\Facades\Validator;


class TenantroleController extends Controller
{
    public function index(Request $request)
    {
        // Get the search term from the request
        $searchTerm = $request->input('search', '');

        // Start the query for tenants
        $query = tenantrole::query()->where('is_delete', 'No');

        // If there's a search term, apply it to the query
        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orwhere('description', 'like', '%' . $searchTerm . '%');
            });
        }
        // Paginate the results
        $perPage = $request->input('per_page', 10);
        $tenant_role = $query->paginate($perPage);
        $page = $request->input('page', 1);

        if ($page === 'all') {
            // If 'page' is 'all', retrieve all records without pagination
            $tenant_role = $query->get();
            return response()->json([
                'data' => $tenant_role,
            ]);
        } else {
            // Paginate the results
            $tenant_role = $query->paginate($perPage);
            // Return the response with pagination meta
            return response()->json([
                'data' => $tenant_role->items(),
                'meta' => [
                    'current_page' => $tenant_role->currentPage(),
                    'last_page' => $tenant_role->lastPage(),
                    'per_page' => $tenant_role->perPage(),
                    'total' => $tenant_role->total(),
                ],
            ]);
        }
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:tenant_role',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $tenantrole = tenantrole::create([
            'name' => $request->name,
            "permission" => $request->permission,
            "description" =>$request->description,
            "status" => $request->status      
        ]);
        return response()->json([
            'message' => 'Tenant Role Created Successfully',
            'data' => new TenantsResource($tenantrole)
        ]);
    }
    public function show(Tenantrole $tenantrole)
    {
        return new TenantsResource($tenantrole);
    }
    public function update(Request $request, Tenantrole $tenantrole)
    {
        $tenantrole->update([
            'name' => $request->name,
            "permission" => $request->permission,
            "description" =>$request->description,
            "status" => $request->status      
        ]);
        return response()->json([
            'message' => 'Tenant Role Updated Successfully',
            'data' => new TenantsResource($tenantrole)
        ]);
    }    
    public function destroy(Tenantrole $tenantrole)
    {
        $tenantrole->is_delete = 'Yes';
        $tenantrole->save();
        return response()->json([
            'message' => 'Tenant Role Deleted Successfully',
        ]);
    }
    public function list(){
        $role_list = array(0=>'user',1=>'user_group');
        return  response()->json([
            'data' => new TenantsResource($role_list),
        ]);
    }
    public function menu(){
        $navItems = [
            [
                'component' => 'CNavItem',
                'name' => 'Test Report',
                'to' => '/test_report',
                'icon' => '<CIcon icon={cilChart} customClassName="nav-icon" style={{color:"white"}} />',
                'badge' => [
                    'color' => 'success',
                    'text' => 'IMP',
                ],
            ],
            [
                'component' => 'CNavTitle',
                'name' => 'Components',
            ],
            [
                'component' => 'CNavItem',
                'name' => 'Tenants',
                'to' => '/tenants',
                'icon' => '<CIcon icon={cilBuilding} customClassName="nav-icon" />',
            ],
            [
                'component' => 'CNavGroup',
                'name' => 'User Management',
                'to' => '/user',
                'icon' => '<CIcon icon={cilUser} customClassName="nav-icon" />',
                'items' => [
                    [
                        'component' => 'CNavItem',
                        'name' => 'Users',
                        'to' => '/user/users',
                    ],
                    [
                        'component' => 'CNavItem',
                        'name' => 'User Groups',
                        'to' => '/user/user_groups',
                    ],
                ],
            ],
            [
                'component' => 'CNavGroup',
                'name' => 'Campaign Mgmt',
                'to' => '/campaign',
                'icon' => '<CIcon icon={cilCursor} customClassName="nav-icon" />',
                'items' => [
                    [
                        'component' => 'CNavItem',
                        'name' => 'Campaigns',
                        'to' => '/campaign/campaigns',
                    ],
                    [
                        'component' => 'CNavItem',
                        'name' => 'Pause Codes',
                        'to' => '/campaign/pause_codes',
                    ],
                    [
                        'component' => 'CNavItem',
                        'name' => 'List Mix',
                        'to' => '/campaign/list_mix',
                    ],
                    [
                        'component' => 'CNavItem',
                        'name' => 'Dynamic Prompt',
                        'to' => '/campaign/dyn_prompt',
                    ],
                    [
                        'component' => 'CNavItem',
                        'name' => 'Conference',
                        'to' => '/campaign/conference',
                    ],
                    [
                        'component' => 'CNavItem',
                        'name' => 'SMS API Gateway',
                        'to' => '/campaign/sms_api_gateway',
                    ],
                ],
            ],
            [
                'component' => 'CNavGroup',
                'name' => 'List Mgmt',
                'to' => '/list',
                'icon' => '<CIcon icon={cilNotes} customClassName="nav-icon" />',
                'items' => [
                    [
                        'component' => 'CNavItem',
                        'name' => 'List',
                        'to' => '/list/list',
                    ],
                    [
                        'component' => 'CNavItem',
                        'name' => 'Load New Leads',
                        'to' => '/list/load_n_leads',
                    ],
                    [
                        'component' => 'CNavItem',
                        'name' => 'List Custom Fields',
                        'to' => '/list/lst_cstm_flds',
                    ],
                    [
                        'component' => 'CNavItem',
                        'name' => 'Copy Custom Fields',
                        'to' => '/list/cpy_cstm_flds',
                    ],
                    [
                        'component' => 'CNavItem',
                        'name' => 'Lead Rechurn',
                        'to' => '/list/lead_rchrn',
                    ],
                    [
                        'component' => 'CNavItem',
                        'name' => 'Callback Mgmt',
                        'to' => '/list/clbck_mgmt',
                    ],
                    [
                        'component' => 'CNavItem',
                        'name' => 'CRM Database Config',
                        'to' => '/list/crm_db_conf',
                    ],
                ],
            ],
            [
                'component' => 'CNavGroup',
                'name' => 'Inbound Mgmt',
                'to' => '/inbound',
                'icon' => '<CIcon icon={cilArrowThickFromTop} customClassName="nav-icon" />',
                'items' => [
                    [
                        'component' => 'CNavItem',
                        'name' => 'In-groups',
                        'to' => '/inbound/ingrps',
                    ],
                    [
                        'component' => 'CNavItem',
                        'name' => 'DIDs',
                        'to' => '/inbound/dids',
                    ],
                    [
                        'component' => 'CNavItem',
                        'name' => 'Call Menus',
                        'to' => '/inbound/call_menu',
                    ],
                ],
            ],
            [
                'component' => 'CNavGroup',
                'name' => 'Service & Feature',
                'to' => '/svc_n_ft',
                'icon' => '<CIcon icon={cilStar} customClassName="nav-icon" />',
                'items' => [
                    [
                        'component' => 'CNavItem',
                        'name' => 'Scripts',
                        'to' => '/svc_n_ft/scripts',
                    ],
                    [
                        'component' => 'CNavGroup',
                        'name' => 'DNC Number',
                        'to' => '/svc_n_ft/dnc',
                        'items' => [
                            [
                                'component' => 'CNavItem',
                                'name' => 'Global DNC',
                                'to' => '/svc_n_ft/dnc/global',
                            ],
                            [
                                'component' => 'CNavItem',
                                'name' => 'Local DNC',
                                'to' => '/svc_n_ft/dnc/local',
                            ],
                        ],
                    ],
                    [
                        'component' => 'CNavItem',
                        'name' => 'Number Profile',
                        'to' => '/svc_n_ft/num_prof',
                    ],
                    [
                        'component' => 'CNavItem',
                        'name' => 'Disposition Statuses',
                        'to' => '/svc_n_ft/disp_status',
                    ],
                    [
                        'component' => 'CNavItem',
                        'name' => 'Auth Token API',
                        'to' => '/svc_n_ft/auth_tkn_api',
                    ],
                ],
            ],
            [
                'component' => 'CNavGroup',
                'name' => 'Call Time Mgmt',
                'to' => '/call_time_mgmt',
                'icon' => '<CIcon icon={cilClock} customClassName="nav-icon" />',
                'items' => [
                    [
                        'component' => 'CNavItem',
                        'name' => 'Call Times',
                        'to' => '/call_time_mgmt/call_times',
                    ],
                    [
                        'component' => 'CNavItem',
                        'name' => 'State Call Times',
                        'to' => '/call_time_mgmt/state_call_times',
                    ],
                    [
                        'component' => 'CNavItem',
                        'name' => 'Shifts',
                        'to' => '/call_time_mgmt/shifts',
                    ],
                ],
            ],
            [
                'component' => 'CNavGroup',
                'name' => 'ACL Mgmt',
                'to' => '/acl_mgmt',
                'icon' => '<CIcon icon={cilFingerprint} customClassName="nav-icon" />',
                'items' => [
                    [
                        'component' => 'CNavItem',
                        'name' => 'ACL User',
                        'to' => '/acl_mgmt/acl_user',
                    ],
                    [
                        'component' => 'CNavItem',
                        'name' => 'ACL Profile',
                        'to' => '/acl_mgmt/acl_prof',
                    ],
                    [
                        'component' => 'CNavItem',
                        'name' => 'ACL User Profile Binding',
                        'to' => '/acl_mgmt/acl_usr_prof_bind',
                    ],
                ],
            ],
            [
                'component' => 'CNavGroup',
                'name' => 'Report Mgmt',
                'to' => '/report',
                'icon' => '<CIcon icon={cilBarcode} customClassName="nav-icon" />',
                'items' => [
                    [
                        'component' => 'CNavItem',
                        'name' => 'Agent Report',
                        'to' => '/report/agent',
                    ],
                    [
                        'component' => 'CNavItem',
                        'name' => 'Inbound Report',
                        'to' => '/report/inbound',
                    ],
                    [
                        'component' => 'CNavItem',
                        'name' => 'Outbound Report',
                        'to' => '/report/outbound',
                    ],
                ],
            ],
            [
                'component' => 'CNavGroup',
                'name' => 'Bulk Operations',
                'to' => '/bulk',
                'icon' => '<CIcon icon={cilBasket} customClassName="nav-icon" />',
                'items' => [
                    [
                        'component' => 'CNavItem',
                        'name' => 'User-Usergroup Mgmt',
                        'to' => '/bulk/usr_usrgrp',
                    ],
                    [
                        'component' => 'CNavItem',
                        'name' => 'User-Ingroup Mgmt',
                        'to' => '/bulk/usr_ingrp',
                    ],
                    [
                        'component' => 'CNavItem',
                        'name' => 'User-Campaign Mgmt',
                        'to' => '/bulk/usr_camp',
                    ],
                    [
                        'component' => 'CNavItem',
                        'name' => 'Bulk Phone Creation',
                        'to' => '/bulk/phn_crtn',
                    ],
                    [
                        'component' => 'CNavItem',
                        'name' => 'Bulk User Creation',
                        'to' => '/bulk/usr_crtn',
                    ],
                ],
            ],
            [
                'component' => 'CNavItem',
                'name' => 'Phones',
                'to' => '/phones',
                'icon' => '<CIcon icon={cilPhone} customClassName="nav-icon" />',
            ],
            [
                'component' => 'CNavGroup',
                'name' => 'System Management',
                'to' => '/sys',
                'icon' => '<CIcon icon={cilBell} customClassName="nav-icon" />',
                'items' => [
                    [
                        'component' => 'CNavItem',
                        'name' => 'Server Mgmt',
                        'to' => '/sys/srvr',
                    ],
                    [
                        'component' => 'CNavItem',
                        'name' => 'Third Party Auth',
                        'to' => '/sys/thrd_prty_auth',
                    ],
                    [
                        'component' => 'CNavItem',
                        'name' => 'Customize GUI',
                        'to' => '/sys/cstmz_gui',
                    ],
                ],
            ],
            [
                'component' => 'CNavGroup',
                'name' => 'Pages',
                'icon' => '<CIcon icon={cilStar} customClassName="nav-icon" />',
                'items' => [
                    [
                        'component' => 'CNavItem',
                        'name' => 'Login',
                        'to' => '/login',
                    ],
                    [
                        'component' => 'CNavItem',
                        'name' => 'Register',
                        'to' => '/register',
                    ],
                    [
                        'component' => 'CNavItem',
                        'name' => 'Error 404',
                        'to' => '/404',
                    ],
                    [
                        'component' => 'CNavItem',
                        'name' => 'Error 500',
                        'to' => '/500',
                    ],
                ],
            ],
            [
                'component' => 'CNavItem',
                'name' => 'Agent',
                'to' => '/agent',
                'icon' => '<CIcon icon={cilUserPlus} customClassName="nav-icon" />',
            ],
        ];

        return response()->json($navItems);
    }
}
