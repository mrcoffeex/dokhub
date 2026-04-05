<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Shield, ChevronLeft } from 'lucide-vue-next';

const lastUpdated = 'April 5, 2026';
const effectiveDate = 'April 5, 2026';
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
                        { label: 'Data we collect', text: 'Name, email, phone, location, professional credentials, appointment/health records, and messages sent through the AI assistant.' },
                        { label: 'Why we collect it', text: 'To operate the platform, connect patients with doctors, power the AI assistant, and comply with Philippine legal obligations.' },
                        { label: 'Who we share with', text: 'We do not sell your data. We share only with service providers (including Google for the AI assistant), legal authorities when required, and parties you explicitly authorize.' },
                        { label: 'Your rights', text: 'Under the Philippine Data Privacy Act (RA 10173), you may access, correct, export, or delete your data at any time by contacting us or the National Privacy Commission (NPC).' },
                        { label: 'Health data', text: 'Appointment records and prescriptions are handled with heightened care under the Philippine Data Privacy Act of 2012.' },
                        { label: 'AI assistant', text: 'Messages sent to Dokie (DokHub AI) are transmitted to Google Gemini API servers. Do not share sensitive personal health details in AI chat.' },
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
                        'Health Information & Data Privacy',
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
                    <p class="leading-relaxed">DokHub ("we", "our", or "us") operates the DokHub platform — a healthcare technology service based in the Philippines that connects patients with licensed medical professionals for appointment booking, prescription management, digital health record keeping, and AI-assisted health information ("Service").</p>
                    <p class="mt-3 leading-relaxed">This Privacy Policy explains how we collect, use, disclose, and protect your personal information when you use the Service. It applies to all users, including patients, registered doctors, sub-users, and website visitors. Our data practices comply with the Philippine Data Privacy Act of 2012 (Republic Act No. 10173) and its implementing rules and regulations.</p>
                </section>

                <section id="pp-section-2">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">2. Information We Collect</h2>

                    <h3 class="mb-2 mt-5 text-base font-semibold text-gray-800 dark:text-gray-200">2.1 Information You Provide</h3>
                    <ul class="list-disc space-y-1.5 pl-6">
                        <li><strong>Account information:</strong> Name, email address, phone number, password, and role (patient or doctor)</li>
                        <li><strong>Google OAuth data:</strong> If you sign in with Google, we receive your name, email address, and profile picture from your Google account</li>
                        <li><strong>Doctor profile information:</strong> Medical specialization(s), qualifications, years of experience, consultation fee, clinic location, languages spoken, professional biography, and PRC license number</li>
                        <li><strong>Sub-user (staff) accounts:</strong> Names and roles of staff members linked to a doctor account</li>
                        <li><strong>Appointment information:</strong> Date, time, reason for visit, appointment status, insurance details, and any notes</li>
                        <li><strong>Health records:</strong> Diagnoses, prescriptions, medications, vitals, and related clinical notes created by doctors during consultations</li>
                        <li><strong>AI assistant messages:</strong> Messages you type into the Dokie (DokHub AI) chat widget are transmitted to Google Gemini API for processing; do not include sensitive personal or health information in AI chats</li>
                        <li><strong>Payment information:</strong> Billing details processed by PayMongo (our Philippine payment processor); we do not store full card or GCash account numbers</li>
                        <li><strong>Reviews:</strong> Ratings and written reviews submitted about doctors</li>
                        <li><strong>Communications:</strong> Messages sent to DokHub support</li>
                    </ul>

                    <h3 class="mb-2 mt-5 text-base font-semibold text-gray-800 dark:text-gray-200">2.2 Information Collected Automatically</h3>
                    <ul class="list-disc space-y-1.5 pl-6">
                        <li><strong>Log data:</strong> IP address, browser type, operating system, referring URL, pages visited, and timestamps</li>
                        <li><strong>IP-based rate limiting:</strong> Your IP address is used to enforce fair-use limits on the guest AI assistant (up to 5 messages per hour); this data is stored temporarily in server memory only and is not linked to your identity</li>
                        <li><strong>Device information:</strong> Device identifiers, screen resolution, and hardware model</li>
                        <li><strong>Cookies and similar technologies:</strong> Session tokens, analytics identifiers, preference cookies, and your theme preference stored in <code>localStorage</code> (see Section 5)</li>
                    </ul>

                    <h3 class="mb-2 mt-5 text-base font-semibold text-gray-800 dark:text-gray-200">2.3 Information from Third Parties</h3>
                    <p class="leading-relaxed">We may receive information about you from:</p>
                    <ul class="mt-2 list-disc space-y-1.5 pl-6">
                        <li><strong>Google:</strong> If you use Google Sign-In (OAuth), we receive your name, email address, and profile picture from Google</li>
                        <li><strong>hCaptcha:</strong> Bot-detection tokens from Intuition Machines, Inc. when you complete hCaptcha challenges on booking or registration forms</li>
                        <li><strong>Medical licensing bodies:</strong> Credential verification services used to confirm doctor licences with the PRC or relevant authority</li>
                        <li><strong>PayMongo:</strong> Payment status and transaction identifiers in connection with consultation fee processing</li>
                    </ul>
                </section>

                <section id="pp-section-3">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">3. How We Use Your Information</h2>
                    <p class="leading-relaxed">We use your information for the following purposes:</p>
                    <ul class="mt-3 list-disc space-y-1.5 pl-6">
                        <li><strong>Service delivery:</strong> To create and manage your account, facilitate appointment booking, and enable doctor–patient interactions</li>
                        <li><strong>AI assistant operation:</strong> To process messages sent to Dokie (DokHub AI) by transmitting them to Google Gemini API. In authenticated sessions (doctor/admin), the AI receives your role context but not your personal health records unless you explicitly include them in a message</li>
                        <li><strong>Doctor verification:</strong> To review and approve doctor applications, including PRC credential validation</li>
                        <li><strong>Communications:</strong> To send appointment confirmations, reminders, service updates via email and SMS (MessageBird), and responses to your support requests</li>
                        <li><strong>Payments:</strong> To process consultation fees via PayMongo and maintain transaction records</li>
                        <li><strong>Safety & integrity:</strong> To detect and prevent fraud, unauthorized access, and abuse of the platform; IP-based rate limiting protects the AI assistant from misuse</li>
                        <li><strong>Analytics & improvement:</strong> To analyze usage patterns and improve Service functionality (using aggregated or anonymized data where possible)</li>
                        <li><strong>Legal compliance:</strong> To fulfill our obligations under applicable Philippine law (including RA 10173), respond to lawful requests, and enforce our Terms of Service</li>
                    </ul>
                    <p class="mt-3 leading-relaxed">We do not use health information you provide for advertising, marketing profiling, or sale to data brokers.</p>
                </section>

                <section id="pp-section-4">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">4. Health Information &amp; Data Privacy</h2>
                    <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-800/40 dark:bg-blue-900/10">
                        <p class="font-semibold text-blue-800 dark:text-blue-300">Sensitive Personal Health Information</p>
                        <p class="mt-1 text-sm leading-relaxed text-blue-700 dark:text-blue-400">Certain information on our platform — including appointment records, diagnoses, prescriptions, and vitals — constitutes sensitive personal information under Republic Act No. 10173 (Philippine Data Privacy Act of 2012). DokHub takes heightened precautions with such data in line with the implementing rules of the National Privacy Commission (NPC).</p>
                    </div>
                    <ul class="mt-4 list-disc space-y-1.5 pl-6">
                        <li>Sensitive health data is accessible only to the treating doctor and the relevant patient, and to authorized DokHub personnel for platform operations</li>
                        <li>We do not disclose health records to third parties without your explicit consent, except as required by law or in a medical emergency</li>
                        <li>Doctors on DokHub are independently responsible for compliance with RA 10173 and applicable DOH and PRC regulations in their professional practice</li>
                        <li>We implement organizational, physical, and technical security measures appropriate for sensitive personal information as defined under the Data Privacy Act</li>
                        <li>In the event of a personal data breach, we will notify affected individuals and the NPC within the timeframes required by law</li>
                    </ul>
                </section>

                <section id="pp-section-5">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">5. Cookies &amp; Tracking Technologies</h2>
                    <p class="leading-relaxed">We use cookies and similar technologies to operate the Service. Types of cookies we use:</p>
                    <div class="mt-4 overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            <div v-for="cookie in [
                                { type: 'Essential', purpose: 'Required for authentication, session management, and CSRF protection. Cannot be disabled.' },
                                { type: 'Functional', purpose: 'Remember your preferences. Your theme (light/dark) preference is stored in localStorage on your device.' },
                                { type: 'Security', purpose: 'hCaptcha verification tokens (Intuition Machines, Inc.) are used on booking and registration forms to prevent automated abuse.' },
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
                        <li><strong>With Google (Gemini API):</strong> Messages you send to the AI assistant (Dokie) are transmitted to Google's Gemini API for processing. Google's data practices are governed by the <a href="https://policies.google.com/privacy" class="text-orange-600 hover:underline dark:text-orange-400" target="_blank" rel="noopener noreferrer">Google Privacy Policy</a>. AI chat messages may be used by Google to improve their models subject to their terms.</li>
                        <li><strong>With PayMongo:</strong> Payment card and GCash transaction data is processed by PayMongo, Inc. (BSP-regulated) under their own privacy policy</li>
                        <li><strong>With MessageBird:</strong> Your phone number and appointment details are shared with MessageBird B.V. to send you SMS notifications</li>
                        <li><strong>With hCaptcha:</strong> Bot-detection data from form interactions is shared with Intuition Machines, Inc. to protect against automated abuse</li>
                        <li><strong>With service providers:</strong> Other trusted vendors who help us operate the Service (hosting, email delivery, analytics) under strict data processing agreements</li>
                        <li><strong>For legal compliance:</strong> When required by law, lawful order from a Philippine authority, or governmental body, or to protect the rights, property, or safety of DokHub, our users, or the public</li>
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
                    <p class="mt-4 leading-relaxed">To exercise any of these rights, contact us at <a href="mailto:privacy@dokhub.com" class="font-medium text-orange-600 hover:underline dark:text-orange-400">privacy@dokhub.com</a>. We will respond within 30 days as required by the Philippine Data Privacy Act. Identity verification may be required before processing your request.</p>
                    <p class="mt-3 leading-relaxed">If you believe your data privacy rights have been violated and we have not resolved your concern, you may file a complaint with the <strong>National Privacy Commission (NPC)</strong> of the Philippines at <a href="https://www.privacy.gov.ph" class="text-orange-600 hover:underline dark:text-orange-400" target="_blank" rel="noopener noreferrer">www.privacy.gov.ph</a>.</p>
                </section>

                <section id="pp-section-10">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">10. Children's Privacy</h2>
                    <p class="leading-relaxed">The Service is not directed to children under the age of 13 (or 16 in applicable jurisdictions). We do not knowingly collect personal information from children. If we become aware that a child under the applicable age has provided us with personal data without verifiable parental consent, we will delete it promptly. If you believe a child has provided us their information, contact us at <a href="mailto:privacy@dokhub.com" class="font-medium text-orange-600 hover:underline dark:text-orange-400">privacy@dokhub.com</a>.</p>
                </section>

                <section id="pp-section-11">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">11. International Data Transfers</h2>
                    <p class="leading-relaxed">DokHub is operated from the Philippines. Your data is primarily stored on servers located in the Philippines or within Southeast Asia. However, some data is transferred internationally as part of using third-party services:</p>
                    <ul class="mt-3 list-disc space-y-1.5 pl-6">
                        <li><strong>Google Gemini API:</strong> AI assistant messages are sent to Google's servers, which may be located in the United States or other countries</li>
                        <li><strong>hCaptcha:</strong> Bot-detection data may be processed by Intuition Machines, Inc. on servers outside the Philippines</li>
                        <li><strong>MessageBird:</strong> SMS delivery data is routed through MessageBird's international infrastructure</li>
                    </ul>
                    <p class="mt-3 leading-relaxed">Where personal data is transferred outside the Philippines, we take appropriate safeguards to ensure compliance with the Philippine Data Privacy Act (RA 10173) Implementing Rules and Regulations on cross-border data transfers, including contractual clauses that require recipients to maintain equivalent data protection standards.</p>
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
                                <span class="text-sm text-gray-900 dark:text-gray-100">DokHub</span>
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
