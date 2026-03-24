<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
// Registration is disabled — redirect to login
router.visit('/login');
</script>

<template>
    <AuthBase
        title="Create your patient account"
        description="Sign up as a patient to book appointments"
    >
        <Head title="Register" />

        <Form
            v-bind="store.form()"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-5">
                <div class="grid gap-2.5">
                    <Label for="name" class="text-sm font-semibold text-gray-700">Full name</Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="name"
                        name="name"
                        placeholder="John Doe"
                        class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100"
                    />
                    <InputError :message="errors.name" class="text-xs" />
                </div>

                <div class="grid gap-2.5">
                    <Label for="email" class="text-sm font-semibold text-gray-700">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        :tabindex="2"
                        autocomplete="email"
                        name="email"
                        placeholder="you@example.com"
                        class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100"
                    />
                    <InputError :message="errors.email" class="text-xs" />
                </div>

                <div class="grid gap-2.5">
                    <Label for="password" class="text-sm font-semibold text-gray-700">Password</Label>
                    <PasswordInput
                        id="password"
                        required
                        :tabindex="3"
                        autocomplete="new-password"
                        name="password"
                        placeholder="••••••••"
                        class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100"
                    />
                    <InputError :message="errors.password" class="text-xs" />
                </div>

                <div class="grid gap-2.5">
                    <Label for="password_confirmation" class="text-sm font-semibold text-gray-700">Confirm password</Label>
                    <PasswordInput
                        id="password_confirmation"
                        required
                        :tabindex="4"
                        autocomplete="new-password"
                        name="password_confirmation"
                        placeholder="••••••••"
                        class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100"
                    />
                    <InputError :message="errors.password_confirmation" class="text-xs" />
                </div>

                <Button
                    type="submit"
                    class="mt-2 w-full rounded-xl bg-violet-600 px-4 py-2.5 font-semibold text-white hover:bg-violet-700 transition-colors"
                    tabindex="5"
                    :disabled="processing"
                    data-test="register-user-button"
                >
                    <Spinner v-if="processing" class="mr-2 h-4 w-4" />
                    {{ processing ? 'Creating account...' : 'Create patient account' }}
                </Button>
            </div>

            <div class="border-t border-gray-100 pt-5">
                <p class="text-center text-sm text-gray-600">
                    Already have an account?
                    <TextLink
                        :href="login()"
                        class="text-violet-600 hover:text-violet-700 font-semibold"
                        :tabindex="6"
                    >
                        Sign in
                    </TextLink>
                </p>
                <p class="mt-3 text-center text-sm text-gray-600">
                    Are you a doctor?
                    <TextLink
                        href="/auth/signup/doctor"
                        class="text-violet-600 hover:text-violet-700 font-semibold"
                        :tabindex="7"
                    >
                        Apply here
                    </TextLink>
                </p>
            </div>
        </Form>
    </AuthBase>
</template>
