<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Update & Refund Confirmation - ApniFactory</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f6f8; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
        <div style="background-color: #ef4444; color: #ffffff; padding: 24px; text-align: center;">
            <h2 style="margin: 0; font-size: 24px;">ApniFactory Buyer Support</h2>
            <p style="margin: 5px 0 0 0; opacity: 0.9;">Order Update & 100% Refund Guarantee</p>
        </div>
        <div style="padding: 24px;">
            <p style="font-size: 16px; color: #333333;">Hi <strong>{{ $name ?? 'Customer' }}</strong>,</p>
            <p style="color: #555555; line-height: 1.6;">We regret to inform you that your order could not be fulfilled by the seller. Rest assured, your payment is 100% secure.</p>
            
            <div style="background-color: #ecfdf5; border-left: 4px solid #10b981; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <p style="margin: 0 0 5px 0; font-weight: bold; color: #065f46;">💰 Full Refund Processed:</p>
                <p style="margin: 0; font-size: 14px; color: #047857;">Your refund has been initiated and credited to your account/wallet. You can reuse this balance instantly for any purchase.</p>
            </div>

            <div style="margin: 25px 0;">
                <h4 style="color: #1e293b; margin-bottom: 10px;">Recommended Alternative Sellers Available</h4>
                <p style="font-size: 14px; color: #64748b; line-height: 1.5;">We have matched other verified factory sellers offering equivalent items in your region with ready stock.</p>
            </div>

            <div style="text-align: center; margin: 25px 0;">
                <a href="{{ url('/') }}" style="background-color: #10b981; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">View Alternative Sellers & Reorder</a>
            </div>

            <p style="color: #888888; font-size: 12px; text-align: center; margin-top: 30px;">
                ApniFactory Guarantee &bull; 100% Buyer Protection &bull; support@apnifactory.com
            </p>
        </div>
    </div>
</body>
</html>
