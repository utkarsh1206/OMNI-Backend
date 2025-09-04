<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f9fafb;
      padding: 20px;
    }

    .card {
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 20px;
      max-width: 680px;
      margin: 0 auto;
    }

    .title {
      font-size: 18px;
      font-weight: bold;
      margin-bottom: 12px;
    }

    .ticket-info {
      margin: 10px 0;
    }

    .footer {
      margin-top: 20px;
      font-size: 12px;
      color: #666;
    }
  </style>
</head>

<body>
  <div class="card">
    <div class="title">
      @switch($type)
      @case('alert_before_response_email')
      SLA Reminder: First Response Pending
      @break

      @case('first_response_email')
      SLA Breach: First Response Not Given
      @break

      @case('alert_before_resolve_email')
      SLA Reminder: Resolution Due Soon
      @break

      @case('resolve_time_email')
      SLA Breach: Resolution Not Met
      @break

      @default
      Support Ticket Notification
      @endswitch
    </div>

    @if (empty($content))
    <p>Hello {{ $ticket->assigned_agent_name ?? 'Agent' }},</p>

    @switch($type)
    @case('alert_before_response_email')
    <p>This is a friendly reminder that the <strong>first response SLA</strong> for Ticket <strong>#{{ $ticket->support_ticket_id }}</strong> is approaching.</p>
    @break

    @case('first_response_email')
    <p>The <strong>first response SLA</strong> for Ticket <strong>#{{ $ticket->support_ticket_id }}</strong> has been <strong>breached</strong>.</p>
    @break

    @case('alert_before_resolve_email')
    <p>This is a reminder that the <strong>resolution SLA</strong> for Ticket <strong>#{{ $ticket->support_ticket_id }}</strong> is approaching.</p>
    @break

    @case('resolve_time_email')
    <p>The <strong>resolution SLA</strong> for Ticket <strong>#{{ $ticket->support_ticket_id }}</strong> has been <strong>breached</strong>.</p>
    @break

    @default
    <p>This is an update regarding Ticket <strong>#{{ $ticket->support_ticket_id }}</strong>.</p>
    @endswitch

    @endif
    
    <div class="ticket-info">
      @if (empty($content))
      <p><strong>Subject:</strong> {{ $ticket->subject }}</p>
      <p><strong>Created On:</strong> {{ optional($ticket->created_at)->format('Y-m-d H:i') }}</p>
      <p><strong>Assigned To:</strong> {{ $ticket->assigned_agent_name ?? 'N/A' }}</p>

      @if($type === 'alert_before_response_email' || $type === 'first_response_email')
      <p><strong>First Response Due:</strong> {{ optional($ticket->first_response_time)->format('Y-m-d H:i') }}</p>
      @elseif($type === 'alert_before_resolve_email' || $type === 'resolve_time_email')
      <p><strong>Resolution Due:</strong> {{ optional($ticket->resolved_time)->format('Y-m-d H:i') }}</p>
      @endif
      @else
      <!-- Render the provided template content if $content is not empty -->
      {!! $content !!}
      @endif
    </div>

    @if (empty($content))
    <div class="footer">
      <p>Please log in to the support portal for more details.</p>
    </div>
    @endif
  </div>
</body>

</html>