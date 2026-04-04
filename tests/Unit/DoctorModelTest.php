<?php

use App\Models\Doctor;
use App\Models\User;

describe('Doctor::isApproved()', function () {
    it('returns true when status is approved', function () {
        $doctor = Doctor::factory()->approved()->make();
        expect($doctor->isApproved())->toBeTrue();
    });

    it('returns false when status is pending', function () {
        $doctor = Doctor::factory()->pending()->make();
        expect($doctor->isApproved())->toBeFalse();
    });

    it('returns false when status is suspended', function () {
        $doctor = Doctor::factory()->suspended()->make();
        expect($doctor->isApproved())->toBeFalse();
    });
});

describe('Doctor::isInTrial()', function () {
    it('returns true when trial_ends_at is in the future', function () {
        $doctor = Doctor::factory()->inTrial()->make();
        expect($doctor->isInTrial())->toBeTrue();
    });

    it('returns false when trial_ends_at is null', function () {
        $doctor = Doctor::factory()->make(['trial_ends_at' => null]);
        expect($doctor->isInTrial())->toBeFalse();
    });

    it('returns false when trial_ends_at is in the past', function () {
        $doctor = Doctor::factory()->make(['trial_ends_at' => now()->subDay()]);
        expect($doctor->isInTrial())->toBeFalse();
    });
});

describe('Doctor::isPaidPro()', function () {
    it('returns true when plan is pro and no expiry set', function () {
        $doctor = Doctor::factory()->make(['plan' => 'pro', 'pro_expires_at' => null]);
        expect($doctor->isPaidPro())->toBeTrue();
    });

    it('returns true when plan is pro and expiry is in the future', function () {
        $doctor = Doctor::factory()->pro()->make();
        expect($doctor->isPaidPro())->toBeTrue();
    });

    it('returns false when plan is free', function () {
        $doctor = Doctor::factory()->make(['plan' => 'free', 'pro_expires_at' => null]);
        expect($doctor->isPaidPro())->toBeFalse();
    });

    it('returns false when plan is pro but expired', function () {
        $doctor = Doctor::factory()->make(['plan' => 'pro', 'pro_expires_at' => now()->subDay()]);
        expect($doctor->isPaidPro())->toBeFalse();
    });
});

describe('Doctor::hasProAccess()', function () {
    it('returns true when in trial', function () {
        $doctor = Doctor::factory()->inTrial()->make();
        expect($doctor->hasProAccess())->toBeTrue();
    });

    it('returns true when paid pro', function () {
        $doctor = Doctor::factory()->pro()->make();
        expect($doctor->hasProAccess())->toBeTrue();
    });

    it('returns false when free plan and no trial', function () {
        $doctor = Doctor::factory()->make(['plan' => 'free', 'trial_ends_at' => null, 'pro_expires_at' => null]);
        expect($doctor->hasProAccess())->toBeFalse();
    });
});

describe('Doctor::trialDaysRemaining()', function () {
    it('returns days remaining when in trial', function () {
        $doctor = Doctor::factory()->make(['trial_ends_at' => now()->addDays(5)]);
        expect($doctor->trialDaysRemaining())->toBeGreaterThanOrEqual(4)->toBeLessThanOrEqual(5);
    });

    it('returns 0 when not in trial', function () {
        $doctor = Doctor::factory()->make(['trial_ends_at' => null]);
        expect($doctor->trialDaysRemaining())->toBe(0);
    });
});

describe('Doctor::generateUniqueSlug()', function () {
    it('generates a slug from a name', function () {
        $slug = Doctor::generateUniqueSlug('Dr John Smith');
        expect($slug)->toBe('dr-john-smith');
    });

    it('appends a number when slug already exists', function () {
        Doctor::factory()->approved()->create(['slug' => 'dr-john-smith']);

        $slug = Doctor::generateUniqueSlug('Dr John Smith');
        expect($slug)->toBe('dr-john-smith-2');
    });

    it('keeps incrementing to find unique slug', function () {
        Doctor::factory()->approved()->create(['slug' => 'dr-jane']);
        Doctor::factory()->approved()->create(['slug' => 'dr-jane-2']);

        $slug = Doctor::generateUniqueSlug('Dr Jane');
        expect($slug)->toBe('dr-jane-3');
    });
});
