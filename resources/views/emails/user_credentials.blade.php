<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MOHZ External Referral System - Login Credentials</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f7; margin: 0; padding: 0;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f7; padding: 20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #004080; padding: 20px; text-align: center; color: #ffffff;">
                            <h1 style="margin: 0; font-size: 24px;">MOHZ External Referral System</h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 30px; color: #333333;">
                            <p style="font-size: 16px;">Hello <strong>{{ $user->first_name }}</strong>,</p>

                            <p style="font-size: 16px;">Welcome to the <strong>MOHZ External Referral System</strong>. Below are your login credentials:</p>

                            <table cellpadding="0" cellspacing="0" width="100%" style="margin: 20px 0;">
                                <tr>
                                    <td style="padding: 10px; background-color: #f0f0f0; border-radius: 4px;">
                                        <strong>Email:</strong> {{ $user->email }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px; background-color: #f0f0f0; border-radius: 4px; margin-top: 10px;">
                                        <strong>Password:</strong> {{ $password }}
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size: 16px;">
                                Please <a href="{{ url('/login') }}" style="color: #ffffff; background-color: #004080; padding: 10px 20px; text-decoration: none; border-radius: 4px;">click here to login</a> and change your password after your first login.
                            </p>

                            <p style="font-size: 16px;">Thanks,<br>MOHZ ICT UNIT</p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f4f4f7; text-align: center; padding: 20px; font-size: 12px; color: #999999;">
                            &copy; {{ date('Y') }} MOHZ. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
