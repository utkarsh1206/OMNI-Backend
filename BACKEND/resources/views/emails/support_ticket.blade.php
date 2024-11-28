<!-- resources/views/emails/support_ticket.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Ticket</title>
</head>
<body>
    <h1>Support Ticket Details</h1>

    <p><strong>Support Ticket No.:</strong> {{ $ticket->ticket_no }}</p>
    <p><strong>Priority:</strong> {{ $ticket->priority }}</p>
    <p><strong>Status:</strong> {{ $ticket->status }}</p>
    <p><strong>Subject:</strong> {{ $ticket->subject }}</p>
    <p><strong>Body:</strong> {{ $ticket->body }}</p>

</body>
</html>
