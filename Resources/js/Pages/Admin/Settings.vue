<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StoreSettingsTabs from '@/Components/Admin/StoreSettingsTabs.vue';
import {
    Squares2X2Icon,
    SwatchIcon,
    HomeIcon,
    RectangleStackIcon,
    ShoppingBagIcon,
    ShoppingCartIcon,
    Bars3Icon,
    ArrowLeftIcon,
    ArrowPathIcon,
    CheckCircleIcon,
    XCircleIcon,
    PaintBrushIcon,
    InformationCircleIcon,
    SparklesIcon,
    EyeIcon,
} from '@heroicons/vue/24/outline';
import { CheckIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
    module: Object,
    stores: Array,
    storeSettings: Object,
    defaultSettings: Object,
    sliders: Array,
});

const storeTabsRef = ref(null);
const saving = ref(false);

const activeSection = ref('layout');

const sections = [
    { id: 'layout', name: 'Layout', icon: Squares2X2Icon, color: 'from-violet-500 to-purple-600' },
    { id: 'colors', name: 'Colors', icon: SwatchIcon, color: 'from-pink-500 to-rose-600' },
    { id: 'homepage', name: 'Homepage', icon: HomeIcon, color: 'from-blue-500 to-indigo-600' },
    { id: 'product_listing', name: 'Product Listing', icon: RectangleStackIcon, color: 'from-emerald-500 to-teal-600' },
    { id: 'product_page', name: 'Product Page', icon: ShoppingBagIcon, color: 'from-amber-500 to-orange-600' },
    { id: 'cart', name: 'Cart', icon: ShoppingCartIcon, color: 'from-cyan-500 to-blue-600' },
    { id: 'header', name: 'Header', icon: Bars3Icon, color: 'from-slate-500 to-gray-600' },
    { id: 'footer', name: 'Footer', icon: Bars3Icon, color: 'from-stone-500 to-neutral-600' },
];

const submit = () => {
    if (!storeTabsRef.value) return;

    saving.value = true;
    router.put(route('admin.themes.default.settings.update'), {
        store_id: storeTabsRef.value.activeStoreId,
        is_enabled: storeTabsRef.value.isEnabled,
        settings: storeTabsRef.value.localSettings,
    }, {
        preserveScroll: true,
        onFinish: () => {
            saving.value = false;
        },
    });
};

const resetSection = (sectionId, settings, updateSetting) => {
    if (confirm(`Reset ${sectionId.replace('_', ' ')} settings to defaults?`)) {
        if (props.defaultSettings[sectionId]) {
            const defaultSectionSettings = JSON.parse(JSON.stringify(props.defaultSettings[sectionId]));
            updateSetting(sectionId, defaultSectionSettings);
        }
    }
};

const resetAll = (updateSetting) => {
    if (confirm('Reset all theme settings to defaults?')) {
        Object.keys(props.defaultSettings).forEach(key => {
            updateSetting(key, JSON.parse(JSON.stringify(props.defaultSettings[key])));
        });
    }
};

const hasChanges = computed(() => {
    if (!storeTabsRef.value) return false;
    const storeId = storeTabsRef.value.activeStoreId;
    const currentSettings = props.storeSettings[storeId];
    const localSettings = storeTabsRef.value.localSettings;
    const isEnabled = storeTabsRef.value.isEnabled;

    if (!currentSettings) {
        return isEnabled || JSON.stringify(localSettings) !== JSON.stringify(props.defaultSettings);
    }

    return isEnabled !== currentSettings.is_enabled ||
        JSON.stringify(localSettings) !== JSON.stringify({ ...props.defaultSettings, ...(currentSettings.settings || {}) });
});

const currentSectionConfig = computed(() => {
    return sections.find(s => s.id === activeSection.value);
});

// Layout options
const headerStyles = [
    { value: 'default', label: 'Default', desc: 'Logo left, navigation right' },
    { value: 'centered', label: 'Centered', desc: 'Centered logo with navigation below' },
    { value: 'minimal', label: 'Minimal', desc: 'Clean, simple header' },
];

const footerStyles = [
    { value: 'default', label: 'Default', desc: 'Standard multi-column layout' },
    { value: 'minimal', label: 'Minimal', desc: 'Simple single-row footer' },
    { value: 'expanded', label: 'Expanded', desc: 'Full-featured with widgets' },
];

const sidebarPositions = [
    { value: 'left', label: 'Left' },
    { value: 'right', label: 'Right' },
    { value: 'none', label: 'Hidden' },
];

const containerWidths = [
    { value: 'default', label: 'Default (1280px)' },
    { value: 'wide', label: 'Wide (1440px)' },
    { value: 'full', label: 'Full Width' },
];

