<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Order Received - ApniFactory</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f6f8; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
        <div style="background-color: #1e293b; color: #ffffff; padding: 24px; text-align: center;">
            <h2 style="margin: 0; font-size: 24px;">🏭 ApniFactory Seller Portal</h2>
            <p style="margin: 5px 0 0 0; opacity: 0.8;">Action Required: New Purchase Order Received</p>
        </div>
        <div style="padding: 24px;">
            <p style="font-size: 16px; color: #333333;">Hi <strong>{{ $name ?? 'Seller' }}</strong>,</p>
            <p style="color: #555555; line-height: 1.6;">You have received a new confirmed order on ApniFactory! Please review the order details and accept the order within your <strong>3-Day Seller SLA Deadline</strong>.</p>
            
            <div style="background-color: #f8fafc; border-left: 4px solid #3b82f6; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <p style="margin: 0 0 5px 0; font-weight: bold; color: #1e293b;">Order SLA Reminder:</p>
                <p style="margin: 0; font-size: 14px; color: #64748b;">Orders not accepted within 3 days will automatically cancel and impact your seller fulfillment rating.</p>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ url('/seller/order') }}" style="background-color: #2563eb; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">View & Accept Order</a>
            </div>

            <p style="color: #888888; font-size: 12px; text-align: center; margin-top: 30px;">
                ApniFactory B2B Marketplace &bull; Manufacturing Hub &bull; Need help? Contact Seller Support
            </p>
        </div>
    </div>
</body>
</html>
