<script setup lang="ts">
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import MultiSelect from 'primevue/multiselect';
import InputNumber from 'primevue/inputnumber';
import Select from 'primevue/select';
import { ref } from 'vue';

const cardCreate = defineProps({
    filterList: Object,
    form: Object,
    errors: Object,
    costs: { type: Array, default: () => [] },
    costValues: { type: Object, default: () => ({}) },
    perks: { type: Array, default: () => [] },
    perkValues: { type: Object, default: () => ({}) },
})

const formCostValues = ref<{ [id: number]: number }>({}); // needed to dynamically add costs
const formCosts = ref<any[]>([]); // needed to dynamically add costs
const formPerkValues =  ref<{ [id: number]: number }>({}); // needed to dynamically add perks
const formPerks = ref<any[]>([]); // needed to dynamically add Perks
const emit = defineEmits(['updateValues']);

formCostValues.value = cardCreate.costValues;
formCosts.value = cardCreate.costs;
formPerkValues.value = cardCreate.perkValues;
formPerks.value = cardCreate.perks;

function emitValues() {
    emit('updateValues', {costs: formCostValues.value, perks: formPerkValues.value});
}
</script>

<template>

    <!-- Nom -->
    <div class="grid grid-cols-2 py-1 items-center">
        <label for="title">Nom*</label>
        <InputText
            name="title"
            type="text"
            placeholder="Nom de la carte"
            fluid
        />
        <div v-if="errors?.title" />
        <Message
            v-if="errors?.title"
            severity="error"
            size="small"
            variant="simple"
        >{{ errors.title[0] }}</Message
        >
    </div>
    <!-- Métier -->
    <div class="grid grid-cols-2 py-1 items-center">
        <label for="metier">Métier</label>
        <MultiSelect
            name="metier"
            display="chip"
            :options="cardCreate.filterList.metier"
            optionLabel="name"
            :show-toggle-all="false"
            placeholder="Choisir le métier"
            class="w-full md:w-80"
        />
    </div>

    <!-- Mana -->
    <div class="grid grid-cols-2 py-1 items-center">
        <label for="mana">Mana</label>
        <MultiSelect
            name="mana"
            display="chip"
            :options="cardCreate.filterList.mana"
            option-label="name"
            :show-toggle-all="false"
            placeholder="Choisir le type de mana principal"
            class="w-full md:w-80"
        />
    </div>

    <!-- Coûts -->
    <div class="grid grid-cols-2 py-1 items-center">
        <label for="cost">Coûts</label>
        <MultiSelect
            v-model="formCosts"
            display="chip"
            :options="cardCreate.filterList.mana"
            option-label="name"
            :show-toggle-all="false"
            placeholder="Choisir les coûts d'utilisation"
            class="w-full md:w-80"
        >
            <template #value="slotProps">
                <div class="flex flex-wrap gap-2 min-h-5">
                    <template v-if="slotProps.value?.length">
                        <div
                            v-for="cost in formCosts"
                            :key="cost.id"
                            class="bg-surface-200 flex items-center gap-2 rounded px-2 py-1"
                        >
                            <div class="w-full grid grid-cols-2 items-center">
                                <span class="text-sm">{{ cost.name }}</span>
                                <div @click.stop @mousedown.stop>
                                    <InputNumber
                                        v-model="formCostValues[cost.id]"
                                        :default-value="formCostValues[cost.id] || 1"
                                        :min="1"
                                        :max="9"
                                        input-class="w-full"
                                        size="small"
                                        showButtons
                                        @input="emitValues"
                                    />
                                </div>
                            </div>
                        </div>
                    </template>
                    <span v-else>{{ slotProps.placeholder}}</span>
                </div>
            </template>
        </MultiSelect>
    </div>

    <!-- Type -->
    <div class="grid grid-cols-2 py-1 items-center">
        <label for="type">Type*</label>
        <Select
            name="type"
            :options="cardCreate.filterList.type"
            optionLabel="name"
            placeholder="Choisir un type"
            class="w-full md:w-80"
        />
        <div v-if="errors?.type_id" />
        <Message
            v-if="errors?.type_id"
            severity="error"
            size="small"
            variant="simple"
        >{{ errors.type_id[0] }}</Message
        >
    </div>

    <!-- Niveau -->
    <div class="grid grid-cols-2 py-1 items-center">
        <label for="level">Niveau*</label>
        <Select
            name="level"
            :options="cardCreate.filterList.level"
            placeholder="Choisir le niveau"
            class="w-full md:w-80"
        />
        <div v-if="errors?.level" />
        <Message
            v-if="errors?.level"
            severity="error"
            size="small"
            variant="simple"
        >{{ errors.level[0] }}</Message
        >
    </div>

    <!-- Perks/Bonus -->
    <div class="grid grid-cols-2 py-1 items-center">
        <label for="perks">Bonus</label>
        <MultiSelect
            v-model="formPerks"
            display="chip"
            :options="cardCreate.filterList.perks"
            option-label="name"
            :show-toggle-all="false"
            placeholder="Choisir les bonus"
            class="w-full md:w-80"
        >
            <template #value="slotProps">
                <div class="flex flex-wrap gap-2 min-h-5">
                    <template v-if="slotProps.value?.length">
                        <div
                            v-for="perk in formPerks"
                            :key="perk.id"
                            class="bg-surface-200 flex items-center gap-2 rounded px-2 py-1"
                        >
                            <div class="w-full grid grid-cols-2 items-center">
                                <span class="text-sm">{{ perk.name }}</span>
                                <div @click.stop @mousedown.stop>
                                    <InputNumber
                                        v-model="formPerkValues[perk.id]"
                                        :default-value="formPerkValues[perk.id] || 1"
                                        :min="1"
                                        :max="9"
                                        input-class="w-full"
                                        size="small"
                                        showButtons
                                        @input="emitValues"
                                    />
                                </div>
                            </div>
                        </div>
                    </template>
                    <span v-else>{{slotProps.placeholder}}</span>
                </div>
            </template>
        </MultiSelect>
    </div>
</template>

<style scoped></style>
