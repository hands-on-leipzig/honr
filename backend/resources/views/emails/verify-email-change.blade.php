<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Neue E-Mail-Adresse bestätigen</title>
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
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #1d4ed8;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 14px;
            color: #6b7280;
        }
        .link {
            color: #2563eb;
            word-break: break-all;
        }
        .info {
            background-color: #eff6ff;
            border-left: 4px solid #2563eb;
            padding: 12px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>HOTR - Hands-on Recognition</h1>
    </div>

    <h2>Neue E-Mail-Adresse bestätigen</h2>

    <p>Hallo {{ $user->nickname }},</p>

    <p>du hast eine Änderung deiner E-Mail-Adresse angefordert. Bitte bestätige deine neue E-Mail-Adresse:</p>

    <div class="info">
        <strong>Neue E-Mail-Adresse:</strong> {{ $newEmail }}
    </div>

    <p style="text-align: center;">
        <a href="{{ $verificationUrl }}" class="button">E-Mail-Adresse bestätigen</a>
    </p>

    <p>Falls der Button nicht funktioniert, kopiere diesen Link in deinen Browser:</p>
    <p class="link">{{ $verificationUrl }}</p>

    <p>Dieser Link ist 24 Stunden gültig.</p>

    <p>Falls du diese Änderung nicht angefordert hast, kannst du diese E-Mail ignorieren. Deine E-Mail-Adresse bleibt unverändert.</p>

    <div class="footer">
        <p>Mit freundlichen Grüßen,<br>Das HOTR-Team</p>
        <p style="font-size: 12px; color: #9ca3af;">
            Falls du Probleme hast, kontaktiere uns unter: honr@hands-on-technology.org
        </p>
    </div>
</body>
</html>

