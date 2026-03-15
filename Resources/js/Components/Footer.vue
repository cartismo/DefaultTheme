<script setup>
import { computed, ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { useThemeTranslations } from '../Composables/useThemeTranslations';
import {
    MapPinIcon,
    PhoneIcon,
    EnvelopeIcon,
    ArchiveBoxIcon,
    ShieldCheckIcon,
    ArrowPathIcon,
    LifebuoyIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    settings: Object,
    store: Object,
    menus: Object,
});

const { t } = useThemeTranslations();

// Footer settings
const footerSettings = computed(() => props.settings?.footer || {});
const showNewsletter = computed(() => footerSettings.value.show_newsletter !== false);
const showSocialLinks = computed(() => footerSettings.value.show_social_links !== false);
const showPaymentIcons = computed(() => footerSettings.value.show_payment_icons !== false);
const footerColumns = computed(() => footerSettings.value.columns || 4);

// Footer style
const footerStyle = computed(() => props.settings?.layout?.footer_style || 'default');
const isMinimalFooter = computed(() => footerStyle.value === 'minimal');
const isExpandedFooter = computed(() => footerStyle.value === 'expanded');

// Colors
const primaryColor = computed(() => props.settings?.colors?.primary || '#4334db');

// Get footer menus
const footerMenu1 = computed(() => props.menus?.footer_1 || props.menus?.footer || null);
const footerMenu2 = computed(() => props.menus?.footer_2 || null);
const footerMenu3 = computed(() => props.menus?.footer_3 || null);

// Newsletter form
const newsletterForm = useForm({
    email: '',
});

const newsletterSubmitted = ref(false);

const submitNewsletter = () => {
    newsletterForm.post('/newsletter/subscribe', {
        preserveScroll: true,
        onSuccess: () => {
            newsletterSubmitted.value = true;
            newsletterForm.reset();
        },
    });
};

// Current year
const currentYear = new Date().getFullYear();

// Social links (from store settings or default)
const socialLinks = computed(() => props.store?.social_links || {});
</script>