const galleryStyles = [
    { value: 'thumbnails', label: 'Thumbnails', desc: 'Small images below main' },
    { value: 'dots', label: 'Dots', desc: 'Simple dot indicators' },
    { value: 'vertical', label: 'Vertical', desc: 'Thumbnails on the side' },
];

const viewModes = [
    { value: 'grid', label: 'Grid View' },
    { value: 'list', label: 'List View' },
];

const miniCartStyles = [
    { value: 'dropdown', label: 'Dropdown', desc: 'Shows on hover' },
    { value: 'sidebar', label: 'Sidebar', desc: 'Slides from right' },
];

// Predefined color palettes
const colorPresets = [
    { name: 'Default', primary: '#4F46E5', secondary: '#6B7280', accent: '#F59E0B' },
    { name: 'Ocean', primary: '#0891B2', secondary: '#64748B', accent: '#14B8A6' },
    { name: 'Forest', primary: '#059669', secondary: '#4B5563', accent: '#84CC16' },
    { name: 'Sunset', primary: '#DC2626', secondary: '#78716C', accent: '#F97316' },
    { name: 'Berry', primary: '#9333EA', secondary: '#6B7280', accent: '#EC4899' },
];

const applyColorPreset = (preset, updateSetting) => {
    const colors = { ...preset };
    delete colors.name;
    updateSetting('colors', colors);
};
</script>

