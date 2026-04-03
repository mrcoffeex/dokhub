<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity;

class ActivityLog extends Activity
{
    protected $fillable = [
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'properties',
        'event',
        'batch_uuid',
        'ip_address',
        'user_agent',
    ];

    public function getDeviceSummaryAttribute(): array
    {
        if (empty($this->user_agent)) {
            return ['browser' => 'Unknown', 'os' => 'Unknown', 'device' => 'Unknown', 'is_bot' => false];
        }

        $agent = new \Jenssegers\Agent\Agent();
        $agent->setUserAgent($this->user_agent);

        return [
            'browser'  => $agent->browser() ?: 'Unknown',
            'os'       => $agent->platform() ?: 'Unknown',
            'device'   => $agent->isPhone() ? 'Mobile' : ($agent->isTablet() ? 'Tablet' : ($agent->isDesktop() ? 'Desktop' : 'Unknown')),
            'is_bot'   => $agent->isRobot(),
            'robot'    => $agent->isRobot() ? ($agent->robot() ?: 'Bot') : null,
        ];
    }
}
