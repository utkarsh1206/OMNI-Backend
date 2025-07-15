<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\ProductCustomField;
use App\Http\Response\CustomApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductCustomFieldsRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list(Request $request)
    {
        try {
            $query = ProductCustomField::query();

            if ($request->has('product_id')) {
                $query->where('product_id', $request->product_id);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $query->get(),
                trans('message.data_fetch')
            );
        } catch (\Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function create($request)
    {
        generateLogs(
            'info',
            'ProductCustomFieldsRepository@create:- Creating custom fields for product' .
                json_encode($request)
        );
        DB::beginTransaction();
        try {
            generateLogs('info', 'Inside Try Block');
            $productId = $request->input('product_id');
            $formItems = $request->input('form_items');
            $inserted = [];
            generateLogs(
                'info',
                'ProductCustomFieldsRepository@create:- Product ID: ' . $productId
            );

            // Delete old records (optional)
            ProductCustomField::where('product_id', $productId)->delete();

            generateLogs(
                'info',
                'ProductCustomFieldsRepository@create:- Deleted old fields for product ID: ' . $productId
            );

            foreach ($formItems as $sectionName => $columns) {
                generateLogs(
                    'info',
                    'ProductCustomFieldsRepository@create:- Processing section: ' . $sectionName
                );
                foreach ($columns as $column => $fields) {
                    generateLogs(
                        'info',
                        'ProductCustomFieldsRepository@create:- Processing column: ' . $column
                    );
                    foreach ($fields as $index => $field) {
                        generateLogs(
                            'info',
                            'ProductCustomFieldsRepository@create:- Processing field: ' . json_encode($field)
                        );
                        $field['product_id'] = $productId;
                        generateLogs('info', 'Product ID: ' . $field['product_id']);
                        $field['section_name'] = $sectionName;
                        generateLogs('info', 'Section Name: ' . $field['section_name']);
                        $field['field_column'] = $column;
                        generateLogs('info', 'Field Column: ' . $field['field_column']);
                        $field['field_order'] = $index + 1;
                        generateLogs('info', 'Field Order: ' . $field['field_order']);

                        unset($field['id'], $field['created_at'], $field['updated_at']);
                        generateLogs('info', 'Field Data unset');

                        $inserted[] = ProductCustomField::create($field);
                        generateLogs('info', 'Field Inserted: ');
                    }
                }
            }
            generateLogs(
                'info',
                'ProductCustomFieldsRepository@create:- Inserted Fields: ' . json_encode($inserted)
            );
            DB::commit();
            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $inserted,
                trans('message.list_field_create_success')
            );
        } catch (\Exception $e) {
            DB::rollBack();
            generateLogs(
                'error',
                'ProductCustomFieldsRepository@create Error: ' . $e->getMessage()
            );
            generateLogs(
                'error',
                'ProductCustomFieldsRepository@create Stack Trace: ' . $e->getTraceAsString()
            );

            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function show($id)
    {
        $field = ProductCustomField::findOrFail($id);
        return $this->apiResponse->getResponseStructure(
            trans('message.success'),
            $field,
            trans('message.data_fetch')
        );
    }

    public function update($request, $id)
    {
        try {
            $rules = [
                'field_name' => 'required|string|max:255',
                'field_label' => 'required|string|max:255',
                'field_type' => ['required', Rule::in(
                    ['text', 'select', 'multi', 'checkbox', 'radio', 'textarea', 'date', 'number']
                )],
                'description' => 'nullable|string|max:500',
                'options' => 'nullable|string',
                'mandatory' => ['required', Rule::in(['Y', 'N'])],
                'default_value' => 'nullable|string|max:255',
                'status' => ['required', Rule::in(['active', 'inactive'])],
                'section_name' => ['required', 'string', 'max:255'],
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return $this->apiResponse->getResponseStructure(
                    trans('message.error'),
                    [],
                    $validator->errors()->first()
                );
            }
            $field = ProductCustomField::findOrFail($id);
            $field->update($validator->validated());
            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $field,
                trans('message.list_field_update_success')
            );
        } catch (\Exception $e) {
            generateLogs('error', 'Error in update function: ' . $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function delete($id)
    {
        try {
            $field = ProductCustomField::findOrFail($id);
            $field->delete();
            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $id,
                trans('message.list_field_delete_success')
            );
        } catch (\Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
