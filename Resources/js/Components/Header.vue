<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { useLocale } from '@/Composables/useLocale';
import { useCurrency } from '@/Composables/useCurrency';
import debounce from 'lodash/debounce';
import 'flag-icons/css/flag-icons.min.css';

const props = defineProps({
    settings: Object,
    store: Object,
    cart: Object,
    customer: Object,
    categories: Array,
    menus: Object,
});

const emit = defineEmits(['toggle-mobile-menu', 'open-search', 'open-cart', 'open-notification']);

const page = usePage();

// Composables
const {
    currentLocale,
    availableLocales,
    languages,
    switchLocale,
    getLocaleName,
    getLocaleFlag,
    localizedUrl,
} = useLocale();

const {
    currentCurrency,
    currenciesList,
    switchCurrency,
    getCurrencySymbol,
} = useCurrency();

// Colors
const primaryColor = computed(() => props.settings?.colors?.primary || '#4F46E5');

// Header settings
const headerSettings = computed(() => props.settings?.header || {});
const showTopBar = computed(() => headerSettings.value.show_top_bar !== false);
const showSearch = computed(() => headerSettings.value.show_search !== false);
const showAccount = computed(() => headerSettings.value.show_account !== false);
const showWishlist = computed(() => headerSettings.value.show_wishlist !== false);
const showCart = computed(() => headerSettings.value.show_cart !== false);
const stickyHeader = computed(() => headerSettings.value.sticky_header !== false);
const showCategoriesMenu = computed(() => headerSettings.value.show_categories_menu !== false);

// Get main menu items
const mainMenuItems = computed(() => {
    const mainMenu = props.menus?.main || props.menus?.header;
    return mainMenu?.items || [];
});

// Get top bar menu items (from admin)
const topMenuItems = computed(() => {
    const topMenu = props.menus?.top;
    return topMenu?.items || [];
});

// Categories for mega menu - filter root categories
const menuCategories = computed(() => {
    return (props.categories || []).filter(c => c.show_in_menu).slice(0, 8);
});

// States
const showUserDropdown = ref(false);
const showLanguageDropdown = ref(false);
const showCurrencyDropdown = ref(false);
const activeMegaMenu = ref(null);
const searchQuery = ref('');
const isScrolled = ref(false);

// Search autocomplete
const searchResults = ref([]);
const isSearching = ref(false);
const showSearchDropdown = ref(false);

// Notifications
const notifications = ref([]);
const unreadCount = ref(0);
const showNotificationsDropdown = ref(false);
const selectedNotification = ref(null);
const showNotificationModal = ref(false);

// Fetch notifications
const fetchNotifications = async () => {
    if (!props.customer) return;

    try {
        const response = await fetch('/api/notifications');
        const data = await response.json();
        notifications.value = data.notifications || [];
        unreadCount.value = data.unread_count || 0;
    } catch (error) {
        console.error('Failed to fetch notifications:', error);
    }
};

// Mark notification as read and show modal
const openNotification = async (notification) => {
    try {
        const response = await fetch(`/api/notifications/${notification.id}`);
        const data = await response.json();
        selectedNotification.value = data.notification;
        showNotificationModal.value = true;
        showNotificationsDropdown.value = false;

        // Update unread count
        if (!notification.is_read) {
            unreadCount.value = Math.max(0, unreadCount.value - 1);
            const idx = notifications.value.findIndex(n => n.id === notification.id);
            if (idx !== -1) {
                notifications.value[idx].is_read = true;
            }
        }
    } catch (error) {
        console.error('Failed to open notification:', error);
    }
};

// Mark all as read
const markAllAsRead = async () => {
    try {
        await fetch('/api/notifications/mark-all-read', { method: 'POST' });
        notifications.value = notifications.value.map(n => ({ ...n, is_read: true }));
        unreadCount.value = 0;
    } catch (error) {
        console.error('Failed to mark all as read:', error);
    }
};

// Close notification modal
const closeNotificationModal = () => {
    showNotificationModal.value = false;
    selectedNotification.value = null;
};

// Format price for search results
const formatPrice = (price) => {
    return new Intl.NumberFormat('bg-BG', {
        style: 'currency',
        currency: 'BGN',
    }).format(price);
};

