export type Specialization = {
    id: number;
    name: string;
    is_active: boolean;
    sort_order: number;
    doctors_count: number;
    created_at: string;
    updated_at: string;
};

export type Insurance = {
    id: number;
    name: string;
    is_active: boolean;
    sort_order: number;
    doctors_count: number;
    created_at: string;
    updated_at: string;
};

export type DoctorSchedule = {
    id: number;
    doctor_id: number;
    day_of_week: number;
    start_time: string;
    end_time: string;
    slot_duration_minutes: number;
    is_active: boolean;
};

export type EducationEntry = {
    degree: string;
    institution: string;
    year?: number | string | null;
};

export type AffiliationEntry = {
    name: string;
    role?: string | null;
};

export type CertificationEntry = {
    name: string;
    issuer?: string | null;
    year?: number | string | null;
};

export type Doctor = {
    id: number;
    user_id: number | null;
    name: string;
    slug: string;
    email: string;
    phone: string | null;
    specialization: string[];
    qualification: string | null;
    bio: string | null;
    avatar: string | null;
    avatar_url: string;
    experience_years: number;
    consultation_fee: string;
    status: 'pending' | 'approved' | 'suspended';
    location: string | null;
    latitude: number | null;
    longitude: number | null;
    languages: string[] | null;
    insurance?: string[] | null;
    appointment_modes?: ('in_person' | 'online')[] | null;
    education?: EducationEntry[] | null;
    affiliations?: AffiliationEntry[] | null;
    certifications?: CertificationEntry[] | null;
    id_documents?: string[] | null;
    schedules?: DoctorSchedule[];
    appointments_count?: number;
    reviews_count?: number;
    review_avg_rating?: number | null;
    created_at: string;
    updated_at: string;
};

export type Appointment = {
    id: number;
    reference: string;
    appointment_type: 'in_person' | 'online';
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

export type PatientRecord = {
    id: number;
    doctor_id: number;
    patient_id: number;
    name: string;
    original_name: string;
    mime_type: string;
    file_size: number;
    created_at: string;
    updated_at: string;
};

export type PatientVital = {
    id: number;
    patient_id: number;
    recorded_at: string;
    blood_pressure_systolic: number | null;
    blood_pressure_diastolic: number | null;
    heart_rate: number | null;
    temperature: number | null;
    weight: number | null;
    height: number | null;
    oxygen_saturation: number | null;
    notes: string | null;
    created_at: string;
    updated_at: string;
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
    vitals?: PatientVital[];
    records?: PatientRecord[];
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

export type DoctorPlan = {
    plan: 'basic' | 'pro';
    hasProAccess: boolean;
    isInTrial: boolean;
    isPaidPro: boolean;
    trialDays: number;
};

export type InventoryCategory = 'medicine' | 'equipment' | 'supplies' | 'consumables' | 'other';
export type InventoryMovementType = 'restock' | 'usage' | 'adjustment' | 'expired';

export type InventoryItem = {
    id: number;
    doctor_id: number;
    name: string;
    category: InventoryCategory;
    sku: string | null;
    unit: string;
    description: string | null;
    current_stock: number;
    min_stock: number;
    cost_price: string | null;
    selling_price: string | null;
    expiry_date: string | null;
    created_at: string;
    updated_at: string;
};

export type InventoryMovement = {
    id: number;
    inventory_item_id: number;
    doctor_id: number;
    type: InventoryMovementType;
    quantity: number;
    note: string | null;
    created_at: string;
    updated_at: string;
};

export type InventoryStats = {
    total: number;
    out_of_stock: number;
    low_stock: number;
    expiring_soon: number;
    expired: number;
    total_cost_value: number | string;
};

export type SubUserRole = 'secretary' | 'nurse';

export type DoctorSubUser = {
    id: number;
    doctor_id: number;
    user_id: number;
    role: SubUserRole;
    is_active: boolean;
    user?: {
        id: number;
        name: string;
        email: string;
        created_at: string;
    };
    created_at: string;
    updated_at: string;
};

export type PageProps = {
    flash: { success: string | null; error: string | null };
    doctor_plan: DoctorPlan | null;
    /** null when user is the doctor owner; role string when user is a sub-user */
    sub_user_context: SubUserRole | null;
    [key: string]: unknown;
};
