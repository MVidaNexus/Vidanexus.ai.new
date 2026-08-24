<!DOCTYPE html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Reset Your Password</title>
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
                        <td align="center" style="padding: 35px 30px 25px; background: linear-gradient(135deg, #0b1120 0%, #311042 100%); border-bottom: 1px solid #1f2937;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <div style="display: inline-block; padding: 8px 18px; background: rgba(168, 85, 247, 0.1); border: 1px solid rgba(168, 85, 247, 0.3); border-radius: 30px; margin-bottom: 12px;">
                                            <span style="font-size: 13px; font-weight: 800; color: #c084fc; letter-spacing: 1.5px; text-transform: uppercase;">✦ VIDANEXUS AI</span>
                                        </div>
                                        <h1 style="margin: 0; font-size: 24px; font-weight: 800; color: #ffffff; letter-spacing: -0.5px;">Password Reset Request</h1>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 35px 35px 25px;">
                            <p style="margin: 0 0 16px; font-size: 16px; line-height: 24px; color: #e2e8f0;">
                                Hello <strong style="color: #c084fc;">{{ $user->name ?? 'there' }}</strong>,
                            </p>
                            <p style="margin: 0 0 24px; font-size: 15px; line-height: 24px; color: #94a3b8;">
                                We received a request to reset your password for your <strong>VidaNexus AI</strong> account.
                            </p>
                            <p style="margin: 0 0 30px; font-size: 15px; line-height: 24px; color: #94a3b8;">
                                Click the button below to choose a new password:
                            </p>

                            <!-- CTA Button -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 30px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $url }}" target="_blank" style="display: inline-block; padding: 14px 36px; background: linear-gradient(135deg, #a855f7 0%, #6366f1 100%); color: #ffffff; text-decoration: none; font-size: 15px; font-weight: 800; border-radius: 12px; letter-spacing: 0.5px; box-shadow: 0 6px 20px rgba(168, 85, 247, 0.35);">
                                            Reset My Password →
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Security Note -->
                            <div style="background-color: rgba(255, 255, 255, 0.03); border: 1px solid #1e293b; border-radius: 12px; padding: 16px; margin-bottom: 25px;">
                                <p style="margin: 0; font-size: 13px; line-height: 20px; color: #64748b;">
                                    🔒 <strong>Security tip:</strong> This password reset link will expire in <strong>60 minutes</strong>. If you did not request a password reset, you can safely ignore this email — your account remains secure.
                                </p>
                            </div>

                            <!-- Fallback Link -->
                            <p style="margin: 0 0 8px; font-size: 12px; color: #64748b;">
                                Having trouble with the button? Copy and paste the link below into your browser:
                            </p>
                            <p style="margin: 0; font-size: 11px; line-height: 18px; word-break: break-all;">
                                <a href="{{ $url }}" target="_blank" style="color: #c084fc; text-decoration: underline;">{{ $url }}</a>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 25px 30px; background-color: #0d131f; border-top: 1px solid #1e293b;">
                            <p style="margin: 0 0 6px; font-size: 12px; font-weight: 700; color: #94a3b8;">
                                VidaNexus AI — Autonomous Intelligence & Marketing Hub
                            </p>
                            <p style="margin: 0; font-size: 11px; color: #475569;">
                                © {{ date('Y') }} VidaNexus AI. All rights reserved. • <a href="https://vidanexus.ai" target="_blank" style="color: #64748b; text-decoration: none;">vidanexus.ai</a>
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>
