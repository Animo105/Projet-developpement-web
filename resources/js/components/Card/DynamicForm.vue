<script setup lang="ts">
import { ref, watch } from 'vue'
import MultiSelect from 'primevue/multiselect';
import Select from 'primevue/select';
import Button from 'primevue/button';

const props = defineProps({
    adminCheck: { type: Boolean },
    characterId: { type: Number},
    schema: { type: Object, required: true },
    modelValue: { type: Object, required: true },
})

const emit = defineEmits(['update:modelValue', 'submit'])

const formData = ref({ ...props.modelValue })

watch(formData, val => emit('update:modelValue', val), { deep: true })

function getComponent(type) {
    switch (type) {
        case 'select': return Select
        case 'multiselect': return MultiSelect
        default: return null
    }
}

function resetFilter() {
    formData.value = {};
}

function onSubmit() {
    emit('submit', formData.value)
}
</script>

<template>
    <form @submit.prevent="onSubmit">
        <div v-for="(field, key) in schema" :key="key">
            <div class="grid grid-cols-2 py-1 items-center"
                 v-if="(key === 'events' && adminCheck && characterId === 0) ||
                  (key !== 'events')">
                <label :for="key" class="grid grid-cols-2">{{ field.label }}</label>

                <component
                    :is="getComponent(field.type)"
                    v-model="formData[key]"
                    v-bind="{
                        optionLabel: 'label',
                        optionValue: 'value',
                        ...field.props,
                      }"
                    :options="field.props?.options || []"
                />

            </div>
        </div>
        <div class="grid grid-cols-2 gap-4 pt-6 pb-1">
            <Button label="Reset" class="w-full" @click="resetFilter"></Button>
            <Button type="submit" label="Confirmer" class="w-full"/>
        </div>

    </form>
</template>

<style scoped>
</style>
