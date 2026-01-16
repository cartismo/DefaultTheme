<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import StorefrontLayout from '@theme/Layouts/StorefrontLayout.vue';
import VueSelect from '@/Components/VueSelect.vue';

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

// LocalStorage key for checkout data
const CHECKOUT_STORAGE_KEY = 'cartismo_checkout_data';

// Current step
const currentStep = ref(1);
const steps = [
    { number: 1, title: 'Информация', icon: 'user' },
    { number: 2, title: 'Доставка', icon: 'truck' },
    { number: 3, title: 'Плащане', icon: 'credit-card' },
];

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
        office_id: '', // For office delivery (Speedy, Econt)
        locker_id: '', // For locker delivery
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
        type: 'company', // 'company' or 'personal'
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
            // Check if data is not too old (24 hours max)
            const savedAt = parsed._savedAt || 0;
            const now = Date.now();
            const maxAge = 24 * 60 * 60 * 1000; // 24 hours

            if (now - savedAt < maxAge) {
                // Remove internal fields and return data
                const { _savedAt, _step, ...formData } = parsed;
                return {
                    formData,
                    step: _step || 1
                };
            } else {
                // Data is too old, clear it
                localStorage.removeItem(CHECKOUT_STORAGE_KEY);
            }
        }
    } catch (e) {
        console.error('Error loading saved checkout data:', e);
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
        console.error('Error saving checkout data:', e);
    }
};

// Clear saved checkout data and dispatch event for modules
const clearSavedCheckoutData = () => {
    try {
        localStorage.removeItem(CHECKOUT_STORAGE_KEY);
        // Dispatch event for shipping modules to clear their storage
        window.dispatchEvent(new CustomEvent('checkout-complete'));
    } catch (e) {
        console.error('Error clearing checkout data:', e);
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
    formatted_cost: 'Безплатна',
});
const calculatingShipping = ref(false);

// Load zones when country changes
watch(() => form.value.delivery.country_id, async (countryId) => {
    if (countryId) {
        loadingZones.value = true;
        try {
            const response = await axios.get(`/checkout/zones/${countryId}`);
            zones.value = response.data.zones;
            // Reset city and zone selections
            form.value.delivery.zone_id = '';
            form.value.delivery.city_id = '';
            cities.value = [];
        } catch (error) {
            console.error('Error loading zones:', error);
        } finally {
            loadingZones.value = false;
        }
        // Load cities for country
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
        console.error('Error loading cities:', error);
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
    // Recalculate shipping when city changes
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
        console.error('Error calculating shipping:', error);
    } finally {
        calculatingShipping.value = false;
    }
};

// Computed
const primaryColor = computed(() => props.settings?.colors?.primary || '#4F46E5');
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
    // Has dynamic component - check if delivery details are provided
    // For office/apt delivery: need office_id
    // For address delivery: need address fields
    const delivery = form.value.delivery;
    if (delivery.office_id) return false; // Office selected
    if (delivery.address_1 && delivery.postcode) return false; // Address provided
    return true;
});

// Format price
const formatPrice = (price) => {
    return new Intl.NumberFormat('bg-BG', {
        style: 'currency',
        currency: 'BGN',
    }).format(price);
};

// Fill from saved address
const fillFromAddress = (address) => {
    form.value.first_name = address.first_name;
    form.value.last_name = address.last_name;
    form.value.delivery.country_id = address.country_id;
    form.value.delivery.zone_id = address.zone_id || '';
    form.value.delivery.city_id = ''; // Will need to match by name
    form.value.delivery.postcode = address.postcode;
    form.value.delivery.address_1 = address.address_1;
    form.value.delivery.address_2 = address.address_2 || '';
};

