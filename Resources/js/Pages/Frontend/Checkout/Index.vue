<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import axios from 'axios';
import StorefrontLayout from '@theme/Layouts/StorefrontLayout.vue';
import VueSelect from '@/Components/VueSelect.vue';
import { useThemeTranslations } from '../../../Composables/useThemeTranslations';
import { useCurrency } from '@/Composables/useCurrency';
import {
    UserIcon,
    CheckIcon,
    PlusIcon,
    BuildingOfficeIcon,
    HomeIcon,
    TruckIcon,
    CpuChipIcon,
    BanknotesIcon,
    BuildingLibraryIcon,
    CreditCardIcon,
    ArrowPathIcon,
    ArchiveBoxIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    settings: Object,
    cart: Object,
    customer: Object,
    addresses: Array,
    billingProfiles: Array,
    countries: Array,
    shippingMethods: Array,
    paymentMethods: Array,
});

const { t } = useThemeTranslations();
const { formatPrice } = useCurrency();

// Redirect to home if catalog mode
const catalogMode = computed(() => usePage().props.store?.catalog_mode === true);
onMounted(() => {
    if (catalogMode.value) {
        router.visit('/');
    }
});

// LocalStorage key for checkout data
const CHECKOUT_STORAGE_KEY = 'cartismo_checkout_data';

// Current step
const currentStep = ref(1);
const steps = computed(() => [
    { number: 1, title: t('checkout.step_info'), icon: 'user' },
    { number: 2, title: t('checkout.step_shipping'), icon: 'truck' },
    { number: 3, title: t('checkout.step_payment'), icon: 'credit-card' },
]);

const shippingMethodIcon = (method) => {
    if (method?.icon === 'archive-box') {
        return ArchiveBoxIcon;
    }

    return TruckIcon;
};

// Get default form data
const getDefaultFormData = () => ({
    // Customer info (Step 1)
    email: props.customer?.email || '',
    phone: props.customer?.phone || '',
    first_name: '',
    last_name: '',

    // Shipping method & delivery data (Step 2)
    shipping_method_id: props.shippingMethods?.[0]?.id || '',
    delivery: {
        country_id: props.countries?.[0]?.id || '',
        zone_id: '',
        city_id: '',
        postcode: '',
        address_1: '',
        address_2: '',
        office_id: '',
        locker_id: '',
    },

    // For order processing (mapped from delivery)
    shipping_address_1: '',
    shipping_address_2: '',
    shipping_city: '',
    shipping_postcode: '',
    shipping_country_id: '',
    shipping_zone_id: '',

    // Billing
    billing_same_as_shipping: true,
    billing_address_1: '',
    billing_address_2: '',
    billing_city: '',
    billing_postcode: '',
    billing_country_id: '',
    billing_zone_id: '',

    // Invoice / Billing Profile
    needs_invoice: false,
    billing_profile_id: null,
    invoice: {
        type: 'company',
        company_name: '',
        eik: '',
        vat_number: '',
        is_vat_registered: false,
        mol: '',
        person_name: '',
        egn: '',
        address: '',
        city: '',
        postcode: '',
        country_id: '',
        phone: '',
        email: '',
    },

    // Payment (Step 3)
    payment_method_id: props.paymentMethods?.[0]?.id || '',

    // Other
    comment: '',
    newsletter: false,
    terms_accepted: false,
});

// Load saved checkout data from localStorage
const loadSavedCheckoutData = () => {
    try {
        const saved = localStorage.getItem(CHECKOUT_STORAGE_KEY);
        if (saved) {
            const parsed = JSON.parse(saved);
            const savedAt = parsed._savedAt || 0;
            const now = Date.now();
            const maxAge = 24 * 60 * 60 * 1000;

            if (now - savedAt < maxAge) {
                const { _savedAt, _step, ...formData } = parsed;
                return {
                    formData,
                    step: _step || 1
                };
            } else {
                localStorage.removeItem(CHECKOUT_STORAGE_KEY);
            }
        }
    } catch (e) {
        localStorage.removeItem(CHECKOUT_STORAGE_KEY);
    }
    return null;
};

// Save checkout data to localStorage
const saveCheckoutData = () => {
    try {
        const dataToSave = {
            ...form.value,
            _savedAt: Date.now(),
            _step: currentStep.value,
        };
        localStorage.setItem(CHECKOUT_STORAGE_KEY, JSON.stringify(dataToSave));
    } catch (e) {
        // silent
    }
};

// Clear saved checkout data and dispatch event for modules
const clearSavedCheckoutData = () => {
    try {
        localStorage.removeItem(CHECKOUT_STORAGE_KEY);
        window.dispatchEvent(new CustomEvent('checkout-complete'));
    } catch (e) {
        // silent
    }
};

// Initialize form with saved data or defaults
const savedData = loadSavedCheckoutData();
const form = ref(savedData?.formData ? { ...getDefaultFormData(), ...savedData.formData } : getDefaultFormData());

// Restore step if there was saved data
if (savedData?.step) {
    currentStep.value = savedData.step;
}

// Auto-save form data when it changes (debounced)
let saveTimeout = null;
watch([form, currentStep], () => {
    clearTimeout(saveTimeout);
    saveTimeout = setTimeout(() => {
        saveCheckoutData();
    }, 500);
}, { deep: true });

