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