// Map delivery data to shipping fields before submit
const mapDeliveryToShipping = () => {
    const delivery = form.value.delivery;
    const city = cities.value.find(c => c.id === delivery.city_id);

    // For office/apt delivery (Speedy, Econt, etc.)
    if (delivery.office_id) {
        form.value.shipping_city = delivery.city_name || delivery.office_city || city?.name || '';
        form.value.shipping_address_1 = delivery.office_name || '';
        form.value.shipping_address_2 = delivery.office_address || '';
        form.value.shipping_postcode = delivery.postcode || '';
        form.value.shipping_country_id = delivery.country_id || '';
        form.value.shipping_zone_id = delivery.zone_id || '';
    } else {
        // Standard address delivery
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
    console.log('Office selected:', { city, office });
    // Recalculate shipping when office is selected
    calculateShipping();
};

// Validate step
const validateStep = (step) => {
    errors.value = {};

    if (step === 1) {
        if (!form.value.email) errors.value.email = 'Въведете имейл адрес';
        else if (!/\S+@\S+\.\S+/.test(form.value.email)) errors.value.email = 'Невалиден имейл адрес';
        if (!form.value.phone) errors.value.phone = 'Въведете телефон';
        if (!form.value.first_name) errors.value.first_name = 'Въведете име';
        if (!form.value.last_name) errors.value.last_name = 'Въведете фамилия';
    }

    if (step === 2) {
        if (!form.value.shipping_method_id) errors.value.shipping_method_id = 'Изберете метод на доставка';

        // Validate based on delivery type
        if (deliveryFormConfig.value?.component) {
            // Module-provided component (e.g., Speedy, Econt)
            const deliveryData = form.value.delivery;
            const componentDeliveryType = deliveryData.delivery_type || deliveryType.value;

            if (componentDeliveryType === 'address') {
                // Address delivery - need city, postcode, address
                if (!deliveryData.city_name && !deliveryData.city) {
                    errors.value['delivery.city'] = 'Моля, въведете град';
                }
                if (!deliveryData.postcode) {
                    errors.value['delivery.postcode'] = 'Моля, въведете пощенски код';
                }
                if (!deliveryData.address_1) {
                    errors.value['delivery.address_1'] = 'Моля, въведете адрес';
                }
            } else {
                // Office/APT delivery - need office_id
                if (!deliveryData.office_id) {
                    errors.value['delivery.office_id'] = 'Моля, изберете офис за доставка';
                }
            }
        } else if (deliveryFormConfig.value?.fields) {
            // Standard delivery form fields
            for (const field of deliveryFormConfig.value.fields) {
                if (field.required && !form.value.delivery[field.name]) {
                    errors.value[`delivery.${field.name}`] = `${field.label} е задължително`;
                }
            }
        }
    }

    if (step === 3) {
        if (!form.value.payment_method_id) errors.value.payment_method_id = 'Изберете метод на плащане';
        if (!form.value.terms_accepted) errors.value.terms_accepted = 'Трябва да приемете условията';
    }

    return Object.keys(errors.value).length === 0;
};

// Next step
const nextStep = () => {
    if (validateStep(currentStep.value)) {
        if (currentStep.value === 2) {
            // Map delivery data before moving to payment
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

    // Make sure delivery is mapped
    mapDeliveryToShipping();

    isProcessing.value = true;
    errors.value = {};

    try {
        const response = await axios.post('/checkout/process', form.value);

        if (response.data.success) {
            // Clear saved checkout data on successful order
            clearSavedCheckoutData();
            window.location.href = response.data.redirect;
        } else {
            errors.value.general = response.data.message;
        }
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
        } else {
            errors.value.general = error.response?.data?.message || 'Възникна грешка';
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
    <StorefrontLayout title="Поръчка">
        <div class="bg-gray-50 min-h-screen py-8">
            <div class="max-w-7xl mx-auto px-4">
                <!-- Breadcrumb -->
                <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-6">
                    <Link href="/" class="hover:text-gray-700">Начало</Link>
                    <span>/</span>
                    <Link href="/cart" class="hover:text-gray-700">Кошница</Link>
                    <span>/</span>
                    <span class="text-gray-900 font-medium">Поръчка</span>
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
                            <h2 class="text-lg font-semibold text-gray-900 mb-6">Информация за контакт</h2>

                            <!-- Login Section (for guests) -->
                            <div v-if="!customer" class="mb-6 p-4 bg-gray-50 rounded-xl border border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Имате акаунт?</p>
                                            <p class="text-xs text-gray-500">Влезте за по-бързо поръчване</p>
                                        </div>
                                    </div>
                                    <Link
                                        :href="`/login?redirect=${encodeURIComponent('/checkout')}`"
                                        class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-100 transition-colors"
                                    >
                                        Вход
                                    </Link>
                                </div>
                            </div>

                            <!-- Logged in user info -->
                            <div v-else class="mb-6 p-4 bg-green-50 rounded-xl border border-green-200">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center" :style="{ backgroundColor: primaryColor + '20' }">
                                        <svg class="w-5 h-5" :style="{ color: primaryColor }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ customer.name }}</p>
                                        <p class="text-xs text-gray-500">{{ customer.email }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Saved Addresses (for quick name fill) -->
                            <div v-if="addresses?.length > 0" class="mb-6">
                                <h3 class="text-sm font-medium text-gray-700 mb-3">Бързо попълване от запазени адреси</h3>
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
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Имейл *</label>
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
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Телефон *</label>
                                    <input
                                        v-model="form.phone"
                                        type="tel"
                                        class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:border-transparent"
                                        :class="errors.phone ? 'border-red-500' : 'border-gray-300'"
                                        placeholder="+359 888 123 456"
                                    />
                                    <p v-if="errors.phone" class="mt-1 text-sm text-red-600">{{ errors.phone }}</p>
                                </div>

                                <!-- First Name -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Име *</label>
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
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Фамилия *</label>
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
                                    <span class="text-sm font-medium text-gray-900">Желая фактура</span>
                                </label>

                                <!-- Invoice form - expandable -->
                                <div v-if="form.needs_invoice" class="space-y-4">
                                    <!-- Saved Billing Profiles (for registered customers) -->
                                    <div v-if="customer && billingProfiles?.length > 0" class="mb-4">
                                        <h3 class="text-sm font-medium text-gray-700 mb-3">Запазени данни за фактуриране</h3>
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
                                                        {{ profile.type === 'company' ? `ЕИК: ${profile.eik}` : `ЕГН: ${profile.egn}` }}
                                                        <span v-if="profile.address"> • {{ profile.city }}, {{ profile.address }}</span>
                                                    </span>
                                                </div>
                                                <span v-if="profile.is_default" class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-600">По подразбиране</span>
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
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                    </svg>
                                                    <span class="text-gray-600">Нови данни за фактуриране</span>
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
                                                <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                </svg>
                                                <span class="font-medium">Фирма</span>
                                            </label>
                                            <label
                                                class="flex-1 flex items-center p-3 border rounded-lg cursor-pointer transition-all"
                                                :class="form.invoice.type === 'personal' ? 'border-2' : 'border-gray-200'"
                                                :style="form.invoice.type === 'personal' ? { borderColor: primaryColor } : {}"
                                            >
                                                <input type="radio" v-model="form.invoice.type" value="personal" class="sr-only" />
                                                <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                <span class="font-medium">Физическо лице</span>
                                            </label>
                                        </div>

                                        <!-- Company fields -->
                                        <div v-if="form.invoice.type === 'company'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div class="sm:col-span-2">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Име на фирма *</label>
                                                <input v-model="form.invoice.company_name" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg" />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">ЕИК *</label>
                                                <input v-model="form.invoice.eik" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg" placeholder="123456789" />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">МОЛ</label>
                                                <input v-model="form.invoice.mol" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg" />
                                            </div>
                                            <div class="sm:col-span-2">
                                                <label class="flex items-center cursor-pointer">
                                                    <input type="checkbox" v-model="form.invoice.is_vat_registered" class="rounded border-gray-300 mr-2" />
                                                    <span class="text-sm text-gray-700">Регистриран по ДДС</span>
                                                </label>
                                            </div>
                                            <div v-if="form.invoice.is_vat_registered" class="sm:col-span-2">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">ДДС номер</label>
                                                <input v-model="form.invoice.vat_number" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg" placeholder="BG123456789" />
                                            </div>
                                        </div>

                                        <!-- Personal fields -->
                                        <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div class="sm:col-span-2">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Три имена *</label>
                                                <input v-model="form.invoice.person_name" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg" />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">ЕГН *</label>
                                                <input v-model="form.invoice.egn" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg" placeholder="1234567890" />
                                            </div>
                                        </div>

                                        <!-- Common address fields -->
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                                            <div class="sm:col-span-2">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Адрес</label>
                                                <input v-model="form.invoice.address" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg" />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Град</label>
                                                <input v-model="form.invoice.city" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg" />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Пощенски код</label>
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
                                    Продължи
                                </button>
                            </div>
                        </div>

                        <!-- Step 2: Shipping Method + Dynamic Delivery Form -->
                        <div v-show="currentStep === 2" class="space-y-6">
                            <!-- Shipping Method Selection -->
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                                    <h2 class="text-lg font-semibold text-gray-900">Изберете начин на доставка</h2>
                                    <p class="text-sm text-gray-500 mt-1">Изберете как искате да получите вашата поръчка</p>
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
                                            <!-- Module-provided SVG logo -->
                                            <span
                                                v-if="method.logo_svg"
                                                class="w-8 h-8"
                                                :style="{ color: method.primary_color || '#374151' }"
                                                v-html="method.logo_svg"
                                            ></span>
                                            <!-- Default truck icon -->
                                            <svg v-else class="w-7 h-7 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 17a2 2 0 100-4 2 2 0 000 4zm10 0a2 2 0 100-4 2 2 0 000 4zm-2-4V8a1 1 0 00-1-1H5a1 1 0 00-1 1v5m12 0H4m12 0h1a2 2 0 012 2v1m0-3l-3-3h-3m4 0V8a2 2 0 012 2v3z" />
                                            </svg>
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
                                                            <svg v-if="type === 'office'" class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                            </svg>
                                                            <svg v-else-if="type === 'apt'" class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                                                            </svg>
                                                            <svg v-else-if="type === 'address'" class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                                            </svg>
                                                            {{ type === 'office' ? 'Офис' : type === 'apt' ? 'Автомат' : 'До адрес' }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <!-- Price -->
                                                <div class="text-right ml-4 flex-shrink-0">
                                                    <template v-if="form.shipping_method_id === method.id && method.delivery_form?.component">
                                                        <!-- Show calculated price for selected method with component -->
                                                        <span v-if="calculatingShipping" class="text-sm text-gray-400">
                                                            <svg class="animate-spin h-4 w-4 inline" fill="none" viewBox="0 0 24 24">
                                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                            </svg>
                                                        </span>
                                                        <span v-else-if="awaitingDeliveryDetails" class="text-sm text-gray-400 italic">
                                                            Изчислява се
                                                        </span>
                                                        <span v-else class="font-bold text-lg" :style="{ color: primaryColor }">
                                                            {{ calculatedShipping.formatted_cost }}
                                                        </span>
                                                    </template>
                                                    <template v-else>
                                                        <span class="font-bold text-lg" :style="{ color: method.cost === 0 ? '#22C55E' : primaryColor }">
                                                            {{ method.cost > 0 ? formatPrice(method.cost) : 'Безплатна' }}
                                                        </span>
                                                    </template>
                                                    <p v-if="method.estimated_days" class="text-xs text-gray-500 mt-0.5">
                                                        {{ method.estimated_days }} работни дни
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
                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                    </label>
                                </div>

                                <p v-if="errors.shipping_method_id" class="px-6 pb-4 text-sm text-red-600">{{ errors.shipping_method_id }}</p>
                            </div>

                            <!-- Dynamic Delivery Form -->
                            <div v-if="selectedShippingMethod && deliveryFormConfig" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                <h2 class="text-lg font-semibold text-gray-900 mb-6">
                                    {{ deliveryType === 'address' ? 'Адрес за доставка' : deliveryType === 'office' ? 'Офис за доставка' : 'Информация за доставка' }}
                                </h2>

                                <!-- Module-provided component (e.g., Speedy, Econt) -->
                                <component
                                    v-if="deliveryFormConfig.component"
                                    :is="deliveryFormConfig.component"
                                    v-model="form.delivery"
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
                                                {{ field.label }} {{ field.required ? '*' : '' }}
                                            </label>

                                            <!-- Country Select -->
                                            <VueSelect
                                                v-if="field.options_source === 'countries'"
                                                v-model="form.delivery[field.name]"
                                                :options="countries"
                                                :reduce="(country) => country.id"
                                                label="name"
                                                :placeholder="'Изберете ' + field.label.toLowerCase()"
                                                :clearable="false"
                                            />

                                            <!-- Zone Select -->
                                            <VueSelect
                                                v-else-if="field.options_source === 'zones'"
                                                v-model="form.delivery[field.name]"
                                                :options="zones"
                                                :reduce="(zone) => zone.id"
                                                label="name"
                                                :placeholder="zones.length === 0 ? 'Няма области' : 'Изберете област'"
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
                                                :placeholder="loadingCities ? 'Зареждане...' : cities.length === 0 ? 'Изберете държава първо' : 'Изберете град'"
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
                                                {{ field.label }} {{ field.required ? '*' : '' }}
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
                                                {{ field.label }} {{ field.required ? '*' : '' }}
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
                                    Назад
                                </button>
                                <button
                                    @click="nextStep"
                                    class="px-6 py-3 text-white rounded-lg font-medium"
                                    :style="{ backgroundColor: primaryColor }"
                                >
                                    Продължи
                                </button>
                            </div>
                        </div>

                        <!-- Step 3: Payment -->
                        <div v-show="currentStep === 3" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-6">Метод на плащане</h2>

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
                                        <!-- Cash icon -->
                                        <svg v-if="method.icon === 'banknotes'" class="w-5 h-5" :style="{ color: form.payment_method_id === method.id ? primaryColor : '#6B7280' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <!-- Bank icon -->
                                        <svg v-else-if="method.icon === 'building-library'" class="w-5 h-5" :style="{ color: form.payment_method_id === method.id ? primaryColor : '#6B7280' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                                        </svg>
                                        <!-- Default card icon -->
                                        <svg v-else class="w-5 h-5" :style="{ color: form.payment_method_id === method.id ? primaryColor : '#6B7280' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <span class="font-medium text-gray-900">{{ method.name }}</span>
                                        <p v-if="method.description" class="text-sm text-gray-500 mt-1">{{ method.description }}</p>
                                        <p v-if="method.fee > 0" class="text-sm font-medium mt-1" :style="{ color: primaryColor }">+ {{ method.formatted_fee }}</p>
                                    </div>
                                    <!-- Checkmark -->
                                    <div v-if="form.payment_method_id === method.id" class="w-6 h-6 rounded-full flex items-center justify-center" :style="{ backgroundColor: primaryColor }">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </label>
                            </div>

                            <p v-if="errors.payment_method_id" class="mt-2 text-sm text-red-600">{{ errors.payment_method_id }}</p>

                            <!-- Comment -->
                            <div class="mt-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Коментар към поръчката</label>
                                <textarea
                                    v-model="form.comment"
                                    rows="3"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent"
                                    placeholder="Допълнителни инструкции за доставката..."
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
                                        Съгласен съм с
                                        <a href="/terms" target="_blank" class="underline">Общите условия</a>
                                        и
                                        <a href="/privacy" target="_blank" class="underline">Политиката за поверителност</a>
                                    </span>
                                </label>
                                <p v-if="errors.terms_accepted" class="mt-1 text-sm text-red-600">{{ errors.terms_accepted }}</p>
                            </div>

                            <div class="mt-6 flex justify-between">
                                <button
                                    @click="prevStep"
                                    class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50"
                                >
                                    Назад
                                </button>
                                <button
                                    @click="submitOrder"
                                    :disabled="isProcessing"
                                    class="px-8 py-3 text-white rounded-lg font-medium flex items-center disabled:opacity-50"
                                    :style="{ backgroundColor: primaryColor }"
                                >
                                    <svg v-if="isProcessing" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ isProcessing ? 'Обработка...' : 'Завърши поръчката' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="lg:col-span-4 mt-8 lg:mt-0">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-24">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Вашата поръчка</h2>

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
                                    <span>Междинна сума</span>
                                    <span>{{ formatPrice(cartTotals.subtotal) }}</span>
                                </div>
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>Доставка</span>
                                    <span class="flex items-center">
                                        <template v-if="calculatingShipping">
                                            <svg class="animate-spin h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </template>
                                        <template v-else-if="awaitingDeliveryDetails">
                                            <span class="text-gray-400 italic">Изчислява се...</span>
                                        </template>
                                        <template v-else>
                                            {{ calculatedShipping.formatted_cost }}
                                        </template>
                                    </span>
                                </div>
                                <hr class="my-2">
                                <div class="flex justify-between text-lg font-semibold text-gray-900">
                                    <span>Общо</span>
                                    <span :style="{ color: primaryColor }">{{ formatPrice(orderTotal) }}</span>
                                </div>
                            </div>

                            <!-- Selected Shipping Method -->
                            <div v-if="selectedShippingMethod" class="mt-4 pt-4 border-t border-gray-100">
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Метод на доставка</p>
                                <p class="text-sm font-medium text-gray-900">{{ selectedShippingMethod.name }}</p>
                            </div>

                            <!-- Edit Cart Link -->
                            <Link href="/cart" class="block mt-4 text-center text-sm text-gray-600 hover:text-gray-900">
                                Редактирай кошницата
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </StorefrontLayout>
</template>
