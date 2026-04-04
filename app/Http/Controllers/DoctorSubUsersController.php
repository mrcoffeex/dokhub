<?php

namespace App\Http\Controllers;

use App\Concerns\ResolvesCurrentDoctor;
use App\Models\DoctorSubUser;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DoctorSubUsersController extends Controller
{
    use ResolvesCurrentDoctor;

    public function index(Request $request): Response
    {
        $this->assertOwner($request);
        $doctor = $this->getDoctor($request);

        $subUsers = DoctorSubUser::where('doctor_id', $doctor->id)
            ->with('user:id,name,email,created_at')
            ->latest()
            ->get();

        return Inertia::render('Doctor/SubUsers/Index', [
            'subUsers'    => $subUsers,
            'permissions' => DoctorSubUser::PERMISSIONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->assertOwner($request);
        $doctor = $this->getDoctor($request);

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role'     => ['required', Rule::in(['secretary', 'nurse'])],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => 'sub_user',
        ]);

        DoctorSubUser::create([
            'doctor_id' => $doctor->id,
            'user_id'   => $user->id,
            'role'      => $validated['role'],
            'is_active' => true,
        ]);

        return back()->with('success', "{$user->name} has been added as a {$validated['role']}.");
    }

    public function update(Request $request, DoctorSubUser $subUser): RedirectResponse
    {
        $this->assertOwner($request);
        $doctor = $this->getDoctor($request);
        abort_unless($subUser->doctor_id === $doctor->id, 403);

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($subUser->user_id)],
            'role'     => ['required', Rule::in(['secretary', 'nurse'])],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $userData = ['name' => $validated['name'], 'email' => $validated['email']];
        if (!empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $subUser->user->update($userData);
        $subUser->update(['role' => $validated['role']]);

        return back()->with('success', "{$validated['name']}'s account has been updated.");
    }

    public function toggleActive(Request $request, DoctorSubUser $subUser): RedirectResponse
    {
        $this->assertOwner($request);
        $doctor = $this->getDoctor($request);
        abort_unless($subUser->doctor_id === $doctor->id, 403);

        $subUser->update(['is_active' => ! $subUser->is_active]);

        $status = $subUser->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Sub-user account has been {$status}.");
    }

    public function destroy(Request $request, DoctorSubUser $subUser): RedirectResponse
    {
        $this->assertOwner($request);
        $doctor = $this->getDoctor($request);
        abort_unless($subUser->doctor_id === $doctor->id, 403);

        $name = $subUser->user->name ?? 'Sub-user';

        // Delete the sub-user link and the user account
        $user = $subUser->user;
        $subUser->delete();
        $user?->delete();

        return back()->with('success', "{$name}'s account has been removed.");
    }
}
