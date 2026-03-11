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

    <br>
    <p>
        <a href="{{ $downloadUrl }}"
            style="background:#4CAF50;color:#fff;padding:10px 20px;text-decoration:none;">
            Download Report
        </a>
    </p>

    <p>This link will expire in 24 hours.</p>

</body>

</html>