<script setup>
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import StorefrontLayout from '@theme/Layouts/StorefrontLayout.vue';
import { useThemeTranslations } from '../../../Composables/useThemeTranslations';
import { PhoneIcon, EnvelopeIcon, MapPinIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';

defineOptions({ layout: StorefrontLayout });

const props = defineProps({
    settings: Object,
    store: Object,
});

const { t } = useThemeTranslations();
const page = usePage();
const primaryColor = computed(() => props.settings?.colors?.primary || '#4334db');

const form = useForm({
    name: '',
    email: '',
    phone: '',
    subject: '',
    message: '',
});

const subjects = computed(() => [
    { value: t('contact.subject_product'), label: t('contact.subject_product') },
    { value: t('contact.subject_order'), label: t('contact.subject_order') },
    { value: t('contact.subject_shipping'), label: t('contact.subject_shipping') },
    { value: t('contact.subject_returns'), label: t('contact.subject_returns') },
    { value: t('contact.subject_partnership'), label: t('contact.subject_partnership') },
    { value: t('contact.subject_other'), label: t('contact.subject_other') },
]);

const submit = () => {
    form.post('/contact', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};

const successMessage = computed(() => page.props.flash?.success);
</script>

<template>
    <div class="min-h-screen bg-gray-50 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{{ t('contact.title') }}</h1>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    {{ t('contact.subtitle') }}
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Contact Info -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-sm p-6 lg:p-8 space-y-6">
                        <h2 class="text-xl font-semibold text-gray-900">{{ t('contact.info_title') }}</h2>

                        <!-- Phone -->
                        <div v-if="store?.phone" class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center" :style="{ backgroundColor: primaryColor + '15' }">
                                <PhoneIcon class="w-5 h-5" :style="{ color: primaryColor }" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ t('contact.phone') }}</p>
                                <a :href="`tel:${store.phone}`" class="text-gray-900 hover:underline">{{ store.phone }}</a>
                            </div>
                        </div>

                        <!-- Email -->
                        <div v-if="store?.email" class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center" :style="{ backgroundColor: primaryColor + '15' }">
                                <EnvelopeIcon class="w-5 h-5" :style="{ color: primaryColor }" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ t('contact.email') }}</p>
                                <a :href="`mailto:${store.email}`" class="text-gray-900 hover:underline">{{ store.email }}</a>
                            </div>
                        </div>

                        <!-- Address -->
                        <div v-if="store?.address" class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center" :style="{ backgroundColor: primaryColor + '15' }">
                                <MapPinIcon class="w-5 h-5" :style="{ color: primaryColor }" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ t('contact.address') }}</p>
                                <p class="text-gray-900">{{ store.address }}</p>
                            </div>
                        </div>

                        <!-- Social Links -->
                        <div v-if="store?.social_facebook || store?.social_instagram || store?.social_tiktok || store?.social_youtube" class="pt-6 border-t border-gray-100">
                            <p class="text-sm font-medium text-gray-500 mb-4">{{ t('contact.follow_us') }}</p>
                            <div class="flex space-x-3">
                                <a v-if="store?.social_facebook" :href="store.social_facebook" target="_blank" class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-blue-100 hover:text-blue-600 transition-colors">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                </a>
                                <a v-if="store?.social_instagram" :href="store.social_instagram" target="_blank" class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-pink-100 hover:text-pink-600 transition-colors">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                                </a>
                                <a v-if="store?.social_tiktok" :href="store.social_tiktok" target="_blank" class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-gray-800 hover:text-white transition-colors">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                                </a>
                                <a v-if="store?.social_youtube" :href="store.social_youtube" target="_blank" class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-red-100 hover:text-red-600 transition-colors">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-sm p-6 lg:p-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">{{ t('contact.send_message') }}</h2>

                        <!-- Success Message -->
                        <div v-if="successMessage" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl">
                            <div class="flex items-center">
                                <CheckCircleIcon class="w-5 h-5 text-green-500 mr-3" />
                                <p class="text-green-700">{{ successMessage }}</p>
                            </div>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Name -->
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ t('contact.name') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        required
                                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:border-transparent transition-all"
                                        :class="{ 'border-red-300': form.errors.name }"
                                        :style="{ '--tw-ring-color': primaryColor }"
                                        :placeholder="t('contact.name_placeholder')"
                                    />
                                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">{{ form.errors.name }}</p>
                                </div>

                                <!-- Email -->
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ t('contact.email_label') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        required
                                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:border-transparent transition-all"
                                        :class="{ 'border-red-300': form.errors.email }"
                                        :style="{ '--tw-ring-color': primaryColor }"
                                        :placeholder="t('contact.email_placeholder')"
                                    />
                                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">{{ form.errors.email }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Phone -->
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ t('contact.phone_label') }}
                                    </label>
                                    <input
                                        id="phone"
                                        v-model="form.phone"
                                        type="tel"
                                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:border-transparent transition-all"
                                        :style="{ '--tw-ring-color': primaryColor }"
                                        :placeholder="t('contact.phone_placeholder')"
                                    />
                                </div>

                                <!-- Subject -->
                                <div>
                                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ t('contact.subject') }} <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        id="subject"
                                        v-model="form.subject"
                                        required
                                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:border-transparent transition-all bg-white"
                                        :class="{ 'border-red-300': form.errors.subject }"
                                        :style="{ '--tw-ring-color': primaryColor }"
                                    >
                                        <option value="">{{ t('contact.select_subject') }}</option>
                                        <option v-for="subject in subjects" :key="subject.value" :value="subject.value">
                                            {{ subject.label }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.subject" class="mt-1 text-sm text-red-500">{{ form.errors.subject }}</p>
                                </div>
                            </div>

                            <!-- Message -->
                            <div>
                                <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ t('contact.message') }} <span class="text-red-500">*</span>
                                </label>
                                <textarea
                                    id="message"
                                    v-model="form.message"
                                    required
                                    rows="5"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:border-transparent transition-all resize-none"
                                    :class="{ 'border-red-300': form.errors.message }"
                                    :style="{ '--tw-ring-color': primaryColor }"
                                    :placeholder="t('contact.message_placeholder')"
                                ></textarea>
                                <p v-if="form.errors.message" class="mt-1 text-sm text-red-500">{{ form.errors.message }}</p>
                            </div>

                            <!-- Submit Button -->
                            <div>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="w-full md:w-auto px-8 py-3 text-white font-medium rounded-xl transition-all hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
                                    :style="{ backgroundColor: primaryColor }"
                                >
                                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ form.processing ? t('contact.sending') : t('contact.send') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>