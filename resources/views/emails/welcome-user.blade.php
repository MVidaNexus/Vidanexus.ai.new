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
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 580px; background-color: #111827; border: 1px solid #1e293b; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.5);">
                    
                    <!-- Header Banner -->
                    <tr>
                        <td align="center" style="padding: 35px 30px 25px; background: linear-gradient(135deg, #0b1120 0%, #1e1b4b 100%); border-bottom: 1px solid #1f2937;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <div style="display: inline-block; padding: 8px 18px; background: rgba(14, 165, 233, 0.1); border: 1px solid rgba(14, 165, 233, 0.3); border-radius: 30px; margin-bottom: 12px;">
                                            <span style="font-size: 13px; font-weight: 800; color: #38bdf8; letter-spacing: 1.5px; text-transform: uppercase;">✦ VIDANEXUS AI</span>
                                        </div>
                                        <h1 style="margin: 0; font-size: 24px; font-weight: 800; color: #ffffff; letter-spacing: -0.5px;">Welcome to the Workspace! 🚀</h1>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 35px 35px 25px;">
                            <p style="margin: 0 0 16px; font-size: 16px; line-height: 24px; color: #e2e8f0;">
                                Hello <strong style="color: #38bdf8;">{{ $user->name ?? 'Creator' }}</strong>,
                            </p>
                            <p style="margin: 0 0 20px; font-size: 15px; line-height: 24px; color: #94a3b8;">
                                We’re excited to have you on board. <strong>VidaNexus AI</strong> gives you the practical intelligence tools to discover competitor keywords, craft viral headlines, and write high-ranking research articles.
                            </p>

                            @if(($welcomeCredits ?? 0) > 0)
                            <!-- Welcome Bonus Box -->
                            <div style="background: rgba(14, 165, 233, 0.08); border: 1px solid rgba(14, 165, 233, 0.25); border-radius: 14px; padding: 18px 20px; margin-bottom: 25px; text-align: center;">
                                <span style="font-size: 14px; color: #94a3b8;">Welcome Gift Added to Wallet</span>
                                <div style="font-size: 24px; font-weight: 800; color: #38bdf8; margin-top: 4px;">
                                    +{{ number_format($welcomeCredits, 0) }} Bonus Credits
                                </div>
                            </div>
                            @endif

                            <!-- Quick Steps Box -->
                            <div style="background: #1e293b; border-radius: 14px; padding: 20px; margin-bottom: 30px;">
                                <h3 style="margin: 0 0 12px; font-size: 14px; font-weight: 700; color: #f8fafc; text-transform: uppercase; letter-spacing: 0.5px;">What you can do right now:</h3>
                                <ul style="margin: 0; padding-left: 20px; color: #cbd5e1; font-size: 14px; line-height: 22px;">
                                    <li style="margin-bottom: 8px;"><strong>Keyword Spy Radar:</strong> Extract high-value search queries before competitors.</li>
                                    <li style="margin-bottom: 8px;"><strong>Discover Headlines:</strong> Generate high-CTR titles built for Google Discover.</li>
                                    <li><strong>Article Writer:</strong> Produce long-form, fact-grounded articles in minutes.</li>
                                </ul>
                            </div>

                            <!-- CTA Button -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 30px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $siteUrl }}/dashboard" target="_blank" style="display: inline-block; padding: 14px 36px; background: linear-gradient(135deg, #0ea5e9 0%, #0070e0 100%); color: #ffffff; text-decoration: none; font-size: 15px; font-weight: 800; border-radius: 12px; letter-spacing: 0.5px; box-shadow: 0 6px 20px rgba(14, 165, 233, 0.35);">
                                            Open My Dashboard →
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0; font-size: 13px; line-height: 20px; color: #64748b; text-align: center;">
                                Have questions or need technical support? We're available 24/7 at <a href="mailto:info@vidanexus.net" style="color: #38bdf8; text-decoration: none;">info@vidanexus.net</a>.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 20px 30px; background-color: #0d131f; border-top: 1px solid #1f2937;">
                            <p style="margin: 0; font-size: 12px; color: #475569;">
                                &copy; {{ date('Y') }} VidaNexus AI Hub. All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>
