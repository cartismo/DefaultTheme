<script setup>
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import StorefrontLayout from '@theme/Layouts/StorefrontLayout.vue';
import { useThemeTranslations } from '../../../Composables/useThemeTranslations';
import {
    PhoneIcon,
    EnvelopeIcon,
    MapPinIcon,
    CheckCircleIcon,
    ArrowPathIcon,
    ChatBubbleLeftRightIcon,
    PhotoIcon,
    MusicalNoteIcon,
    PlayIcon,
} from '@heroicons/vue/24/outline';

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
                                    <ChatBubbleLeftRightIcon class="w-5 h-5" />
                                </a>
                                <a v-if="store?.social_instagram" :href="store.social_instagram" target="_blank" class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-pink-100 hover:text-pink-600 transition-colors">
                                    <PhotoIcon class="w-5 h-5" />
                                </a>
                                <a v-if="store?.social_tiktok" :href="store.social_tiktok" target="_blank" class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-gray-800 hover:text-white transition-colors">
                                    <MusicalNoteIcon class="w-5 h-5" />
                                </a>
                                <a v-if="store?.social_youtube" :href="store.social_youtube" target="_blank" class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-red-100 hover:text-red-600 transition-colors">
                                    <PlayIcon class="w-5 h-5" />
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
                                    <ArrowPathIcon v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" />
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
