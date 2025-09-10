<?php

namespace App\Repositories;

use App\Http\Response\CustomApiResponse;
use App\Models\ListTemplateMap;
use App\Models\MailTemplate;
use Illuminate\Http\Request;
use Exception;

class ListTemplateMapRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function store(Request $request)
    {
        try {
            $listId = $request->list_id;
            $templateIds = $request->template_ids;

            // delete old mappings
            ListTemplateMap::where('list_id', $listId)->delete();

            // insert new mappings
            foreach ($templateIds as $templateId) {
                ListTemplateMap::create([
                    'list_id' => $listId,
                    'template_id' => $templateId,
                ]);
            }

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                null,
                trans('message.mapping_modified')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in storing list-template mapping: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function getByList(int $listId)
    {
        try {
            $templates = MailTemplate::select('mail_template.id', 'mail_template.title', 'mail_template.subject')
                ->join('list_template_maps', 'mail_template.id', '=', 'list_template_maps.template_id')
                ->where('list_template_maps.list_id', $listId)->where('mail_template.status', 'active')
                ->get();

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $templates,
                trans('message.data_fetch')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in fetching mapped templates: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