// Errors
const errors = ref({});
const isProcessing = ref(false);

// Dynamic data
const cities = ref([]);
const zones = ref([]);
const loadingCities = ref(false);
const loadingZones = ref(false);
const citySearch = ref('');

// Shipping calculation
const calculatedShipping = ref({
    cost: 0,
    formatted_cost: t('checkout.free'),
});
const calculatingShipping = ref(false);

// Load zones when country changes
watch(() => form.value.delivery.country_id, async (countryId) => {
    if (countryId) {
        loadingZones.value = true;
        try {
            const response = await axios.get(`/checkout/zones/${countryId}`);
            zones.value = response.data.zones;
            form.value.delivery.zone_id = '';
            form.value.delivery.city_id = '';
            cities.value = [];
        } catch (error) {
            // silent
        } finally {
            loadingZones.value = false;
        }
        loadCities();
    } else {
        zones.value = [];
        cities.value = [];
    }
}, { immediate: true });

// Load cities when zone changes
watch(() => form.value.delivery.zone_id, () => {
    form.value.delivery.city_id = '';
    loadCities();
});

// Load cities function
const loadCities = async () => {
    const countryId = form.value.delivery.country_id;
    if (!countryId) return;

    loadingCities.value = true;
    try {
        const params = new URLSearchParams();
        if (form.value.delivery.zone_id) {
            params.append('zone_id', form.value.delivery.zone_id);
        }
        if (citySearch.value) {
            params.append('search', citySearch.value);
        }

        const url = `/checkout/cities/${countryId}${params.toString() ? '?' + params.toString() : ''}`;
        const response = await axios.get(url);
        cities.value = response.data.cities;
    } catch (error) {
        // silent
    } finally {
        loadingCities.value = false;
    }
};

// Debounced city search
let citySearchTimeout = null;
watch(citySearch, (value) => {
    clearTimeout(citySearchTimeout);
    citySearchTimeout = setTimeout(() => {
        loadCities();
    }, 300);
});

// Auto-fill postcode when city is selected
watch(() => form.value.delivery.city_id, (cityId) => {
    if (cityId) {
        const city = cities.value.find(c => c.id === cityId);
        if (city && city.postcode) {
            form.value.delivery.postcode = city.postcode;
        }
    }
    calculateShipping();
});

// Calculate shipping when method or delivery data changes
watch(() => form.value.shipping_method_id, () => {
    calculateShipping();
});

watch(() => form.value.delivery, () => {
    calculateShipping();
}, { deep: true });

// Calculate shipping
const calculateShipping = async () => {
    if (!form.value.shipping_method_id) return;

    calculatingShipping.value = true;
    try {
        const response = await axios.post('/checkout/shipping', {
            shipping_method_id: form.value.shipping_method_id,
            delivery_data: form.value.delivery,
        });

        if (response.data.success) {
            calculatedShipping.value = {
                cost: response.data.cost,
                formatted_cost: response.data.formatted_cost,
            };
        }
    } catch (error) {
        // silent
    } finally {
        calculatingShipping.value = false;
    }
};

// Computed
const primaryColor = computed(() => props.settings?.colors?.primary || '#4334db');
const cartItems = computed(() => props.cart?.items || []);
const cartTotals = computed(() => props.cart?.totals || {});

const selectedShippingMethod = computed(() => {
    return props.shippingMethods?.find(m => m.id === form.value.shipping_method_id);
});

const selectedPaymentMethod = computed(() => {
    return props.paymentMethods?.find(m => m.id === form.value.payment_method_id);
});

const shippingCost = computed(() => {
    return calculatedShipping.value.cost || 0;
});

const orderTotal = computed(() => {
    return (cartTotals.value.subtotal || 0) + shippingCost.value;
});

// Get delivery form configuration from selected shipping method
const deliveryFormConfig = computed(() => {
    return selectedShippingMethod.value?.delivery_form || null;
});

const deliveryType = computed(() => {
    return selectedShippingMethod.value?.delivery_type || 'address';
});

// Check if shipping method needs delivery details but not yet provided
const awaitingDeliveryDetails = computed(() => {
    const method = selectedShippingMethod.value;
    if (!method?.delivery_form?.component) return false;
    const delivery = form.value.delivery;
    if (delivery.office_id) return false;
    if (delivery.address_1 && delivery.postcode) return false;
    return true;
});

// Fill from saved address
const fillFromAddress = (address) => {
    form.value.first_name = address.first_name;
    form.value.last_name = address.last_name;
    form.value.delivery.country_id = address.country_id;
    form.value.delivery.zone_id = address.zone_id || '';
    form.value.delivery.city_id = '';
    form.value.delivery.postcode = address.postcode;
    form.value.delivery.address_1 = address.address_1;
    form.value.delivery.address_2 = address.address_2 || '';
};

