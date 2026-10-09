<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import cartProducts from '@/routes/cart/products';

type CatalogProduct = {
    id: number;
    name: string;
    price_cents: number;
    price_label: string;
};

defineProps<{
    products: CatalogProduct[];
    emptyMessage: string | null;
    addToCartLabel: string;
}>();

// Solo se envía el producto (en la URL): el servidor decide precio y cantidad.
function addToCart(product: CatalogProduct) {
    router.post(
        cartProducts.store.url(product.id),
        {},
        { preserveScroll: true },
    );
}
</script>

<template>
    <Head title="Catálogo" />

    <h1 class="mb-6 text-2xl font-medium">Catálogo</h1>

    <ul
        v-if="products.length > 0"
        class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
    >
        <li
            v-for="product in products"
            :key="product.id"
            class="flex flex-col gap-2 rounded-lg border border-[#e3e3e0] bg-white p-6 dark:border-[#3E3E3A] dark:bg-[#161615]"
        >
            <span class="font-medium">{{ product.name }}</span>
            <span class="text-[#706f6c] dark:text-[#A1A09A]">
                {{ product.price_label }}
            </span>
            <Button class="mt-2" @click="addToCart(product)">
                {{ addToCartLabel }}
            </Button>
        </li>
    </ul>

    <p v-else class="text-[#706f6c] dark:text-[#A1A09A]">
        {{ emptyMessage }}
    </p>
</template>
