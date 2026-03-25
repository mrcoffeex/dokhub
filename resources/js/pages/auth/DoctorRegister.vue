<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { ref } from 'vue';

const page = usePage();

const form = ref({
    name: '',
    email: '',
    phone: '',
    specialization: [] as string[],
    qualification: '',
    bio: '',
    experience_years: '',
    consultation_fee: '',
    location: '',
    languages: 'English',
    password: '',
    password_confirmation: '',
});

const processing = ref(false);
const errors = ref<Record<string, string>>({});

const specializations = [
    'Allergy & Immunology', 'Anesthesiology', 'Cardiology', 'Colorectal Surgery',
    'Critical Care Medicine', 'Dermatology', 'Emergency Medicine', 'Endocrinology',
    'Family Medicine', 'Gastroenterology', 'General Surgery', 'Geriatrics',
    'Hematology', 'Infectious Disease', 'Internal Medicine', 'Nephrology',
    'Neurology', 'Neurosurgery', 'Obstetrics & Gynecology', 'Oncology',
    'Ophthalmology', 'Orthopedic Surgery', 'Otolaryngology (ENT)', 'Pathology',
    'Pediatrics', 'Physical Medicine & Rehabilitation', 'Plastic Surgery',
    'Psychiatry', 'Pulmonology', 'Radiology', 'Rheumatology', 'Thoracic Surgery',
    'Urology', 'Vascular Surgery',
];

async function handleSubmit() {
    processing.value = true;
    errors.value = {};
    try {
        const headers: Record<string, string> = {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        };

        // Get CSRF token from meta tag or use it if available
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            headers['X-CSRF-Token'] = csrfToken;
        }

        const res = await fetch('/api/doctor-register', {
            method: 'POST',
            headers,
            body: JSON.stringify(form.value),
        });
        const data = await res.json();
        if (!res.ok) {
            errors.value = data.errors || { form: 'Registration failed' };
            processing.value = false;
            return;
        }
        window.location.href = '/auth/doctor-signup-success';
    } catch (err: any) {
        errors.value.form = err.message || 'An error occurred';
        processing.value = false;
    }
}
</script>