// Map delivery data to shipping fields before submit
const mapDeliveryToShipping = () => {
    const delivery = form.value.delivery;
    const city = cities.value.find(c => c.id === delivery.city_id);

    if (delivery.office_id) {
        form.value.shipping_city = delivery.city_name || delivery.office_city || city?.name || '';
        form.value.shipping_address_1 = delivery.office_name || '';
        form.value.shipping_address_2 = delivery.office_address || '';
        form.value.shipping_postcode = delivery.postcode || '';
        form.value.shipping_country_id = delivery.country_id || '';
        form.value.shipping_zone_id = delivery.zone_id || '';
    } else {
        form.value.shipping_country_id = delivery.country_id;
        form.value.shipping_zone_id = delivery.zone_id;
        form.value.shipping_city = city?.name || delivery.city_name || '';
        form.value.shipping_postcode = delivery.postcode;
        form.value.shipping_address_1 = delivery.address_1;
        form.value.shipping_address_2 = delivery.address_2;
    }
};

// Handle office selection from module component
const onOfficeSelected = ({ city, office }) => {
    calculateShipping();
};

// Validate step
const validateStep = (step) => {
    errors.value = {};

    if (step === 1) {
        if (!form.value.email) errors.value.email = t('checkout.validation.email_required');
        else if (!/\S+@\S+\.\S+/.test(form.value.email)) errors.value.email = t('checkout.validation.email_invalid');
        if (!form.value.phone) errors.value.phone = t('checkout.validation.phone_required');
        if (!form.value.first_name) errors.value.first_name = t('checkout.validation.first_name_required');
        if (!form.value.last_name) errors.value.last_name = t('checkout.validation.last_name_required');
    }

    if (step === 2) {
        if (!form.value.shipping_method_id) errors.value.shipping_method_id = t('checkout.validation.shipping_required');

        if (deliveryFormConfig.value?.component) {
            const deliveryData = form.value.delivery;
            const componentDeliveryType = deliveryData.delivery_type || deliveryType.value;

            if (componentDeliveryType === 'address') {
                if (!deliveryData.city_name && !deliveryData.city) {
                    errors.value['delivery.city'] = t('checkout.validation.city_required');
                }
                if (!deliveryData.postcode) {
                    errors.value['delivery.postcode'] = t('checkout.validation.postcode_required');
                }
                if (!deliveryData.address_1) {
                    errors.value['delivery.address_1'] = t('checkout.validation.address_required');
                }
            } else {
                if (!deliveryData.office_id) {
                    errors.value['delivery.office_id'] = t('checkout.validation.office_required');
                }
            }
        } else if (deliveryFormConfig.value?.fields) {
            for (const field of deliveryFormConfig.value.fields) {
                if (field.required && !form.value.delivery[field.name]) {
                    errors.value[`delivery.${field.name}`] = t('checkout.validation.field_required', { field: field.label });
                }
            }
        }
    }

    if (step === 3) {
        if (!form.value.payment_method_id) errors.value.payment_method_id = t('checkout.validation.payment_required');
        if (!form.value.terms_accepted) errors.value.terms_accepted = t('checkout.validation.terms_required');
    }

    return Object.keys(errors.value).length === 0;
};

// Next step
const nextStep = () => {
    if (validateStep(currentStep.value)) {
        if (currentStep.value === 2) {
            mapDeliveryToShipping();
        }
        currentStep.value = Math.min(currentStep.value + 1, 3);
    }
};

// Previous step
const prevStep = () => {
    currentStep.value = Math.max(currentStep.value - 1, 1);
};

// Submit order
const submitOrder = async () => {
    if (!validateStep(3)) return;

    mapDeliveryToShipping();

    isProcessing.value = true;
    errors.value = {};

    try {
        const response = await axios.post('/checkout/process', form.value);

        if (response.data.success) {
            clearSavedCheckoutData();
            window.location.href = response.data.redirect;
        } else {
            errors.value.general = response.data.message;
        }
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
        } else {
            errors.value.general = error.response?.data?.message || t('checkout.error_occurred');
        }
    } finally {
        isProcessing.value = false;
    }
};

// Initial shipping calculation
onMounted(() => {
    calculateShipping();
});
</script>

