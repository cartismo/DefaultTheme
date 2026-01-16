<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import StorefrontLayout from '@theme/Layouts/StorefrontLayout.vue';

const props = defineProps({
    settings: Object,
});

const page = usePage();
const status = computed(() => page.props.flash?.success);

// Theme colors
const primaryColor = computed(() => props.settings?.colors?.primary || '#4F46E5');

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <StorefrontLayout title="Вход">
        <Head title="Вход" />

        <div class="min-h-[70vh] flex items-center justify-center py-12 px-4">
            <div class="w-full max-w-md">
                <!-- Card -->
                <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
                    <!-- Header -->
                    <div class="px-8 pt-8 pb-6 text-center border-b border-gray-100">
                        <h1 class="text-2xl font-bold text-gray-900">Добре дошли отново</h1>
                        <p class="mt-2 text-gray-600">Влезте в акаунта си</p>
                    </div>

                    <!-- Form -->
                    <div class="p-8">
                        <!-- Status Message -->
                        <div v-if="status" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <p class="text-sm text-green-700">{{ status }}</p>
                        </div>

                        <form @submit.prevent="submit" class="space-y-5">
                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Имейл адрес
                                </label>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    required
                                    autofocus
                                    autocomplete="username"
                                    placeholder="you@example.com"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition-all"
                                    :class="form.errors.email ? 'border-red-500' : ''"
                                    :style="{ '--tw-ring-color': primaryColor }"
                                />
                                <p v-if="form.errors.email" class="mt-1.5 text-sm text-red-600">
                                    {{ form.errors.email }}
                                </p>
                            </div>

                            <!-- Password -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label for="password" class="block text-sm font-medium text-gray-700">
                                        Парола
                                    </label>
                                    <Link
                                        href="/forgot-password"
                                        class="text-sm font-medium hover:underline"
                                        :style="{ color: primaryColor }"
                                    >
                                        Забравена парола?
                                    </Link>
                                </div>
                                <input
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="********"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition-all"
                                    :class="form.errors.password ? 'border-red-500' : ''"
                                    :style="{ '--tw-ring-color': primaryColor }"
                                />
                                <p v-if="form.errors.password" class="mt-1.5 text-sm text-red-600">
                                    {{ form.errors.password }}
                                </p>
                            </div>

                            <!-- Remember Me -->
                            <div class="flex items-center">
                                <input
                                    id="remember"
                                    v-model="form.remember"
                                    type="checkbox"
                                    class="w-4 h-4 rounded border-gray-300 focus:ring-2"
                                    :style="{ color: primaryColor, '--tw-ring-color': primaryColor }"
                                />
                                <label for="remember" class="ml-2 text-sm text-gray-600">
                                    Запомни ме
                                </label>
                            </div>

                            <!-- Submit Button -->
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full py-3 px-4 text-white font-semibold rounded-lg transition-all hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
                                :style="{ backgroundColor: primaryColor }"
                            >
                                <svg
                                    v-if="form.processing"
                                    class="animate-spin -ml-1 mr-2 h-5 w-5 text-white"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ form.processing ? 'Влизане...' : 'Вход' }}
                            </button>
                        </form>
                    </div>

                    <!-- Footer -->
                    <div class="px-8 py-6 bg-gray-50 border-t border-gray-100 text-center">
                        <p class="text-sm text-gray-600">
                            Нямате акаунт?
                            <Link
                                href="/register"
                                class="font-semibold hover:underline"
                                :style="{ color: primaryColor }"
                            >
                                Регистрирайте се
                            </Link>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </StorefrontLayout>
</template>
