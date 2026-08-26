<!DOCTYPE html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Low Credit Balance Alert</title>
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
                        <td align="center" style="padding: 35px 30px 25px; background: linear-gradient(135deg, #1c1917 0%, #292524 100%); border-bottom: 1px solid #292524;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <div style="display: inline-block; padding: 8px 18px; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.35); border-radius: 30px; margin-bottom: 12px;">
                                            <span style="font-size: 13px; font-weight: 800; color: #fbbf24; letter-spacing: 1.5px; text-transform: uppercase;">⚠️ BALANCE ALERT</span>
                                        </div>
                                        <h1 style="margin: 0; font-size: 24px; font-weight: 800; color: #ffffff; letter-spacing: -0.5px;">Your Credits Are Running Low</h1>
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
                                Your active credit balance has reached a low threshold. To avoid interrupting your ongoing research, automated headline monitoring, or article generations, top up your wallet anytime.
                            </p>

                            <!-- Current Balance Box -->
                            <div style="background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.25); border-radius: 14px; padding: 18px 20px; margin-bottom: 25px; text-align: center;">
                                <span style="font-size: 13px; font-weight: 600; color: #94a3b8; text-transform: uppercase;">Current Remaining Balance</span>
                                <div style="font-size: 28px; font-weight: 900; color: #fbbf24; margin-top: 4px;">
                                    {{ number_format($currentBalance, 2) }} Credits
                                </div>
                            </div>

                            <p style="margin: 0 0 30px; font-size: 14px; line-height: 22px; color: #cbd5e1; text-align: center;">
                                Credit packages start from just <strong>35 EGP</strong> with instant activation, zero recurring commitments, and credits that never expire.
                            </p>

                            <!-- CTA Button -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 30px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $pricingUrl }}" target="_blank" style="display: inline-block; padding: 14px 36px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #000000; text-decoration: none; font-size: 15px; font-weight: 800; border-radius: 12px; letter-spacing: 0.5px; box-shadow: 0 6px 20px rgba(245, 158, 11, 0.35);">
                                            Top Up Credits Now →
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0; font-size: 13px; line-height: 20px; color: #64748b; text-align: center;">
                                Need assistance or a custom high-volume plan? Reach out directly to <a href="mailto:info@vidanexus.net" style="color: #38bdf8; text-decoration: none;">info@vidanexus.net</a>.
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
