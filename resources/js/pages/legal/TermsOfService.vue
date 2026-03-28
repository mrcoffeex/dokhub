<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { FileText, ChevronLeft } from 'lucide-vue-next';

const lastUpdated = 'March 26, 2026';
const effectiveDate = 'March 26, 2026';
</script>

<template>
    <Head title="Terms of Service — DokHub" />

    <div class="min-h-screen bg-gray-50 dark:bg-gray-950">

        <!-- Top nav bar -->
        <header class="sticky top-0 z-10 border-b border-gray-200 bg-white/90 backdrop-blur dark:border-gray-800 dark:bg-gray-900/90">
            <div class="mx-auto flex max-w-4xl items-center justify-between px-6 py-3.5">
                <Link href="/" class="flex items-center gap-2 font-bold text-gray-900 dark:text-white">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-violet-600">
                        <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </div>
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
                <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-violet-200 bg-violet-50 px-3 py-1.5 dark:border-violet-800/40 dark:bg-violet-900/20">
                    <FileText class="h-3.5 w-3.5 text-violet-600 dark:text-violet-400" />
                    <span class="text-xs font-medium text-violet-700 dark:text-violet-300">Legal Document</span>
                </div>
                <h1 class="text-4xl font-bold tracking-tight text-gray-900 dark:text-white">Terms of Service</h1>
                <p class="mt-3 text-base text-gray-500 dark:text-gray-400">
                    Please read these terms carefully before using DokHub.
                </p>
                <div class="mt-4 flex flex-wrap gap-4 text-sm text-gray-400 dark:text-gray-500">
                    <span><strong class="text-gray-600 dark:text-gray-300">Effective:</strong> {{ effectiveDate }}</span>
                    <span><strong class="text-gray-600 dark:text-gray-300">Last updated:</strong> {{ lastUpdated }}</span>
                </div>
            </div>

            <!-- Table of contents -->
            <div class="mb-10 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 bg-gray-50/80 px-6 py-3.5 dark:border-gray-800 dark:bg-gray-800/50">
                    <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Table of Contents</h2>
                </div>
                <ol class="grid gap-0 divide-y divide-gray-100 px-6 dark:divide-gray-800">
                    <li v-for="(section, i) in [
                        'Acceptance of Terms',
                        'Description of Services',
                        'User Accounts & Eligibility',
                        'Doctor Registration & Verification',
                        'Prohibited Conduct',
                        'Medical Disclaimer',
                        'Fees & Payments',
                        'Intellectual Property',
                        'Privacy',
                        'Disclaimers & Limitation of Liability',
                        'Indemnification',
                        'Termination',
                        'Governing Law & Dispute Resolution',
                        'Changes to These Terms',
                        'Contact Us',
                    ]" :key="i" class="py-2.5">
                        <a :href="'#section-' + (i + 1)" class="flex items-center gap-2 text-sm text-violet-600 hover:text-violet-800 dark:text-violet-400 dark:hover:text-violet-200">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-violet-50 text-[11px] font-bold text-violet-600 dark:bg-violet-900/40 dark:text-violet-400">{{ i + 1 }}</span>
                            {{ section }}
                        </a>
                    </li>
                </ol>
            </div>

            <!-- Sections -->
            <div class="space-y-10 text-gray-700 dark:text-gray-300">

                <section id="section-1">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">1. Acceptance of Terms</h2>
                    <p class="leading-relaxed">By accessing or using the DokHub platform ("Service"), including our website and any associated mobile or web applications, you agree to be bound by these Terms of Service ("Terms") and our Privacy Policy. If you do not agree to these Terms, you must not use our Service.</p>
                    <p class="mt-3 leading-relaxed">These Terms apply to all visitors, users, registered patients, and healthcare professionals (collectively, "Users") who access or use the Service. Use of the Service by a minor requires the consent and supervision of a parent or legal guardian.</p>
                </section>

                <section id="section-2">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">2. Description of Services</h2>
                    <p class="leading-relaxed">DokHub is an online healthcare platform that connects patients with licensed medical professionals. The Service provides tools including, but not limited to:</p>
                    <ul class="mt-3 list-disc space-y-1.5 pl-6">
                        <li>Doctor profiles and specialization discovery</li>
                        <li>Online appointment booking and scheduling</li>
                        <li>Patient management tools for registered doctors</li>
                        <li>Digital prescription and diagnosis record keeping</li>
                        <li>Doctor schedule management</li>
                        <li>Patient appointment lookup and history</li>
                    </ul>
                    <p class="mt-3 leading-relaxed">DokHub is a technology intermediary only. We do not provide medical services, employ physicians, or practice medicine. All medical decisions remain the sole responsibility of the treating healthcare professional and the patient.</p>
                </section>

                <section id="section-3">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">3. User Accounts &amp; Eligibility</h2>
                    <p class="leading-relaxed">To access certain features of the Service, you must create an account. You agree to:</p>
                    <ul class="mt-3 list-disc space-y-1.5 pl-6">
                        <li>Provide accurate, current, and complete information during registration</li>
                        <li>Maintain and promptly update your account information</li>
                        <li>Keep your password confidential and not share access with third parties</li>
                        <li>Accept full responsibility for all activity that occurs under your account</li>
                        <li>Immediately notify DokHub of any unauthorized use of your account</li>
                    </ul>
                    <p class="mt-3 leading-relaxed">You must be at least 18 years of age to create an account. DokHub reserves the right to suspend or terminate accounts that provide false or misleading information.</p>
                </section>

                <section id="section-4">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">4. Doctor Registration &amp; Verification</h2>
                    <p class="leading-relaxed">Healthcare professionals who register as doctors on DokHub agree to additional conditions:</p>
                    <ul class="mt-3 list-disc space-y-1.5 pl-6">
                        <li>All submitted credentials, qualifications, and licensure information must be accurate and verifiable</li>
                        <li>Doctor profiles are subject to review and approval by DokHub's administrative team before activation</li>
                        <li>You must maintain a valid, current medical license in your jurisdiction during the entire period you use the Service</li>
                        <li>You are solely responsible for complying with all applicable medical practice laws, regulations, and professional ethical standards</li>
                        <li>DokHub reserves the right to suspend or remove a doctor profile at any time if submitted information is found to be inaccurate, a license lapses, or a complaint is substantiated</li>
                        <li>Doctors set their own consultation fees and are solely responsible for any applicable taxes on earnings</li>
                    </ul>
                </section>

                <section id="section-5">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">5. Prohibited Conduct</h2>
                    <p class="leading-relaxed">You agree not to engage in any of the following activities:</p>
                    <ul class="mt-3 list-disc space-y-1.5 pl-6">
                        <li>Impersonating any person or entity, including medical professionals</li>
                        <li>Submitting false, misleading, or fraudulent medical credentials or patient information</li>
                        <li>Using the Service for any unlawful purpose or in violation of any local, state, national, or international law</li>
                        <li>Attempting to gain unauthorized access to any portion of the Service or other users' accounts</li>
                        <li>Transmitting any unsolicited communications, spam, or malware</li>
                        <li>Interfering with or disrupting the integrity or performance of the Service</li>
                        <li>Scraping, crawling, or harvesting data from the Service without express written permission</li>
                        <li>Using the Service to harass, threaten, or abuse any user</li>
                        <li>Engaging in any fraudulent billing or prescribing conduct through the platform</li>
                    </ul>
                </section>

                <section id="section-6">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">6. Medical Disclaimer</h2>
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-800/40 dark:bg-amber-900/10">
                        <p class="font-semibold text-amber-800 dark:text-amber-300">Important Notice</p>
                        <p class="mt-1 text-sm leading-relaxed text-amber-700 dark:text-amber-400">DokHub is not a provider of medical advice, diagnosis, or treatment. Content on this platform — including doctor profiles, appointment records, prescriptions, and diagnoses — is provided for informational purposes only and does not constitute a patient–physician relationship unless explicitly and lawfully established.</p>
                    </div>
                    <p class="mt-3 leading-relaxed">In a medical emergency, call your local emergency services (e.g., 911) immediately. Never disregard professional medical advice or delay seeking it because of information obtained on or through DokHub.</p>
                </section>

                <section id="section-7">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">7. Fees &amp; Payments</h2>
                    <p class="leading-relaxed">Consultation fees are set by individual doctors and displayed on their profiles. DokHub may charge a platform service fee on transactions processed through the Service. All fees are listed in US Dollars (USD) unless stated otherwise.</p>
                    <ul class="mt-3 list-disc space-y-1.5 pl-6">
                        <li>Payments are processed by third-party payment processors subject to their respective terms and privacy policies</li>
                        <li>Refund and cancellation policies are subject to the individual doctor's stated policy and DokHub's dispute resolution procedures</li>
                        <li>DokHub reserves the right to modify its fee structure with 30 days' notice to registered users</li>
                    </ul>
                </section>

                <section id="section-8">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">8. Intellectual Property</h2>
                    <p class="leading-relaxed">All content, features, and functionality of the Service — including but not limited to text, graphics, logos, icons, software, and the DokHub name — are the exclusive property of DokHub or its licensors and are protected by applicable intellectual property laws.</p>
                    <p class="mt-3 leading-relaxed">You are granted a limited, non-exclusive, non-transferable license to use the Service for its intended purpose. You may not reproduce, distribute, modify, create derivative works from, or commercially exploit any portion of the Service without express prior written consent from DokHub.</p>
                    <p class="mt-3 leading-relaxed">Content you submit to DokHub (such as doctor bios, profile information, or reviews) remains yours, but you grant DokHub a worldwide, royalty-free license to display, reproduce, and distribute it in connection with operating the Service.</p>
                </section>

                <section id="section-9">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">9. Privacy</h2>
                    <p class="leading-relaxed">Your use of the Service is also governed by our <Link href="/privacy-policy" target="_blank" rel="noopener noreferrer" class="font-medium text-violet-600 hover:underline dark:text-violet-400">Privacy Policy</Link>, which is incorporated into these Terms by reference. Please review our Privacy Policy to understand our data practices, including how we collect and handle health-related information.</p>
                </section>

                <section id="section-10">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">10. Disclaimers &amp; Limitation of Liability</h2>
                    <p class="leading-relaxed">The Service is provided on an "as is" and "as available" basis without warranties of any kind, either express or implied, including but not limited to implied warranties of merchantability, fitness for a particular purpose, or non-infringement.</p>
                    <p class="mt-3 leading-relaxed">To the fullest extent permitted by law, DokHub and its officers, directors, employees, and agents shall not be liable for any indirect, incidental, special, consequential, or punitive damages — including loss of data, loss of revenue, or any harm arising from healthcare decisions made using the Service — even if DokHub has been advised of the possibility of such damages.</p>
                    <p class="mt-3 leading-relaxed">DokHub's total liability to you for any claim arising from your use of the Service shall not exceed the greater of (a) $100 USD or (b) the total fees paid by you to DokHub in the 12 months preceding the claim.</p>
                </section>

                <section id="section-11">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">11. Indemnification</h2>
                    <p class="leading-relaxed">You agree to indemnify, defend, and hold harmless DokHub and its affiliates, officers, directors, employees, and agents from and against any claims, liabilities, damages, losses, and expenses (including reasonable legal fees) arising out of or in any way connected to:</p>
                    <ul class="mt-3 list-disc space-y-1.5 pl-6">
                        <li>Your access to or use of the Service</li>
                        <li>Your violation of these Terms</li>
                        <li>Your violation of any third-party rights, including any intellectual property or privacy rights</li>
                        <li>Any medical advice given or received through the platform by you as a healthcare professional</li>
                    </ul>
                </section>

                <section id="section-12">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">12. Termination</h2>
                    <p class="leading-relaxed">DokHub reserves the right to suspend or permanently terminate your account at any time, with or without notice, for any reason including violation of these Terms, fraudulent activity, or inactivity. Upon termination, your right to use the Service ceases immediately.</p>
                    <p class="mt-3 leading-relaxed">You may terminate your account at any time by contacting support. Termination does not relieve you of obligations incurred prior to the termination date, including any outstanding payment obligations.</p>
                </section>

                <section id="section-13">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">13. Governing Law &amp; Dispute Resolution</h2>
                    <p class="leading-relaxed">These Terms are governed by and construed in accordance with the laws of the State of Delaware, United States, without regard to its conflict-of-law provisions.</p>
                    <p class="mt-3 leading-relaxed">Any dispute arising from or relating to these Terms or the Service shall first be attempted to be resolved through good-faith negotiation. If unresolved, disputes shall be submitted to binding arbitration administered by the American Arbitration Association (AAA) under its Consumer Arbitration Rules. Class action lawsuits and jury trials are waived to the extent permitted by law.</p>
                </section>

                <section id="section-14">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">14. Changes to These Terms</h2>
                    <p class="leading-relaxed">DokHub reserves the right to update or modify these Terms at any time. When we make material changes, we will notify registered users by email or by posting a prominent notice on the Service at least 14 days before the changes take effect. Your continued use of the Service after the effective date of the revised Terms constitutes your acceptance of the changes.</p>
                </section>

                <section id="section-15">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">15. Contact Us</h2>
                    <p class="leading-relaxed">If you have any questions about these Terms, please contact us:</p>
                    <div class="mt-4 overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            <div class="flex gap-3 px-5 py-3.5">
                                <span class="w-28 shrink-0 text-sm font-medium text-gray-500 dark:text-gray-400">Company</span>
                                <span class="text-sm text-gray-900 dark:text-gray-100">DokHub, Inc.</span>
                            </div>
                            <div class="flex gap-3 px-5 py-3.5">
                                <span class="w-28 shrink-0 text-sm font-medium text-gray-500 dark:text-gray-400">Email</span>
                                <a href="mailto:legal@dokhub.com" class="text-sm text-violet-600 hover:underline dark:text-violet-400">legal@dokhub.com</a>
                            </div>
                            <div class="flex gap-3 px-5 py-3.5">
                                <span class="w-28 shrink-0 text-sm font-medium text-gray-500 dark:text-gray-400">Support</span>
                                <a href="mailto:support@dokhub.com" class="text-sm text-violet-600 hover:underline dark:text-violet-400">support@dokhub.com</a>
                            </div>
                        </div>
                    </div>
                </section>

            </div>

            <!-- Footer nav -->
            <div class="mt-14 flex flex-wrap items-center justify-between gap-4 border-t border-gray-200 pt-8 dark:border-gray-800">
                <Link href="/privacy-policy" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-sm font-medium text-violet-600 hover:text-violet-800 dark:text-violet-400 dark:hover:text-violet-200">
                    View Privacy Policy →
                </Link>
                <Link href="/" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-100">
                    <ChevronLeft class="h-4 w-4" />
                    Back to DokHub
                </Link>
            </div>
        </main>
    </div>
</template>
