<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $subject ?? 'ApniFactory Agent Attribution Update' }}</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f6f8; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
        <div style="background-color: #0f172a; color: #ffffff; padding: 24px; text-align: center;">
            <h2 style="margin: 0; font-size: 22px;">ApniFactory Field Operations</h2>
            <p style="margin: 6px 0 0 0; opacity: 0.85; font-size: 14px;">{{ $headline ?? 'Buyer Attribution Update' }}</p>
        </div>
        <div style="padding: 24px;">
            <p style="font-size: 16px; color: #1e293b;">Hello <strong>{{ $name ?? 'Partner' }}</strong>,</p>
            <p style="color: #475569; line-height: 1.6;">{{ $bodyMessage ?? '' }}</p>

            @if(!empty($details) && is_array($details))
            <div style="background-color: #f8fafc; border-left: 4px solid #2563eb; padding: 14px; margin: 18px 0; border-radius: 4px;">
                @foreach($details as $label => $value)
                    <p style="margin: 4px 0; font-size: 14px; color: #334155;">
                        <strong>{{ $label }}:</strong> {{ $value }}
                    </p>
                @endforeach
            </div>
            @endif

            @if(!empty($actionUrl))
            <div style="text-align: center; margin: 28px 0;">
                <a href="{{ $actionUrl }}" style="background-color: #2563eb; color: #ffffff; padding: 12px 26px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">
                    {{ $actionLabel ?? 'View in Portal' }}
                </a>
            </div>
            @endif

            <p style="color: #94a3b8; font-size: 12px; text-align: center; margin-top: 28px;">
                ApniFactory B2B Marketplace &bull; Automated Field Attribution Notification
            </p>
        </div>
    </div>
</body>
</html>

