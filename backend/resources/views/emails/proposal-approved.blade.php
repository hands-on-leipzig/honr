<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vorschlag genehmigt</title>
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

    <h2>Vorschlag genehmigt</h2>

    <p>Hallo {{ $user->nickname }},</p>

    <div class="success-box">
        <p><strong>Dein Vorschlag wurde genehmigt!</strong></p>
    </div>

    <div class="info-box">
        <p><strong>Art des Vorschlags:</strong> 
        @if($proposalType === 'level')
            Level
        @elseif($proposalType === 'role')
            Rolle
        @elseif($proposalType === 'country')
            Land
        @elseif($proposalType === 'location')
            Standort
        @elseif($proposalType === 'event')
            Veranstaltung
        @else
            {{ $proposalType }}
        @endif
        </p>
        
        <p><strong>Name:</strong> {{ $proposalName }}</p>
        
        @if($proposalDescription)
        <p><strong>Beschreibung:</strong> {{ $proposalDescription }}</p>
        @endif

        @if($proposalType === 'role' && isset($additionalContext['first_program']))
        <p><strong>Programm:</strong> {{ $additionalContext['first_program'] }}</p>
        @endif

        @if($proposalType === 'event' && isset($additionalContext))
        <p><strong>Veranstaltungsdetails:</strong></p>
        <ul style="margin-left: 20px; margin-top: 8px;">
            @if(isset($additionalContext['program']))
            <li>Programm: {{ $additionalContext['program'] }}</li>
            @endif
            @if(isset($additionalContext['season']))
            <li>Saison: {{ $additionalContext['season'] }}</li>
            @endif
            @if(isset($additionalContext['level']))
            <li>Level: {{ $additionalContext['level'] }}</li>
            @endif
            @if(isset($additionalContext['location']))
            <li>Standort: {{ $additionalContext['location'] }}</li>
            @endif
            @if(isset($additionalContext['date']))
            <li>Datum: {{ $additionalContext['date'] }}</li>
            @endif
        </ul>
        @endif

        @if($proposalType === 'location' && isset($additionalContext['country']))
        <p><strong>Land:</strong> {{ $additionalContext['country'] }}</p>
        @endif
    </div>

    <p>Dein Vorschlag ist jetzt für alle Benutzer verfügbar und kann bei der Erfassung von Engagements verwendet werden.</p>

    <div class="footer">
        <p>Mit freundlichen Grüßen,<br>Das HONR-Team</p>
        <p style="font-size: 12px; color: #9ca3af;">
            Falls du Fragen hast, kontaktiere uns unter: honr@hands-on-technology.org
        </p>
    </div>
</body>
</html>

