<?php

namespace App\Http\Controllers;

use App\Concerns\ResolvesCurrentDoctor;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PatientRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DoctorPatientRecordsController extends Controller
{
    use ResolvesCurrentDoctor;

    public function store(Request $request, Patient $patient)
    {
        $this->assertPermission($request, 'records.manage');
        $doctor = $this->getDoctor($request);
        abort_unless($patient->doctor_id === $doctor->id, 403);

        $validated = $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx,txt'],
            'name' => ['nullable', 'string', 'max:200'],
        ]);

        $file         = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $filename     = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path         = $file->storeAs("patient-records/{$patient->id}", $filename, 'local');

        PatientRecord::create([
            'doctor_id'     => $doctor->id,
            'patient_id'    => $patient->id,
            'name'          => $validated['name'] ?: pathinfo($originalName, PATHINFO_FILENAME),
            'original_name' => $originalName,
            'file_path'     => $path,
            'mime_type'     => $file->getMimeType(),
            'file_size'     => $file->getSize(),
        ]);

        return back()->with('success', 'Record uploaded successfully.');
    }

    public function destroy(Request $request, Patient $patient, PatientRecord $record)
    {
        $this->assertPermission($request, 'records.manage');
        $doctor = $this->getDoctor($request);
        abort_unless($patient->doctor_id === $doctor->id, 403);
        abort_unless($record->patient_id === $patient->id, 403);
        abort_unless($record->doctor_id === $doctor->id, 403);

        Storage::disk('local')->delete($record->file_path);
        $record->delete();

        return back()->with('success', 'Record deleted.');
    }

    public function download(Request $request, Patient $patient, PatientRecord $record): BinaryFileResponse
    {
        $this->assertPermission($request, 'records.manage');
        $doctor = $this->getDoctor($request);
        abort_unless($patient->doctor_id === $doctor->id, 403);
        abort_unless($record->patient_id === $patient->id, 403);
        abort_unless($record->doctor_id === $doctor->id, 403);

        abort_unless(Storage::disk('local')->exists($record->file_path), 404);

        return response()->download(
            Storage::disk('local')->path($record->file_path),
            $record->original_name
        );
    }
}