<template>
    <AdminLayout :title="`${module.name} Settings`">
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link
                        :href="route('admin.modules.installed.index')"
                        class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                    >
                        <ArrowLeftIcon class="w-5 h-5" />
                    </Link>
                    <div class="flex items-center space-x-3">
                        <div class="p-3 bg-gradient-to-br from-violet-500 to-purple-600 rounded-xl shadow-lg shadow-violet-500/25">
                            <PaintBrushIcon class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">{{ module.name }}</h1>
                            <p class="text-sm text-gray-500">Theme Customization</p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <span v-if="hasChanges" class="flex items-center text-sm text-amber-600 font-medium">
                        <span class="w-2 h-2 bg-amber-500 rounded-full mr-2 animate-pulse"></span>
                        Unsaved changes
                    </span>
                    <button
                        type="submit"
                        form="settings-form"
                        :disabled="saving || !hasChanges"
                        class="px-6 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-violet-600 to-purple-600 rounded-xl hover:from-violet-700 hover:to-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg shadow-violet-500/25"
                    >
                        <CheckIcon class="w-4 h-4 inline mr-2" />
                        Save Changes
                    </button>
                </div>
            </div>
        </template>

        <form id="settings-form" @submit.prevent="submit">
            <StoreSettingsTabs
                ref="storeTabsRef"
                :stores="stores"
                :store-settings="storeSettings"
                :default-settings="defaultSettings"
                module-slug="default-theme"
            >
                <template #default="{ store, settings, updateSetting, isEnabled }">
                    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                        <!-- Left Sidebar -->
                        <div class="lg:col-span-1 space-y-6">
                            <!-- Section Navigation -->
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-3">
                                <div class="space-y-1">
                                    <button
                                        v-for="section in sections"
                                        :key="section.id"
                                        type="button"
                                        @click="activeSection = section.id"
                                        class="w-full flex items-center px-3 py-2.5 rounded-xl text-left transition-all duration-200"
                                        :class="activeSection === section.id
                                            ? 'bg-gradient-to-r ' + section.color + ' text-white shadow-lg'
                                            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                                    >
                                        <component
                                            :is="section.icon"
                                            class="w-5 h-5 mr-3"
                                            :class="activeSection === section.id ? 'text-white' : 'text-gray-400'"
                                        />
                                        <span class="text-sm font-medium">{{ section.name }}</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Theme Info Card -->
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                                <div class="p-4 border-b border-gray-100">
                                    <h3 class="font-semibold text-gray-900 text-sm">Theme Info</h3>
                                </div>
                                <div class="p-4 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-500">Status</span>
                                        <span class="bg-green-100 text-green-700 px-2 py-0.5 text-xs font-semibold rounded-full">
                                            Active
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-500">Version</span>
                                        <span class="text-xs font-mono text-gray-900">v{{ module.installed_version }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-500">Author</span>
                                        <span class="text-xs text-gray-900">{{ module.author }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Color Preview -->
                            <div class="bg-gradient-to-br from-violet-500 to-purple-600 rounded-2xl shadow-lg p-4 text-white">
                                <div class="flex items-center space-x-2 mb-3">
                                    <SwatchIcon class="w-5 h-5 opacity-80" />
                                    <span class="text-sm font-medium">Color Palette</span>
                                </div>
                                <div class="flex space-x-2">
                                    <div
                                        v-for="(color, key) in settings.colors"
                                        :key="key"
                                        class="flex-1 h-8 rounded-lg shadow-inner"
                                        :style="{ backgroundColor: color }"
                                        :title="key"
                                    ></div>
                                </div>
                            </div>

                            <!-- Help Card -->
                            <div class="bg-blue-50 rounded-2xl p-4 border border-blue-100">
                                <div class="flex items-start space-x-2">
                                    <InformationCircleIcon class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" />
                                    <div>
                                        <h4 class="text-xs font-medium text-blue-900">Tips</h4>
                                        <p class="text-xs text-blue-700 mt-1">
                                            Changes are previewed live. Don't forget to save!
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Main Content Area -->
                        <div class="lg:col-span-4 space-y-6">
                            <!-- Section Header -->
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <div :class="'bg-gradient-to-br ' + currentSectionConfig.color" class="p-3 rounded-xl shadow-lg">
                                            <component :is="currentSectionConfig.icon" class="w-6 h-6 text-white" />
                                        </div>
                                        <div>
                                            <h2 class="text-lg font-bold text-gray-900">{{ currentSectionConfig.name }} Settings</h2>
                                            <p class="text-sm text-gray-500">Customize how your {{ currentSectionConfig.name.toLowerCase() }} looks and behaves</p>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        @click="resetSection(activeSection, settings, updateSetting)"
                                        class="text-sm text-gray-500 hover:text-gray-700 font-medium"
                                    >
                                        <ArrowPathIcon class="w-4 h-4 inline mr-1" />
                                        Reset Section
                                    </button>
                                </div>
                            </div>

                            <!-- Layout Settings -->
                            <div v-show="activeSection === 'layout'" class="space-y-6">
                                <!-- Layout Style Cards -->
                                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                                        <h3 class="font-semibold text-gray-900">Header Style</h3>
                                    </div>
                                    <div class="p-6">
                                        <div class="grid grid-cols-3 gap-4">
                                            <label
                                                v-for="option in headerStyles"
                                                :key="option.value"
                                                class="relative cursor-pointer"
                                            >
                                                <input
                                                    type="radio"
                                                    :checked="settings.layout.header_style === option.value"
                                                    @change="updateSetting('layout', { ...settings.layout, header_style: option.value })"
                                                    class="sr-only"
                                                />
                                                <div
                                                    class="p-4 rounded-xl border-2 transition-all"
                                                    :class="settings.layout.header_style === option.value
                                                        ? 'border-violet-500 bg-violet-50'
                                                        : 'border-gray-200 hover:border-gray-300'"
                                                >
                                                    <div class="flex items-center justify-between mb-2">
                                                        <span class="font-medium text-gray-900">{{ option.label }}</span>
                                                        <CheckCircleIcon
                                                            v-if="settings.layout.header_style === option.value"
                                                            class="w-5 h-5 text-violet-500"
                                                        />
                                                    </div>
                                                    <p class="text-xs text-gray-500">{{ option.desc }}</p>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                                        <h3 class="font-semibold text-gray-900">Footer Style</h3>
                                    </div>
                                    <div class="p-6">
                                        <div class="grid grid-cols-3 gap-4">
                                            <label
                                                v-for="option in footerStyles"
                                                :key="option.value"
                                                class="relative cursor-pointer"
                                            >
                                                <input
                                                    type="radio"
                                                    :checked="settings.layout.footer_style === option.value"
                                                    @change="updateSetting('layout', { ...settings.layout, footer_style: option.value })"
                                                    class="sr-only"
                                                />
                                                <div
                                                    class="p-4 rounded-xl border-2 transition-all"
                                                    :class="settings.layout.footer_style === option.value
                                                        ? 'border-violet-500 bg-violet-50'
                                                        : 'border-gray-200 hover:border-gray-300'"
                                                >
                                                    <div class="flex items-center justify-between mb-2">
                                                        <span class="font-medium text-gray-900">{{ option.label }}</span>
                                                        <CheckCircleIcon
                                                            v-if="settings.layout.footer_style === option.value"
                                                            class="w-5 h-5 text-violet-500"
                                                        />
                                                    </div>
                                                    <p class="text-xs text-gray-500">{{ option.desc }}</p>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-6">
                                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-3">Sidebar Position</label>
                                        <div class="flex space-x-2">
                                            <button
                                                v-for="option in sidebarPositions"
                                                :key="option.value"
                                                type="button"
                                                @click="updateSetting('layout', { ...settings.layout, sidebar_position: option.value })"
                                                class="flex-1 py-3 px-4 rounded-xl border-2 font-medium text-sm transition-all"
                                                :class="settings.layout.sidebar_position === option.value
                                                    ? 'border-violet-500 bg-violet-50 text-violet-700'
                                                    : 'border-gray-200 text-gray-600 hover:border-gray-300'"
                                            >
                                                {{ option.label }}
                                            </button>
                                        </div>
                                    </div>

                                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-3">Container Width</label>
                                        <select
                                            :value="settings.layout.container_width"
                                            @change="updateSetting('layout', { ...settings.layout, container_width: $event.target.value })"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-colors"
                                        >
                                            <option v-for="option in containerWidths" :key="option.value" :value="option.value">
                                                {{ option.label }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Colors Settings -->
                            <div v-show="activeSection === 'colors'" class="space-y-6">
                                <!-- Color Presets -->
                                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                                        <div class="flex items-center space-x-2">
                                            <SparklesIcon class="w-5 h-5 text-gray-400" />
                                            <h3 class="font-semibold text-gray-900">Color Presets</h3>
                                        </div>
                                    </div>
                                    <div class="p-6">
                                        <div class="grid grid-cols-5 gap-4">
                                            <button
                                                v-for="preset in colorPresets"
                                                :key="preset.name"
                                                type="button"
                                                @click="applyColorPreset(preset, updateSetting)"
                                                class="group p-4 rounded-xl border-2 border-gray-200 hover:border-gray-300 transition-all hover:shadow-md"
                                            >
                                                <div class="flex space-x-1 mb-3">
                                                    <div class="w-6 h-6 rounded-full shadow-sm" :style="{ backgroundColor: preset.primary }"></div>
                                                    <div class="w-6 h-6 rounded-full shadow-sm" :style="{ backgroundColor: preset.secondary }"></div>
                                                    <div class="w-6 h-6 rounded-full shadow-sm" :style="{ backgroundColor: preset.accent }"></div>
                                                </div>
                                                <span class="text-xs font-medium text-gray-700 group-hover:text-gray-900">{{ preset.name }}</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Individual Colors -->
                                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                                        <h3 class="font-semibold text-gray-900">Custom Colors</h3>
                                    </div>
                                    <div class="p-6">
                                        <div class="grid grid-cols-3 gap-6">
                                            <div v-for="(color, key) in settings.colors" :key="key" class="space-y-3">
                                                <label class="block text-sm font-medium text-gray-700 capitalize">
                                                    {{ key.replace('_', ' ') }} Color
                                                </label>
                                                <div class="flex items-center space-x-3">
                                                    <div class="relative">
                                                        <input
                                                            type="color"
                                                            :value="settings.colors[key]"
                                                            @input="updateSetting('colors', { ...settings.colors, [key]: $event.target.value })"
                                                            class="w-14 h-14 rounded-xl cursor-pointer border-2 border-gray-200 hover:border-gray-300 transition-colors"
                                                        />
                                                    </div>
                                                    <div class="flex-1">
                                                        <input
                                                            type="text"
                                                            :value="settings.colors[key]"
                                                            @input="updateSetting('colors', { ...settings.colors, [key]: $event.target.value })"
                                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl font-mono text-sm focus:ring-2 focus:ring-violet-500 focus:border-violet-500 uppercase"
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Color Preview -->
                                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                                        <div class="flex items-center space-x-2">
                                            <EyeIcon class="w-5 h-5 text-gray-400" />
                                            <h3 class="font-semibold text-gray-900">Live Preview</h3>
                                        </div>
                                    </div>
                                    <div class="p-6">
                                        <div class="bg-gray-100 rounded-xl p-6">
                                            <div class="flex items-center space-x-4 mb-4">
                                                <button
                                                    type="button"
                                                    class="px-6 py-2.5 rounded-lg text-white font-medium text-sm shadow-lg"
                                                    :style="{ backgroundColor: settings.colors.primary }"
                                                >
                                                    Primary Button
                                                </button>
                                                <button
                                                    type="button"
                                                    class="px-6 py-2.5 rounded-lg text-white font-medium text-sm shadow-lg"
                                                    :style="{ backgroundColor: settings.colors.secondary }"
                                                >
                                                    Secondary
                                                </button>
                                                <button
                                                    type="button"
                                                    class="px-6 py-2.5 rounded-lg text-white font-medium text-sm shadow-lg"
                                                    :style="{ backgroundColor: settings.colors.accent }"
                                                >
                                                    Accent
                                                </button>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <span class="font-medium" :style="{ color: settings.colors.primary }">Primary Text</span>
                                                <span class="text-gray-400">|</span>
                                                <span class="font-medium" :style="{ color: settings.colors.secondary }">Secondary Text</span>
                                                <span class="text-gray-400">|</span>
                                                <span class="font-medium" :style="{ color: settings.colors.accent }">Accent Text</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Homepage Settings -->
                            <div v-show="activeSection === 'homepage'" class="space-y-6">
                                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                                        <h3 class="font-semibold text-gray-900">Homepage Sections</h3>
                                        <p class="text-sm text-gray-500 mt-1">Toggle which sections appear on your homepage</p>
                                    </div>
                                    <div class="p-6">
                                        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                                            <label
                                                v-for="(label, key) in {
                                                    show_slider: 'Hero Slider',
                                                    show_featured_categories: 'Featured Categories',
                                                    show_featured_products: 'Featured Products',
                                                    show_new_arrivals: 'New Arrivals',
                                                    show_bestsellers: 'Bestsellers',
                                                    show_brands: 'Brand Logos',
                                                    show_newsletter: 'Newsletter Signup'
                                                }"
                                                :key="key"
                                                class="relative flex items-center p-4 rounded-xl cursor-pointer transition-all"
                                                :class="settings.homepage[key]
                                                    ? 'bg-blue-50 border-2 border-blue-200'
                                                    : 'bg-gray-50 border-2 border-transparent hover:bg-gray-100'"
                                                @click="updateSetting('homepage', { ...settings.homepage, [key]: !settings.homepage[key] })"
                                            >
                                                <input
                                                    type="checkbox"
                                                    :checked="settings.homepage[key]"
                                                    class="sr-only"
                                                />
                                                <div
                                                    class="w-10 h-6 rounded-full relative transition-colors mr-3"
                                                    :class="settings.homepage[key] ? 'bg-blue-500' : 'bg-gray-300'"
                                                >
                                                    <div
                                                        class="absolute top-1 w-4 h-4 bg-white rounded-full shadow transition-transform"
                                                        :class="settings.homepage[key] ? 'translate-x-5' : 'translate-x-1'"
                                                    ></div>
                                                </div>
                                                <span class="text-sm font-medium" :class="settings.homepage[key] ? 'text-blue-700' : 'text-gray-700'">
                                                    {{ label }}
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Slider Selection -->
                                <div v-if="sliders && sliders.length > 0" class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                                    <div class="px-6 py-4 bg-gradient-to-r from-pink-50 to-rose-50 border-b border-pink-100">
                                        <div class="flex items-center space-x-2">
                                            <RectangleStackIcon class="w-5 h-5 text-pink-500" />
                                            <h3 class="font-semibold text-gray-900">Slider Selection</h3>
                                        </div>
                                        <p class="text-sm text-gray-500 mt-1">Choose which sliders to display in different sections</p>
                                    </div>
                                    <div class="p-6">
                                        <div class="grid grid-cols-2 gap-6">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Hero Slider</label>
                                                <select
                                                    :value="settings.homepage.hero_slider_id"
                                                    @change="updateSetting('homepage', { ...settings.homepage, hero_slider_id: $event.target.value ? Number($event.target.value) : null })"
                                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-colors"
                                                >
                                                    <option :value="null">No slider selected</option>
                                                    <option v-for="slider in sliders" :key="slider.id" :value="slider.id">
                                                        {{ slider.name }}
                                                        <template v-if="slider.location"> ({{ slider.location }})</template>
                                                    </option>
                                                </select>
                                                <p class="mt-1 text-xs text-gray-500">Main slider at the top of homepage</p>
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Banner Slider</label>
                                                <select
                                                    :value="settings.homepage.banner_slider_id"
                                                    @change="updateSetting('homepage', { ...settings.homepage, banner_slider_id: $event.target.value ? Number($event.target.value) : null })"
                                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-colors"
                                                >
                                                    <option :value="null">No slider selected</option>
                                                    <option v-for="slider in sliders" :key="slider.id" :value="slider.id">
                                                        {{ slider.name }}
                                                        <template v-if="slider.location"> ({{ slider.location }})</template>
                                                    </option>
                                                </select>
                                                <p class="mt-1 text-xs text-gray-500">Secondary promotional banner</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- No sliders message -->
                                <div v-else class="bg-pink-50 rounded-2xl p-6 border border-pink-100">
                                    <div class="flex items-start space-x-3">
                                        <RectangleStackIcon class="w-6 h-6 text-pink-400 flex-shrink-0" />
                                        <div>
                                            <h4 class="font-medium text-pink-900">No Sliders Available</h4>
                                            <p class="text-sm text-pink-700 mt-1">
                                                Install and configure the Simple Slider module to add hero sections and banners.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-6">
                                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-3">Products Per Row</label>
                                        <div class="flex space-x-2">
                                            <button
                                                v-for="num in [3, 4, 5, 6]"
                                                :key="num"
                                                type="button"
                                                @click="updateSetting('homepage', { ...settings.homepage, products_per_row: num })"
                                                class="flex-1 py-3 rounded-xl border-2 font-medium text-sm transition-all"
                                                :class="settings.homepage.products_per_row === num
                                                    ? 'border-blue-500 bg-blue-50 text-blue-700'
                                                    : 'border-gray-200 text-gray-600 hover:border-gray-300'"
                                            >
                                                {{ num }}
                                            </button>
                                        </div>
                                    </div>

                                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-3">Featured Products Limit</label>
                                        <input
                                            type="number"
                                            :value="settings.homepage.featured_products_limit"
                                            @input="updateSetting('homepage', { ...settings.homepage, featured_products_limit: Number($event.target.value) })"
                                            min="4"
                                            max="24"
                                            step="4"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                        />
                                        <p class="mt-2 text-xs text-gray-500">Number of products shown (4-24)</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Product Listing Settings -->
                            <div v-show="activeSection === 'product_listing'" class="space-y-6">
                                <div class="grid grid-cols-2 gap-6">
                                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-3">Default View Mode</label>
                                        <div class="flex space-x-2">
                                            <button
                                                v-for="option in viewModes"
                                                :key="option.value"
                                                type="button"
                                                @click="updateSetting('product_listing', { ...settings.product_listing, default_view: option.value })"
                                                class="flex-1 py-3 px-4 rounded-xl border-2 font-medium text-sm transition-all"
                                                :class="settings.product_listing.default_view === option.value
                                                    ? 'border-emerald-500 bg-emerald-50 text-emerald-700'
                                                    : 'border-gray-200 text-gray-600 hover:border-gray-300'"
                                            >
                                                {{ option.label }}
                                            </button>
                                        </div>
                                    </div>

                                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-3">Products Per Page</label>
                                        <div class="flex space-x-2">
                                            <button
                                                v-for="num in [12, 24, 36, 48]"
                                                :key="num"
                                                type="button"
                                                @click="updateSetting('product_listing', { ...settings.product_listing, products_per_page: num })"
                                                class="flex-1 py-3 rounded-xl border-2 font-medium text-sm transition-all"
                                                :class="settings.product_listing.products_per_page === num
                                                    ? 'border-emerald-500 bg-emerald-50 text-emerald-700'
                                                    : 'border-gray-200 text-gray-600 hover:border-gray-300'"
                                            >
                                                {{ num }}
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                                        <h3 class="font-semibold text-gray-900">Product Card Features</h3>
                                    </div>
                                    <div class="p-6">
                                        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                                            <label
                                                v-for="(label, key) in {
                                                    show_filters: 'Filter Sidebar',
                                                    show_sorting: 'Sort Options',
                                                    show_compare: 'Compare Button',
                                                    show_wishlist: 'Wishlist Button',
                                                    show_quick_view: 'Quick View'
                                                }"
                                                :key="key"
                                                class="relative flex items-center p-4 rounded-xl cursor-pointer transition-all"
                                                :class="settings.product_listing[key]
                                                    ? 'bg-emerald-50 border-2 border-emerald-200'
                                                    : 'bg-gray-50 border-2 border-transparent hover:bg-gray-100'"
                                                @click="updateSetting('product_listing', { ...settings.product_listing, [key]: !settings.product_listing[key] })"
                                            >
                                                <input
                                                    type="checkbox"
                                                    :checked="settings.product_listing[key]"
                                                    class="sr-only"
                                                />
                                                <div
                                                    class="w-10 h-6 rounded-full relative transition-colors mr-3"
                                                    :class="settings.product_listing[key] ? 'bg-emerald-500' : 'bg-gray-300'"
                                                >
                                                    <div
                                                        class="absolute top-1 w-4 h-4 bg-white rounded-full shadow transition-transform"
                                                        :class="settings.product_listing[key] ? 'translate-x-5' : 'translate-x-1'"
                                                    ></div>
                                                </div>
                                                <span class="text-sm font-medium" :class="settings.product_listing[key] ? 'text-emerald-700' : 'text-gray-700'">
                                                    {{ label }}
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Product Page Settings -->
                            <div v-show="activeSection === 'product_page'" class="space-y-6">
                                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                                        <h3 class="font-semibold text-gray-900">Image Gallery Style</h3>
                                    </div>
                                    <div class="p-6">
                                        <div class="grid grid-cols-3 gap-4">
                                            <label
                                                v-for="option in galleryStyles"
                                                :key="option.value"
                                                class="relative cursor-pointer"
                                            >
                                                <input
                                                    type="radio"
                                                    :checked="settings.product_page.gallery_style === option.value"
                                                    @change="updateSetting('product_page', { ...settings.product_page, gallery_style: option.value })"
                                                    class="sr-only"
                                                />
                                                <div
                                                    class="p-4 rounded-xl border-2 transition-all"
                                                    :class="settings.product_page.gallery_style === option.value
                                                        ? 'border-amber-500 bg-amber-50'
                                                        : 'border-gray-200 hover:border-gray-300'"
                                                >
                                                    <div class="flex items-center justify-between mb-2">
                                                        <span class="font-medium text-gray-900">{{ option.label }}</span>
                                                        <CheckCircleIcon
                                                            v-if="settings.product_page.gallery_style === option.value"
                                                            class="w-5 h-5 text-amber-500"
                                                        />
                                                    </div>
                                                    <p class="text-xs text-gray-500">{{ option.desc }}</p>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-3">Related Products Limit</label>
                                    <input
                                        type="number"
                                        :value="settings.product_page.related_products_limit"
                                        @input="updateSetting('product_page', { ...settings.product_page, related_products_limit: Number($event.target.value) })"
                                        min="2"
                                        max="12"
                                        class="w-48 px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors"
                                    />
                                    <p class="mt-2 text-xs text-gray-500">Number of related products to display</p>
                                </div>

                                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                                        <h3 class="font-semibold text-gray-900">Product Page Elements</h3>
                                    </div>
                                    <div class="p-6">
                                        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                                            <label
                                                v-for="(label, key) in {
                                                    show_related_products: 'Related Products',
                                                    show_reviews: 'Customer Reviews',
                                                    show_stock_status: 'Stock Status',
                                                    show_sku: 'Product SKU',
                                                    show_social_share: 'Social Share'
                                                }"
                                                :key="key"
                                                class="relative flex items-center p-4 rounded-xl cursor-pointer transition-all"
                                                :class="settings.product_page[key]
                                                    ? 'bg-amber-50 border-2 border-amber-200'
                                                    : 'bg-gray-50 border-2 border-transparent hover:bg-gray-100'"
                                                @click="updateSetting('product_page', { ...settings.product_page, [key]: !settings.product_page[key] })"
                                            >
                                                <input
                                                    type="checkbox"
                                                    :checked="settings.product_page[key]"
                                                    class="sr-only"
                                                />
                                                <div
                                                    class="w-10 h-6 rounded-full relative transition-colors mr-3"
                                                    :class="settings.product_page[key] ? 'bg-amber-500' : 'bg-gray-300'"
                                                >
                                                    <div
                                                        class="absolute top-1 w-4 h-4 bg-white rounded-full shadow transition-transform"
                                                        :class="settings.product_page[key] ? 'translate-x-5' : 'translate-x-1'"
                                                    ></div>
                                                </div>
                                                <span class="text-sm font-medium" :class="settings.product_page[key] ? 'text-amber-700' : 'text-gray-700'">
                                                    {{ label }}
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Cart Settings -->
                            <div v-show="activeSection === 'cart'" class="space-y-6">
                                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                                        <h3 class="font-semibold text-gray-900">Mini Cart Style</h3>
                                    </div>
                                    <div class="p-6">
                                        <div class="grid grid-cols-2 gap-4">
                                            <label
                                                v-for="option in miniCartStyles"
                                                :key="option.value"
                                                class="relative cursor-pointer"
                                            >
                                                <input
                                                    type="radio"
                                                    :checked="settings.cart.mini_cart_style === option.value"
                                                    @change="updateSetting('cart', { ...settings.cart, mini_cart_style: option.value })"
                                                    class="sr-only"
                                                />
                                                <div
                                                    class="p-4 rounded-xl border-2 transition-all"
                                                    :class="settings.cart.mini_cart_style === option.value
                                                        ? 'border-cyan-500 bg-cyan-50'
                                                        : 'border-gray-200 hover:border-gray-300'"
                                                >
                                                    <div class="flex items-center justify-between mb-2">
                                                        <span class="font-medium text-gray-900">{{ option.label }}</span>
                                                        <CheckCircleIcon
                                                            v-if="settings.cart.mini_cart_style === option.value"
                                                            class="w-5 h-5 text-cyan-500"
                                                        />
                                                    </div>
                                                    <p class="text-xs text-gray-500">{{ option.desc }}</p>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                                        <h3 class="font-semibold text-gray-900">Cart Features</h3>
                                    </div>
                                    <div class="p-6">
                                        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                                            <label
                                                v-for="(label, key) in {
                                                    show_mini_cart: 'Mini Cart',
                                                    show_cart_totals: 'Cart Totals',
                                                    show_shipping_calculator: 'Shipping Calculator'
                                                }"
                                                :key="key"
                                                class="relative flex items-center p-4 rounded-xl cursor-pointer transition-all"
                                                :class="settings.cart[key]
                                                    ? 'bg-cyan-50 border-2 border-cyan-200'
                                                    : 'bg-gray-50 border-2 border-transparent hover:bg-gray-100'"
                                                @click="updateSetting('cart', { ...settings.cart, [key]: !settings.cart[key] })"
                                            >
                                                <input
                                                    type="checkbox"
                                                    :checked="settings.cart[key]"
                                                    class="sr-only"
                                                />
                                                <div
                                                    class="w-10 h-6 rounded-full relative transition-colors mr-3"
                                                    :class="settings.cart[key] ? 'bg-cyan-500' : 'bg-gray-300'"
                                                >
                                                    <div
                                                        class="absolute top-1 w-4 h-4 bg-white rounded-full shadow transition-transform"
                                                        :class="settings.cart[key] ? 'translate-x-5' : 'translate-x-1'"
                                                    ></div>
                                                </div>
                                                <span class="text-sm font-medium" :class="settings.cart[key] ? 'text-cyan-700' : 'text-gray-700'">
                                                    {{ label }}
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Header Settings -->
                            <div v-show="activeSection === 'header'" class="space-y-6">
                                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                                        <h3 class="font-semibold text-gray-900">Header Elements</h3>
                                        <p class="text-sm text-gray-500 mt-1">Choose what to display in your site header</p>
                                    </div>
                                    <div class="p-6">
                                        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                                            <label
                                                v-for="(label, key) in {
                                                    show_top_bar: 'Top Bar',
                                                    show_search: 'Search Box',
                                                    show_account: 'Account Link',
                                                    show_wishlist: 'Wishlist Icon',
                                                    show_cart: 'Cart Icon',
                                                    sticky_header: 'Sticky Header',
                                                    show_categories_menu: 'Categories Menu'
                                                }"
                                                :key="key"
                                                class="relative flex items-center p-4 rounded-xl cursor-pointer transition-all"
                                                :class="settings.header[key]
                                                    ? 'bg-slate-100 border-2 border-slate-300'
                                                    : 'bg-gray-50 border-2 border-transparent hover:bg-gray-100'"
                                                @click="updateSetting('header', { ...settings.header, [key]: !settings.header[key] })"
                                            >
                                                <input
                                                    type="checkbox"
                                                    :checked="settings.header[key]"
                                                    class="sr-only"
                                                />
                                                <div
                                                    class="w-10 h-6 rounded-full relative transition-colors mr-3"
                                                    :class="settings.header[key] ? 'bg-slate-600' : 'bg-gray-300'"
                                                >
                                                    <div
                                                        class="absolute top-1 w-4 h-4 bg-white rounded-full shadow transition-transform"
                                                        :class="settings.header[key] ? 'translate-x-5' : 'translate-x-1'"
                                                    ></div>
                                                </div>
                                                <span class="text-sm font-medium" :class="settings.header[key] ? 'text-slate-700' : 'text-gray-700'">
                                                    {{ label }}
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Settings -->
                            <div v-show="activeSection === 'footer'" class="space-y-6">
                                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-3">Footer Columns</label>
                                    <div class="flex space-x-2">
                                        <button
                                            v-for="num in [2, 3, 4, 5]"
                                            :key="num"
                                            type="button"
                                            @click="updateSetting('footer', { ...settings.footer, columns: num })"
                                            class="flex-1 py-3 rounded-xl border-2 font-medium text-sm transition-all"
                                            :class="settings.footer.columns === num
                                                ? 'border-stone-500 bg-stone-100 text-stone-700'
                                                : 'border-gray-200 text-gray-600 hover:border-gray-300'"
                                        >
                                            {{ num }} Columns
                                        </button>
                                    </div>
                                </div>

                                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                                        <h3 class="font-semibold text-gray-900">Footer Elements</h3>
                                    </div>
                                    <div class="p-6">
                                        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                                            <label
                                                v-for="(label, key) in {
                                                    show_newsletter: 'Newsletter Signup',
                                                    show_social_links: 'Social Links',
                                                    show_payment_icons: 'Payment Icons'
                                                }"
                                                :key="key"
                                                class="relative flex items-center p-4 rounded-xl cursor-pointer transition-all"
                                                :class="settings.footer[key]
                                                    ? 'bg-stone-100 border-2 border-stone-300'
                                                    : 'bg-gray-50 border-2 border-transparent hover:bg-gray-100'"
                                                @click="updateSetting('footer', { ...settings.footer, [key]: !settings.footer[key] })"
                                            >
                                                <input
                                                    type="checkbox"
                                                    :checked="settings.footer[key]"
                                                    class="sr-only"
                                                />
                                                <div
                                                    class="w-10 h-6 rounded-full relative transition-colors mr-3"
                                                    :class="settings.footer[key] ? 'bg-stone-600' : 'bg-gray-300'"
                                                >
                                                    <div
                                                        class="absolute top-1 w-4 h-4 bg-white rounded-full shadow transition-transform"
                                                        :class="settings.footer[key] ? 'translate-x-5' : 'translate-x-1'"
                                                    ></div>
                                                </div>
                                                <span class="text-sm font-medium" :class="settings.footer[key] ? 'text-stone-700' : 'text-gray-700'">
                                                    {{ label }}
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </StoreSettingsTabs>
        </form>
    </AdminLayout>
</template>