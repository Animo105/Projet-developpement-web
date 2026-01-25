<script setup lang="ts">
import DynamicForm from './DynamicForm.vue'
import { ref, computed } from 'vue'

const props = defineProps({
    filterList: { type: Object, required: true },
    characterId: Number,
    adminCheck: { type: Boolean },
})

const emit = defineEmits(['submit']);

const schema = computed(() => ({
    events: {
        label: "Évènement",
        type: 'select',
        props: {
            options: (props.filterList.events || []).map(m => ({
                label: m.name, value: m.id,
            })),
            placeholder:"Choisir un évènement",
            class:"w-full",
        },
        admin: true,
    },
    metier: {
        label: 'Métier',
        type: 'multiselect',
        props: {
            options: (props.filterList.metier || []).map(m => ({
                label: m.name, value: m.id,
            })),
            showToggleAll:false,
            display:"chip",
            placeholder:"Choisir le métier",
            class:"w-full md:w-80",
            maxSelectedLabels:2,
        },
    },
    mana: {
        label: 'Mana',
        type: 'multiselect',
        props: {
            options: (props.filterList.mana || []).map(m => ({
                label:m.name, value: m.id,
            })),
            showToggleAll:false,
            display:"chip",
            placeholder:"Choisir le type de mana principal",
            class:"w-full md:w-80",
            maxSelectedLabels:2,
        },
    },
    type: {
        label: 'Type',
        type: 'multiselect',
        props: {
            options: (props.filterList.type || []).map(m => ({
                label:m.name, value:m.id,
            })),
            showToggleAll:false,
            placeholder:"Choisir un type",
            class:"w-full md:w-80",
            maxSelectedLabels:2,
        },
    },
    level: {
        label: 'Niveau',
        type: 'multiselect',
        props: {
            options: (props.filterList.level || []).map(m => ({
                label:`${m}`, value:m,
            })),
            showToggleAll:false,
            placeholder:"Choisir le niveau",
            class:"w-full md:w-80",
            maxSelectedLabels:2,
        },
    },
    perk: {
        label: 'Bonus',
        type: 'multiselect',
        props: {
            options: (props.filterList.perks || []).map(m => ({
                label:m.name, value:m.id,
            })),
            display:"chip",
            showToggleAll:false,
            placeholder:"Choisir un bonus",
            class:"w-full md:w-80",
            maxSelectedLabels:2,
        }
    }
}))

const formData = ref({
    events: null,
    metier: null,
    mana: null,
    type: null,
    level: null,
    perk: null,
})

function handleSubmit() {
    emit('submit', formData.value);
}
</script>

<template>
    <DynamicForm
        :schema="schema"
        :adminCheck = "adminCheck"
        :characterId="characterId"
        v-model="formData"
        @submit="handleSubmit" />
</template>
