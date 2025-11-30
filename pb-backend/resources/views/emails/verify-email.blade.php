<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email - PurpleBug</title>
</head>
<body style="font-family: 'Inter', Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f5f5f5;">
    <div style="background-color: #ffffff; border-radius: 10px; padding: 40px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
        <div style="text-align: center; margin-bottom: 30px;">
            <div style="background-color: #8B3F93; color: white; padding: 15px 30px; border-radius: 8px; font-size: 24px; font-weight: bold; display: inline-block;">PurpleBug</div>
            <h1 style="color: #8B3F93; margin-top: 20px;">Verify Your Email</h1>
        </div>
        
        <div style="margin-bottom: 30px;">
            <p>Hi {{ $user->name }},</p>
            
            <p>Thank you for registering with PurpleBug! Please click the button below to verify your email address.</p>
            
            <center>
                <a href="http://localhost:5173/verify-email?token={{ $token }}" style="display: inline-block; background-color: #8B3F93; color: #ffffff !important; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 20px;">
                    Verify Email Address
                </a>
            </center>
            
            <p style="margin-top: 20px; font-size: 14px; color: #666;">
                If the button doesn't work, copy and paste this link into your browser:<br>
                <a href="http://localhost:5173/verify-email?token={{ $token }}" style="color: #8B3F93; word-break: break-all;">
                    http://localhost:5173/verify-email?token={{ $token }}
                </a>
            </p>
            
            <p style="margin-top: 20px; font-size: 14px; color: #666;">
                This link will expire in 24 hours.
            </p>
        </div>
        
        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #888; font-size: 14px;">
            <p>If you didn't create an account with PurpleBug, please ignore this email.</p>
            <p>&copy; {{ date('Y') }} PurpleBug. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
