<script setup lang="ts">
import { computed, ref } from 'vue';
import { FilterMatchMode } from '@primevue/core/api';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import {
    IconExternalLink,
    IconXboxX,
    IconCircleCheck,
    IconTrash,
    IconPencil,
} from '@tabler/icons-vue';
import { Entry, Spending, EventFull } from '@/interfaces/event';
import Dialog from 'primevue/dialog';
import Message from 'primevue/message';
import InputNumber from 'primevue/inputnumber';
import { Form } from '@primevue/forms';
import { router, useForm } from '@inertiajs/vue3';
import { z } from 'zod';
import { zodResolver } from '@primevue/forms/resolvers/zod';

interface Props {
    entries: Entry[];
    spendings: Spending[];
    event: EventFull;
}
const props = defineProps<Props>();

const total_arme = ref(0);
const total_costume = ref(0);
const expandedRows = ref({});
const total_cost = ref<number>(0);
const spendingRef = computed(() => props.spendings);
let spendingType: string = '';

const calc_total_spent = () => {
    total_cost.value = 0;
    props.spendings.forEach((spending: Spending) => {
        total_cost.value += Number(spending.amount);
    });
};
calc_total_spent();

props.entries.forEach((entry) => {
    if (entry.types.includes('arme')) {
        total_arme.value += 1;
    }
    if (entry.types.includes('costume')) {
        total_costume.value += 1;
    }
});

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const zodSchema = z.object({
    name: z.string({
        message: 'Le champs dois contenir une chaine de charactère',
    }),
});

const registrationValidationSchema = zodResolver(zodSchema);

const form = useForm<{
    id: number;
    name: string | null;
    amount: number | null;
    event_id: number;
}>({
    id: 0,
    name: null,
    amount: 0,
    event_id: props.event.id,
});

const visitCharacter = (id: number) => {
    router.get(
        `/impersonate/${id}`,
        {},
        {
            onSuccess: () => router.reload({ only: ['auth'] }),
            preserveScroll: true,
        },
    );
};
const createFormVisible = ref(false);

const findIndexById = (id: number) => {
    let index = -1;
    for (let i = 0; i < spendingRef.value.length; i++) {
        if (spendingRef.value[i].id === id) {
            index = i;
            break;
        }
    }

    return index;
};

const createSpending = () => {
    spendingType = 'create';
    createFormVisible.value = true;
};

const editSpending = (id: number) => {
    spendingType = 'edit';
    const index = findIndexById(id);
    const spending = spendingRef.value[index];
    form.id = id;
    form.name = spending.name;
    form.amount = spending.amount;
    createFormVisible.value = true;
};

const handleSpending = (formData: any) => {
    if (!formData.valid) return;

    form.transform((data) => ({
        ...data,
        name: data.name?.trim(),
        event_id: data.event_id,
        amount: data.amount,
    }));

    if (spendingType == 'create') {
        form.post('/spending/store', {
            preserveScroll: true,
            onFinish: () => {
                createFormVisible.value = false;
                calc_total_spent();
            },
        });
    } else if (spendingType == 'edit') {
        form.post('/spending/update', {
            preserveScroll: true,
            onFinish: () => {
                createFormVisible.value = false;
                calc_total_spent();
            },
        });
    }
    form.reset();
    form.resetAndClearErrors();
};

const delSpending = (id: number) => {
    useForm({ id: id }).post('/spending/destroy', {
        onFinish: calc_total_spent,
    });
};
</script>

