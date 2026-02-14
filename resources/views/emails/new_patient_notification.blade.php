<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>External Referral Information System - New Patient Referral</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f7; margin: 0; padding: 0;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f7; padding: 20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="background-color: #004080; padding: 20px; text-align: center; color: #ffffff;">
                            <h1 style="margin: 0; font-size: 24px;">External Referral Information System</h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 30px; color: #333333;">
                            <p style="font-size: 16px;">Hello <strong>Director</strong>,</p>

                            <p style="font-size: 16px;">A new patient record has been registered and is pending. Below are the referral details:</p>

                            <table cellpadding="0" cellspacing="0" width="100%" style="margin: 20px 0;">
                                <tr>
                                    <td style="padding: 10px; background-color: #f0f0f0; border-radius: 4px;">
                                        <strong>Patient Name:</strong> {{ $patient_name }}
                                    </td>
                                </tr>
                                <tr style="height: 10px;"><td></td></tr>
                                <tr>
                                    <td style="padding: 10px; background-color: #f0f0f0; border-radius: 4px;">
                                        <strong>Matibabu Card:</strong> {{ $matibabu_card }}
                                    </td>
                                </tr>
                                <tr style="height: 10px;"><td></td></tr>
                                <tr>
                                    <td style="padding: 10px; background-color: #f0f0f0; border-radius: 4px;">
                                        <strong>Case Type:</strong> {{ $case_type }}
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size: 16px;">
                                Please <a href="{{ url('/login') }}" style="color: #ffffff; background-color: #004080; padding: 10px 20px; text-decoration: none; border-radius: 4px;">click here to login</a> and make review.
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
