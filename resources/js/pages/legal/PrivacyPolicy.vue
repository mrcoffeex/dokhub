<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Shield, ChevronLeft } from 'lucide-vue-next';

const lastUpdated = 'March 26, 2026';
const effectiveDate = 'March 26, 2026';
</script>

<template>
    <Head title="Privacy Policy — DokHub" />

    <div class="min-h-screen bg-gray-50 dark:bg-gray-950">

        <!-- Top nav bar -->
        <header class="sticky top-0 z-10 border-b border-gray-200 bg-white/90 backdrop-blur dark:border-gray-800 dark:bg-gray-900/90">
            <div class="mx-auto flex max-w-4xl items-center justify-between px-6 py-3.5">
                <Link href="/" class="flex items-center gap-2 font-bold text-gray-900 dark:text-white">
                    <img src="/logo.png" alt="DokHub" class="h-7 w-auto" />
                    DokHub
                </Link>
                <Link href="/" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm text-gray-500 transition hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100">
                    <ChevronLeft class="h-4 w-4" />
                    Back to home
                </Link>
            </div>
        </header>

        <main class="mx-auto max-w-4xl px-6 py-12">

            <!-- Page header -->
            <div class="mb-10">
                <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 dark:border-emerald-800/40 dark:bg-emerald-900/20">
                    <Shield class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400" />
                    <span class="text-xs font-medium text-emerald-700 dark:text-emerald-300">Legal Document</span>
                </div>
                <h1 class="text-4xl font-bold tracking-tight text-gray-900 dark:text-white">Privacy Policy</h1>
                <p class="mt-3 text-base text-gray-500 dark:text-gray-400">
                    We are committed to protecting your personal and health information.
                </p>
                <div class="mt-4 flex flex-wrap gap-4 text-sm text-gray-400 dark:text-gray-500">
                    <span><strong class="text-gray-600 dark:text-gray-300">Effective:</strong> {{ effectiveDate }}</span>
                    <span><strong class="text-gray-600 dark:text-gray-300">Last updated:</strong> {{ lastUpdated }}</span>
                </div>
            </div>

            <!-- Summary callout -->
            <div class="mb-10 overflow-hidden rounded-2xl border border-emerald-200 bg-emerald-50 dark:border-emerald-800/40 dark:bg-emerald-900/10">
                <div class="border-b border-emerald-200 bg-emerald-100/60 px-6 py-3.5 dark:border-emerald-800/40 dark:bg-emerald-900/20">
                    <h2 class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">Privacy at a Glance</h2>
                </div>
                <div class="grid gap-0 divide-y divide-emerald-200 px-6 dark:divide-emerald-800/40">
                    <div v-for="item in [
                        { label: 'Data we collect', text: 'Name, email, phone, location, professional credentials, and appointment/health records you create.' },
                        { label: 'Why we collect it', text: 'To operate the platform, connect patients with doctors, and comply with legal obligations.' },
                        { label: 'Who we share with', text: 'We do not sell your data. We share only with service providers, legal authorities when required, and parties you explicitly authorize.' },
                        { label: 'Your rights', text: 'You may access, correct, export, or delete your data at any time by contacting us.' },
                        { label: 'Health data', text: 'Appointment records and prescriptions may constitute Protected Health Information (PHI) and are handled with heightened care in line with applicable law.' },
                    ]" :key="item.label" class="py-3.5">
                        <div class="flex gap-3">
                            <span class="w-40 shrink-0 text-sm font-semibold text-emerald-800 dark:text-emerald-300">{{ item.label }}</span>
                            <span class="text-sm leading-relaxed text-emerald-700 dark:text-emerald-400">{{ item.text }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table of contents -->
            <div class="mb-10 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 bg-gray-50/80 px-6 py-3.5 dark:border-gray-800 dark:bg-gray-800/50">
                    <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Table of Contents</h2>
                </div>
                <ol class="grid gap-0 divide-y divide-gray-100 px-6 dark:divide-gray-800">
                    <li v-for="(section, i) in [
                        'Who We Are',
                        'Information We Collect',
                        'How We Use Your Information',
                        'Health Information & HIPAA',
                        'Cookies & Tracking Technologies',
                        'How We Share Your Information',
                        'Data Retention',
                        'Data Security',
                        'Your Rights & Choices',
                        'Children\'s Privacy',
                        'International Data Transfers',
                        'Third-Party Links & Services',
                        'Changes to This Policy',
                        'Contact Us',
                    ]" :key="i" class="py-2.5">
                        <a :href="'#pp-section-' + (i + 1)" class="flex items-center gap-2 text-sm text-orange-600 hover:text-orange-800 dark:text-orange-400 dark:hover:text-orange-200">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-orange-50 text-[11px] font-bold text-orange-600 dark:bg-orange-900/40 dark:text-orange-400">{{ i + 1 }}</span>
                            {{ section }}
                        </a>
                    </li>
                </ol>
            </div>

            <!-- Sections -->
            <div class="space-y-10 text-gray-700 dark:text-gray-300">

                <section id="pp-section-1">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">1. Who We Are</h2>
                    <p class="leading-relaxed">DokHub, Inc. ("DokHub", "we", "our", or "us") operates the DokHub platform — a healthcare technology service that connects patients with licensed medical professionals for appointment booking, prescription management, and digital health record keeping ("Service").</p>
                    <p class="mt-3 leading-relaxed">This Privacy Policy explains how we collect, use, disclose, and protect your personal information when you use the Service. It applies to all users, including patients, registered doctors, and website visitors.</p>
                </section>

                <section id="pp-section-2">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">2. Information We Collect</h2>

                    <h3 class="mb-2 mt-5 text-base font-semibold text-gray-800 dark:text-gray-200">2.1 Information You Provide</h3>
                    <ul class="list-disc space-y-1.5 pl-6">
                        <li><strong>Account information:</strong> Name, email address, phone number, password, and role (patient or doctor)</li>
                        <li><strong>Doctor profile information:</strong> Medical specialization(s), qualifications, years of experience, consultation fee, clinic location, languages spoken, and professional biography</li>
                        <li><strong>Appointment information:</strong> Date, time, reason for visit, appointment status, and any notes</li>
                        <li><strong>Health records:</strong> Diagnoses, prescriptions, medications, and related clinical notes created by doctors during consultations</li>
                        <li><strong>Payment information:</strong> Billing details processed by our third-party payment processors (we do not store full card numbers)</li>
                        <li><strong>Reviews:</strong> Ratings and written reviews submitted about doctors</li>
                        <li><strong>Communications:</strong> Messages sent to DokHub support</li>
                    </ul>

                    <h3 class="mb-2 mt-5 text-base font-semibold text-gray-800 dark:text-gray-200">2.2 Information Collected Automatically</h3>
                    <ul class="list-disc space-y-1.5 pl-6">
                        <li><strong>Log data:</strong> IP address, browser type, operating system, referring URL, pages visited, and timestamps</li>
                        <li><strong>Device information:</strong> Device identifiers, screen resolution, and hardware model</li>
                        <li><strong>Cookies and similar technologies:</strong> Session tokens, analytics identifiers, and preference cookies (see Section 5)</li>
                    </ul>

                    <h3 class="mb-2 mt-5 text-base font-semibold text-gray-800 dark:text-gray-200">2.3 Information from Third Parties</h3>
                    <p class="leading-relaxed">We may receive information about you from third-party identity verification or medical licensing verification services used to confirm doctor credentials, and from payment processors in connection with transactions on the platform.</p>
                </section>

                <section id="pp-section-3">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">3. How We Use Your Information</h2>
                    <p class="leading-relaxed">We use your information for the following purposes:</p>
                    <ul class="mt-3 list-disc space-y-1.5 pl-6">
                        <li><strong>Service delivery:</strong> To create and manage your account, facilitate appointment booking, and enable doctor–patient interactions</li>
                        <li><strong>Doctor verification:</strong> To review and approve doctor applications, including credential validation</li>
                        <li><strong>Communications:</strong> To send appointment confirmations, reminders, service updates, and responses to your support requests</li>
                        <li><strong>Payments:</strong> To process consultation fees and maintain transaction records</li>
                        <li><strong>Safety & integrity:</strong> To detect and prevent fraud, unauthorized access, and abuse of the platform</li>
                        <li><strong>Analytics & improvement:</strong> To analyze usage patterns and improve Service functionality (using aggregated or anonymized data where possible)</li>
                        <li><strong>Legal compliance:</strong> To fulfill our obligations under applicable law, respond to lawful requests, and enforce our Terms of Service</li>
                    </ul>
                    <p class="mt-3 leading-relaxed">We do not use health information you provide for advertising, marketing profiling, or sale to data brokers.</p>
                </section>

                <section id="pp-section-4">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">4. Health Information &amp; HIPAA</h2>
                    <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-800/40 dark:bg-blue-900/10">
                        <p class="font-semibold text-blue-800 dark:text-blue-300">Protected Health Information</p>
                        <p class="mt-1 text-sm leading-relaxed text-blue-700 dark:text-blue-400">Certain information on our platform — including appointment records, diagnoses, and prescriptions — may constitute Protected Health Information (PHI) under the Health Insurance Portability and Accountability Act (HIPAA) or equivalent local health privacy laws. DokHub takes heightened precautions with such data.</p>
                    </div>
                    <ul class="mt-4 list-disc space-y-1.5 pl-6">
                        <li>PHI is accessible only to the treating doctor and the relevant patient, and to authorized DokHub personnel for platform operations</li>
                        <li>We do not disclose PHI to third parties without your explicit consent, except as required by law or in a medical emergency</li>
                        <li>Doctors on DokHub are independently responsible for their own HIPAA compliance obligations as covered entities or business associates where applicable</li>
                        <li>We implement administrative, physical, and technical safeguards appropriate for the sensitivity of health data</li>
                    </ul>
                </section>

                <section id="pp-section-5">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">5. Cookies &amp; Tracking Technologies</h2>
                    <p class="leading-relaxed">We use cookies and similar technologies to operate the Service. Types of cookies we use:</p>
                    <div class="mt-4 overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            <div v-for="cookie in [
                                { type: 'Essential', purpose: 'Required for authentication, session management, and CSRF protection. Cannot be disabled.' },
                                { type: 'Functional', purpose: 'Remember your preferences such as language and display settings.' },
                                { type: 'Analytics', purpose: 'Aggregate usage data to understand how users interact with the Service. Data is anonymized or pseudonymized.' },
                            ]" :key="cookie.type" class="flex gap-4 px-5 py-3.5">
                                <span class="w-24 shrink-0 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ cookie.type }}</span>
                                <span class="text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ cookie.purpose }}</span>
                            </div>
                        </div>
                    </div>
                    <p class="mt-3 leading-relaxed">You can control cookies through your browser settings. Disabling essential cookies will impact Service functionality. We do not use third-party advertising cookies.</p>
                </section>

                <section id="pp-section-6">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">6. How We Share Your Information</h2>
                    <p class="leading-relaxed">We do not sell, rent, or trade your personal information. We share information only in the following circumstances:</p>
                    <ul class="mt-3 list-disc space-y-2 pl-6">
                        <li><strong>With doctors and patients:</strong> Information necessary to facilitate booked appointments is shared between the relevant doctor and patient</li>
                        <li><strong>With service providers:</strong> Trusted vendors who help us operate the Service (hosting, email delivery, payment processing, analytics) under strict data processing agreements</li>
                        <li><strong>For legal compliance:</strong> When required by law, court order, or governmental authority, or to protect the rights, property, or safety of DokHub, our users, or the public</li>
                        <li><strong>Business transfers:</strong> In the event of a merger, acquisition, or sale of assets, your information may be transferred to the successor entity, with prior notice to you</li>
                        <li><strong>With your consent:</strong> For any other purpose with your explicit prior consent</li>
                    </ul>
                </section>

                <section id="pp-section-7">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">7. Data Retention</h2>
                    <p class="leading-relaxed">We retain your personal information for as long as your account is active or as needed to provide the Service. Specific retention periods:</p>
                    <ul class="mt-3 list-disc space-y-1.5 pl-6">
                        <li><strong>Account data:</strong> Retained until account deletion, plus up to 90 days for fraud prevention</li>
                        <li><strong>Health records (appointments, prescriptions, diagnoses):</strong> Retained for a minimum of 7 years to comply with applicable medical record-keeping laws, unless a longer period is required</li>
                        <li><strong>Payment records:</strong> Retained for 7 years for financial compliance and dispute resolution</li>
                        <li><strong>Log data:</strong> Retained for up to 12 months</li>
                    </ul>
                    <p class="mt-3 leading-relaxed">After the applicable retention period, data is securely deleted or anonymized.</p>
                </section>

                <section id="pp-section-8">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">8. Data Security</h2>
                    <p class="leading-relaxed">We implement industry-standard security measures to protect your information, including:</p>
                    <ul class="mt-3 list-disc space-y-1.5 pl-6">
                        <li>Encryption in transit via TLS/HTTPS for all data communications</li>
                        <li>Bcrypt hashing for all user passwords — plaintext passwords are never stored</li>
                        <li>Role-based access controls limiting staff access to personal and health data</li>
                        <li>Regular security assessments and penetration testing</li>
                        <li>CSRF protection on all state-changing requests</li>
                    </ul>
                    <p class="mt-3 leading-relaxed">No method of transmission over the internet is 100% secure. While we strive to protect your data, we cannot guarantee absolute security. In the event of a data breach affecting your rights and freedoms, we will notify you as required by applicable law.</p>
                </section>

                <section id="pp-section-9">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">9. Your Rights &amp; Choices</h2>
                    <p class="leading-relaxed">Depending on your jurisdiction, you may have the following rights regarding your personal data:</p>
                    <div class="mt-4 overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            <div v-for="right in [
                                { name: 'Access', description: 'Request a copy of the personal data we hold about you.' },
                                { name: 'Correction', description: 'Request that we correct inaccurate or incomplete data.' },
                                { name: 'Deletion', description: 'Request deletion of your personal data, subject to legal retention obligations.' },
                                { name: 'Portability', description: 'Receive your data in a structured, machine-readable format.' },
                                { name: 'Restriction', description: 'Request that we restrict processing of your data in certain circumstances.' },
                                { name: 'Objection', description: 'Object to processing based on legitimate interests or for direct marketing.' },
                                { name: 'Withdraw consent', description: 'Where processing is based on consent, withdraw it at any time without affecting prior processing.' },
                            ]" :key="right.name" class="flex gap-4 px-5 py-3.5">
                                <span class="w-36 shrink-0 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ right.name }}</span>
                                <span class="text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ right.description }}</span>
                            </div>
                        </div>
                    </div>
                    <p class="mt-4 leading-relaxed">To exercise any of these rights, contact us at <a href="mailto:privacy@dokhub.com" class="font-medium text-orange-600 hover:underline dark:text-orange-400">privacy@dokhub.com</a>. We will respond within 30 days. Identity verification may be required before processing your request.</p>
                </section>

                <section id="pp-section-10">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">10. Children's Privacy</h2>
                    <p class="leading-relaxed">The Service is not directed to children under the age of 13 (or 16 in applicable jurisdictions). We do not knowingly collect personal information from children. If we become aware that a child under the applicable age has provided us with personal data without verifiable parental consent, we will delete it promptly. If you believe a child has provided us their information, contact us at <a href="mailto:privacy@dokhub.com" class="font-medium text-orange-600 hover:underline dark:text-orange-400">privacy@dokhub.com</a>.</p>
                </section>

                <section id="pp-section-11">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">11. International Data Transfers</h2>
                    <p class="leading-relaxed">DokHub is operated from the United States. If you access the Service from outside the United States, your information may be transferred to and processed in the United States or other countries that may have different data protection laws than your own. We take appropriate safeguards to ensure such transfers comply with applicable law, including using Standard Contractual Clauses where required.</p>
                </section>

                <section id="pp-section-12">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">12. Third-Party Links &amp; Services</h2>
                    <p class="leading-relaxed">The Service may contain links to third-party websites or integrate with third-party services (e.g., payment processors). This Privacy Policy does not apply to those third parties. We encourage you to review the privacy policies of any third-party services you interact with through our platform.</p>
                </section>

                <section id="pp-section-13">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">13. Changes to This Policy</h2>
                    <p class="leading-relaxed">We may update this Privacy Policy from time to time. When we make material changes, we will notify you by email or by posting a prominent notice on the Service at least 14 days before the changes take effect. Your continued use of the Service after the effective date constitutes acceptance of the revised policy.</p>
                </section>

                <section id="pp-section-14">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">14. Contact Us</h2>
                    <p class="leading-relaxed">For privacy-related questions, data requests, or to report a concern:</p>
                    <div class="mt-4 overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            <div class="flex gap-3 px-5 py-3.5">
                                <span class="w-28 shrink-0 text-sm font-medium text-gray-500 dark:text-gray-400">Company</span>
                                <span class="text-sm text-gray-900 dark:text-gray-100">DokHub, Inc.</span>
                            </div>
                            <div class="flex gap-3 px-5 py-3.5">
                                <span class="w-28 shrink-0 text-sm font-medium text-gray-500 dark:text-gray-400">Privacy email</span>
                                <a href="mailto:privacy@dokhub.com" class="text-sm text-orange-600 hover:underline dark:text-orange-400">privacy@dokhub.com</a>
                            </div>
                            <div class="flex gap-3 px-5 py-3.5">
                                <span class="w-28 shrink-0 text-sm font-medium text-gray-500 dark:text-gray-400">Legal email</span>
                                <a href="mailto:legal@dokhub.com" class="text-sm text-orange-600 hover:underline dark:text-orange-400">legal@dokhub.com</a>
                            </div>
                        </div>
                    </div>
                </section>

            </div>

            <!-- Footer nav -->
            <div class="mt-14 flex flex-wrap items-center justify-between gap-4 border-t border-gray-200 pt-8 dark:border-gray-800">
                <Link href="/terms-of-service" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-sm font-medium text-orange-600 hover:text-orange-800 dark:text-orange-400 dark:hover:text-orange-200">
                    View Terms of Service →
                </Link>
                <Link href="/" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-100">
                    <ChevronLeft class="h-4 w-4" />
                    Back to DokHub
                </Link>
            </div>
        </main>
    </div>
</template>
