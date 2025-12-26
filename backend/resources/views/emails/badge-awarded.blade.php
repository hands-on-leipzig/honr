<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Badge erreicht</title>
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
        .badge-info {
            text-align: center;
            margin: 20px 0;
        }
        .badge-level {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
        }
        .level-1 { color: #6b7280; }
        .level-2 { color: #CD7F32; }
        .level-3 { color: #C0C0C0; }
        .level-4 { color: #FFD700; }
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

    <h2>Badge erreicht!</h2>

    <p>Hallo {{ $user->nickname }},</p>

    <div class="success-box">
        <p><strong>Herzlichen Glückwunsch! Du hast ein neues Badge-Level erreicht.</strong></p>
    </div>

    <div class="badge-info">
        @if($logoPath)
        <img src="{{ config('app.url') }}/storage/{{ $logoPath }}" alt="{{ $roleName }}" style="max-width: 80px; height: auto; margin: 10px 0;">
        @endif
        <p><strong>{{ $roleName }}</strong></p>
        <div class="badge-level level-{{ $newLevel }}">
            @if($newLevel === 1)
                Basis
            @elseif($newLevel === 2)
                Bronze
            @elseif($newLevel === 3)
                Silber
            @elseif($newLevel === 4)
                Gold
            @else
                Level {{ $newLevel }}
            @endif
        </div>
        <p style="color: #6b7280; font-size: 14px;">{{ $engagementCount }} anerkannte Einsätze</p>
    </div>

    <div class="info-box">
        <p>Du hast jetzt <strong>{{ $engagementCount }}</strong> anerkannte Einsätze in der Rolle <strong>{{ $roleName }}</strong>.</p>
        @if($newLevel < 4)
        <p style="margin-top: 12px;">
            @if($newLevel === 1)
                Nächstes Level: Bronze (5 Einsätze)
            @elseif($newLevel === 2)
                Nächstes Level: Silber (20 Einsätze)
            @elseif($newLevel === 3)
                Nächstes Level: Gold (50 Einsätze)
            @endif
        </p>
        @endif
    </div>

    <p>Weiter so! Dein Engagement wird geschätzt.</p>

    <div class="footer">
        <p>Mit freundlichen Grüßen,<br>Das HONR-Team</p>
        <p style="font-size: 12px; color: #9ca3af;">
            Falls du Fragen hast, kontaktiere uns unter: honr@hands-on-technology.org
        </p>
    </div>
</body>
</html>

