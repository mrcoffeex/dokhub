<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Approved</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f6f9fc; margin: 0; padding: 40px 20px; color: #1a1a2e; }
        .container { max-width: 560px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #635bff 0%, #0ea5e9 100%); padding: 40px 40px 32px; color: #fff; }
        .header h1 { margin: 0 0 4px; font-size: 24px; font-weight: 700; }
        .header p { margin: 0; opacity: 0.85; font-size: 15px; }
        .body { padding: 36px 40px; }
        .badge { display: inline-block; background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; margin-bottom: 24px; }
        .detail-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 24px; margin-bottom: 24px; }
        .detail-row { display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 0; border-bottom: 1px solid #e2e8f0; }
        .detail-row:last-child { border-bottom: none; padding-bottom: 0; }
        .detail-label { font-size: 13px; color: #64748b; font-weight: 500; padding-right: 16px; }
        .detail-value { font-size: 14px; color: #1e293b; font-weight: 600; text-align: right; }
        .cta { display: block; text-align: center; background: linear-gradient(135deg, #635bff 0%, #0ea5e9 100%); color: #fff; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-size: 15px; font-weight: 600; margin-bottom: 24px; }
        .footer { background: #f8fafc; padding: 24px 40px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
        .footer a { color: #635bff; text-decoration: none; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <p style="font-size:13px;opacity:0.7;margin:0 0 8px;">DOKHUB APP</p>
        <h1>You're Approved! 🎉</h1>
        <p>Your doctor account has been reviewed and approved by our admin team.</p>
    </div>
    <div class="body">
        <div class="badge">✅ Account Approved</div>

        <p style="color:#475569;font-size:15px;margin:0 0 24px;">
            Hi <strong>Dr. {{ $doctor->name }}</strong>, congratulations! Your DokHub account has been approved. You can now log in and start accepting patient appointments.
        </p>

        <div class="detail-card">
            <div class="detail-row">
                <span class="detail-label">Name</span>
                <span class="detail-value">Dr. {{ $doctor->name }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Email</span>
                <span class="detail-value">{{ $doctor->email }}</span>
            </div>
            @if($doctor->specialization)
            <div class="detail-row">
                <span class="detail-label">Specialization</span>
                <span class="detail-value">{{ is_array($doctor->specialization) ? implode(', ', $doctor->specialization) : $doctor->specialization }}</span>
            </div>
            @endif
            <div class="detail-row">
                <span class="detail-label">Status</span>
                <span class="detail-value" style="color:#16a34a;">Approved</span>
            </div>
        </div>

        <a href="{{ url('/login') }}" class="cta">Log In to Your Dashboard</a>

        <p style="color:#94a3b8;font-size:13px;text-align:center;margin:0;">
            If you have any questions, contact us at <a href="mailto:support@dokhub.com" style="color:#635bff;">support@dokhub.com</a>.
        </p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} DokHub. All rights reserved.<br>
        <a href="{{ url('/') }}">Visit DokHub</a>
    </div>
</div>
</body>
</html>
