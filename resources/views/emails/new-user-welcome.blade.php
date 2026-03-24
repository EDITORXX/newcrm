<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - {{ $appName }}</title>
</head>
<body style="margin:0; padding:0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
        <tr>
            <td style="padding: 24px 24px 16px;">
                <h1 style="margin: 0 0 8px; font-size: 22px; color: #111;">Welcome to {{ $appName }}</h1>
                <p style="margin: 0; font-size: 14px; color: #555;">Dear {{ $user->name }},</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 0 24px 16px;">
                <p style="margin: 0 0 16px; font-size: 14px; color: #333; line-height: 1.5;">Your account has been created. Please find your login details below.</p>
                <table width="100%" cellpadding="8" cellspacing="0" style="border: 1px solid #e5e5e5; border-radius: 6px; font-size: 14px;">
                    <tr style="background: #f9f9f9;"><td style="border-bottom: 1px solid #eee; color: #666;">Full name</td><td style="border-bottom: 1px solid #eee;">{{ $user->name }}</td></tr>
                    <tr style="background: #f9f9f9;"><td style="border-bottom: 1px solid #eee; color: #666;">Email</td><td style="border-bottom: 1px solid #eee;">{{ $user->email }}</td></tr>
                    <tr style="background: #f9f9f9;"><td style="border-bottom: 1px solid #eee; color: #666;">Temporary password</td><td style="border-bottom: 1px solid #eee;">{{ $plainPassword }}</td></tr>
                    <tr style="background: #f9f9f9;"><td style="border-bottom: 1px solid #eee; color: #666;">Position</td><td style="border-bottom: 1px solid #eee;">{{ $roleName }}</td></tr>
                    <tr style="background: #f9f9f9;"><td style="border-bottom: 1px solid #eee; color: #666;">Reporting manager</td><td style="border-bottom: 1px solid #eee;">{{ $managerName ?? '—' }}</td></tr>
                    <tr style="background: #f9f9f9;"><td style="border-bottom: 1px solid #eee; color: #666;">Phone</td><td style="border-bottom: 1px solid #eee;">{{ $user->phone ?? '—' }}</td></tr>
                    <tr style="background: #f9f9f9;"><td style="color: #666;">Status</td><td>{{ $user->is_active ? 'Active' : 'Inactive' }}</td></tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="padding: 8px 24px 20px;">
                <p style="margin: 0 0 16px; font-size: 14px; color: #333;">Please log in and change your password after your first login.</p>
                <p style="margin: 0 0 12px; font-size: 14px; color: #333;">Use the links below:</p>
                <table cellpadding="0" cellspacing="0" style="margin: 0;">
                    <tr>
                        <td style="padding-right: 12px; padding-bottom: 8px;">
                            <a href="{{ $loginUrl }}" style="display: inline-block; padding: 12px 24px; background: #15803d; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px;">Log in to {{ $appName }}</a>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <a href="{{ $installAppUrl ?? url('/install-app') }}" style="display: inline-block; padding: 12px 24px; background: #205A44; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px; border: 2px solid #205A44;">Install App (PWA)</a>
                        </td>
                    </tr>
                </table>
                <p style="margin: 12px 0 0; font-size: 13px; color: #666;">Install the app on your phone for quick access. After installing, use &quot;Log in&quot; to sign in.</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 16px 24px 24px; border-top: 1px solid #eee; font-size: 13px; color: #666;">
                If you have any questions, please contact your administrator or support.<br><br>
                Thanks,<br>{{ $appName }}
            </td>
        </tr>
    </table>
</body>
</html>
