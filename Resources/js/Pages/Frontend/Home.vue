<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import StorefrontLayout from '@theme/Layouts/StorefrontLayout.vue';
import ProductCard from '../../Components/ProductCard.vue';

const props = defineProps({
    settings: Object,
    featuredProducts: Array,
    newProducts: Array,
    bestsellers: Array,
    categories: Array,
    sliders: Array,
    brands: Array,
});

// Theme settings
const homepageSettings = computed(() => props.settings?.homepage || {});
const showSlider = computed(() => homepageSettings.value.show_slider !== false);
const showFeaturedCategories = computed(() => homepageSettings.value.show_featured_categories !== false);
const showFeaturedProducts = computed(() => homepageSettings.value.show_featured_products !== false);
const showNewArrivals = computed(() => homepageSettings.value.show_new_arrivals !== false);
const showBestsellers = computed(() => homepageSettings.value.show_bestsellers !== false);
const showBrands = computed(() => homepageSettings.value.show_brands !== false);
const showNewsletter = computed(() => homepageSettings.value.show_newsletter !== false);
const productsPerRow = computed(() => homepageSettings.value.products_per_row || 4);

// Colors
const primaryColor = computed(() => props.settings?.colors?.primary || '#4F46E5');

// Featured categories (show_in_home)
const featuredCategories = computed(() => {
    return (props.categories || []).filter(c => c.show_in_home && !c.parent_id).slice(0, 6);
});

// Current slide
const currentSlide = ref(0);
const slidesCount = computed(() => props.sliders?.length || 1);

const nextSlide = () => {
    currentSlide.value = (currentSlide.value + 1) % slidesCount.value;
};

const prevSlide = () => {
    currentSlide.value = (currentSlide.value - 1 + slidesCount.value) % slidesCount.value;
};

// Auto slide
let slideInterval = null;
const startSlideshow = () => {
    slideInterval = setInterval(nextSlide, 5000);
};
const stopSlideshow = () => {
    if (slideInterval) clearInterval(slideInterval);
};

// Format price
const formatPrice = (price) => {
    return new Intl.NumberFormat('bg-BG', {
        style: 'currency',
        currency: 'BGN',
    }).format(price);
};

// Quick view
const showQuickView = ref(false);
const quickViewProduct = ref(null);

const openQuickView = (product) => {
    quickViewProduct.value = product;
    showQuickView.value = true;
};
</script>

