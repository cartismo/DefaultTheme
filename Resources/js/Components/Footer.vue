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
    ChatBubbleLeftRightIcon,
    PhotoIcon,
    AtSymbolIcon,
    PlayIcon,
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
                                <ChatBubbleLeftRightIcon class="w-5 h-5" />
                            </a>
                            <a
                                v-if="socialLinks.instagram"
                                :href="socialLinks.instagram"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gradient-to-br hover:from-purple-600 hover:to-pink-500 hover:text-white transition-all"
                            >
                                <PhotoIcon class="w-5 h-5" />
                            </a>
                            <a
                                v-if="socialLinks.twitter"
                                :href="socialLinks.twitter"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center text-gray-400 hover:bg-sky-500 hover:text-white transition-all"
                            >
                                <AtSymbolIcon class="w-5 h-5" />
                            </a>
                            <a
                                v-if="socialLinks.youtube"
                                :href="socialLinks.youtube"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center text-gray-400 hover:bg-red-600 hover:text-white transition-all"
                            >
                                <PlayIcon class="w-5 h-5" />
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
