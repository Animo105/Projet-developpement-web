<script lang="ts" setup>
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { useToast } from 'primevue';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import { ref, computed } from 'vue';
import { Icon } from '@iconify/vue';
import DataView from 'primevue/dataview';
import Panel from 'primevue/panel';

// Define the structure for the element data
interface ElementData {
    id: number;
    name: string;
    count: number; // The calculated count from the withSum
    icon: {
        path: string; // The icon path for the Icon component
    };
}

const toast = useToast();
const page = usePage();

// Extract props from Inertia page
const character = page.props.character as any;
const statistics = computed<ElementData[]>(() => page.props.statistics as ElementData[]);
const inventory = computed<ElementData[]>(() => page.props.inventory as ElementData[]);

const showLoreDialog = ref(false);

const loreForm = ref({
    lore: character.lore || '',
});

function openLoreDialog() {
    loreForm.value = {
        lore: character.lore || '',
    } as any;
    showLoreDialog.value = true;
}

async function submitLoreForm() {
    try {
        const response = await axios.put(
            `/character/${character.id}`,
            loreForm.value,
            // ... (rest of the axios call)
        );

        showLoreDialog.value = false;
        character.lore = loreForm.value.lore;

        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: response.data?.message,
            life: 3000,
        });
    } catch (err: unknown) {
        const error = err as any;
        const errorMessage =
            error.response?.data?.message || error.response?.data?.error;

        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: errorMessage,
            life: 4000,
        });
    }
}

</script>

<template>
  <section class="flex w-full max-w-4xl flex-col gap-4 mx-auto p-4">
    <!-- Character Info Card -->
    <Card class="w-full text-left cursor-pointer" @click="openLoreDialog">
      <template #title>
        <div class="flex justify-between items-center">
          <h1 class="text-2xl font-bold">{{ character.name }}</h1>
          <Icon class="text-2xl" icon="tabler:edit"/>
        </div>
      </template>
      <template #content>
        <small class="text-surface-600 dark:text-surface-400">{{ character.lore || 'Cliquez pour ajouter une histoire...' }}</small>
      </template>
    </Card>

    <!-- Statistics Section -->
    <Panel header="Statistiques" toggleable collapsed>
      <DataView :value="statistics" layout="list">
        <template #list="slotProps">
          <div class="flex flex-col">
            <div v-for="item in slotProps.items" :key="item.id"
                 class="flex items-center justify-between p-3 border-b border-surface-200 dark:border-surface-700"
                 :class="{ 'opacity-50': item.count === 0 }">
              <div class="flex items-center gap-3">
                <Icon class="text-2xl" :icon="item.icon?.path || 'tabler:circle-off'" />
                <span class="font-medium" :class="{ 'text-surface-500 dark:text-surface-400': item.count === 0 }">{{ item.name }}</span>
              </div>
              <span class="text-xl font-bold" :class="{ 'text-surface-500 dark:text-surface-400': item.count === 0 }">{{ item.count }}</span>
            </div>
          </div>
        </template>
      </DataView>
    </Panel>

    <!-- Inventory Section -->
    <Panel header="Inventaire" toggleable collapsed>
      <DataView :value="inventory" layout="list">
        <template #list="slotProps">
          <div class="flex flex-col">
            <div v-for="item in slotProps.items" :key="item.id"
                 class="flex items-center justify-between p-3 border-b border-surface-200 dark:border-surface-700"
                 :class="{ 'opacity-50': item.count === 0 }">
              <div class="flex items-center gap-3">
                <Icon class="text-2xl" :icon="item.icon?.path || 'tabler:circle-off'" />
                <span class="font-medium" :class="{ 'text-surface-500 dark:text-surface-400': item.count === 0 }">{{ item.name }}</span>
              </div>
              <span class="text-xl font-bold" :class="{ 'text-surface-500 dark:text-surface-400': item.count === 0 }">{{ item.count }}</span>
            </div>
          </div>
        </template>
        <template #empty>
          <p class="text-center text-surface-500 dark:text-surface-400">Aucun élément d'inventaire trouvé.</p>
        </template>
      </DataView>
    </Panel>

    <!-- Dialog Modifier Compte -->
    <Dialog
        v-model:visible="showLoreDialog"
        :style="{ width: '30rem' }"
        header="Histoire"
        modal
    >
      <div class="mb-4 flex w-full items-center gap-4">
        <div class="w-full">
          <Textarea v-model="loreForm.lore" class="w-full" rows="15" />
        </div>
      </div>
      <div class="flex">
        <Button
            class="p-button-primary w-full"
            label="Confirmer"
            @click="submitLoreForm"
        />
      </div>
    </Dialog>
  </section>
</template>
