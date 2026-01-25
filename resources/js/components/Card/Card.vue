<script setup lang="ts">
import InputNumber from 'primevue/inputnumber';
import { computed, ref, watch } from 'vue';
import { debounce } from 'lodash';
import axios from 'axios';

const props = defineProps({
    card: Object,
    stockNeeded: Object,
    characterId: Number,
    currentEvent: Number,
    adminCheck: Boolean,
});

const showStock = computed(() => props.adminCheck && props.currentEvent !== 0);
const updatedStock = ref(props.card.stock);

const debouncedStockUpdate = ref(updatedStock.value);
watch(updatedStock, debounce((val) => {
    props.card.stock = val;
    updateStock()
    }, 300));

async function updateStock() {
    const id = props.card.id;
    const payload = { stock: updatedStock.value };
    try {
        const response = await axios.put(`cards/update/${id}`, payload);
    }
    catch (error) {
        console.error('Failed to put card data', error);
        alert('Une erreur est survenue');
    }
}
</script>

<template>
    <div
        class="card_item overflow-hidden rounded-lg shadow transition-shadow duration-200 hover:shadow-xl"
    >
        <div>
            <img
                :alt="card.title ? card.title : ''"
                :src="card.image_path ? '/storage/' + card.image_path : '/storage/uploads/cards/NoCardImage.png'"
                data-open-dialog="card_focus"
                :data-card-id="card.id"
                class="w-full h-full"
            />
        </div>

        <span v-if="adminCheck">
            <div @click.stop @mousedown.stop v-if="adminCheck && characterId === 0">
                <InputNumber
                    v-model="updatedStock"
                    :min="0"
                    input-class="w-full"
                    size="small"
                    showButtons
                />
            </div>
            <div v-if="showStock">
                <InputNumber
                    v-model="stockNeeded[card.id]"
                    :min="0"
                    :input-class="[
                        'w-full',
                        stockNeeded[card.id] > updatedStock
                            ? '!border !border-red-500 !ring-0'
                            : '!border !border-green-500 !ring-0',
                    ]"
                    size="small"
                    disabled
                />
            </div>
        </span>
    </div>
</template>

<style scoped></style>