<template>
    <StorefrontLayout :title="t('checkout.title')">
        <div class="bg-gray-50 min-h-screen py-8">
            <div class="max-w-7xl mx-auto px-4">
                <!-- Breadcrumb -->
                <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-6">
                    <Link href="/" class="hover:text-gray-700">{{ t('home') }}</Link>
                    <span>/</span>
                    <Link href="/cart" class="hover:text-gray-700">{{ t('cart.title') }}</Link>
                    <span>/</span>
                    <span class="text-gray-900 font-medium">{{ t('checkout.title') }}</span>
                </nav>

                <!-- Steps -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-8">
                    <div class="flex items-center justify-center">
                        <template v-for="(step, index) in steps" :key="step.number">
                            <div class="flex items-center">
                                <div
                                    class="w-10 h-10 rounded-full flex items-center justify-center font-semibold text-sm transition-colors"
                                    :class="currentStep >= step.number ? 'text-white' : 'bg-gray-200 text-gray-500'"
                                    :style="currentStep >= step.number ? { backgroundColor: primaryColor } : {}"
                                >
                                    {{ step.number }}
                                </div>
                                <span class="ml-2 font-medium text-sm hidden sm:inline" :class="currentStep >= step.number ? 'text-gray-900' : 'text-gray-400'">
                                    {{ step.title }}
                                </span>
                            </div>
                            <div v-if="index < steps.length - 1" class="w-16 sm:w-24 h-1 mx-2 sm:mx-4 rounded" :class="currentStep > step.number ? '' : 'bg-gray-200'" :style="currentStep > step.number ? { backgroundColor: primaryColor } : {}" />
                        </template>
                    </div>
                </div>

                <div class="lg:grid lg:grid-cols-12 lg:gap-8">
                    <!-- Form Section -->
                    <div class="lg:col-span-8">
                        <!-- Error Message -->
                        <div v-if="errors.general" class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl">
                            {{ errors.general }}
                        </div>

                        <!-- Step 1: Contact Info -->
                        <div v-show="currentStep === 1" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-6">{{ t('checkout.contact_info') }}</h2>

                            <!-- Login Section (for guests) -->
                            <div v-if="!customer" class="mb-6 p-4 bg-gray-50 rounded-xl border border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                                            <UserIcon class="w-5 h-5 text-gray-500" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ t('checkout.have_account') }}</p>
                                            <p class="text-xs text-gray-500">{{ t('checkout.faster_checkout') }}</p>
                                        </div>
                                    </div>
                                    <Link
                                        :href="`/login?redirect=${encodeURIComponent('/checkout')}`"
                                        class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-100 transition-colors"
                                    >
                                        {{ t('checkout.login') }}
                                    </Link>
                                </div>
                            </div>

                            <!-- Logged in user info -->
                            <div v-else class="mb-6 p-4 bg-green-50 rounded-xl border border-green-200">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center" :style="{ backgroundColor: primaryColor + '20' }">
                                        <CheckIcon class="w-5 h-5" :style="{ color: primaryColor }" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ customer.name }}</p>
                                        <p class="text-xs text-gray-500">{{ customer.email }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Saved Addresses (for quick name fill) -->
                            <div v-if="addresses?.length > 0" class="mb-6">
                                <h3 class="text-sm font-medium text-gray-700 mb-3">{{ t('checkout.quick_fill') }}</h3>
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        v-for="address in addresses"
                                        :key="address.id"
                                        @click="fillFromAddress(address)"
                                        class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:border-gray-400 transition-colors"
                                    >
                                        {{ address.first_name }} {{ address.last_name }}
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Email -->
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('checkout.email_label') }} {{ t('checkout.required') }}</label>
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:border-transparent"
                                        :class="errors.email ? 'border-red-500' : 'border-gray-300'"
                                        placeholder="your@email.com"
                                    />
                                    <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
                                </div>

                                <!-- Phone -->
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('checkout.phone_label') }} {{ t('checkout.required') }}</label>
                                    <input
                                        v-model="form.phone"
                                        type="tel"
                                        class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:border-transparent"
                                        :class="errors.phone ? 'border-red-500' : 'border-gray-300'"
                                    />
                                    <p v-if="errors.phone" class="mt-1 text-sm text-red-600">{{ errors.phone }}</p>
                                </div>

                                <!-- First Name -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('checkout.first_name') }} {{ t('checkout.required') }}</label>
                                    <input
                                        v-model="form.first_name"
                                        type="text"
                                        class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:border-transparent"
                                        :class="errors.first_name ? 'border-red-500' : 'border-gray-300'"
                                    />
                                    <p v-if="errors.first_name" class="mt-1 text-sm text-red-600">{{ errors.first_name }}</p>
                                </div>

                                <!-- Last Name -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('checkout.last_name') }} {{ t('checkout.required') }}</label>
                                    <input
                                        v-model="form.last_name"
                                        type="text"
                                        class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:border-transparent"
                                        :class="errors.last_name ? 'border-red-500' : 'border-gray-300'"
                                    />
                                    <p v-if="errors.last_name" class="mt-1 text-sm text-red-600">{{ errors.last_name }}</p>
                                </div>
                            </div>

                            <!-- Invoice / Billing Data Section -->
                            <div class="mt-8 pt-6 border-t border-gray-200">
                                <label class="flex items-center cursor-pointer mb-4">
                                    <input
                                        type="checkbox"
                                        v-model="form.needs_invoice"
                                        class="rounded border-gray-300 mr-3"
                                        :style="{ color: primaryColor }"
                                    />
                                    <span class="text-sm font-medium text-gray-900">{{ t('checkout.want_invoice') }}</span>
                                </label>

                                <!-- Invoice form - expandable -->
                                <div v-if="form.needs_invoice" class="space-y-4">
                                    <!-- Saved Billing Profiles (for registered customers) -->
                                    <div v-if="customer && billingProfiles?.length > 0" class="mb-4">
                                        <h3 class="text-sm font-medium text-gray-700 mb-3">{{ t('checkout.saved_billing') }}</h3>
                                        <div class="space-y-2">
                                            <label
                                                v-for="profile in billingProfiles"
                                                :key="profile.id"
                                                class="flex items-center p-3 border rounded-lg cursor-pointer transition-all"
                                                :class="form.billing_profile_id === profile.id ? 'border-2 bg-opacity-10' : 'border-gray-200 hover:border-gray-300'"
                                                :style="form.billing_profile_id === profile.id ? { borderColor: primaryColor, backgroundColor: primaryColor + '10' } : {}"
                                            >
                                                <input
                                                    type="radio"
                                                    v-model="form.billing_profile_id"
                                                    :value="profile.id"
                                                    class="sr-only"
                                                />
                                                <div class="flex-1">
                                                    <span class="font-medium text-gray-900 block">{{ profile.display_name }}</span>
                                                    <span class="text-sm text-gray-500">
                                                        {{ profile.type === 'company' ? `${t('checkout.eik_prefix')} ${profile.eik}` : `${t('checkout.egn_prefix')} ${profile.egn}` }}
                                                        <span v-if="profile.address"> &bull; {{ profile.city }}, {{ profile.address }}</span>
                                                    </span>
                                                </div>
                                                <span v-if="profile.is_default" class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-600">{{ t('checkout.default_label') }}</span>
                                            </label>

                                            <!-- New profile option -->
                                            <label
                                                class="flex items-center p-3 border rounded-lg cursor-pointer transition-all"
                                                :class="form.billing_profile_id === null ? 'border-2 bg-opacity-10' : 'border-gray-200 hover:border-gray-300'"
                                                :style="form.billing_profile_id === null ? { borderColor: primaryColor, backgroundColor: primaryColor + '10' } : {}"
                                            >
                                                <input
                                                    type="radio"
                                                    v-model="form.billing_profile_id"
                                                    :value="null"
                                                    class="sr-only"
                                                />
                                                <div class="flex items-center gap-2">
                                                    <PlusIcon class="w-5 h-5 text-gray-400" />
                                                    <span class="text-gray-600">{{ t('checkout.new_billing') }}</span>
                                                </div>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Invoice form (for guests OR when "new profile" is selected) -->
                                    <div v-if="!customer || form.billing_profile_id === null">
                                        <!-- Type selection -->
                                        <div class="flex gap-4 mb-4">
                                            <label
                                                class="flex-1 flex items-center p-3 border rounded-lg cursor-pointer transition-all"
                                                :class="form.invoice.type === 'company' ? 'border-2' : 'border-gray-200'"
                                                :style="form.invoice.type === 'company' ? { borderColor: primaryColor } : {}"
                                            >
                                                <input type="radio" v-model="form.invoice.type" value="company" class="sr-only" />
                                                <BuildingOfficeIcon class="w-5 h-5 mr-2 text-gray-500" />
                                                <span class="font-medium">{{ t('checkout.company') }}</span>
                                            </label>
                                            <label
                                                class="flex-1 flex items-center p-3 border rounded-lg cursor-pointer transition-all"
                                                :class="form.invoice.type === 'personal' ? 'border-2' : 'border-gray-200'"
                                                :style="form.invoice.type === 'personal' ? { borderColor: primaryColor } : {}"
                                            >
                                                <input type="radio" v-model="form.invoice.type" value="personal" class="sr-only" />
                                                <UserIcon class="w-5 h-5 mr-2 text-gray-500" />
                                                <span class="font-medium">{{ t('checkout.individual') }}</span>
                                            </label>
                                        </div>

                                        <!-- Company fields -->
                                        <div v-if="form.invoice.type === 'company'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div class="sm:col-span-2">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('checkout.company_name') }} {{ t('checkout.required') }}</label>
                                                <input v-model="form.invoice.company_name" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg" />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('checkout.eik') }} {{ t('checkout.required') }}</label>
                                                <input v-model="form.invoice.eik" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg" placeholder="123456789" />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('checkout.mol') }}</label>
                                                <input v-model="form.invoice.mol" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg" />
                                            </div>
                                            <div class="sm:col-span-2">
                                                <label class="flex items-center cursor-pointer">
                                                    <input type="checkbox" v-model="form.invoice.is_vat_registered" class="rounded border-gray-300 mr-2" />
                                                    <span class="text-sm text-gray-700">{{ t('checkout.vat_registered') }}</span>
                                                </label>
                                            </div>
                                            <div v-if="form.invoice.is_vat_registered" class="sm:col-span-2">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('checkout.vat_number') }}</label>
                                                <input v-model="form.invoice.vat_number" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg" placeholder="BG123456789" />
                                            </div>
                                        </div>

                                        <!-- Personal fields -->
                                        <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div class="sm:col-span-2">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('checkout.full_name') }} {{ t('checkout.required') }}</label>
                                                <input v-model="form.invoice.person_name" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg" />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('checkout.egn') }} {{ t('checkout.required') }}</label>
                                                <input v-model="form.invoice.egn" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg" placeholder="1234567890" />
                                            </div>
                                        </div>

                                        <!-- Common address fields -->
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                                            <div class="sm:col-span-2">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('checkout.address') }}</label>
                                                <input v-model="form.invoice.address" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg" />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('checkout.city') }}</label>
                                                <input v-model="form.invoice.city" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg" />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('checkout.postcode') }}</label>
                                                <input v-model="form.invoice.postcode" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <button
                                    @click="nextStep"
                                    class="px-6 py-3 text-white rounded-lg font-medium"
                                    :style="{ backgroundColor: primaryColor }"
                                >
                                    {{ t('checkout.continue') }}
                                </button>
                            </div>
                        </div>

                        <!-- Step 2: Shipping Method + Dynamic Delivery Form -->
                        <div v-show="currentStep === 2" class="space-y-6">
                            <!-- Shipping Method Selection -->
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                                    <h2 class="text-lg font-semibold text-gray-900">{{ t('checkout.choose_shipping') }}</h2>
                                    <p class="text-sm text-gray-500 mt-1">{{ t('checkout.choose_shipping_text') }}</p>
                                </div>

                                <div class="p-4 space-y-3">
                                    <label
                                        v-for="method in shippingMethods"
                                        :key="method.id"
                                        class="group relative flex items-start p-4 border-2 rounded-xl cursor-pointer transition-all hover:shadow-md"
                                        :class="form.shipping_method_id === method.id
                                            ? 'border-current ring-1 ring-current shadow-md'
                                            : 'border-gray-200 hover:border-gray-300'"
                                        :style="form.shipping_method_id === method.id ? { borderColor: primaryColor, '--tw-ring-color': primaryColor } : {}"
                                    >
                                        <input
                                            type="radio"
                                            v-model="form.shipping_method_id"
                                            :value="method.id"
                                            class="sr-only"
                                        />

                                        <!-- Shipping Logo/Icon -->
                                        <div
                                            class="w-14 h-14 rounded-xl flex items-center justify-center mr-4 flex-shrink-0 transition-colors"
                                            :class="form.shipping_method_id === method.id ? 'bg-opacity-10' : 'bg-gray-100 group-hover:bg-gray-200'"
                                            :style="form.shipping_method_id === method.id ? { backgroundColor: (method.primary_color || primaryColor) + '15' } : {}"
                                        >
                                            <component
                                                :is="shippingMethodIcon(method)"
                                                class="w-8 h-8"
                                                :style="{ color: method.primary_color || '#374151' }"
                                            />
                                        </div>

                                        <!-- Method Info -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-start justify-between">
                                                <div>
                                                    <span class="font-semibold text-gray-900 block">{{ method.name }}</span>
                                                    <p v-if="method.description" class="text-sm text-gray-500 mt-0.5">{{ method.description }}</p>

                                                    <!-- Delivery Types Badge -->
                                                    <div v-if="method.delivery_form?.delivery_types" class="flex flex-wrap gap-1.5 mt-2">
                                                        <span
                                                            v-for="type in method.delivery_form.delivery_types"
                                                            :key="type"
                                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600"
                                                        >
                                                            <BuildingOfficeIcon v-if="type === 'office'" class="w-3 h-3 mr-1" />
                                                            <CpuChipIcon v-else-if="type === 'apt'" class="w-3 h-3 mr-1" />
                                                            <HomeIcon v-else-if="type === 'address'" class="w-3 h-3 mr-1" />
                                                            {{ type === 'office' ? t('checkout.delivery_office') : type === 'apt' ? t('checkout.delivery_apt') : t('checkout.delivery_address') }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <!-- Price -->
                                                <div class="text-right ml-4 flex-shrink-0">
                                                    <template v-if="form.shipping_method_id === method.id && method.delivery_form?.component">
                                                        <span v-if="calculatingShipping" class="text-sm text-gray-400">
                                                            <ArrowPathIcon class="animate-spin h-4 w-4 inline" />
                                                        </span>
                                                        <span v-else-if="awaitingDeliveryDetails" class="text-sm text-gray-400 italic">
                                                            {{ t('checkout.calculating') }}
                                                        </span>
                                                        <span v-else class="font-bold text-lg" :style="{ color: primaryColor }">
                                                            {{ calculatedShipping.formatted_cost }}
                                                        </span>
                                                    </template>
                                                    <template v-else>
                                                        <span class="font-bold text-lg" :style="{ color: method.cost === 0 ? '#22C55E' : primaryColor }">
                                                            {{ method.cost > 0 ? formatPrice(method.cost) : t('checkout.free') }}
                                                        </span>
                                                    </template>
                                                    <p v-if="method.estimated_days" class="text-xs text-gray-500 mt-0.5">
                                                        {{ t('checkout.working_days', { days: method.estimated_days }) }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Selected indicator -->
                                        <div
                                            v-if="form.shipping_method_id === method.id"
                                            class="absolute top-3 right-3 w-5 h-5 rounded-full flex items-center justify-center"
                                            :style="{ backgroundColor: primaryColor }"
                                        >
                                            <CheckIcon class="w-3 h-3 text-white" />
                                        </div>
                                    </label>
                                </div>

                                <p v-if="errors.shipping_method_id" class="px-6 pb-4 text-sm text-red-600">{{ errors.shipping_method_id }}</p>
                            </div>

                            <!-- Dynamic Delivery Form -->
                            <div v-if="selectedShippingMethod && deliveryFormConfig" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                <h2 class="text-lg font-semibold text-gray-900 mb-6">
                                    {{ deliveryType === 'address' ? t('checkout.delivery_address_title') : deliveryType === 'office' ? t('checkout.delivery_office_title') : t('checkout.delivery_info_title') }}
                                </h2>

                                <!-- Module-provided component (e.g., Speedy, Econt) -->
                                <component
                                    v-if="deliveryFormConfig.component"
                                    :is="deliveryFormConfig.component"
                                    v-model="form.delivery"
                                    :config="selectedShippingMethod?.checkout?.config || {}"
                                    :delivery-types="deliveryFormConfig.delivery_types || ['office']"
                                    :show-map="deliveryFormConfig.show_map || false"
                                    :country-id="deliveryFormConfig.country_id || null"
                                    :primary-color="primaryColor"
                                    @office-selected="onOfficeSelected"
                                />

                                <!-- Standard delivery form fields (fallback) -->
                                <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <template v-for="field in deliveryFormConfig.fields" :key="field.name">
                                        <!-- Select field -->
                                        <div
                                            v-if="field.type === 'select'"
                                            :class="field.col_span === 2 ? 'sm:col-span-2' : ''"
                                        >
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                {{ field.label }} {{ field.required ? t('checkout.required') : '' }}
                                            </label>

                                            <!-- Country Select -->
                                            <VueSelect
                                                v-if="field.options_source === 'countries'"
                                                v-model="form.delivery[field.name]"
                                                :options="countries"
                                                :reduce="(country) => country.id"
                                                label="name"
                                                :placeholder="t('checkout.select_field', { field: field.label.toLowerCase() })"
                                                :clearable="false"
                                            />

                                            <!-- Zone Select -->
                                            <VueSelect
                                                v-else-if="field.options_source === 'zones'"
                                                v-model="form.delivery[field.name]"
                                                :options="zones"
                                                :reduce="(zone) => zone.id"
                                                label="name"
                                                :placeholder="zones.length === 0 ? t('checkout.no_zones') : t('checkout.select_zone')"
                                                :disabled="zones.length === 0"
                                                :clearable="true"
                                            />

                                            <!-- City Select with Search -->
                                            <VueSelect
                                                v-else-if="field.options_source === 'cities'"
                                                v-model="form.delivery[field.name]"
                                                :options="cities"
                                                :reduce="(city) => city.id"
                                                :get-option-label="(city) => city.postcode ? `${city.name} (${city.postcode})` : city.name"
                                                :placeholder="loadingCities ? t('checkout.loading_cities') : cities.length === 0 ? t('checkout.select_country_first') : t('checkout.select_city')"
                                                :disabled="loadingCities || cities.length === 0"
                                                :loading="loadingCities"
                                                :clearable="true"
                                                @search="(query) => citySearch = query"
                                            />

                                            <p v-if="errors[`delivery.${field.name}`]" class="mt-1 text-sm text-red-600">
                                                {{ errors[`delivery.${field.name}`] }}
                                            </p>
                                        </div>

                                        <!-- Text field -->
                                        <div
                                            v-else-if="field.type === 'text'"
                                            :class="field.col_span === 2 ? 'sm:col-span-2' : ''"
                                        >
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                {{ field.label }} {{ field.required ? t('checkout.required') : '' }}
                                            </label>
                                            <input
                                                v-model="form.delivery[field.name]"
                                                type="text"
                                                class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:border-transparent"
                                                :class="errors[`delivery.${field.name}`] ? 'border-red-500' : 'border-gray-300'"
                                                :placeholder="field.placeholder || ''"
                                            />
                                            <p v-if="errors[`delivery.${field.name}`]" class="mt-1 text-sm text-red-600">
                                                {{ errors[`delivery.${field.name}`] }}
                                            </p>
                                        </div>

                                        <!-- Textarea field -->
                                        <div
                                            v-else-if="field.type === 'textarea'"
                                            :class="field.col_span === 2 ? 'sm:col-span-2' : ''"
                                        >
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                {{ field.label }} {{ field.required ? t('checkout.required') : '' }}
                                            </label>
                                            <textarea
                                                v-model="form.delivery[field.name]"
                                                :rows="field.rows || 3"
                                                class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:border-transparent"
                                                :class="errors[`delivery.${field.name}`] ? 'border-red-500' : 'border-gray-300'"
                                                :placeholder="field.placeholder || ''"
                                            ></textarea>
                                            <p v-if="errors[`delivery.${field.name}`]" class="mt-1 text-sm text-red-600">
                                                {{ errors[`delivery.${field.name}`] }}
                                            </p>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Navigation buttons -->
                            <div class="flex justify-between">
                                <button
                                    @click="prevStep"
                                    class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50"
                                >
                                    {{ t('checkout.back') }}
                                </button>
                                <button
                                    @click="nextStep"
                                    class="px-6 py-3 text-white rounded-lg font-medium"
                                    :style="{ backgroundColor: primaryColor }"
                                >
                                    {{ t('checkout.continue') }}
                                </button>
                            </div>
                        </div>

                        <!-- Step 3: Payment -->
                        <div v-show="currentStep === 3" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-6">{{ t('checkout.payment_method') }}</h2>

                            <div class="space-y-3">
                                <label
                                    v-for="method in paymentMethods"
                                    :key="method.id"
                                    class="flex items-center p-4 border rounded-xl cursor-pointer transition-all"
                                    :class="form.payment_method_id === method.id ? 'border-2' : 'border-gray-200 hover:border-gray-300'"
                                    :style="form.payment_method_id === method.id ? { borderColor: primaryColor, backgroundColor: primaryColor + '10' } : {}"
                                >
                                    <input
                                        type="radio"
                                        v-model="form.payment_method_id"
                                        :value="method.id"
                                        class="sr-only"
                                    />
                                    <!-- Payment Icon -->
                                    <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center mr-4" :style="form.payment_method_id === method.id ? { backgroundColor: primaryColor + '20' } : {}">
                                        <BanknotesIcon v-if="method.icon === 'banknotes'" class="w-5 h-5" :style="{ color: form.payment_method_id === method.id ? primaryColor : '#6B7280' }" />
                                        <BuildingLibraryIcon v-else-if="method.icon === 'building-library'" class="w-5 h-5" :style="{ color: form.payment_method_id === method.id ? primaryColor : '#6B7280' }" />
                                        <CreditCardIcon v-else class="w-5 h-5" :style="{ color: form.payment_method_id === method.id ? primaryColor : '#6B7280' }" />
                                    </div>
                                    <div class="flex-1">
                                        <span class="font-medium text-gray-900">{{ method.name }}</span>
                                        <p v-if="method.description" class="text-sm text-gray-500 mt-1">{{ method.description }}</p>
                                        <p v-if="method.fee > 0" class="text-sm font-medium mt-1" :style="{ color: primaryColor }">+ {{ method.formatted_fee }}</p>
                                    </div>
                                    <!-- Checkmark -->
                                    <div v-if="form.payment_method_id === method.id" class="w-6 h-6 rounded-full flex items-center justify-center" :style="{ backgroundColor: primaryColor }">
                                        <CheckIcon class="w-4 h-4 text-white" />
                                    </div>
                                </label>
                            </div>

                            <p v-if="errors.payment_method_id" class="mt-2 text-sm text-red-600">{{ errors.payment_method_id }}</p>

                            <!-- Comment -->
                            <div class="mt-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('checkout.order_comment') }}</label>
                                <textarea
                                    v-model="form.comment"
                                    rows="3"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent"
                                    :placeholder="t('checkout.comment_placeholder')"
                                ></textarea>
                            </div>

                            <!-- Terms -->
                            <div class="mt-6">
                                <label class="flex items-start cursor-pointer">
                                    <input
                                        type="checkbox"
                                        v-model="form.terms_accepted"
                                        class="mt-1 rounded border-gray-300"
                                        :style="{ color: primaryColor }"
                                    />
                                    <span class="ml-3 text-sm text-gray-600">
                                        {{ t('checkout.agree_terms') }}
                                        <a href="/terms" target="_blank" class="underline">{{ t('checkout.terms') }}</a>
                                        {{ t('checkout.and') }}
                                        <a href="/privacy" target="_blank" class="underline">{{ t('checkout.privacy') }}</a>
                                    </span>
                                </label>
                                <p v-if="errors.terms_accepted" class="mt-1 text-sm text-red-600">{{ errors.terms_accepted }}</p>
                            </div>

                            <div class="mt-6 flex justify-between">
                                <button
                                    @click="prevStep"
                                    class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50"
                                >
                                    {{ t('checkout.back') }}
                                </button>
                                <button
                                    @click="submitOrder"
                                    :disabled="isProcessing"
                                    class="px-8 py-3 text-white rounded-lg font-medium flex items-center disabled:opacity-50"
                                    :style="{ backgroundColor: primaryColor }"
                                >
                                    <ArrowPathIcon v-if="isProcessing" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" />
                                    {{ isProcessing ? t('checkout.processing') : t('checkout.complete_order') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="lg:col-span-4 mt-8 lg:mt-0">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-24">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ t('checkout.your_order') }}</h2>

                            <!-- Items -->
                            <div class="space-y-4 max-h-64 overflow-y-auto">
                                <div v-for="item in cartItems" :key="item.cart_key" class="flex gap-3">
                                    <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                        <img v-if="item.image" :src="item.image" :alt="item.name" class="w-full h-full object-cover" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 line-clamp-2">{{ item.name }}</p>
                                        <p class="text-sm text-gray-500">x{{ item.quantity }}</p>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900">{{ formatPrice(item.total) }}</span>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Totals -->
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>{{ t('checkout.subtotal') }}</span>
                                    <span>{{ formatPrice(cartTotals.subtotal) }}</span>
                                </div>
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>{{ t('checkout.shipping') }}</span>
                                    <span class="flex items-center">
                                        <template v-if="calculatingShipping">
                                            <ArrowPathIcon class="animate-spin h-4 w-4 mr-2 text-gray-400" />
                                        </template>
                                        <template v-else-if="awaitingDeliveryDetails">
                                            <span class="text-gray-400 italic">{{ t('checkout.calculating_shipping') }}</span>
                                        </template>
                                        <template v-else>
                                            {{ calculatedShipping.formatted_cost }}
                                        </template>
                                    </span>
                                </div>
                                <hr class="my-2">
                                <div class="flex justify-between text-lg font-semibold text-gray-900">
                                    <span>{{ t('checkout.total') }}</span>
                                    <span :style="{ color: primaryColor }">{{ formatPrice(orderTotal) }}</span>
                                </div>
                            </div>

                            <!-- Selected Shipping Method -->
                            <div v-if="selectedShippingMethod" class="mt-4 pt-4 border-t border-gray-100">
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">{{ t('checkout.shipping_method') }}</p>
                                <p class="text-sm font-medium text-gray-900">{{ selectedShippingMethod.name }}</p>
                            </div>

                            <!-- Edit Cart Link -->
                            <Link href="/cart" class="block mt-4 text-center text-sm text-gray-600 hover:text-gray-900">
                                {{ t('checkout.edit_cart') }}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </StorefrontLayout>
</template>
