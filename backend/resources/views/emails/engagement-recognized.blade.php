<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Einsatz anerkannt</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .success-box {
            background-color: #d1fae5;
            border-left: 4px solid #10b981;
            padding: 16px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-box {
            background-color: #eff6ff;
            border-left: 4px solid #2563eb;
            padding: 16px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 14px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>HONR - Hands-on Recognition</h1>
    </div>

    <h2>Einsatz anerkannt</h2>

    <p>Hallo {{ $user->nickname }},</p>

    <div class="success-box">
        <p><strong>Dein Einsatz wurde anerkannt!</strong></p>
    </div>

    <div class="info-box">
        <p><strong>Rolle:</strong> {{ $engagement->role->name }}</p>
        @if($engagement->role->firstProgram)
        <p><strong>Programm:</strong> {{ $engagement->role->firstProgram->name }}</p>
        @endif
        
        <p><strong>Veranstaltung:</strong></p>
        <ul style="margin-left: 20px; margin-top: 8px;">
            <li>Datum: {{ \Carbon\Carbon::parse($engagement->event->date)->format('d.m.Y') }}</li>
            @if($engagement->event->level)
            <li>Level: {{ $engagement->event->level->name }}</li>
            @endif
            @if($engagement->event->location)
            <li>Standort: {{ $engagement->event->location->name }}{{ $engagement->event->location->city ? ', ' . $engagement->event->location->city : '' }}</li>
            @endif
            @if($engagement->event->season)
            <li>Saison: {{ $engagement->event->season->name }}</li>
            @endif
        </ul>
    </div>

    <p>Dein Einsatz zählt jetzt zu deinen anerkannten Engagements und wird bei der Berechnung deiner Badges berücksichtigt.</p>

    <div class="footer">
        <p>Mit freundlichen Grüßen,<br>Das HONR-Team</p>
        <p style="font-size: 12px; color: #9ca3af;">
            Falls du Fragen hast, kontaktiere uns unter: honr@hands-on-technology.org
        </p>
    </div>
</body>
</html>

