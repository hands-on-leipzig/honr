<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'HONR')</title>
    <style>
        /* Base Styles */
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Header */
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        /* Content Boxes */
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
        
        .error-box {
            background-color: #fee2e2;
            border-left: 4px solid #ef4444;
            padding: 16px;
            margin: 20px 0;
            border-radius: 4px;
        }
        
        .warning-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 16px;
            margin: 20px 0;
            border-radius: 4px;
        }
        
        .reason-box {
            background-color: #fee2e2;
            border-left: 4px solid #ef4444;
            padding: 16px;
            margin: 20px 0;
            border-radius: 4px;
        }
        
        /* Password Box (for invitation emails) */
        .password-box {
            background-color: #f3f4f6;
            border: 2px solid #d1d5db;
            padding: 16px;
            margin: 20px 0;
            border-radius: 4px;
            text-align: center;
            font-family: 'Courier New', monospace;
            font-size: 18px;
            font-weight: bold;
            color: #1f2937;
        }
        
        /* Buttons */
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
        
        /* Links */
        .link {
            color: #2563eb;
            word-break: break-all;
        }
        
        /* Lists */
        .steps {
            margin: 20px 0;
            padding-left: 20px;
        }
        .steps li {
            margin: 10px 0;
        }
        
        /* Badge Info (for badge emails) */
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
        
        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 14px;
            color: #6b7280;
        }
        .footer-small {
            font-size: 12px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>HONR - Hands-on Recognition</h1>
    </div>

    @yield('content')

    <div class="footer">
        <p>Mit freundlichen Grüßen,<br>Das HONR-Team</p>
        <p class="footer-small">
            Falls du Fragen hast, kontaktiere uns unter: honr@hands-on-technology.org
        </p>
    </div>
</body>
</html>

