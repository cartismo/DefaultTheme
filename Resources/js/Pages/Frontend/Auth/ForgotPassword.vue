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
});

const submit = () => {
    form.post('/forgot-password');
};
</script>

<template>
    <StorefrontLayout title="Забравена парола">
        <Head title="Забравена парола" />

        <div class="min-h-[70vh] flex items-center justify-center py-12 px-4">
            <div class="w-full max-w-md">
                <!-- Card -->
                <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
                    <!-- Header -->
                    <div class="px-8 pt-8 pb-6 text-center border-b border-gray-100">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center" :style="{ backgroundColor: primaryColor + '15' }">
                            <svg class="w-8 h-8" :style="{ color: primaryColor }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900">Забравена парола?</h1>
                        <p class="mt-2 text-gray-600 text-sm">
                            Въведете вашия имейл адрес и ще ви изпратим линк за възстановяване на паролата.
                        </p>
                    </div>

                    <!-- Form -->
                    <div class="p-8">
                        <!-- Success Message -->
                        <div v-if="status" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-sm text-green-700">{{ status }}</p>
                            </div>
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
                                    autocomplete="email"
                                    placeholder="you@example.com"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition-all"
                                    :class="form.errors.email ? 'border-red-500' : ''"
                                    :style="{ '--tw-ring-color': primaryColor }"
                                />
                                <p v-if="form.errors.email" class="mt-1.5 text-sm text-red-600">
                                    {{ form.errors.email }}
                                </p>
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
                                {{ form.processing ? 'Изпращане...' : 'Изпрати линк за възстановяване' }}
                            </button>
                        </form>
                    </div>

                    <!-- Footer -->
                    <div class="px-8 py-6 bg-gray-50 border-t border-gray-100 text-center">
                        <Link
                            href="/login"
                            class="inline-flex items-center text-sm font-medium hover:underline"
                            :style="{ color: primaryColor }"
                        >
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Обратно към входа
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </StorefrontLayout>
</template>
