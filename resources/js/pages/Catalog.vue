<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login } from '@/routes';
/* @chisel-registration */
import { register } from '@/routes';
/* @end-chisel-registration */

type CatalogProduct = {
    id: number;
    name: string;
    price_cents: number;
    price_label: string;
};

defineProps<{
    products: CatalogProduct[];
    emptyMessage: string | null;
}>();
</script>

<template>
    <Head title="Catálogo" />
    <div
        class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:p-8 dark:bg-[#0a0a0a] dark:text-[#EDEDEC]"
    >
        <header class="mb-6 w-full max-w-4xl text-sm">
            <nav class="flex items-center justify-end gap-4">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                >
                    Dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="login()"
                        class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                    >
                        Log in
                    </Link>
                    <!-- @chisel-registration -->
                    <Link
                        :href="register()"
                        class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                    >
                        Register
                    </Link>
                    <!-- @end-chisel-registration -->
                </template>
            </nav>
        </header>

        <main class="w-full max-w-4xl">
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
                </li>
            </ul>

            <p v-else class="text-[#706f6c] dark:text-[#A1A09A]">
                {{ emptyMessage }}
            </p>
        </main>
    </div>
</template>
