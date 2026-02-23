<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $schedule->subject ?? 'Scheduled Report' }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6;">

    <h2>{{ $schedule->report_name ?? 'Scheduled Report' }}</h2>

    @if(!empty($schedule->body))
        <p>
            {{ $schedule->body }}
        </p>
    @endif

    <p>
        Please find attached your scheduled report.
    </p>

</body>
</html>
