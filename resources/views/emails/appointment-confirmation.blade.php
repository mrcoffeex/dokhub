<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Confirmed</title>
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
        .detail-label { font-size: 13px; color: #64748b; font-weight: 500; }
        .detail-value { font-size: 14px; color: #1e293b; font-weight: 600; text-align: right; max-width: 60%; }
        .ref { font-family: monospace; background: #f1f5f9; padding: 2px 8px; border-radius: 4px; font-size: 13px; }
        .note { background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 16px; font-size: 13px; color: #92400e; margin-bottom: 24px; }
        .footer { background: #f8fafc; padding: 24px 40px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
        .footer a { color: #635bff; text-decoration: none; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <p style="font-size:13px;opacity:0.7;margin:0 0 8px;">DOKHUB HEALTHCARE</p>
        <h1>Request Received!</h1>
        <p>Your appointment request has been submitted and is awaiting confirmation.</p>
    </div>
    <div class="body">
        <div class="badge" style="background:#fffbeb;color:#92400e;border-color:#fde68a;">⏳ Pending Confirmation</div>

        <p style="color:#475569;font-size:15px;margin:0 0 24px;">
            Hi <strong>{{ $appointment->patient_name }}</strong>, your appointment request has been received. The doctor will confirm it shortly.
        </p>

        <div class="detail-card">
            <div class="detail-row">
                <span class="detail-label">Reference</span>
                <span class="detail-value"><span class="ref">{{ $appointment->reference }}</span></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Doctor</span>
                <span class="detail-value">Dr. {{ $appointment->doctor->name }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Specialization</span>
                <span class="detail-value">{{ is_array($appointment->doctor->specialization) ? implode(', ', $appointment->doctor->specialization) : $appointment->doctor->specialization }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Date</span>
                <span class="detail-value">{{ $appointment->appointment_date->format('D, M j, Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Time</span>
                <span class="detail-value">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</span>
            </div>
            @if($appointment->reason)
            <div class="detail-row">
                <span class="detail-label">Reason</span>
                <span class="detail-value">{{ $appointment->reason }}</span>
            </div>
            @endif
        </div>

        <div class="note">
            📋 Please save your reference number <strong>{{ $appointment->reference }}</strong> for future correspondence. Bring a valid ID to your appointment.
        </div>

        <p style="font-size:13px;color:#64748b;">
            Need to cancel or reschedule? Reply to this email or contact us at least 24 hours before your appointment.
        </p>
    </div>
    <div class="footer">
        <p style="margin:0 0 4px;">DOKHUB · Healthcare Appointment Platform</p>
        <p style="margin:0;">Questions? <a href="mailto:{{ config('mail.from.address') }}">Contact Support</a></p>
    </div>
</div>
</body>
</html>