<template>
    <DataTable :value="spendings" class="mt-5" data-key="id">
        <template #header>
            <div class="mb-3 flex items-center justify-between">
                <h1>Dépenses ({{ total_cost }}$)</h1>
                <Button label="Ajouter une dépense" @click="createSpending" />
            </div>
        </template>
        <template #empty>Aucune dépense.</template>
        <Column field="name" header="Dépense" />
        <Column field="amount" header="Montant" />
        <Column header="Détails">
            <template #body="SlotProps">
                <Button variant="text" severity="info">
                    <IconPencil @click="editSpending(SlotProps.data.id)" />
                </Button>
                <Button
                    @click="delSpending(SlotProps.data.id)"
                    variant="text"
                    severity="danger"
                >
                    <IconTrash />
                </Button>
            </template>
        </Column>
    </DataTable>
    <br />
    <DataTable
        v-model:filters="filters"
        :value="entries"
        class="mt-5"
        data-key="id"
        :global-filter-fields="['participant_name', 'character_name']"
        :expandedRows="expandedRows"
    >
        <template #header>
            <div class="mb-3 flex items-center justify-between">
                <div>
                    <h1>Participants inscrits ({{ entries.length }})</h1>
                    <div class="flex gap-5">
                        <small>Armes: {{ total_arme }}</small>
                        <small>Costumes: {{ total_costume }}</small>
                    </div>
                </div>
                <InputText
                    v-model="filters['global'].value"
                    placeholder="Keyword Search"
                />
            </div>
        </template>
        <template #empty>Aucun participant trouvé.</template>
        <Column expander style="width: 5rem" />
        <Column field="participant_name" header="Nom" />
        <Column field="character_name" header="Personnage" />
        <Column field="is_adult" header="Accompagnateur">
            <template #body="SlotProps">
                <IconCircleCheck v-if="SlotProps.data.is_adult" class="ml-10" />
            </template>
        </Column>
        <Column header="Détails">
            <template #body="SlotProps">
                <Button
                    @click="visitCharacter(SlotProps.data.character_id)"
                    variant="text"
                    severity="info"
                >
                    <IconExternalLink />
                </Button>
            </template>
        </Column>
        <template #expansion="slotProps">
            <div class="flex justify-start gap-10">
                <div class="flex gap-3">
                    <p>Arme:</p>
                    <IconCircleCheck
                        v-if="slotProps.data.types.includes('arme')"
                        class="text-green-500"
                    />
                    <IconXboxX v-else class="text-destructive" />
                </div>
                <div class="flex gap-3">
                    <p>Costume:</p>
                    <IconCircleCheck
                        v-if="slotProps.data.types.includes('costume')"
                        class="text-green-500"
                    />
                    <IconXboxX v-else class="text-destructive" />
                </div>
            </div>
        </template>
    </DataTable>

    <Dialog modal header="Informations" v-model:visible="createFormVisible">
        <Form
            v-slot="$form"
            :initialValues="form"
            :validateOnBlur="true"
            :validateOnValueUpdate="false"
            :resolver="registrationValidationSchema"
            class="flex flex-col gap-4"
            @submit="handleSpending"
        >
            <Message
                v-if="form.errors && Object.keys(form.errors).length > 0"
                severity="error"
                :closable="false"
            >
                <ul class="list-inside list-disc space-y-1">
                    <li v-for="(error, field) in form.errors" :key="field">
                        {{ error }}
                    </li>
                </ul>
            </Message>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="flex flex-col gap-2">
                    <label for="spending" class="font-medium"> Dépense </label>
                    <InputText
                        id="spending"
                        v-model="form.name"
                        name="name"
                        type="text"
                        :disabled="form.processing"
                        :invalid="$form.name?.invalid"
                        class="w-full"
                    />
                    <template v-if="$form.name?.invalid">
                        <Message
                            v-for="(error, index) of $form.name.errors"
                            :key="index"
                            severity="error"
                            size="small"
                            variant="simple"
                        >
                            {{ error.message }}
                        </Message>
                    </template>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="register-familyName" class="font-medium">
                        Montant
                    </label>
                    <InputNumber
                        id="register-familyName"
                        v-model="form.amount"
                        name="amount"
                        mode="currency"
                        currency="CAD"
                        locale="fr-CA"
                        :disabled="form.processing"
                        :invalid="$form.amount?.invalid"
                        class="w-full"
                    />
                    <template v-if="$form.amount?.invalid">
                        <Message
                            v-for="(error, index) of $form.amount.errors"
                            :key="index"
                            severity="error"
                            size="small"
                            variant="simple"
                        >
                            {{ error.message }}
                        </Message>
                    </template>
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <Button
                    type="button"
                    severity="secondary"
                    label="Annuler"
                    @click="createFormVisible = false"
                    :loading="form.processing"
                    :disabled="form.processing"
                />
                <Button
                    type="submit"
                    label="Sauvegarder"
                    :loading="form.processing"
                    :disabled="form.processing"
                />
            </div>
        </Form>
    </Dialog>
</template>

<style scoped>
h1 {
    font-weight: 700;
    font-size: 2rem;
    line-height: 2;
    margin: 0;
}
small {
    color: var(--text-secondary);
    font-size: medium;
}
</style>