// Debounced search
const performSearch = debounce(async () => {
    if (searchQuery.value.length < 2) {
        searchResults.value = [];
        showSearchDropdown.value = false;
        return;
    }

    isSearching.value = true;
    showSearchDropdown.value = true;

    try {
        const response = await fetch(`/api/search?q=${encodeURIComponent(searchQuery.value)}`);
        const data = await response.json();
        searchResults.value = data.products || [];
    } catch (error) {
        console.error('Search error:', error);
        searchResults.value = [];
    } finally {
        isSearching.value = false;
    }
}, 300);

// Watch search query
watch(searchQuery, () => {
    performSearch();
});

// Go to product and close dropdown
const goToProduct = (slug) => {
    showSearchDropdown.value = false;
    searchQuery.value = '';
    searchResults.value = [];
    router.visit(`/product/${slug}`);
};

// Cart
const pageCart = computed(() => page.props.cart || { items: [], totals: {} });
const cartCount = computed(() => pageCart.value.totals?.items_count || 0);

// Current language data
const currentLanguage = computed(() => {
    return languages.value.find(l => l.code === currentLocale.value) || { name: 'Bulgarian', native_name: 'Български', code: 'bg', flag: 'bg' };
});

// Current currency data
const currentCurrencyData = computed(() => {
    return currenciesList.value.find(c => c.code === currentCurrency.value) || { code: 'EUR', symbol: '€' };
});

// Search
const handleSearch = () => {
    if (searchQuery.value.length >= 2) {
        router.get(localizedUrl('/search'), { q: searchQuery.value });
    }
};

// Language switch with proper handling
const handleLanguageSwitch = (code) => {
    switchLocale(code);
    showLanguageDropdown.value = false;
};

// Currency switch
const handleCurrencySwitch = (code) => {
    switchCurrency(code);
    showCurrencyDropdown.value = false;
};

// Scroll handler
const handleScroll = () => {
    isScrolled.value = window.scrollY > 50;
};

// Close dropdowns on click outside
const closeDropdowns = (e) => {
    if (!e.target.closest('.user-dropdown-container')) {
        showUserDropdown.value = false;
    }
    if (!e.target.closest('.language-dropdown-container')) {
        showLanguageDropdown.value = false;
    }
    if (!e.target.closest('.currency-dropdown-container')) {
        showCurrencyDropdown.value = false;
    }
    if (!e.target.closest('.notifications-dropdown-container')) {
        showNotificationsDropdown.value = false;
    }
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    document.addEventListener('click', closeDropdowns);
    fetchNotifications();
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    document.removeEventListener('click', closeDropdowns);
});
</script>

