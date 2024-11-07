<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EcdialInboundGroup;
use Illuminate\Http\Request;

class IngroupListController extends Controller
{
    public function index(Request $request)
    {
        $acl_ingroup_qstring = '';

        $query = EcdialInboundGroup::query();

        // Apply ACL logic if needed (this is a placeholder)
        if ($acl_ingroup_qstring) {
            // Assuming it needs to be a where clause
            $query->whereRaw($acl_ingroup_qstring);
        }

        // Fetch the results
        $groups = $query->orderBy('group_id')->get(['group_id', 'group_name']);

        // Return the response
        return response()->json($groups);
    }
}
