<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to PurpleBug</title>
    <style>
        body {
            font-family: 'Inter', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            background-color: #8B3F93;
            color: white;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 24px;
            font-weight: bold;
            display: inline-block;
        }
        h1 {
            color: #8B3F93;
            margin-top: 20px;
        }
        .content {
            margin-bottom: 30px;
        }
        .highlight {
            background-color: #f8f4f9;
            border-left: 4px solid #8B3F93;
            padding: 15px;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            background-color: #8B3F93;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin-top: 20px;
        }
        .button:hover {
            background-color: #7a3680;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #888;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">PurpleBug</div>
            <h1>Welcome, {{ $user->name }}!</h1>
        </div>
        
        <div class="content">
            <p>Thank you for creating an account with PurpleBug! We're excited to have you on board.</p>
            
            <div class="highlight">
                <strong>Your Account Details:</strong><br>
                <strong>Name:</strong> {{ $user->name }}<br>
                <strong>Email:</strong> {{ $user->email }}<br>
                <strong>Account Created:</strong> {{ $user->created_at->format('F j, Y \a\t g:i A') }}
            </div>
            
            <p>You can now:</p>
            <ul>
                <li>Browse our amazing products</li>
                <li>Add items to your cart</li>
                <li>Place orders and track them</li>
            </ul>
            
            <center>
                <a href="http://localhost:5173/login" class="button" style="display: inline-block; background-color: #8B3F93; color: #ffffff !important; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 20px;">
                    Login to Your Account
                </a>
            </center>
        </div>
        
        <div class="footer">
            <p>If you didn't create this account, please ignore this email.</p>
            <p>&copy; {{ date('Y') }} PurpleBug. All rights reserved.</p>
        </div>
    </div>
</body>
</html>