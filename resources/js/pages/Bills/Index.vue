<script lang="ts" setup>
import { router, usePage } from '@inertiajs/vue3';
import { FilterMatchMode } from '@primevue/core/api';
import { IconField, InputIcon } from 'primevue';
import { IconExternalLink } from '@tabler/icons-vue';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import InputText from 'primevue/inputtext';
import MultiSelect from 'primevue/multiselect';
import { computed, ref } from 'vue';

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS }
});

const page = usePage();
const bills = page.props.bills as any[];
const events = page.props.events as any[];
const selectedEvents = ref<number[]>([]);
const selectedStatuses = ref<number[]>([]);
const statuses = page.props.statuses as any[];


const filteredBills = computed(() => {
    return bills
        // Filtre ÉVÉNEMENTS
        .filter(bill => {
            if (selectedEvents.value.length === 0) return true;
            const evId = bill.inscriptions?.[0]?.tarif?.event?.id ?? null;
            return selectedEvents.value.includes(evId);
        })
        // Filtre STATUTS
        .filter(bill => {
            if (selectedStatuses.value.length === 0) return true;
            return selectedStatuses.value.includes(bill.status_id);
        });
});

const goToBillShow = (billId: number) => {
    router.get(`/bills/${billId}`);
};

</script>

<template>


    <DataTable
        v-model:filters="filters"
        :global-filter-fields="['name', 'character.name']"
        :rows="10"
        :value="filteredBills"
        dataKey="id"
        filter-display="menu"
        paginator
    >
        <template #header>
            <div></div>
            <div class="flex">
                <div class="flex justify-start">
                    <!-- FILTRE PAR ÉVÉNEMENT -->
                    <MultiSelect
                        v-model="selectedEvents"
                        :options="events"
                        :show-toggle-all="false"
                        class="w-60 mr-2"
                        optionLabel="name"
                        optionValue="id"
                        placeholder="Tous les événements"
                    />

                    <!-- FILTRE PAR STATUT -->
                    <MultiSelect
                        v-model="selectedStatuses"
                        :options="statuses"
                        :show-toggle-all="false"
                        class="w-60"
                        optionLabel="name"
                        optionValue="id"
                        placeholder="Tous les statuts"
                    />
                </div>

                <IconField class="ml-auto" style="width: 20rem">
                    <InputIcon>
                        <i class="pi pi-search" />
                    </InputIcon>
                    <InputText
                        v-model="filters['global'].value"
                        placeholder="Rechercher"
                    />
                </IconField>
            </div>
        </template>
        <template #empty> Aucune factures trouvé.</template>
        <template #loading> Chargement des factures...</template>
        <Column
            :showFilterMenu="false"
            field="event.name"
            header="Événement"
            sortable
        >
            <template #body="{ data }">
                {{ data.inscriptions?.[0]?.tarif?.event?.name ?? 'N/A' }}
            </template>
        </Column>
        <Column v-if="page.props.auth.isAdmin"
                :showFilterMenu="false"
                field="user_id"
                header="Compte"
                sortable
        >
            <template #body="{ data }">
                {{ data.user.given_name }} {{ data.user.family_name }}
            </template>
        </Column>

        <Column header="Participants">
            <template #body="{ data }">
                <ul>
                    <ul>
                        <li v-for="par in data.participants" :key="par.id">
                            {{ par.given_name }} {{ par.family_name }}
                        </li>
                    </ul>
                </ul>
            </template>
        </Column>

        <Column :showFilterMenu="false" field="status" header="Statut">
            <template #body="{ data }">
                {{ data.status.name }}
            </template>
        </Column>

        <Column :showFilterMenu="false" field="Amount" header="Montant payé">
            <template #body="{ data }">
                {{ data.payments?.reduce((s, p) => s + Number(p.amount), 0).toFixed(2) }}
                /
                {{ data.amount?.toFixed(2) }}
            </template>
        </Column>

        <Column header="Détail">
            <template #body="{ data }">
                <div class="flex gap-2">
                    <!-- Bouton Modifier (icone bleue) -->
                    <Button text @click="goToBillShow(data.id)">
                        <template #icon>
                            <IconExternalLink :size="26" />
                        </template>
                    </Button>
                </div>
            </template>
        </Column>
    </DataTable>

</template>

<style scoped>
.p-datatable {
    background-color: white;
}
</style>
