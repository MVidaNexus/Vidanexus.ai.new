<!DOCTYPE html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Welcome to VidaNexus AI</title>
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        body { margin: 0; padding: 0; width: 100% !important; background-color: #0b0f17; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #0b0f17; color: #f1f5f9;">

    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #0b0f17;">
        <tr>
            <td align="center" style="padding: 40px 15px;">
                
                <!-- Main Container -->
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 580px; background-color: #111827; border: 1px solid #1e293b; border-radius: 16px; overflow: hidden;">
                    
                    <!-- Header Banner -->
                    <tr>
                        <td align="center" style="padding: 30px 30px 20px; background-color: #0f172a; border-bottom: 1px solid #1e293b;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <div style="display: inline-block; padding: 6px 14px; background-color: #1e293b; border-radius: 20px; margin-bottom: 10px;">
                                            <span style="font-size: 12px; font-weight: 700; color: #38bdf8; letter-spacing: 1px; text-transform: uppercase;">VIDANEXUS AI</span>
                                        </div>
                                        <h1 style="margin: 0; font-size: 22px; font-weight: 700; color: #ffffff; letter-spacing: -0.3px;">Welcome to Your Workspace!</h1>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 30px 30px 20px;">
                            <p style="margin: 0 0 16px; font-size: 15px; line-height: 24px; color: #e2e8f0;">
                                Hello <strong style="color: #38bdf8;">{{ $user->name ?? 'Creator' }}</strong>,
                            </p>
                            <p style="margin: 0 0 20px; font-size: 14px; line-height: 22px; color: #94a3b8;">
                                We are glad to welcome you to <strong>VidaNexus AI</strong>. Your account is active and you now have access to our suite of AI content research, keyword intelligence, and trend tracking tools.
                            </p>

                            @if(($welcomeCredits ?? 0) > 0)
                            <!-- Welcome Bonus Box -->
                            <div style="background-color: #1e293b; border-left: 4px solid #0284c7; border-radius: 8px; padding: 16px 20px; margin-bottom: 24px;">
                                <span style="font-size: 13px; color: #94a3b8; display: block; margin-bottom: 4px;">Welcome Bonus Added to Your Wallet</span>
                                <div style="font-size: 22px; font-weight: 700; color: #38bdf8;">
                                    +{{ number_format($welcomeCredits, 0) }} Credits
                                </div>
                            </div>
                            @endif

                            <!-- Quick Steps Box -->
                            <div style="background-color: #1e293b; border-radius: 10px; padding: 18px; margin-bottom: 25px;">
                                <h3 style="margin: 0 0 10px; font-size: 13px; font-weight: 700; color: #f8fafc; text-transform: uppercase; letter-spacing: 0.5px;">Recommended Next Steps:</h3>
                                <ul style="margin: 0; padding-left: 18px; color: #cbd5e1; font-size: 13px; line-height: 22px;">
                                    <li style="margin-bottom: 6px;"><strong>Keyword Spy Radar:</strong> Uncover high-value search queries before competitors.</li>
                                    <li style="margin-bottom: 6px;"><strong>Discover Headlines:</strong> Generate high-CTR titles built for Google Discover.</li>
                                    <li><strong>Article Writer:</strong> Produce structured, research-backed articles in minutes.</li>
                                </ul>
                            </div>

                            <!-- CTA Button (Solid, Flat, Professional) -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 25px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $siteUrl }}/dashboard" target="_blank" style="display: inline-block; padding: 12px 32px; background-color: #0284c7; color: #ffffff; text-decoration: none; font-size: 14px; font-weight: 600; border-radius: 8px;">
                                            Open My Dashboard →
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0; font-size: 13px; line-height: 20px; color: #64748b; text-align: center;">
                                Need any assistance? Our support team is available at <a href="mailto:info@vidanexus.net" style="color: #38bdf8; text-decoration: none;">info@vidanexus.net</a>.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 16px 20px; background-color: #0d131f; border-top: 1px solid #1e293b;">
                            <p style="margin: 0; font-size: 12px; color: #475569;">
                                &copy; {{ date('Y') }} VidaNexus AI. All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>
