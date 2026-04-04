<?php

namespace App\Concerns;

use App\Models\Doctor;
use App\Models\DoctorSubUser;
use Illuminate\Http\Request;

trait ResolvesCurrentDoctor
{
    /**
     * Resolve the effective doctor and role for the current request.
     * Result is cached on the request attributes to avoid repeated DB hits.
     */
    private function resolveContext(Request $request): array
    {
        if ($request->attributes->has('_doctor_context')) {
            return $request->attributes->get('_doctor_context');
        }

        $user = $request->user();

        // Main doctor account
        $doctor = Doctor::where('user_id', $user->id)->first();
        if ($doctor) {
            $ctx = ['doctor' => $doctor, 'role' => 'owner', 'sub_user' => null];
            $request->attributes->set('_doctor_context', $ctx);
            return $ctx;
        }

        // Sub-user
        $subUser = DoctorSubUser::where('user_id', $user->id)
            ->where('is_active', true)
            ->with('doctor')
            ->first();

        if ($subUser && $subUser->doctor) {
            $ctx = ['doctor' => $subUser->doctor, 'role' => $subUser->role, 'sub_user' => $subUser];
            $request->attributes->set('_doctor_context', $ctx);
            return $ctx;
        }

        abort(403, 'Access denied.');
    }

    protected function getDoctor(Request $request): Doctor
    {
        return $this->resolveContext($request)['doctor'];
    }

    protected function currentRole(Request $request): string
    {
        return $this->resolveContext($request)['role'];
    }

    /**
     * Returns true if the current user (owner or sub-user) has the given permission.
     * Owners have all permissions.
     */
    protected function can(Request $request, string $permission): bool
    {
        $role = $this->currentRole($request);
        if ($role === 'owner') {
            return true;
        }
        return in_array($permission, DoctorSubUser::PERMISSIONS[$role] ?? []);
    }

    /**
     * Abort 403 if the current role lacks the given permission.
     */
    protected function assertPermission(Request $request, string $permission): void
    {
        if (! $this->can($request, $permission)) {
            abort(403, 'Your role does not have permission to perform this action.');
        }
    }

    /**
     * Require the user to be the owner (not a sub-user) of the doctor account.
     */
    protected function assertOwner(Request $request): void
    {
        if ($this->currentRole($request) !== 'owner') {
            abort(403, 'Only the account owner can perform this action.');
        }
    }
}
