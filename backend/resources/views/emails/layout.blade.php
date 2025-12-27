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
            color: #1f2937;
            background-color: #f9fafb;
            margin: 0;
            padding: 0;
        }
        
        /* Email Container */
        .email-wrapper {
            background-color: #f9fafb;
            padding: 20px 0;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .email-content {
            padding: 40px 30px;
        }
        
        /* Header */
        .header {
            background: linear-gradient(135deg, #FF6B35 0%, #F97316 100%);
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }
        
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        
        .header-subtitle {
            margin-top: 8px;
            font-size: 14px;
            opacity: 0.9;
            font-weight: 400;
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
            background-color: #fff4e6;
            border-left: 4px solid #FF6B35;
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
        
        /* Typography */
        h2 {
            color: #111827;
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 20px 0;
            line-height: 1.3;
        }
        
        h3 {
            color: #111827;
            font-size: 20px;
            font-weight: 600;
            margin: 24px 0 16px 0;
            line-height: 1.3;
        }
        
        p {
            margin: 0 0 16px 0;
            color: #374151;
        }
        
        /* Buttons */
        .button {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #FF6B35 0%, #F97316 100%);
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 2px 4px rgba(255, 107, 53, 0.3);
        }
        .button:hover {
            background: linear-gradient(135deg, #F97316 0%, #EA580C 100%);
            box-shadow: 0 4px 8px rgba(255, 107, 53, 0.4);
        }
        
        /* Links */
        .link {
            color: #FF6B35;
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
            background-color: #f9fafb;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .footer-content {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.6;
        }
        .footer-small {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 12px;
        }
        .footer-link {
            color: #FF6B35;
            text-decoration: none;
        }
        .footer-link:hover {
            text-decoration: underline;
        }
        
        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-content {
                padding: 30px 20px;
            }
            .header {
                padding: 25px 15px;
            }
            .header h1 {
                font-size: 24px;
            }
            h2 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="header">
                <h1>HONR</h1>
                <div class="header-subtitle">Hands-on Recognition</div>
            </div>
            
            <div class="email-content">
                @yield('content')
            </div>
            
            <div class="footer">
                <div class="footer-content">
                    <p style="margin: 0 0 8px 0;"><strong>Mit freundlichen Grüßen,<br>Das HONR-Team</strong></p>
                    <p class="footer-small" style="margin: 0;">
                        HANDS on TECHNOLOGY e.V.<br>
                        <a href="mailto:honr@hands-on-technology.org" class="footer-link">honr@hands-on-technology.org</a><br>
                        <a href="https://www.hands-on-technology.org" class="footer-link" style="color: #FF6B35;">www.hands-on-technology.org</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

