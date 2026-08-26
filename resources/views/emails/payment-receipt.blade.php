<!DOCTYPE html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Payment Receipt</title>
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
                        <td align="center" style="padding: 35px 30px 25px; background: linear-gradient(135deg, #064e3b 0%, #0f172a 100%); border-bottom: 1px solid #064e3b;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <div style="display: inline-block; padding: 8px 18px; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.35); border-radius: 30px; margin-bottom: 12px;">
                                            <span style="font-size: 13px; font-weight: 800; color: #34d399; letter-spacing: 1.5px; text-transform: uppercase;">✓ PAYMENT SUCCESSFUL</span>
                                        </div>
                                        <h1 style="margin: 0; font-size: 24px; font-weight: 800; color: #ffffff; letter-spacing: -0.5px;">Your Receipt & Confirmation</h1>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 35px 35px 25px;">
                            <p style="margin: 0 0 16px; font-size: 16px; line-height: 24px; color: #e2e8f0;">
                                Hello <strong style="color: #38bdf8;">{{ $user->name ?? 'Customer' }}</strong>,
                            </p>
                            <p style="margin: 0 0 20px; font-size: 15px; line-height: 24px; color: #94a3b8;">
                                Thank you for your purchase! Your payment has been confirmed and the assets have been automatically activated in your account.
                            </p>

                            <!-- Invoice Details Table -->
                            <div style="background: #1e293b; border-radius: 14px; padding: 22px; margin-bottom: 30px;">
                                <table width="100%" cellpadding="0" cellspacing="0" style="font-size: 14px; line-height: 24px;">
                                    <tr>
                                        <td style="color: #94a3b8; padding-bottom: 10px;">Item / Plan:</td>
                                        <td align="right" style="color: #f8fafc; font-weight: 700; padding-bottom: 10px;">{{ $details['item_name'] ?? 'Credit Package' }}</td>
                                    </tr>
                                    <tr>
                                        <td style="color: #94a3b8; padding-bottom: 10px;">Amount Paid:</td>
                                        <td align="right" style="color: #34d399; font-weight: 800; padding-bottom: 10px;">{{ number_format((float)($details['amount'] ?? 0), 2) }} EGP</td>
                                    </tr>
                                    @if(isset($details['credits_added']) && $details['credits_added'] > 0)
                                    <tr>
                                        <td style="color: #94a3b8; padding-bottom: 10px;">Credits Granted:</td>
                                        <td align="right" style="color: #38bdf8; font-weight: 700; padding-bottom: 10px;">+{{ number_format((float)$details['credits_added'], 0) }} Credits</td>
                                    </tr>
                                    @endif
                                    @if(isset($details['new_balance']))
                                    <tr>
                                        <td style="color: #94a3b8; padding-bottom: 10px;">Updated Balance:</td>
                                        <td align="right" style="color: #ffffff; font-weight: 700; padding-bottom: 10px;">{{ number_format((float)$details['new_balance'], 2) }} Credits</td>
                                    </tr>
                                    @endif
                                    <tr style="border-top: 1px solid #334155;">
                                        <td style="color: #64748b; padding-top: 10px; font-size: 12px;">Reference #:</td>
                                        <td align="right" style="color: #94a3b8; padding-top: 10px; font-size: 12px; font-family: monospace;">{{ $details['reference'] ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td style="color: #64748b; padding-top: 4px; font-size: 12px;">Date:</td>
                                        <td align="right" style="color: #94a3b8; padding-top: 4px; font-size: 12px;">{{ date('F j, Y - g:i A') }}</td>
                                    </tr>
                                </table>
                            </div>

                            <!-- CTA Button -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 30px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $dashboardUrl }}" target="_blank" style="display: inline-block; padding: 14px 36px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; text-decoration: none; font-size: 15px; font-weight: 800; border-radius: 12px; letter-spacing: 0.5px; box-shadow: 0 6px 20px rgba(16, 185, 129, 0.35);">
                                            Start Using Tools in Dashboard →
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0; font-size: 13px; line-height: 20px; color: #64748b; text-align: center;">
                                Need an official business invoice or tax document? Contact us at <a href="mailto:info@vidanexus.net" style="color: #38bdf8; text-decoration: none;">info@vidanexus.net</a>.
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