<template>
    <footer class="bg-gray-900 text-white">
        <!-- Newsletter Section -->
        <div v-if="showNewsletter && !isMinimalFooter" class="border-b border-gray-800">
            <div class="max-w-7xl mx-auto px-4 py-12">
                <div class="max-w-2xl mx-auto text-center">
                    <h3 class="text-2xl font-bold mb-2">{{ t('footer.newsletter_title') }}</h3>
                    <p class="text-gray-400 mb-6">{{ t('footer.newsletter_text') }}</p>

                    <div v-if="newsletterSubmitted" class="bg-green-500/20 border border-green-500 rounded-xl p-4 text-green-400">
                        {{ t('footer.newsletter_success') }}
                    </div>

                    <form v-else @submit.prevent="submitNewsletter" class="flex flex-col sm:flex-row gap-3">
                        <input
                            v-model="newsletterForm.email"
                            type="email"
                            required
                            :placeholder="t('footer.newsletter_placeholder')"
                            class="flex-1 px-5 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        />
                        <button
                            type="submit"
                            :disabled="newsletterForm.processing"
                            class="px-8 py-3 font-semibold text-white rounded-xl transition-all hover:opacity-90 disabled:opacity-50"
                            :style="{ backgroundColor: primaryColor }"
                        >
                            {{ newsletterForm.processing ? t('footer.newsletter_sending') : t('footer.newsletter_button') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Footer Content -->
        <div v-if="!isMinimalFooter" class="max-w-7xl mx-auto px-4 py-12">
            <div class="grid gap-8" :class="{
                'grid-cols-2 md:grid-cols-2 lg:grid-cols-3': footerColumns === 2,
                'grid-cols-2 md:grid-cols-3 lg:grid-cols-4': footerColumns === 3,
                'grid-cols-2 md:grid-cols-4 lg:grid-cols-5': footerColumns === 4,
                'grid-cols-2 md:grid-cols-4 lg:grid-cols-6': footerColumns === 5,
            }">
                <!-- Company Info -->
                <div class="col-span-2 md:col-span-4 lg:col-span-1">
                    <Link href="/" class="inline-block mb-4">
                        <img
                            v-if="store?.logo"
                            :src="store.logo"
                            :alt="store?.name || 'Store'"
                            class="h-8 w-auto brightness-0 invert"
                        />
                        <img
                            v-else
                            src="/modules/defaulttheme/images/logo.svg"
                            :alt="store?.name || 'Cartismo'"
                            class="h-8 w-auto brightness-0 invert"
                        />
                    </Link>
                    <p class="text-gray-400 text-sm mb-4">
                        {{ store?.description || t('footer.default_description') }}
                    </p>

                    <!-- Contact Info -->
                    <div class="space-y-2 text-sm text-gray-400">
                        <div v-if="store?.address" class="flex items-start">
                            <MapPinIcon class="w-5 h-5 mr-2 flex-shrink-0 mt-0.5" />
                            {{ store.address }}
                        </div>
                        <div v-if="store?.phone" class="flex items-center">
                            <PhoneIcon class="w-5 h-5 mr-2 flex-shrink-0" />
                            {{ store.phone }}
                        </div>
                        <div v-if="store?.email" class="flex items-center">
                            <EnvelopeIcon class="w-5 h-5 mr-2 flex-shrink-0" />
                            {{ store.email }}
                        </div>
                    </div>
                </div>

                <!-- Menu Column 1 -->
                <div>
                    <h4 class="font-semibold text-white mb-4">{{ footerMenu1?.name || t('footer.info') }}</h4>
                    <ul class="space-y-2">
                        <template v-if="footerMenu1?.items?.length">
                            <li v-for="item in footerMenu1.items" :key="item.id">
                                <Link :href="item.url || '#'" class="text-gray-400 hover:text-white text-sm transition-colors">
                                    {{ item.title }}
                                </Link>
                            </li>
                        </template>
                        <template v-else>
                            <li><Link href="/about" class="text-gray-400 hover:text-white text-sm transition-colors">{{ t('footer.about') }}</Link></li>
                            <li><Link href="/contact" class="text-gray-400 hover:text-white text-sm transition-colors">{{ t('contact') }}</Link></li>
                            <li><Link href="/blog" class="text-gray-400 hover:text-white text-sm transition-colors">{{ t('blog') }}</Link></li>
                            <li><Link href="/careers" class="text-gray-400 hover:text-white text-sm transition-colors">{{ t('footer.careers') }}</Link></li>
                        </template>
                    </ul>
                </div>

                <!-- Menu Column 2 -->
                <div>
                    <h4 class="font-semibold text-white mb-4">{{ footerMenu2?.name || t('footer.service') }}</h4>
                    <ul class="space-y-2">
                        <template v-if="footerMenu2?.items?.length">
                            <li v-for="item in footerMenu2.items" :key="item.id">
                                <Link :href="item.url || '#'" class="text-gray-400 hover:text-white text-sm transition-colors">
                                    {{ item.title }}
                                </Link>
                            </li>
                        </template>
                        <template v-else>
                            <li><Link href="/shipping" class="text-gray-400 hover:text-white text-sm transition-colors">{{ t('footer.shipping') }}</Link></li>
                            <li><Link href="/returns" class="text-gray-400 hover:text-white text-sm transition-colors">{{ t('footer.returns') }}</Link></li>
                            <li><Link href="/faq" class="text-gray-400 hover:text-white text-sm transition-colors">{{ t('footer.faq') }}</Link></li>
                            <li><Link href="/track-order" class="text-gray-400 hover:text-white text-sm transition-colors">{{ t('footer.tracking') }}</Link></li>
                        </template>
                    </ul>
                </div>

                <!-- Menu Column 3 -->
                <div>
                    <h4 class="font-semibold text-white mb-4">{{ footerMenu3?.name || t('footer.account_section') }}</h4>
                    <ul class="space-y-2">
                        <template v-if="footerMenu3?.items?.length">
                            <li v-for="item in footerMenu3.items" :key="item.id">
                                <Link :href="item.url || '#'" class="text-gray-400 hover:text-white text-sm transition-colors">
                                    {{ item.title }}
                                </Link>
                            </li>
                        </template>
                        <template v-else>
                            <li><Link href="/account" class="text-gray-400 hover:text-white text-sm transition-colors">{{ t('footer.my_account') }}</Link></li>
                            <li><Link href="/account/orders" class="text-gray-400 hover:text-white text-sm transition-colors">{{ t('footer.order_history') }}</Link></li>
                            <li><Link href="/wishlist" class="text-gray-400 hover:text-white text-sm transition-colors">{{ t('footer.wishlist') }}</Link></li>
                            <li><Link href="/compare" class="text-gray-400 hover:text-white text-sm transition-colors">{{ t('footer.compare') }}</Link></li>
                        </template>
                    </ul>
                </div>

                <!-- Social & Payment Column -->
                <div>
                    <!-- Social Links -->
                    <div v-if="showSocialLinks && Object.keys(socialLinks).length" class="mb-6">
                        <h4 class="font-semibold text-white mb-4">{{ t('footer.follow_us') }}</h4>
                        <div class="flex space-x-3">
                            <a
                                v-if="socialLinks.facebook"
                                :href="socialLinks.facebook"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center text-gray-400 hover:bg-blue-600 hover:text-white transition-all"
                            >
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M9.198 21.5h4v-8.01h3.604l.396-3.98h-4V7.5a1 1 0 011-1h3v-4h-3a5 5 0 00-5 5v2.01h-2l-.396 3.98h2.396v8.01z"/>
                                </svg>
                            </a>
                            <a
                                v-if="socialLinks.instagram"
                                :href="socialLinks.instagram"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gradient-to-br hover:from-purple-600 hover:to-pink-500 hover:text-white transition-all"
                            >
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2c2.717 0 3.056.01 4.122.06 1.065.05 1.79.217 2.428.465.66.254 1.216.598 1.772 1.153a4.908 4.908 0 011.153 1.772c.247.637.415 1.363.465 2.428.047 1.066.06 1.405.06 4.122 0 2.717-.01 3.056-.06 4.122-.05 1.065-.218 1.79-.465 2.428a4.883 4.883 0 01-1.153 1.772 4.915 4.915 0 01-1.772 1.153c-.637.247-1.363.415-2.428.465-1.066.047-1.405.06-4.122.06-2.717 0-3.056-.01-4.122-.06-1.065-.05-1.79-.218-2.428-.465a4.89 4.89 0 01-1.772-1.153 4.904 4.904 0 01-1.153-1.772c-.248-.637-.415-1.363-.465-2.428C2.013 15.056 2 14.717 2 12c0-2.717.01-3.056.06-4.122.05-1.066.217-1.79.465-2.428a4.88 4.88 0 011.153-1.772A4.897 4.897 0 015.45 2.525c.638-.248 1.362-.415 2.428-.465C8.944 2.013 9.283 2 12 2zm0 1.802c-2.67 0-2.986.01-4.04.058-.976.045-1.505.207-1.858.344-.466.182-.8.398-1.15.748-.35.35-.566.684-.748 1.15-.137.353-.3.882-.344 1.857-.048 1.055-.058 1.37-.058 4.041 0 2.67.01 2.986.058 4.04.045.976.207 1.505.344 1.858.182.466.399.8.748 1.15.35.35.684.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058 2.67 0 2.987-.01 4.04-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.684.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041 0-2.67-.01-2.986-.058-4.04-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.055-.048-1.37-.058-4.041-.058zm0 3.063a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 8.468a3.333 3.333 0 100-6.666 3.333 3.333 0 000 6.666zm6.538-8.671a1.2 1.2 0 11-2.4 0 1.2 1.2 0 012.4 0z"/>
                                </svg>
                            </a>
                            <a
                                v-if="socialLinks.twitter"
                                :href="socialLinks.twitter"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center text-gray-400 hover:bg-sky-500 hover:text-white transition-all"
                            >
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                </svg>
                            </a>
                            <a
                                v-if="socialLinks.youtube"
                                :href="socialLinks.youtube"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center text-gray-400 hover:bg-red-600 hover:text-white transition-all"
                            >
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Payment Icons -->
                    <div v-if="showPaymentIcons">
                        <h4 class="font-semibold text-white mb-4">{{ t('footer.payment_methods') }}</h4>
                        <div class="flex flex-wrap gap-2">
                            <div class="bg-white rounded px-3 py-1.5">
                                <span class="text-xs font-bold text-blue-600">VISA</span>
                            </div>
                            <div class="bg-white rounded px-3 py-1.5">
                                <span class="text-xs font-bold text-red-500">MC</span>
                            </div>
                            <div class="bg-white rounded px-3 py-1.5">
                                <span class="text-xs font-bold text-blue-800">PayPal</span>
                            </div>
                            <div class="bg-white rounded px-3 py-1.5">
                                <span class="text-xs font-bold text-green-600">COD</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expanded Footer Widgets -->
        <div v-if="isExpandedFooter" class="border-t border-gray-800">
            <div class="max-w-7xl mx-auto px-4 py-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div class="flex items-center space-x-4 p-4 bg-gray-800/50 rounded-xl">
                        <div class="flex-shrink-0 w-12 h-12 rounded-lg flex items-center justify-center" :style="{ backgroundColor: primaryColor + '30' }">
                            <ArchiveBoxIcon class="w-6 h-6" :style="{ color: primaryColor }" />
                        </div>
                        <div>
                            <h4 class="text-white font-medium text-sm">{{ t('footer.free_shipping') }}</h4>
                            <p class="text-gray-400 text-xs">{{ t('footer.free_shipping_text') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4 p-4 bg-gray-800/50 rounded-xl">
                        <div class="flex-shrink-0 w-12 h-12 rounded-lg flex items-center justify-center" :style="{ backgroundColor: primaryColor + '30' }">
                            <ShieldCheckIcon class="w-6 h-6" :style="{ color: primaryColor }" />
                        </div>
                        <div>
                            <h4 class="text-white font-medium text-sm">{{ t('footer.secure_payment') }}</h4>
                            <p class="text-gray-400 text-xs">{{ t('footer.secure_payment_text') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4 p-4 bg-gray-800/50 rounded-xl">
                        <div class="flex-shrink-0 w-12 h-12 rounded-lg flex items-center justify-center" :style="{ backgroundColor: primaryColor + '30' }">
                            <ArrowPathIcon class="w-6 h-6" :style="{ color: primaryColor }" />
                        </div>
                        <div>
                            <h4 class="text-white font-medium text-sm">{{ t('footer.easy_returns') }}</h4>
                            <p class="text-gray-400 text-xs">{{ t('footer.easy_returns_text') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4 p-4 bg-gray-800/50 rounded-xl">
                        <div class="flex-shrink-0 w-12 h-12 rounded-lg flex items-center justify-center" :style="{ backgroundColor: primaryColor + '30' }">
                            <LifebuoyIcon class="w-6 h-6" :style="{ color: primaryColor }" />
                        </div>
                        <div>
                            <h4 class="text-white font-medium text-sm">{{ t('footer.support_24_7') }}</h4>
                            <p class="text-gray-400 text-xs">{{ t('footer.support_text') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="border-t border-gray-800">
            <div class="max-w-7xl mx-auto px-4 py-6">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-gray-400">
                    <p>&copy; {{ currentYear }} {{ store?.name || 'Cartismo' }}. {{ t('footer.all_rights') }}</p>
                    <div class="flex items-center space-x-4">
                        <Link href="/privacy" class="hover:text-white transition-colors">{{ t('footer.privacy') }}</Link>
                        <Link href="/terms" class="hover:text-white transition-colors">{{ t('footer.terms') }}</Link>
                        <Link href="/cookies" class="hover:text-white transition-colors">{{ t('footer.cookies') }}</Link>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</template>