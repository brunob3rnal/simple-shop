<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';

const props = defineProps<{
    productName: string;
    amountCents: number;
    paid: boolean;
    message: string | null;
}>();

const processing = ref(false);

const price = computed(() =>
    new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(props.amountCents / 100),
);

// Spike: el navegador no envía ningún precio; el servidor decide el monto.
function pay() {
    router.post(
        '/spike/stripe/checkout',
        {},
        {
            onStart: () => (processing.value = true),
            onFinish: () => (processing.value = false),
        },
    );
}
</script>

<template>
    <Head title="Spike Stripe" />

    <div class="flex min-h-screen items-center justify-center p-6">
        <div class="w-full max-w-sm space-y-4 rounded-xl border p-6">
            <p
                v-if="message"
                class="rounded-md bg-green-100 p-3 text-sm font-medium text-green-900"
                role="status"
            >
                {{ message }}
            </p>

            <div>
                <h1 class="text-lg font-semibold">{{ productName }}</h1>
                <p class="text-muted-foreground">{{ price }}</p>
            </div>

            <Button class="w-full" :disabled="processing" @click="pay">
                Pagar
            </Button>
        </div>
    </div>
</template>