<template>
    <StorefrontLayout title="Начало">
        <!-- Hero Slider -->
        <section
            v-if="showSlider"
            class="relative"
            @mouseenter="stopSlideshow"
            @mouseleave="startSlideshow"
        >
            <!-- Slides -->
            <div class="relative h-[400px] md:h-[500px] lg:h-[600px] overflow-hidden">
                <!-- If we have sliders from DB -->
                <template v-if="sliders?.length > 0">
                    <div
                        v-for="(slide, index) in sliders"
                        :key="slide.id"
                        class="absolute inset-0 transition-opacity duration-500"
                        :class="index === currentSlide ? 'opacity-100 z-10' : 'opacity-0 z-0'"
                    >
                        <img
                            v-if="slide.image"
                            :src="slide.image"
                            :alt="slide.title"
                            class="w-full h-full object-cover"
                        />
                        <div class="absolute inset-0 bg-gradient-to-r from-black/60 to-transparent">
                            <div class="max-w-7xl mx-auto px-4 h-full flex items-center">
                                <div class="max-w-xl text-white">
                                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-4">
                                        {{ slide.title }}
                                    </h1>
                                    <p class="text-lg md:text-xl opacity-90 mb-8">
                                        {{ slide.description }}
                                    </p>
                                    <Link
                                        v-if="slide.button_url"
                                        :href="slide.button_url"
                                        class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white rounded-xl transition-all hover:scale-105"
                                        :style="{ backgroundColor: primaryColor }"
                                    >
                                        {{ slide.button_text || 'Виж повече' }}
                                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                        </svg>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Default Hero if no sliders -->
                <template v-else>
                    <div
                        class="absolute inset-0 flex items-center justify-center text-white"
                        :style="{ background: `linear-gradient(135deg, ${primaryColor} 0%, #1e1b4b 100%)` }"
                    >
                        <div class="text-center px-4">
                            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-4">
                                Добре дошли в нашия магазин
                            </h1>
                            <p class="text-lg md:text-xl opacity-90 mb-8 max-w-2xl mx-auto">
                                Открийте невероятни продукти на страхотни цени
                            </p>
                            <Link
                                href="/products"
                                class="inline-flex items-center px-8 py-4 bg-white text-lg font-semibold rounded-xl transition-all hover:scale-105"
                                :style="{ color: primaryColor }"
                            >
                                Разгледай продуктите
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </Link>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Navigation Arrows -->
            <template v-if="slidesCount > 1">
                <button
                    @click="prevSlide"
                    class="absolute left-4 top-1/2 -translate-y-1/2 z-20 w-12 h-12 bg-white/20 hover:bg-white/40 backdrop-blur-sm rounded-full flex items-center justify-center text-white transition-all"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <button
                    @click="nextSlide"
                    class="absolute right-4 top-1/2 -translate-y-1/2 z-20 w-12 h-12 bg-white/20 hover:bg-white/40 backdrop-blur-sm rounded-full flex items-center justify-center text-white transition-all"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <!-- Dots -->
                <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-20 flex space-x-2">
                    <button
                        v-for="(_, index) in sliders"
                        :key="index"
                        @click="currentSlide = index"
                        class="w-3 h-3 rounded-full transition-all"
                        :class="index === currentSlide ? 'bg-white w-8' : 'bg-white/50 hover:bg-white/75'"
                    />
                </div>
            </template>
        </section>

        <!-- Featured Categories -->
        <section v-if="showFeaturedCategories && featuredCategories.length > 0" class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4">
                <div class="text-center mb-10">
                    <h2 class="text-3xl font-bold text-gray-900">Разгледай по категории</h2>
                    <p class="text-gray-600 mt-2">Намери продуктите, които търсиш</p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <Link
                        v-for="category in featuredCategories"
                        :key="category.id"
                        :href="`/category/${category.slug}`"
                        class="group relative aspect-square rounded-2xl overflow-hidden bg-gray-100"
                    >
                        <img
                            v-if="category.image_url"
                            :src="category.image_url"
                            :alt="category.name"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-200 to-gray-300">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent">
                            <div class="absolute bottom-0 left-0 right-0 p-4">
                                <h3 class="text-white font-semibold text-lg">{{ category.name }}</h3>
                                <p v-if="category.products_count" class="text-white/80 text-sm">
                                    {{ category.products_count }} продукта
                                </p>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Featured Products -->
        <section v-if="showFeaturedProducts" class="py-16 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex items-center justify-between mb-10">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-900">Препоръчани продукти</h2>
                        <p class="text-gray-600 mt-2">Най-добрите ни предложения за вас</p>
                    </div>
                    <Link
                        href="/products?featured=1"
                        class="hidden sm:inline-flex items-center px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors"
                    >
                        Виж всички
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>

                <div v-if="featuredProducts?.length > 0" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6">
                    <ProductCard
                        v-for="product in featuredProducts"
                        :key="product.id"
                        :product="product"
                        @quick-view="openQuickView"
                    />
                </div>

                <div v-else class="text-center py-12 bg-white rounded-xl border border-gray-200">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <p class="text-gray-500">Няма препоръчани продукти</p>
                </div>
            </div>
        </section>

        <!-- New Arrivals -->
        <section v-if="showNewArrivals && newProducts?.length > 0" class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex items-center justify-between mb-10">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-900">Нови продукти</h2>
                        <p class="text-gray-600 mt-2">Последни попълнения в каталога</p>
                    </div>
                    <Link
                        href="/products?sort=newest"
                        class="hidden sm:inline-flex items-center px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors"
                    >
                        Виж всички
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6">
                    <ProductCard
                        v-for="product in newProducts"
                        :key="product.id"
                        :product="product"
                        @quick-view="openQuickView"
                    />
                </div>
            </div>
        </section>

        <!-- Promo Banner -->
        <section class="py-16">
            <div class="max-w-7xl mx-auto px-4">
                <div
                    class="relative rounded-3xl overflow-hidden"
                    :style="{ background: `linear-gradient(135deg, ${primaryColor} 0%, #1e1b4b 100%)` }"
                >
                    <div class="absolute inset-0 opacity-10">
                        <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                            <pattern id="pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                                <circle cx="10" cy="10" r="2" fill="white" />
                            </pattern>
                            <rect x="0" y="0" width="100" height="100" fill="url(#pattern)" />
                        </svg>
                    </div>
                    <div class="relative grid md:grid-cols-2 gap-8 p-8 md:p-12 lg:p-16">
                        <div class="text-white">
                            <span class="inline-block px-4 py-1 bg-white/20 rounded-full text-sm font-medium mb-4">
                                Специална оферта
                            </span>
                            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold mb-4">
                                До 50% отстъпка
                            </h2>
                            <p class="text-lg opacity-90 mb-8">
                                на избрани продукти от новата колекция. Не пропускай!
                            </p>
                            <Link
                                href="/products?sale=1"
                                class="inline-flex items-center px-8 py-4 bg-white font-semibold rounded-xl transition-all hover:scale-105"
                                :style="{ color: primaryColor }"
                            >
                                Пазарувай сега
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </Link>
                        </div>
                        <div class="hidden md:flex items-center justify-center">
                            <div class="text-[120px] font-black text-white/20">
                                -50%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Bestsellers -->
        <section v-if="showBestsellers && bestsellers?.length > 0" class="py-16 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex items-center justify-between mb-10">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-900">Най-продавани</h2>
                        <p class="text-gray-600 mt-2">Любимите на нашите клиенти</p>
                    </div>
                    <Link
                        href="/products?sort=bestsellers"
                        class="hidden sm:inline-flex items-center px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors"
                    >
                        Виж всички
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6">
                    <ProductCard
                        v-for="product in bestsellers"
                        :key="product.id"
                        :product="product"
                        @quick-view="openQuickView"
                    />
                </div>
            </div>
        </section>

        <!-- Brands -->
        <section v-if="showBrands && brands?.length > 0" class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4">
                <div class="text-center mb-10">
                    <h2 class="text-3xl font-bold text-gray-900">Нашите марки</h2>
                    <p class="text-gray-600 mt-2">Работим с най-добрите производители</p>
                </div>

                <div class="flex flex-wrap items-center justify-center gap-8 md:gap-12">
                    <Link
                        v-for="brand in brands"
                        :key="brand.id"
                        :href="`/products?manufacturer=${brand.id}`"
                        class="grayscale hover:grayscale-0 opacity-60 hover:opacity-100 transition-all"
                    >
                        <img
                            v-if="brand.image_url"
                            :src="brand.image_url"
                            :alt="brand.name"
                            class="h-12 w-auto object-contain"
                        />
                        <span v-else class="text-xl font-bold text-gray-400">{{ brand.name }}</span>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section class="py-16 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div class="text-center p-6">
                        <div
                            class="w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center"
                            :style="{ backgroundColor: primaryColor + '15' }"
                        >
                            <svg class="w-8 h-8" :style="{ color: primaryColor }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-1">Безплатна доставка</h3>
                        <p class="text-sm text-gray-500">за поръчки над 100 лв.</p>
                    </div>

                    <div class="text-center p-6">
                        <div
                            class="w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center"
                            :style="{ backgroundColor: primaryColor + '15' }"
                        >
                            <svg class="w-8 h-8" :style="{ color: primaryColor }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-1">Сигурно плащане</h3>
                        <p class="text-sm text-gray-500">100% защитени транзакции</p>
                    </div>

                    <div class="text-center p-6">
                        <div
                            class="w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center"
                            :style="{ backgroundColor: primaryColor + '15' }"
                        >
                            <svg class="w-8 h-8" :style="{ color: primaryColor }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-1">Лесно връщане</h3>
                        <p class="text-sm text-gray-500">до 14 дни гаранция</p>
                    </div>

                    <div class="text-center p-6">
                        <div
                            class="w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center"
                            :style="{ backgroundColor: primaryColor + '15' }"
                        >
                            <svg class="w-8 h-8" :style="{ color: primaryColor }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-1">24/7 Поддръжка</h3>
                        <p class="text-sm text-gray-500">винаги на линия</p>
                    </div>
                </div>
            </div>
        </section>
    </StorefrontLayout>
</template>
