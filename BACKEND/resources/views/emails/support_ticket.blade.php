@if (empty($content))

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Support Ticket Created</title>
    <style>
        /* .header {
            background: #2563eb;
            color: #ffffff;
            padding: 5px 5px;
            border-radius: 8px 8px 0 0;
        }

        .content {
            padding: 10px;
            font-size: 15px;
            line-height: 1.6;
            color: #333333;
        }

        .ticket-info {
            background: #f1f5f9;
            border-left: 4px solid #2563eb;
            margin: 5px 0;
            padding: 5px;
            border-radius: 4px;
        } */
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <!-- <div class="header">
            <h2>🎫 New Support Ticket Created</h2>
        </div> -->

        <!-- Content -->
        <div class="content">
            <p>Hello,</p>
            <p>A new support ticket has been created with the following details:</p>

            <!-- Ticket Info -->
            <div class="ticket-info">
                <p><strong>Ticket ID:</strong> {{ $ticket->support_ticket_id }}</p>
                <p><strong>Subject:</strong> {{ $ticket->subject ?? $subject }}</p>
                <p><strong>Status:</strong> {{ $ticket->status_name }}</p>
                <p><strong>Created At:</strong> {{ $ticket->created_at->timezone(config('app.timezone'))->format('d M Y, H:i:s A T') }}</p>
            </div>

            <!-- User Message -->
            <p><strong>Message:</strong></p>
            <p>{!! nl2br(e($ticket->description)) !!}</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Thank you,<br>Support Team</p>
        </div>
    </div>
</body>

</html>
<!-- Render the provided template content if $content is not empty -->
@else
{!! $content !!}
@endif