<template>
    <AuthBase
        title="Join DokHub as a Doctor"
        description="Fill out your professional profile to get started"
    >
        <Head title="Doctor Registration" />

        <form @submit.prevent="handleSubmit" class="flex flex-col gap-6">
            <div class="grid gap-5">
                <!-- Personal Info -->
                <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-4">
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">Personal Information</h3>
                    <div class="grid gap-4">
                        <div class="grid gap-2.5">
                            <Label for="name" class="text-sm font-semibold text-gray-700">Full name</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                autofocus
                                placeholder="Dr. John Doe"
                                class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100"
                            />
                            <InputError :message="errors.name" class="text-xs" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2.5">
                                <Label for="email" class="text-sm font-semibold text-gray-700">Email</Label>
                                <Input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    required
                                    placeholder="doctor@example.com"
                                    class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100"
                                />
                                <InputError :message="errors.email" class="text-xs" />
                            </div>
                            <div class="grid gap-2.5">
                                <Label for="phone" class="text-sm font-semibold text-gray-700">Phone</Label>
                                <Input
                                    id="phone"
                                    v-model="form.phone"
                                    type="tel"
                                    required
                                    placeholder="+1 (555) 000-0000"
                                    class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100"
                                />
                                <InputError :message="errors.phone" class="text-xs" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Professional Info -->
                <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-4">
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">Professional Information</h3>
                    <div class="grid gap-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2.5">
                                <Label for="specialization" class="text-sm font-semibold text-gray-700">Specialization</Label>
                                <div
                                    class="max-h-40 overflow-y-auto rounded-xl border border-gray-200 bg-white p-3"
                                    :class="{ 'border-red-400': errors.specialization }"
                                >
                                    <div class="flex flex-wrap gap-1.5">
                                        <button
                                            v-for="spec in specializations" :key="spec"
                                            type="button"
                                            @click="form.specialization.includes(spec) ? form.specialization = form.specialization.filter(x => x !== spec) : form.specialization.push(spec)"
                                            class="rounded-lg border px-2.5 py-1 text-xs font-medium transition"
                                            :class="form.specialization.includes(spec)
                                                ? 'border-violet-500 bg-violet-600 text-white'
                                                : 'border-gray-200 text-gray-600 hover:border-violet-300 hover:text-violet-600'"
                                        >
                                            {{ spec }}
                                        </button>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-400">
                                    {{ form.specialization.length ? form.specialization.length + ' selected' : 'Select one or more' }}
                                </p>
                                <InputError :message="errors.specialization" class="text-xs" />
                            </div>
                            <div class="grid gap-2.5">
                                <Label for="qualification" class="text-sm font-semibold text-gray-700">Qualification</Label>
                                <Input
                                    id="qualification"
                                    v-model="form.qualification"
                                    type="text"
                                    required
                                    placeholder="e.g., MBBS, MD, Board Certified"
                                    class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100"
                                />
                                <InputError :message="errors.qualification" class="text-xs" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2.5">
                                <Label for="experience_years" class="text-sm font-semibold text-gray-700">Years of Experience</Label>
                                <Input
                                    id="experience_years"
                                    v-model.number="form.experience_years"
                                    type="number"
                                    min="0"
                                    max="70"
                                    required
                                    placeholder="10"
                                    class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100"
                                />
                                <InputError :message="errors.experience_years" class="text-xs" />
                            </div>
                            <div class="grid gap-2.5">
                                <Label for="consultation_fee" class="text-sm font-semibold text-gray-700">Consultation Fee ($)</Label>
                                <Input
                                    id="consultation_fee"
                                    v-model.number="form.consultation_fee"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    required
                                    placeholder="50.00"
                                    class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100"
                                />
                                <InputError :message="errors.consultation_fee" class="text-xs" />
                            </div>
                        </div>

                        <div class="grid gap-2.5">
                            <Label for="location" class="text-sm font-semibold text-gray-700">Location</Label>
                            <Input
                                id="location"
                                v-model="form.location"
                                type="text"
                                required
                                placeholder="City, State"
                                class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100"
                            />
                            <InputError :message="errors.location" class="text-xs" />
                        </div>

                        <div class="grid gap-2.5">
                            <Label for="bio" class="text-sm font-semibold text-gray-700">Professional Bio</Label>
                            <textarea
                                id="bio"
                                v-model="form.bio"
                                required
                                rows="3"
                                placeholder="Tell patients about your experience, approach, and specialties..."
                                class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100"
                            />
                            <InputError :message="errors.bio" class="text-xs" />
                        </div>

                        <div class="grid gap-2.5">
                            <Label for="languages" class="text-sm font-semibold text-gray-700">Languages (comma-separated)</Label>
                            <Input
                                id="languages"
                                v-model="form.languages"
                                type="text"
                                required
                                placeholder="English, Spanish, French"
                                class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100"
                            />
                            <InputError :message="errors.languages" class="text-xs" />
                        </div>
                    </div>
                </div>

                <!-- Security -->
                <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-4">
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">Security</h3>
                    <div class="grid gap-4">
                        <div class="grid gap-2.5">
                            <Label for="password" class="text-sm font-semibold text-gray-700">Password</Label>
                            <PasswordInput
                                id="password"
                                v-model="form.password"
                                required
                                autocomplete="new-password"
                                placeholder="••••••••"
                                class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100"
                            />
                            <InputError :message="errors.password" class="text-xs" />
                        </div>

                        <div class="grid gap-2.5">
                            <Label for="password_confirmation" class="text-sm font-semibold text-gray-700">Confirm Password</Label>
                            <PasswordInput
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                required
                                autocomplete="new-password"
                                placeholder="••••••••"
                                class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100"
                            />
                            <InputError :message="errors.password_confirmation" class="text-xs" />
                        </div>
                    </div>
                </div>

                <div v-if="errors.form" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                    {{ errors.form }}
                </div>

                <Button
                    type="submit"
                    class="mt-2 w-full rounded-xl bg-violet-600 px-4 py-2.5 font-semibold text-white hover:bg-violet-700 transition-colors"
                    :disabled="processing"
                >
                    <Spinner v-if="processing" class="mr-2 h-4 w-4" />
                    {{ processing ? 'Creating profile...' : 'Complete Registration' }}
                </Button>
            </div>

            <div class="border-t border-gray-100 pt-5">
                <p class="text-center text-sm text-gray-600">
                    Already have an account?
                    <TextLink
                        href="/login"
                        class="text-violet-600 hover:text-violet-700 font-semibold"
                    >
                        Sign in
                    </TextLink>
                </p>
                <p class="mt-3 text-center text-xs text-gray-500">
                    By registering, you agree to our Terms of Service and Privacy Policy. Your profile will be reviewed by our team before approval.
                </p>
            </div>
        </form>
    </AuthBase>
</template>
