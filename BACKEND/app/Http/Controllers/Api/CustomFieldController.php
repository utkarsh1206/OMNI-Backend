<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\EcdialLists;
use App\Models\EcdialListField;

class CustomFieldController extends Controller
{
    public function custumFieldHeader(Request $request)
    {
        $cdr_list = $request->input('cdr_list');
        $export = $request->input('export');
        $list_ids = $this->getListIds($cdr_list);
        $isNumberHidingOn = $request->input('isNumberHidingOn', 'N');
        $fieldsToExclude = $this->getFieldsToExclude($isNumberHidingOn);

        $custom_headers_list = [];
        foreach ($list_ids as $list) {
            $list_id = $list->list_id;
            $fieldsArray = $this->getFields($list_id, $fieldsToExclude);

            $custom_headers_list[$list_id] = [
                'fields' => $fieldsArray,
                'export' => $export,
            ];
        }

        return response()->json($custom_headers_list);
    }

    public function custumFieldvalue(Request $request)
    {
        $cdr_list = $request->input('cdr_list');
        $custom_list_id = $request->input('custom_list_id');
        $closecallid = $request->input('closecallid');
        $isNumberHidingOn = $request->input('isNumberHidingOn', 'N');
        $export = $request->input('Export', 'NO');

        $list_ids = $this->getListIds($cdr_list);
        $fieldsToExclude = $this->getFieldsToExclude($isNumberHidingOn);

        foreach ($list_ids as $list) {
            $list_id = $list->list_id;
            if ($list_id == $custom_list_id) {
                return response()->json($this->getCdrData($list_id, $closecallid, $fieldsToExclude, $export));
            }
        }

        return response()->json(['data' => []]); // Return an empty data if no matching list_id is found
    }

    private function getListIds($cdr_list)
    {
        $query = EcdialLists::query();

        if (!empty($cdr_list)) {
            $query->where('list_id', $cdr_list);
        }

        // Execute the query
        $results = $query->select('list_id')->get();

        return $results;
    }

    private function getFieldsToExclude($isNumberHidingOn)
    {
        $fieldsToExclude = ['country_code', 'phone_code', 'phone_number'];
        if ($isNumberHidingOn === 'Y') {
            $fieldsToExclude = array_merge($fieldsToExclude, ['alt_phone', 'address3']);
        }
        return $fieldsToExclude;
    }

    private function getFields($list_id, $fieldsToExclude)
    {
        $fields = EcdialListField::where('list_id', $list_id)
            ->whereNotIn('field_label', $fieldsToExclude)
            ->where('use_in_cdr', 'Y')
            ->orderBy('field_rank')
            ->orderBy('field_order')
            ->get();
        generateLogs('info', 'Fetched raw fields: ' . $fields);

        $mappedFields = $fields->map(function ($field) {
            return [
                'label' => $field->field_label,
                'type' => $field->field_type,
                'label_name' => $field->field_name,
                'editable' => $field->editable,
            ];
        })
            ->toArray();

        return $mappedFields;
    }

    private function getCdrData($list_id, $closecallid, $fieldsToExclude, $export)
    {
        $fields = EcdialListField::where('list_id', $list_id)
            ->whereNotIn('field_label', $fieldsToExclude)
            ->where('use_in_cdr', 'Y')
            ->orderBy('field_rank')
            ->orderBy('field_order')
            ->get();

        $custom_cdr_fields = $fields->pluck('field_label')->toArray();
        $custom_cdr_fields_string = implode(',', $custom_cdr_fields);
        $custom_cdr_data = DB::table('custom_cdr_' . $list_id)
            ->selectRaw($custom_cdr_fields_string)
            ->where('closecallid', $closecallid)
            ->first();

        $cdrData = [];
        if ($custom_cdr_data) {
            foreach ($fields as $field) {
                $value = $custom_cdr_data->{$field->field_label} ?? '-';
                $cdrData[] = [
                    'label' => $field->field_label,
                    'type' => $field->field_type,
                    'label_name' => $field->field_name,
                    'value' => ($export === 'NO' || $export === 'PDF') ? $value : preg_replace('/\n|\t|\r/', ' ', $value),
                    'editable' => $field->editable,
                ];
            }
        }

        return ['data' => $cdrData];
    }
}
