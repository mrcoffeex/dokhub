<?php

namespace App\Http\Controllers;

use App\Concerns\ResolvesCurrentDoctor;
use App\Models\Diagnosis;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PatientRecord;
use App\Models\PatientVital;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DoctorPatientTransferController extends Controller
{
    use ResolvesCurrentDoctor;

    public function store(Request $request, Patient $patient)
    {
        $this->assertOwner($request);
        $doctor = $this->getDoctor($request);
        abort_unless($patient->doctor_id === $doctor->id, 403);

        $validated = $request->validate([
            'target_doctor_id' => ['required', 'integer', 'exists:doctors,id'],
        ]);

        $targetDoctorId = (int) $validated['target_doctor_id'];

        abort_if($targetDoctorId === $doctor->id, 422, 'Cannot send a copy to yourself.');

        $targetDoctor = Doctor::where('id', $targetDoctorId)
            ->where('status', 'approved')
            ->firstOrFail();

        DB::transaction(function () use ($patient, $doctor, $targetDoctor) {
            // Copy patient demographics
            $newPatient = $patient->replicate(['first_seen_at']);
            $newPatient->doctor_id = $targetDoctor->id;
            $newPatient->first_seen_at = now();
            $newPatient->save();

            // Copy diagnoses (without appointment link — belongs to original doctor)
            $diagIdMap = [];
            foreach ($patient->diagnoses()->where('doctor_id', $doctor->id)->get() as $diag) {
                $newDiag = $diag->replicate();
                $newDiag->patient_id = $newPatient->id;
                $newDiag->doctor_id = $targetDoctor->id;
                $newDiag->appointment_id = null;
                $newDiag->save();
                $diagIdMap[$diag->id] = $newDiag->id;
            }

            // Copy prescriptions
            foreach ($patient->prescriptions()->where('doctor_id', $doctor->id)->get() as $rx) {
                $newRx = $rx->replicate();
                $newRx->patient_id = $newPatient->id;
                $newRx->doctor_id = $targetDoctor->id;
                $newRx->reference = Prescription::generateReference();
                $newRx->diagnosis_id = $diagIdMap[$rx->diagnosis_id] ?? null;
                $newRx->save();
            }

            // Copy vitals
            foreach ($patient->vitals as $vital) {
                $newVital = $vital->replicate();
                $newVital->patient_id = $newPatient->id;
                $newVital->save();
            }

            // Copy uploaded patient record files
            foreach (PatientRecord::where('patient_id', $patient->id)->where('doctor_id', $doctor->id)->get() as $record) {
                $newFilePath = 'patient-records/' . $newPatient->id . '/' . basename($record->file_path);
                if (Storage::disk('local')->exists($record->file_path)) {
                    Storage::disk('local')->copy($record->file_path, $newFilePath);
                }

                $newRecord = $record->replicate();
                $newRecord->patient_id = $newPatient->id;
                $newRecord->doctor_id = $targetDoctor->id;
                $newRecord->file_path = $newFilePath;
                $newRecord->save();
            }
        });

        return back()->with('success', "A copy of {$patient->name}'s records has been sent to Dr. {$targetDoctor->name}.");
    }
}
