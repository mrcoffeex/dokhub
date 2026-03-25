export type DoctorSchedule = {
    id: number;
    doctor_id: number;
    day_of_week: number;
    start_time: string;
    end_time: string;
    slot_duration_minutes: number;
    is_active: boolean;
};

export type Doctor = {
    id: number;
    name: string;
    slug: string;
    email: string;
    phone: string | null;
    specialization: string;
    qualification: string | null;
    bio: string | null;
    avatar: string | null;
    avatar_url: string;
    experience_years: number;
    consultation_fee: string;
    status: 'pending' | 'approved' | 'suspended';
    location: string | null;
    languages: string[] | null;
    schedules?: DoctorSchedule[];
    appointments_count?: number;
    created_at: string;
    updated_at: string;
};

export type Appointment = {
    id: number;
    reference: string;
    doctor_id: number;
    doctor?: Doctor;
    patient_name: string;
    patient_email: string;
    patient_phone: string;
    appointment_date: string;
    appointment_time: string;
    reason: string | null;
    notes: string | null;
    status: 'pending' | 'confirmed' | 'cancelled' | 'completed';
    cancellation_reason: string | null;
    confirmed_at: string | null;
    created_at: string;
    updated_at: string;
};

export type DoctorReview = {
    id: number;
    patient_name: string;
    rating: number;
    comment: string | null;
    created_at: string;
};

export type ReviewStats = {
    average: number;
    total: number;
    counts: Record<number, number>;
};

export type Patient = {
    id: number;
    doctor_id: number;
    name: string;
    email: string;
    phone: string | null;
    gender: 'male' | 'female' | 'other' | null;
    date_of_birth: string | null;
    age: number | null;
    allergies: string | null;
    medical_history: string | null;
    notes: string | null;
    first_seen_at: string | null;
    diagnoses?: Diagnosis[];
    prescriptions?: Prescription[];
    diagnoses_count?: number;
    prescriptions_count?: number;
    created_at: string;
    updated_at: string;
};

export type Diagnosis = {
    id: number;
    doctor_id: number;
    patient_id: number;
    appointment_id: number | null;
    appointment?: Pick<Appointment, 'id' | 'reference' | 'appointment_date'>;
    title: string;
    symptoms: string | null;
    description: string | null;
    treatment: string | null;
    follow_up_date: string | null;
    prescriptions?: Prescription[];
    created_at: string;
    updated_at: string;
};

export type Prescription = {
    id: number;
    reference: string;
    doctor_id: number;
    patient_id: number;
    diagnosis_id: number | null;
    diagnosis?: Pick<Diagnosis, 'id' | 'title'> | null;
    patient?: Patient;
    medications: PrescriptionMedication[];
    notes: string | null;
    created_at: string;
    updated_at: string;
};

export type PrescriptionMedication = {
    name: string;
    dosage: string;
    frequency: string;
    duration: string;
    instructions: string | null;
};

export type PaginatedData<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: { url: string | null; label: string; active: boolean }[];
};

export type PageProps = {
    flash: { success: string | null; error: string | null };
    [key: string]: unknown;
};
