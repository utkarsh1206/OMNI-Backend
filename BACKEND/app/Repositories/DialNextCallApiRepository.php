<?php

namespace App\Repositories;

use App\Http\Requests\DialNextCallApiRequest;
use App\Http\Response\CustomApiResponse;
use App\Models\EcdialHopper;
use App\Models\EcdialList;
use App\Models\LiveAgents;
use App\Services\ApiRequestService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DialNextCallApiRepository
{
    private CustomApiResponse $apiResponse;
    private ApiRequestService $apiRequestService;

    public function __construct(
        CustomApiResponse $customApiResponse,
        ApiRequestService $apiRequestService
    ) {
        $this->apiResponse = $customApiResponse;
        $this->apiRequestService = $apiRequestService;
    }

    public function dialNextCall($request)
    {
        DB::beginTransaction();

        try {
            /** Get paused agent */
            $agent = $this->getPausedAgent($request->id);

            if (!$agent) {
                return $this->apiResponse->getResponseStructure(
                    trans('message.error'),
                    null,
                    trans('message.user_not_found')
                );
            }

            /** Get next lead */
            $lead = $this->getNextLead($agent->campaign_id);

            if (!$lead) {
                return $this->apiResponse->getResponseStructure(
                    trans('message.error'),
                    null,
                    trans('message.lead_not_found')
                );
            }

            /** Get mobile number */
            $mobile = $this->getLeadMobile($lead->lead_id, $lead->list_id);

            if (!$mobile) {
                return $this->apiResponse->getResponseStructure(
                    trans('message.error'),
                    null,
                    trans('message.phone_not_found')
                );
            }

            $payload = [
                'dialed_phone_number' => $mobile,
            ];

            $request->merge($payload);

            /** Call external API AFTER getting number */
            $apiResult = $this->apiRequestService->handleApiRequest(
                $request,
                'agent_dial_call',
                DialNextCallApiRequest::class
            );

            if ($apiResult instanceof \Illuminate\Http\JsonResponse) {
                $apiResult = $apiResult->getData(true);
            }

            if (!isset($apiResult['code']) || $apiResult['code'] !== 0) {
                return $apiResult;
            }

            $uniqueId = $apiResult['data']['UniqueId'];

            /** Update DB after successful API call */
            $responseData = $this->updateAfterSuccessfulDial(
                $request->id,
                $lead,
                $mobile,
                $uniqueId
            );

            DB::commit();

            return $this->apiResponse
                ->getResponseStructure(
                    trans('message.success'),
                    $responseData,
                    trans('message.success')
                )->original + ['neox_status' => true];
        } catch (Exception $e) {
            DB::rollBack();
            generateLogs('error', 'DialNextCall failed', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    /* =======================
       Helper Methods
    ======================== */

    private function getPausedAgent($userId)
    {
        return LiveAgents::where('user_id', $userId)
            ->where('status', 'pause')
            ->first();
    }

    private function getNextLead($campaignId)
    {
        return EcdialHopper::where('campaign_id', $campaignId)
            ->orderBy('lead_id')
            ->first();
    }

    private function getLeadMobile($leadId, $listId)
    {
        $lead = DB::table('ecdial_list as l')
            ->join('custom_lead_' . $listId . ' as c', 'c.id', '=', 'l.lead_id')
            ->where('l.status', 'NEW')
            ->where('l.lead_id', $leadId)
            ->select('c.mobile')
            ->first();

        return $lead->mobile ?? null;
    }

    private function updateAfterSuccessfulDial($userId, $lead, $mobile, $uniqueId)
    {
        $calledSinceLastReset = $this->updateCalledSinceLastReset('N');

        EcdialList::where('lead_id', $lead->lead_id)
            ->where('list_id', $lead->list_id)
            ->update([
                'status' => 'INCALL',
                'user'   => $userId,
                'called_since_last_reset' => $calledSinceLastReset,
                'called_count' => DB::raw('called_count + 1'),
                'modify_date' => now(),
                'last_local_call_time' => now(),
            ]);

        EcdialHopper::where('lead_id', $lead->lead_id)->where('list_id', $lead->list_id)->delete();

        LiveAgents::where('user_id', $userId)->update([
            'status' => 'INCALL',
            'uniqueid' => $uniqueId,
            'lead_id' => $lead->lead_id,
            'customer' => $mobile,
            'updated_at' => Carbon::now(),
        ]);

        return [
            'lead_id' => $lead->lead_id,
            'list_id' => $lead->list_id,
            'user_id' => $userId,
            'status' => 'INCALL',
            'customer' => $mobile,
            'uniqueid' => $uniqueId,
            'called_since_last_reset' => $calledSinceLastReset,
            'call_time' => now(),
        ];
    }

    private function updateCalledSinceLastReset($value)
    {
        if (str_contains($value, 'Y')) {
            $number = (int) str_replace('Y', '', $value);
            return 'Y' . (++$number);
        }

        return 'Y';
    }
}