<template>
    <header
        class="bg-white transition-shadow duration-300"
        :class="[
            stickyHeader ? 'sticky top-0 z-50' : '',
            isScrolled ? 'shadow-lg' : 'shadow-sm'
        ]"
    >
        <!-- Top Bar -->
        <div v-if="showTopBar" class="bg-gray-900 text-gray-300 text-sm">
            <div class="max-w-7xl mx-auto px-4 py-2">
                <div class="flex items-center justify-between">
                    <!-- Left: Contact Info -->
                    <div class="flex items-center space-x-4 lg:space-x-6">
                        <a v-if="store?.phone" :href="`tel:${store.phone}`" class="flex items-center hover:text-white transition-colors">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <span class="hidden sm:inline">{{ store.phone }}</span>
                        </a>
                        <a v-if="store?.email" :href="`mailto:${store.email}`" class="hidden md:flex items-center hover:text-white transition-colors">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            {{ store.email }}
                        </a>
                        <span v-if="store?.address" class="hidden lg:flex items-center text-xs">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            {{ store.address }}
                        </span>
                    </div>

                    <!-- Right: Top Menu + Language/Currency -->
                    <div class="flex items-center space-x-4">
                        <!-- Top Menu Links (from admin) -->
                        <div v-if="topMenuItems.length > 0" class="hidden md:flex items-center space-x-4 text-xs">
                            <Link
                                v-for="item in topMenuItems"
                                :key="item.id"
                                :href="item.url || '#'"
                                class="hover:text-white transition-colors"
                            >
                                {{ item.title }}
                            </Link>
                        </div>

                        <!-- Currency Switcher -->
                        <div v-if="currenciesList.length > 1" class="relative currency-dropdown-container">
                            <button
                                @click.stop="showCurrencyDropdown = !showCurrencyDropdown"
                                class="flex items-center space-x-1.5 hover:text-white transition-colors"
                            >
                                <span class="font-medium">{{ currentCurrencyData.symbol || currentCurrency }}</span>
                                <span class="hidden sm:inline">{{ currentCurrency }}</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div
                                v-show="showCurrencyDropdown"
                                class="absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-xl border border-gray-200 py-1 z-50"
                            >
                                <button
                                    v-for="currency in currenciesList"
                                    :key="currency.code"
                                    @click="handleCurrencySwitch(currency.code)"
                                    class="w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center justify-between"
                                    :class="{ 'bg-gray-50 font-medium': currency.code === currentCurrency }"
                                >
                                    <span>{{ currency.name }}</span>
                                    <span class="text-gray-500">{{ currency.symbol || currency.code }}</span>
                                </button>
                            </div>
                        </div>

                        <!-- Language Switcher -->
                        <div v-if="languages.length > 1" class="relative language-dropdown-container">
                            <button
                                @click.stop="showLanguageDropdown = !showLanguageDropdown"
                                class="flex items-center space-x-1.5 hover:text-white transition-colors"
                            >
                                <span class="fi" :class="`fi-${currentLanguage.flag || currentLanguage.code}`"></span>
                                <span class="hidden sm:inline">{{ currentLanguage.native_name || currentLanguage.name }}</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div
                                v-show="showLanguageDropdown"
                                class="absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-xl border border-gray-200 py-1 z-50"
                            >
                                <button
                                    v-for="lang in languages"
                                    :key="lang.code"
                                    @click="handleLanguageSwitch(lang.code)"
                                    class="w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center space-x-2"
                                    :class="{ 'bg-gray-50 font-medium': lang.code === currentLocale }"
                                >
                                    <span class="fi" :class="`fi-${lang.flag || lang.code}`"></span>
                                    <span>{{ lang.native_name || lang.name }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Header -->
        <div class="border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex items-center justify-between h-16 lg:h-20 gap-4">
                    <!-- Mobile Menu Button -->
                    <button
                        @click="$emit('toggle-mobile-menu')"
                        class="lg:hidden p-2 -ml-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <!-- Logo -->
                    <Link href="/" class="flex-shrink-0">
                        <img
                            v-if="store?.logo"
                            :src="store.logo"
                            :alt="store?.name || 'Store'"
                            class="h-8 lg:h-10 w-auto"
                        />
                        <span v-else class="text-xl lg:text-2xl font-bold" :style="{ color: primaryColor }">
                            {{ store?.name || 'Cartismo' }}
                        </span>
                    </Link>

                    <!-- Desktop Search Bar -->
                    <div v-if="showSearch" class="hidden lg:flex flex-1 max-w-xl mx-8 relative search-dropdown-container">
                        <form @submit.prevent="handleSearch" class="w-full relative">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Търсене на продукти..."
                                class="w-full pl-4 pr-12 py-3 bg-gray-100 border-0 rounded-full focus:bg-white focus:ring-2 focus:outline-none transition-all"
                                :style="{ '--tw-ring-color': primaryColor }"
                                @focus="searchQuery.length >= 2 && (showSearchDropdown = true)"
                                @blur="setTimeout(() => showSearchDropdown = false, 200)"
                            />
                            <button
                                type="submit"
                                class="absolute right-1 top-1/2 -translate-y-1/2 p-2 rounded-full text-white transition-colors"
                                :style="{ backgroundColor: primaryColor }"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </button>
                        </form>

                        <!-- Search Autocomplete Dropdown -->
                        <transition
                            enter-active-class="transition ease-out duration-100"
                            enter-from-class="transform opacity-0 scale-95"
                            enter-to-class="transform opacity-100 scale-100"
                            leave-active-class="transition ease-in duration-75"
                            leave-from-class="transform opacity-100 scale-100"
                            leave-to-class="transform opacity-0 scale-95"
                        >
                            <div
                                v-if="showSearchDropdown && (isSearching || searchResults.length > 0 || searchQuery.length >= 2)"
                                class="absolute top-full left-0 right-0 mt-2 bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden z-50"
                            >
                                <!-- Loading -->
                                <div v-if="isSearching" class="flex items-center justify-center py-6">
                                    <svg class="animate-spin h-6 w-6" :style="{ color: primaryColor }" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>

                                <!-- Results -->
                                <div v-else-if="searchResults.length > 0" class="max-h-96 overflow-y-auto">
                                    <button
                                        v-for="product in searchResults"
                                        :key="product.id"
                                        @mousedown.prevent="goToProduct(product.slug)"
                                        class="w-full flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors text-left"
                                    >
                                        <div class="w-12 h-12 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                            <img
                                                v-if="product.image_url"
                                                :src="product.image_url"
                                                :alt="product.name"
                                                class="w-full h-full object-cover"
                                            />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-gray-900 truncate">{{ product.name }}</p>
                                            <p class="text-sm text-gray-500">{{ product.category?.name || product.sku }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-bold" :style="{ color: primaryColor }">{{ formatPrice(product.final_price) }}</p>
                                            <p v-if="product.final_price < product.price" class="text-xs text-gray-400 line-through">
                                                {{ formatPrice(product.price) }}
                                            </p>
                                        </div>
                                    </button>

                                    <!-- View All -->
                                    <div class="border-t border-gray-100 px-4 py-3">
                                        <button
                                            @mousedown.prevent="handleSearch"
                                            class="w-full text-center font-medium hover:opacity-80 transition-opacity"
                                            :style="{ color: primaryColor }"
                                        >
                                            Виж всички резултати за "{{ searchQuery }}"
                                        </button>
                                    </div>
                                </div>

                                <!-- No Results -->
                                <div v-else class="py-6 text-center text-gray-500">
                                    Няма намерени продукти за "{{ searchQuery }}"
                                </div>
                            </div>
                        </transition>
                    </div>

                    <!-- Header Icons -->
                    <div class="flex items-center space-x-1">
                        <!-- Mobile Search -->
                        <button
                            v-if="showSearch"
                            @click="$emit('open-search')"
                            class="lg:hidden p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>

                        <!-- Wishlist -->
                        <Link
                            v-if="showWishlist"
                            href="/wishlist"
                            class="hidden sm:flex p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg relative"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </Link>

                        <!-- Account -->
                        <div v-if="showAccount" class="relative user-dropdown-container">
                            <button
                                v-if="customer"
                                @click.stop="showUserDropdown = !showUserDropdown"
                                class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg flex items-center"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span class="hidden md:inline ml-1 text-sm font-medium">{{ customer.first_name || customer.name?.split(' ')[0] }}</span>
                            </button>
                            <Link
                                v-else
                                href="/login"
                                class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg flex items-center"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span class="hidden md:inline ml-1 text-sm">Вход</span>
                            </Link>

                            <!-- User Dropdown -->
                            <transition
                                enter-active-class="transition ease-out duration-100"
                                enter-from-class="transform opacity-0 scale-95"
                                enter-to-class="transform opacity-100 scale-100"
                                leave-active-class="transition ease-in duration-75"
                                leave-from-class="transform opacity-100 scale-100"
                                leave-to-class="transform opacity-0 scale-95"
                            >
                                <div
                                    v-if="customer && showUserDropdown"
                                    class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-gray-200 py-2 z-50"
                                >
                                    <div class="px-4 py-3 border-b border-gray-100">
                                        <p class="font-medium text-gray-900">{{ customer.name }}</p>
                                        <p class="text-sm text-gray-500 truncate">{{ customer.email }}</p>
                                    </div>
                                    <Link href="/account" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                        </svg>
                                        Табло
                                    </Link>
                                    <Link href="/account/orders" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                        </svg>
                                        Поръчки
                                    </Link>
                                    <Link href="/wishlist" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                        </svg>
                                        Любими
                                    </Link>
                                    <Link href="/account/profile" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Настройки
                                    </Link>
                                    <hr class="my-2 border-gray-100">
                                    <Link href="/logout" method="post" as="button" class="w-full flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50">
                                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        Изход
                                    </Link>
                                </div>
                            </transition>
                        </div>

                        <!-- Notifications Bell (for logged in customers) -->
                        <div v-if="customer" class="relative notifications-dropdown-container">
                            <button
                                @click.stop="showNotificationsDropdown = !showNotificationsDropdown"
                                class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <span
                                    v-if="unreadCount > 0"
                                    class="absolute -top-0.5 -right-0.5 w-5 h-5 flex items-center justify-center text-xs font-bold text-white rounded-full"
                                    :style="{ backgroundColor: primaryColor }"
                                >
                                    {{ unreadCount > 9 ? '9+' : unreadCount }}
                                </span>
                            </button>

                            <!-- Notifications Dropdown -->
                            <transition
                                enter-active-class="transition ease-out duration-100"
                                enter-from-class="transform opacity-0 scale-95"
                                enter-to-class="transform opacity-100 scale-100"
                                leave-active-class="transition ease-in duration-75"
                                leave-from-class="transform opacity-100 scale-100"
                                leave-to-class="transform opacity-0 scale-95"
                            >
                                <div
                                    v-if="showNotificationsDropdown"
                                    class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden z-50"
                                >
                                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                                        <h3 class="font-semibold text-gray-900">Известия</h3>
                                        <button
                                            v-if="unreadCount > 0"
                                            @click="markAllAsRead"
                                            class="text-xs hover:underline"
                                            :style="{ color: primaryColor }"
                                        >
                                            Маркирай всички като прочетени
                                        </button>
                                    </div>

                                    <div class="max-h-96 overflow-y-auto">
                                        <div v-if="notifications.length === 0" class="py-8 text-center text-gray-500">
                                            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                            </svg>
                                            <p>Няма известия</p>
                                        </div>

                                        <button
                                            v-for="notification in notifications"
                                            :key="notification.id"
                                            @click="openNotification(notification)"
                                            class="w-full px-4 py-3 text-left hover:bg-gray-50 transition-colors border-b border-gray-50 last:border-0"
                                            :class="{ 'bg-indigo-50/50': !notification.is_read }"
                                        >
                                            <div class="flex items-start gap-3">
                                                <div
                                                    class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0"
                                                    :class="notification.is_read ? 'bg-gray-100 text-gray-500' : 'bg-indigo-100 text-indigo-600'"
                                                >
                                                    <svg v-if="notification.icon === 'shopping-bag'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                                    </svg>
                                                    <svg v-else-if="notification.icon === 'truck'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                                    </svg>
                                                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="font-medium text-gray-900 text-sm truncate">{{ notification.title }}</p>
                                                    <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ notification.message }}</p>
                                                    <p class="text-xs text-gray-400 mt-1">{{ notification.time_ago }}</p>
                                                </div>
                                                <div v-if="!notification.is_read" class="w-2 h-2 rounded-full flex-shrink-0" :style="{ backgroundColor: primaryColor }"></div>
                                            </div>
                                        </button>
                                    </div>
                                </div>
                            </transition>
                        </div>

                        <!-- Cart -->
                        <button
                            v-if="showCart"
                            @click="$emit('open-cart')"
                            class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg flex items-center"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span
                                v-if="cartCount > 0"
                                class="absolute -top-0.5 -right-0.5 w-5 h-5 flex items-center justify-center text-xs font-bold text-white rounded-full"
                                :style="{ backgroundColor: primaryColor }"
                            >
                                {{ cartCount > 99 ? '99+' : cartCount }}
                            </span>
                            <span class="hidden md:inline ml-1 text-sm font-medium">Количка</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Bar with Categories -->
        <nav v-if="showCategoriesMenu && menuCategories.length > 0" class="hidden lg:block bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex items-center space-x-1 h-12">
                    <!-- All Categories Button -->
                    <div class="relative group">
                        <button
                            class="flex items-center px-4 py-2 text-white font-medium rounded-lg transition-colors"
                            :style="{ backgroundColor: primaryColor }"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            Всички категории
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Mega Menu Dropdown -->
                        <div class="absolute left-0 top-full pt-2 w-[800px] opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                            <div class="bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden">
                                <div class="grid grid-cols-4 gap-0">
                                    <!-- Categories List -->
                                    <div class="col-span-1 bg-gray-50 py-4">
                                        <div class="space-y-1">
                                            <Link
                                                v-for="category in menuCategories"
                                                :key="category.id"
                                                :href="`/category/${category.slug}`"
                                                class="flex items-center justify-between px-4 py-2.5 text-sm text-gray-700 hover:bg-white hover:text-gray-900 group/item"
                                            >
                                                <span class="flex items-center">
                                                    <span v-if="category.icon" class="mr-2" v-html="category.icon"></span>
                                                    {{ category.name }}
                                                </span>
                                                <svg v-if="category.children?.length" class="w-4 h-4 text-gray-400 group-hover/item:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </Link>
                                        </div>
                                    </div>

                                    <!-- Featured Categories / Subcategories -->
                                    <div class="col-span-3 p-6">
                                        <div class="grid grid-cols-3 gap-6">
                                            <div v-for="category in menuCategories.slice(0, 6)" :key="`sub-${category.id}`">
                                                <Link
                                                    :href="`/category/${category.slug}`"
                                                    class="font-semibold text-gray-900 hover:underline block mb-3"
                                                    :style="{ color: primaryColor }"
                                                >
                                                    {{ category.name }}
                                                </Link>
                                                <ul v-if="category.children?.length" class="space-y-2">
                                                    <li v-for="child in category.children.slice(0, 5)" :key="child.id">
                                                        <Link
                                                            :href="`/category/${child.slug}`"
                                                            class="text-sm text-gray-600 hover:text-gray-900 hover:underline"
                                                        >
                                                            {{ child.name }}
                                                        </Link>
                                                    </li>
                                                    <li v-if="category.children.length > 5">
                                                        <Link
                                                            :href="`/category/${category.slug}`"
                                                            class="text-sm font-medium"
                                                            :style="{ color: primaryColor }"
                                                        >
                                                            Виж всички &rarr;
                                                        </Link>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Menu Items -->
                    <template v-for="item in mainMenuItems" :key="item.id">
                        <Link
                            :href="item.url || '#'"
                            class="px-4 py-2 text-gray-700 hover:text-gray-900 font-medium rounded-lg hover:bg-gray-100 transition-colors text-sm"
                        >
                            {{ item.title }}
                        </Link>
                    </template>

                    <!-- Quick Links -->
                    <Link href="/products?sale=1" class="px-4 py-2 text-red-600 hover:text-red-700 font-medium rounded-lg hover:bg-red-50 transition-colors text-sm">
                        Промоции
                    </Link>
                    <Link href="/products?featured=1" class="px-4 py-2 text-gray-700 hover:text-gray-900 font-medium rounded-lg hover:bg-gray-100 transition-colors text-sm">
                        Препоръчани
                    </Link>
                </div>
            </div>
        </nav>
    </header>

    <!-- Notification Modal -->
    <Teleport to="body">
        <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showNotificationModal && selectedNotification"
                class="fixed inset-0 z-[100] overflow-y-auto"
            >
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/50" @click="closeNotificationModal"></div>

                <!-- Modal Content -->
                <div class="relative min-h-screen flex items-center justify-center p-4">
                    <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden">
                        <!-- Modal Header -->
                        <div class="sticky top-0 bg-white px-6 py-4 border-b border-gray-100 flex items-center justify-between z-10">
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ selectedNotification.title }}</h3>
                                <p class="text-sm text-gray-500">{{ selectedNotification.created_at }}</p>
                            </div>
                            <button
                                @click="closeNotificationModal"
                                class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Modal Body -->
                        <div class="overflow-y-auto max-h-[calc(90vh-80px)]">
                            <!-- If has HTML content, show it in iframe for safety -->
                            <div
                                v-if="selectedNotification.html_content"
                                class="notification-email-content"
                                v-html="selectedNotification.html_content"
                            ></div>

                            <!-- Fallback to message if no HTML -->
                            <div v-else class="p-6">
                                <p class="text-gray-700">{{ selectedNotification.message }}</p>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="sticky bottom-0 bg-white px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                            <Link
                                v-if="selectedNotification.link"
                                :href="selectedNotification.link"
                                class="inline-flex items-center px-4 py-2 text-white rounded-lg font-medium transition-colors"
                                :style="{ backgroundColor: primaryColor }"
                                @click="closeNotificationModal"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Виж детайли
                            </Link>
                            <button
                                @click="closeNotificationModal"
                                class="px-4 py-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg font-medium transition-colors"
                            >
                                Затвори
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </Teleport>
</template>
