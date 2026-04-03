<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visit Summary</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f6f9fc; margin: 0; padding: 40px 20px; color: #1a1a2e; }
        .container { max-width: 560px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #0369a1 0%, #6366f1 100%); padding: 40px 40px 32px; color: #fff; }
        .header h1 { margin: 0 0 4px; font-size: 24px; font-weight: 700; }
        .header p { margin: 0; opacity: 0.85; font-size: 15px; }
        .body { padding: 36px 40px; }
        .badge { display: inline-block; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; margin-bottom: 24px; }
        .section-title { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #94a3b8; margin: 0 0 12px; }
        .detail-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 24px; margin-bottom: 24px; }
        .detail-row { display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 0; border-bottom: 1px solid #e2e8f0; }
        .detail-row:last-child { border-bottom: none; padding-bottom: 0; }
        .detail-label { font-size: 13px; color: #64748b; font-weight: 500; padding-right: 16px; }
        .detail-value { font-size: 14px; color: #1e293b; font-weight: 600; text-align: right; max-width: 60%; }
        .ref { font-family: monospace; background: #f1f5f9; padding: 2px 8px; border-radius: 4px; font-size: 13px; }
        .dx-card { background: #fafafa; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px 24px; margin-bottom: 16px; }
        .dx-title { font-size: 15px; font-weight: 700; color: #1e293b; margin: 0 0 12px; }
        .dx-field { margin-bottom: 10px; }
        .dx-field-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #94a3b8; margin-bottom: 3px; }
        .dx-field-value { font-size: 13px; color: #334155; line-height: 1.5; }
        .rx-card { background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 10px; padding: 20px 24px; margin-bottom: 12px; }
        .rx-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; padding-bottom: 10px; border-bottom: 1px solid #bae6fd; }
        .rx-ref { font-family: monospace; font-size: 12px; color: #0369a1; background: #e0f2fe; padding: 2px 8px; border-radius: 4px; }
        .med-table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        .med-table th { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #64748b; text-align: left; padding: 6px 8px; border-bottom: 1px solid #bae6fd; }
        .med-table td { font-size: 13px; color: #1e293b; padding: 7px 8px; border-bottom: 1px solid #e0f2fe; vertical-align: top; }
        .med-table tr:last-child td { border-bottom: none; }
        .follow-up { background: #fefce8; border: 1px solid #fde68a; border-radius: 8px; padding: 14px 16px; font-size: 13px; color: #92400e; margin-top: 12px; }
        .note { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 16px; font-size: 13px; color: #1e40af; margin-bottom: 24px; }
        .footer { background: #f8fafc; padding: 24px 40px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
        .footer a { color: #0369a1; text-decoration: none; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <p style="font-size:13px;opacity:0.7;margin:0 0 8px;">DOKHUB HEALTHCARE</p>
        <h1>Visit Summary</h1>
        <p>Your appointment has been completed. Here is your visit summary.</p>
    </div>
    <div class="body">
        <div class="badge">🏥 Visit Completed</div>

        <p style="color:#475569;font-size:15px;margin:0 0 24px;">
            Hi <strong>{{ $appointment->patient_name }}</strong>, thank you for your visit with Dr. <strong>{{ $appointment->doctor->name }}</strong>. Please find your visit summary below.
        </p>

        {{-- Appointment Details --}}
        <p class="section-title">Appointment Details</p>
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
                <span class="detail-label">Date</span>
                <span class="detail-value">{{ $appointment->appointment_date->format('D, M j, Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Time</span>
                <span class="detail-value">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</span>
            </div>
        </div>

        {{-- Diagnoses --}}
        @if($appointment->diagnoses->isNotEmpty())
        <p class="section-title">Diagnosis & Treatment</p>
        @foreach($appointment->diagnoses as $diagnosis)
        <div class="dx-card">
            <div class="dx-title">{{ $diagnosis->title }}</div>

            @if($diagnosis->symptoms)
            <div class="dx-field">
                <div class="dx-field-label">Symptoms</div>
                <div class="dx-field-value">{{ $diagnosis->symptoms }}</div>
            </div>
            @endif

            @if($diagnosis->description)
            <div class="dx-field">
                <div class="dx-field-label">Description</div>
                <div class="dx-field-value">{{ $diagnosis->description }}</div>
            </div>
            @endif

            @if($diagnosis->treatment)
            <div class="dx-field">
                <div class="dx-field-label">Treatment Plan</div>
                <div class="dx-field-value">{{ $diagnosis->treatment }}</div>
            </div>
            @endif

            {{-- Prescriptions under this diagnosis --}}
            @if($diagnosis->prescriptions->isNotEmpty())
            <div style="margin-top:14px;">
                <div class="dx-field-label" style="margin-bottom:8px;">Prescriptions</div>
                @foreach($diagnosis->prescriptions as $rx)
                <div class="rx-card">
                    <div class="rx-header">
                        <span style="font-size:13px;font-weight:700;color:#0369a1;">{{ $rx->reference }}</span>
                        <span class="rx-ref">Prescription</span>
                    </div>
                    @if($rx->medications && count($rx->medications) > 0)
                    <table class="med-table">
                        <thead>
                            <tr>
                                <th>Medication</th>
                                <th>Dosage</th>
                                <th>Frequency</th>
                                <th>Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rx->medications as $med)
                            <tr>
                                <td>{{ $med['name'] ?? '—' }}</td>
                                <td>{{ $med['dosage'] ?? '—' }}</td>
                                <td>{{ $med['frequency'] ?? '—' }}</td>
                                <td>{{ $med['duration'] ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                    @if($rx->notes)
                    <p style="font-size:12px;color:#475569;margin:10px 0 0;">📝 {{ $rx->notes }}</p>
                    @endif
                </div>
                @endforeach
            </div>
            @endif

            @if($diagnosis->follow_up_date)
            <div class="follow-up">
                📅 Follow-up appointment recommended on <strong>{{ $diagnosis->follow_up_date->format('D, M j, Y') }}</strong>. Please book your next visit through DokHub.
            </div>
            @endif
        </div>
        @endforeach
        @endif

        @if($appointment->notes)
        <p class="section-title">Doctor's Notes</p>
        <div class="detail-card">
            <p style="margin:0;font-size:13px;color:#334155;line-height:1.6;">{{ $appointment->notes }}</p>
        </div>
        @endif

        <div class="note">
            🔒 This document contains your personal medical information. Please keep it confidential and share only with healthcare providers involved in your care.
        </div>

        <p style="font-size:13px;color:#64748b;">
            If you have any questions about your diagnosis or prescriptions, please contact your doctor or reply to this email.
        </p>
    </div>
    <div class="footer">
        <p style="margin:0 0 4px;">DOKHUB · Healthcare Appointment Platform</p>
        <p style="margin:0;">Questions? <a href="mailto:{{ config('mail.from.address') }}">Contact Support</a></p>
    </div>
</div>
</body>
</html>
