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
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 580px; background-color: #111827; border: 1px solid #1e293b; border-radius: 16px; overflow: hidden;">
                    
                    <!-- Header Banner -->
                    <tr>
                        <td align="center" style="padding: 30px 30px 20px; background-color: #0f172a; border-bottom: 1px solid #1e293b;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <div style="display: inline-block; padding: 6px 14px; background-color: #1e293b; border-radius: 20px; margin-bottom: 10px;">
                                            <span style="font-size: 12px; font-weight: 700; color: #34d399; letter-spacing: 1px; text-transform: uppercase;">PAYMENT CONFIRMED</span>
                                        </div>
                                        <h1 style="margin: 0; font-size: 22px; font-weight: 700; color: #ffffff; letter-spacing: -0.3px;">Your Payment Receipt</h1>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 30px 30px 20px;">
                            <p style="margin: 0 0 16px; font-size: 15px; line-height: 24px; color: #e2e8f0;">
                                Hello <strong style="color: #38bdf8;">{{ $user->name ?? 'Customer' }}</strong>,
                            </p>
                            <p style="margin: 0 0 20px; font-size: 14px; line-height: 22px; color: #94a3b8;">
                                Thank you for your purchase! Your payment has been confirmed and the credits have been added directly to your account.
                            </p>

                            <!-- Invoice Details Table -->
                            <div style="background-color: #1e293b; border-radius: 10px; padding: 20px; margin-bottom: 25px;">
                                <table width="100%" cellpadding="0" cellspacing="0" style="font-size: 14px; line-height: 24px;">
                                    <tr>
                                        <td style="color: #94a3b8; padding-bottom: 10px;">Item / Plan:</td>
                                        <td align="right" style="color: #f8fafc; font-weight: 600; padding-bottom: 10px;">{{ $details['item_name'] ?? 'Credit Package' }}</td>
                                    </tr>
                                    <tr>
                                        <td style="color: #94a3b8; padding-bottom: 10px;">Amount Paid:</td>
                                        <td align="right" style="color: #34d399; font-weight: 700; padding-bottom: 10px;">{{ number_format((float)($details['amount'] ?? 0), 2) }} EGP</td>
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
                                        <td align="right" style="color: #ffffff; font-weight: 700; padding-bottom: 10px;">{{ number_format((float)$details['new_balance'], 0) }} Credits</td>
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

                            <!-- CTA Button (Solid, Flat, Linking to Tools Tab) -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 25px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $toolsUrl }}" target="_blank" style="display: inline-block; padding: 12px 32px; background-color: #059669; color: #ffffff; text-decoration: none; font-size: 14px; font-weight: 600; border-radius: 8px;">
                                            Explore & Use Tools →
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0; font-size: 13px; line-height: 20px; color: #64748b; text-align: center;">
                                Need an invoice for tax or accounting purposes? Email us at <a href="mailto:info@vidanexus.net" style="color: #38bdf8; text-decoration: none;">info@vidanexus.net</a>.
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
