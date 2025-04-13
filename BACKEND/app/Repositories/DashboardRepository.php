<?php

namespace App\Repositories;

use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Department;
use App\Models\IncidentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Response\CustomApiResponse;
use Exception;

class DashboardRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    private function extractPayload(Request $request)
    {
        return extractPayload($request->bearerToken());
    }

    public function index(Request $request)
    {
        try {
            $payload = $this->extractPayload($request);
            $applyAgentFilter = $payload && $payload['role_id'] == 3;
            $userId = $payload ? $payload['id'] : null;

            // Helper function to apply filter conditionally
            $conditionalFilter = function ($query) use ($applyAgentFilter, $userId) {
                if ($applyAgentFilter) {
                    $query->where('assign_to_id', $userId);
                }
            };

            $totalUsers = User::where('type', '!=', 0)->count();

            //Total Accounts
            $totalAccounts = Account::count();

            // // Total Ticket Count
            // $totalTicketCount = SupportTicket::count();
            // Total Ticket Count
            $totalTicketCount = SupportTicket::where($conditionalFilter)->count();

            // Total Pending Tickets
            $totalPendingTickets = SupportTicket::where($conditionalFilter)->whereNotIn('default_status', ['Resolved', 'Closed'])->count();

            // Total Departments
            $totalDepartments = Department::count();

            // Total Contacts
            $totalContacts = Contact::count();

            // Tickets by status
            $ticketsByStatus = SupportTicket::where($conditionalFilter)->select(DB::raw('default_status, COUNT(*) as count'))
                ->groupBy('default_status')
                ->get()
                ->keyBy('default_status');

            // Tickets by priority
            $ticketsByPriority = SupportTicket::where($conditionalFilter)->select(DB::raw('default_priority, COUNT(*) as count'))
                ->groupBy('default_priority')
                ->get()
                ->keyBy('default_priority');

            // Tickets by department
            $ticketsByDepartment = SupportTicket::where($conditionalFilter)->select(DB::raw('department_id, COUNT(*) as count'))
                ->groupBy('department_id')
                ->get()
                ->keyBy('department_id');

            // // Pending tickets by agent (assign_to_id represents agent)
            // $pendingTicketsByAgent = SupportTicket::whereNotIn('default_status', ['Resolved', 'Closed'])
            //     ->select('assign_to_id', DB::raw('COUNT(*) as count'))
            //     ->groupBy('assign_to_id')
            //     ->get();

            // // Get agent names (from users table)
            // $agents = User::whereIn('id', $pendingTicketsByAgent->pluck('assign_to_id'))->get()->keyBy('id');

            // Pending tickets by agent (assign_to_id represents agent)
            $pendingTicketsByAgent = SupportTicket::where($conditionalFilter)->whereNotIn('default_status', ['Resolved', 'Closed'])
                ->select('assign_to_id', DB::raw('COUNT(*) as count'))
                ->groupBy('assign_to_id')
                ->orderByDesc(DB::raw('COUNT(*)'))  // Order by count descending
                ->take(5)  // Take only top 5 agents
                ->get();

            // Get agent names (from users table)
            $agents = User::whereIn('id', $pendingTicketsByAgent->pluck('assign_to_id'))
                ->get()
                ->keyBy('id');

            // Tickets by incident type (incident_id is a reference to incident_types table)
            $ticketsByIncidentType = SupportTicket::where($conditionalFilter)->select('incident_id', DB::raw('COUNT(*) as count'))
                ->groupBy('incident_id')
                ->get();

            // Get incident names
            $incidentTypes = IncidentType::whereIn('id', $ticketsByIncidentType->pluck('incident_id'))->get()->keyBy('id');

            // Tickets by product type (product_id is a reference to product_types table)
            $ticketsByProductType = SupportTicket::where($conditionalFilter)->select('product_id', DB::raw('COUNT(*) as count'))
                ->groupBy('product_id')
                ->get();

            // Get product names
            // $productTypes = Product::whereIn('id', $ticketsByProductType->pluck('product_id'))->get()->keyBy('id');

            // Tickets by type
            $ticketsByType = SupportTicket::where($conditionalFilter)->select('default_type', DB::raw('COUNT(*) as count'))
                ->groupBy('default_type')
                ->get()
                ->keyBy('default_type');

            // Activity summary (Tickets created today, last 7 days, last 30 days, and updated vs. created)
            $activitySummary = [
                'today' => SupportTicket::where($conditionalFilter)->whereDate('created_at', now()->toDateString())->count(),
                'last_7_days' => SupportTicket::where($conditionalFilter)->where('created_at', '>=', now()->subDays(7))->count(),
                'last_30_days' => SupportTicket::where($conditionalFilter)->where('created_at', '>=', now()->subDays(30))->count(),
            ];

            // Deleted ticket count
            // $deletedTicketCount = SupportTicket::count();
            //

            // Combine all the data into a single response
            $dashboardData = [
                'total_users' => $totalUsers,
                'total_accounts' => $totalAccounts,
                'total_pending_tickets' => $totalPendingTickets,
                'total_departments' => $totalDepartments,
                'total_contacts' => $totalContacts,
                'total_ticket_count' => $totalTicketCount,
                'tickets_by_status' => $ticketsByStatus,
                'tickets_by_priority' => $ticketsByPriority,
                'tickets_by_department' => $ticketsByDepartment,
                'pending_tickets_by_agent' => $pendingTicketsByAgent->map(function ($ticket) use ($agents) {
                    $ticket->agent_name = $agents->get($ticket->assign_to_id)->first_name ?? 'Unknown Agent';
                    return $ticket;
                }),
                'tickets_by_incident_type' => $ticketsByIncidentType->map(function ($ticket) use ($incidentTypes) {
                    $ticket->incident_name = $incidentTypes->get($ticket->incident_id)->name ?? 'Unknown Incident Type';
                    return $ticket;
                }),
                // 'tickets_by_product' => $ticketsByProductType->map(function ($ticket) use ($productTypes) {
                //     $ticket->product_name = $productTypes->get($ticket->product_id)->name ?? 'Unknown Product';
                //     return $ticket;
                // }),
                'tickets_by_product' => $ticketsByProductType,
                'tickets_by_type' => $ticketsByType,
                'activity_summary' => $activitySummary,
                // 'deleted_ticket_count' => $deletedTicketCount
            ];
            return $this->apiResponse->getResponseStructure(trans('message.success'), $dashboardData, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating data of dashboard: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
