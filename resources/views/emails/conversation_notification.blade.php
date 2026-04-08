<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient History Information System - New Message</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f7; margin: 0; padding: 0;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f7; padding: 20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="background-color: #004080; padding: 20px; text-align: center; color: #ffffff;">
                            <h1 style="margin: 0; font-size: 20px;">Patient History Information System</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 30px; color: #333333;">
                            <p style="font-size: 16px;">Hello <strong>{{ $recipient->first_name }}</strong>,</p>
                            <p style="font-size: 16px;">You have received a new message regarding a patient history record. Below are the details:</p>
                            <table cellpadding="0" cellspacing="0" width="100%" style="margin: 20px 0;">
                                <tr>
                                    <td style="padding: 10px; background-color: #f9f9f9; border-left: 4px solid #004080; border-radius: 4px;">
                                        <strong>Sender:</strong> {{ $senderName }}
                                    </td>
                                </tr>
                                <tr style="height: 10px;"><td></td></tr>
                                <tr>
                                    <td style="padding: 15px; background-color: #f0f7ff; border-radius: 4px; font-style: italic; color: #1e3a8a;">
                                        <strong>Message:</strong><br>
                                        "{{ $conversation->message }}"
                                    </td>
                                </tr>
                            </table>
                            <p style="text-align: center; margin: 30px 0;">
                                <a href="{{ url('/login') }}" style="color: #ffffff; background-color: #004080; padding: 12px 25px; text-decoration: none; border-radius: 4px; font-weight: bold; display: inline-block;">Login to Reply</a>
                            </p>
                            <p style="font-size: 16px;">Thanks,<br>ICT Unit</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f4f4f7; text-align: center; padding: 20px; font-size: 12px; color: #999999;">
                            &copy; {{ date('Y') }} Ministry of Health Zanzibar. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>