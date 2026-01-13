<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmailScheduleRequest;
use App\Models\EmailSchedule;
use App\Repositories\EmailScheduleRepository;
use Illuminate\Http\Request;

class EmailScheduleController extends Controller
{
    protected $emailScheduleRepository;

    public function __construct(EmailScheduleRepository $emailScheduleRepository)
    {
        $this->emailScheduleRepository = $emailScheduleRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->emailScheduleRepository->list($request);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EmailScheduleRequest $request)
    {
        return $this->emailScheduleRepository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(EmailSchedule $email_schedule)
    {
        return $this->emailScheduleRepository->show($email_schedule);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmailSchedule $email_schedule)
    {
        return $this->emailScheduleRepository->update($request, $email_schedule);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmailSchedule $email_schedule)
    {
        return $this->emailScheduleRepository->delete($email_schedule);
    }
}
