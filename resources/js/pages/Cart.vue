<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

type CartLine = {
    id: number;
    name: string;
    quantity: number;
    unit_price_label: string;
    subtotal_cents: number;
    subtotal_label: string;
};

defineProps<{
    lines: CartLine[];
    total_cents: number;
    total_label: string;
    emptyMessage: string | null;
}>();
</script>

<template>
    <Head title="Carrito" />

    <h1 class="mb-6 text-2xl font-medium">Carrito</h1>

    <p v-if="lines.length === 0" class="text-[#706f6c] dark:text-[#A1A09A]">
        {{ emptyMessage }}
    </p>

    <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                    <th class="py-2 pr-4 font-medium">Producto</th>
                    <th class="px-4 py-2 text-right font-medium">Cantidad</th>
                    <th class="px-4 py-2 text-right font-medium">Precio</th>
                    <th class="py-2 pl-4 text-right font-medium">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="line in lines"
                    :key="line.id"
                    class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]"
                >
                    <td class="py-3 pr-4">{{ line.name }}</td>
                    <td class="px-4 py-3 text-right">{{ line.quantity }}</td>
                    <td class="px-4 py-3 text-right">
                        {{ line.unit_price_label }}
                    </td>
                    <td class="py-3 pl-4 text-right">
                        {{ line.subtotal_label }}
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="py-3 pr-4 text-right font-medium">
                        Total
                    </td>
                    <td class="py-3 pl-4 text-right font-medium">
                        {{ total_label }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</template>
