<script setup lang="ts">
import { ref, watch } from 'vue';
import { Icon } from '@iconify/vue';
import Select from 'primevue/select';

// --- PROPS & EMITS ---
const props = defineProps<{
    title: string;
    classes: { id: number; name: string; icon: string }[];
    manas: { id: number; name: string; icon: string }[];
    selectedClass: { id: number; name: string; icon: string };
    selectedMana: { id: number; name: string; icon: string };
    skill_point: number;
}>();

const emit = defineEmits(['selection-change']);

// --- LOCAL STATE ---
const localSelectedClass = ref(props.selectedClass);
const localSelectedMana = ref(props.selectedMana);

// --- WATCHERS ---
watch([localSelectedClass, localSelectedMana], ([cls, mana]) => {
    emit('selection-change', { cls, mana });
});

watch(() => [props.selectedClass, props.selectedMana], ([newClass, newMana]) => {
    localSelectedClass.value = newClass;
    localSelectedMana.value = newMana;
});
</script>

<template>
    <div class="flex flex-wrap items-center gap-3 sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold">
                {{ title }}
            </h2>

            <div class="flex items-center gap-2 mt-1 text-lg">
                <Icon icon="game-icons:orb-direction" />
                <span>{{ skill_point }} point{{ skill_point > 1 ? 's' : '' }} de compétence</span>
            </div>
        </div>

        <div class="flex flex-wrap gap-2 flex-grow sm:flex-none">
            <Select
                class="flex-grow"
                v-model="localSelectedClass"
                :options="classes"
                optionLabel="name"
            >
                <template #option="slotProps">
                    <div class="flex items-center gap-2">
                        <Icon :icon="slotProps.option.icon" class="text-2xl" />
                        <span>{{ slotProps.option.name }}</span>
                    </div>
                </template>
                <template #value="slotProps">
                    <div class="flex items-center gap-2">
                        <Icon :icon="slotProps.value.icon" class="text-2xl" />
                        <span>{{ slotProps.value.name }}</span>
                    </div>
                </template>
            </Select>

            <Select
                class="flex-grow"
                v-model="localSelectedMana"
                :options="manas"
                optionLabel="name"
            >
                <template #option="slotProps">
                    <div class="flex items-center gap-2">
                        <Icon :icon="slotProps.option.icon" class="text-2xl" />
                        <span>{{ slotProps.option.name }}</span>
                    </div>
                </template>
                <template #value="slotProps">
                    <div class="flex items-center gap-2">
                        <Icon :icon="slotProps.value.icon" class="text-2xl" />
                        <span>{{ slotProps.value.name }}</span>
                    </div>
                </template>
            </Select>
        </div>
    </div>
</template>
