<script lang="ts" setup>
import AddParticipantDialog from '@/components/Participant/AddParticipantDialog.vue';
import { Icon } from '@iconify/vue';
import { router, usePage } from '@inertiajs/vue3';
import { FilterMatchMode } from '@primevue/core/api';
import { IconEdit, IconExternalLink, IconTrash } from '@tabler/icons-vue';
import axios from 'axios';
import { useToast } from 'primevue';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import { InputIcon, IconField } from 'primevue';
import Message from 'primevue/message';
import MultiSelect from 'primevue/multiselect';
import Password from 'primevue/password';
import Tag from 'primevue/tag';
import Textarea from 'primevue/textarea';
import { computed, reactive, ref } from 'vue';

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS }
});
const errors = reactive({});
const toast = useToast();
const page = usePage();
const participants = page.props.participants as any[];
const events = page.props.events as any[];
const inscriptions = page.props.inscriptions as any[];
const tarifs = page.props.tarifs as any[];

// --- États des dialogues ---
const showAccountDialog = ref(false);
const showDeleteDialog = ref(false);
const showPasswordDialog = ref(false);
const showAddParticipantDialog = ref(false);

const accountForm = ref<any>({});
const selectedParticipant = ref<any>(null);
const selectedEvents = ref<any>(null);

const passwordForm = ref({
    account_password: '',
    new_password: '',
    confirm_password: ''
});

const filteredParticipants = computed(() => {
    const allParticipants = page.props.participants ?? [];

    if (!selectedEvents.value || selectedEvents.value.length === 0)
        return allParticipants;

    return allParticipants.filter((participant) => {
        //Récupérer les inscriptions
        const inscriptionsForParticipant = inscriptions.filter(
            (i) => i.participant_id === participant.id
        );

        const participantEventIds = inscriptionsForParticipant
            .map((i) => {
                const tarif = tarifs.find((t) => t.id === i.tarif_id);
                return tarif ? tarif.event_id : null;
            })
            .filter(Boolean);

        return participantEventIds.some((id) =>
            selectedEvents.value.includes(id)
        );
    });
});

const impersonate = (characterId: number) => {
    router.get(
        `/impersonate/${characterId}`,
        {},
        {
            onSuccess: () => router.reload({ only: ['auth'] }),
            preserveScroll: true
        }
    );
};

//Formatte pour mettre dans l'input'
function toDateInputValue(dateString: string) {
    if (!dateString) return '';
    return dateString.split('T')[0];
}

//Formatte pour mettre dans le tableau
function formatDate(dateString: string): string {
    if (!dateString) return '';
    const date = new Date(dateString);
    date.setDate(date.getDate() + 1);
    return date.toLocaleDateString('fr-CA', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
}

// --- Ouverture du dialogue de modification ---
async function openAccountDialog(selected: any) {
    Object.keys(errors).forEach((key) => delete errors[key]);
    try {
        const response = await axios.get(`/participants/${selected.id}`);
        selectedParticipant.value = response.data;

        accountForm.value = {
            given_name: selectedParticipant.value.given_name || '',
            family_name: selectedParticipant.value.family_name || '',
            birthdate:
                toDateInputValue(selectedParticipant.value.birthdate) || '',
            condition: selectedParticipant.value.condition || '',
            skill_point: selectedParticipant.value.character?.skill_point || 0,
            knowledge_point:
                selectedParticipant.value.character?.knowledge_point || 0
        };
        showAccountDialog.value = true;
    } catch (error) {
        console.error('Erreur lors du chargement du participant:', error);
    }
}

// --- Suppression du participant ---
function openDeleteDialog(participant: any) {
    selectedParticipant.value = participant;
    showDeleteDialog.value = true;
}

function openPasswordDialog(participant: any) {
    selectedParticipant.value = participant;
    showPasswordDialog.value = true;
}

async function submitDeleteForm() {
    try {
        const response = await axios.delete(
            `/participants/${selectedParticipant.value.id}`
        );
        showDeleteDialog.value = false;

        // Retirer le participant de la liste locale
        const index = participants.findIndex(
            (p) => p.id === selectedParticipant.value.id
        );
        if (index !== -1) participants.splice(index, 1);

        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: response.data?.message,
            life: 3000
        });
    } catch (err: unknown) {
        const error = err as any;
        const errorMessage =
            error.response?.data?.message || error.response?.data?.error;

        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: errorMessage,
            life: 4000
        });
    }
}

function submitPasswordForm() {
    router.put(
        `/participants/${selectedParticipant.value.id}/updatePassword`,
        passwordForm.value,
        { preserveScroll: true }
    );
    showPasswordDialog.value = false;
}

// --- Soumission du formulaire de modification ---
async function submitAccountForm() {
    try {
        const participantResponse = await axios.put(
            `/participants/${selectedParticipant.value.id}`,
            accountForm.value
        );

        await axios.patch(
            `/character/${selectedParticipant.value.character.id}/points`,
            {
                skill_point: accountForm.value.skill_point,
                knowledge_point: accountForm.value.knowledge_point
            }
        );

        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: participantResponse.data?.message,
            life: 3000
        });

        showAccountDialog.value = false;

        // Update du tableau
        const updated = participantResponse.data.participant;
        const index = participants.findIndex(
            (p) => p.id === selectedParticipant.value.id
        );
        if (index !== -1) {
            participants[index] = {
                ...participants[index],
                ...updated,
                birthdate: formatDate(updated.birthdate)
            };
        }

       handleParticipantAdded();
    } catch (err: unknown) {
        const error = err as any;
        const errorMessage =
            error.response?.data?.message || error.response?.data?.error;

        Object.assign(errors, error.response?.data?.errors || {});
    }
}

// --- Handler pour l'ajout de participant ---
function handleParticipantAdded() {
    router.reload({
        only: ['participants'],
        preserveScroll: true,
    });
}
</script>

<template>
    <div>
        <Button
            class="my-3"
            label="Ajouter un participant"
            @click="showAddParticipantDialog = true"
        >
            <template #icon>
                <Icon icon="tabler:user-plus" />
            </template>
        </Button>
    </div>

    <DataTable
        v-model:filters="filters"
        :global-filter-fields="['name', 'character.name']"
        :rows="10"
        :value="filteredParticipants"
        dataKey="id"
        filter-display="menu"
        paginator
    >
        <template #header>
            <div class="flex justify-end">
                <MultiSelect
                    v-model="selectedEvents"
                    :options="events"
                    :show-toggle-all="false"
                    class="mr-40 w-full text-left"
                    optionLabel="name"
                    optionValue="id"
                    placeholder="Tous les événements"
                />
                <IconField>
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
        <template #empty> Aucun participant trouvé.</template>
        <template #loading> Chargement des participants...</template>
        <Column
            :showFilterMenu="false"
            field="name"
            header="Participant"
            sortable
        >
            <template #body="{ data }">
                {{ data.name }}
            </template>
        </Column>
        <Column
            :showFilterMenu="false"
            field="character.name"
            header="Personnage"
            sortable
        >
            <template #body="{ data }">
                <div class="flex items-center gap-2">
                    <template v-if="data.character">
                        <span>{{ data.character.name }}</span>
                        <Button
                            rounded
                            severity="secondary"
                            variant="text"
                            @click="impersonate(data.character.id)"
                        >
                            <template #icon>
                                <IconExternalLink />
                            </template>
                        </Button>
                    </template>
                    <template v-else>
                        <Tag severity="secondary" value="Aucun" />
                    </template>
                </div>
            </template>
            <template #filter="{ filterModel, filterCallback }">
                <InputText
                    v-model="filterModel.value"
                    placeholder="Rechercher par alias"
                    type="text"
                    @input="filterCallback()"
                />
            </template>
        </Column>

        <Column field="birthdate" header="Date de naissance" />
        <Column :showFilterMenu="false" field="age" header="Âge" sortable>
            <template #body="{ data }">
                {{ data.age }}
            </template>
        </Column>

        <Column header="Actions">
            <template #body="{ data }">
                <div class="flex gap-2">
                    <!-- Bouton Modifier (icone bleue) -->
                    <Button text @click="openAccountDialog(data)">
                        <template #icon>
                            <IconEdit :size="26" class="text-blue-500" />
                        </template>
                    </Button>

                    <!-- Bouton Supprimer (icone rouge) -->
                    <Button
                        severity="danger"
                        text
                        @click="openDeleteDialog(data)"
                    >
                        <template #icon>
                            <IconTrash :size="26" class="text-red-500" />
                        </template>
                    </Button>
                </div>
            </template>
        </Column>
    </DataTable>

    <!-- Dialog Modifier -->
    <Dialog
        v-model:visible="showAccountDialog"
        :style="{ width: '30rem' }"
        header="Modifier le compte"
        modal
    >
        <div class="mb-4 flex items-center gap-4">
            <label class="w-full">Prénom</label>
            <div class="w-full">
                <InputText v-model="accountForm.given_name" class="w-full" />
                <div v-if="errors?.given_name" />
                <Message
                    v-if="errors?.given_name"
                    severity="error"
                    size="small"
                    variant="simple"
                >{{ errors.given_name[0] }}
                </Message
                >
            </div>
        </div>
        <div class="mb-4 flex items-center gap-4">
            <label class="w-full">Nom</label>
            <div class="w-full">
                <InputText v-model="accountForm.family_name" class="w-full" />
                <div v-if="errors?.family_name" />
                <Message
                    v-if="errors?.family_name"
                    severity="error"
                    size="small"
                    variant="simple"
                >{{ errors.family_name[0] }}
                </Message
                >
            </div>
        </div>
        <div class="mb-4 flex items-center gap-4">
            <label class="w-full">Date de naissance</label>
            <div class="w-full">
                <InputText
                    v-model="accountForm.birthdate"
                    class="w-full"
                    type="date"
                />
                <div v-if="errors?.birthdate" />
                <Message
                    v-if="errors?.birthdate"
                    severity="error"
                    size="small"
                    variant="simple"
                >{{ errors.birthdate[0] }}
                </Message
                >
            </div>
        </div>
        <div class="mb-4 flex flex-col gap-2">
            <label>Allergies & conditions</label>
            <div class="mb-4 flex items-center gap-4">
                <Textarea
                    v-model="accountForm.condition"
                    class="w-full"
                    rows="3"
                />
                <Message
                    v-if="errors?.condition"
                    severity="error"
                    size="small"
                    variant="simple"
                >{{ errors.condition[0] }}
                </Message
                >
            </div>
        </div>

        <div
            v-if="page.props.auth.isAdmin"
            class="mt-4 mb-4 flex items-center gap-4"
        >
            <label class="w-1/2">Points de compétence</label>
            <InputNumber
                v-model="accountForm.skill_point"
                :min="0"
                buttonLayout="horizontal"
                showButtons
            >
                <template #incrementbuttonicon>
                    <span>+</span>
                </template>
                <template #decrementbuttonicon>
                    <span>-</span>
                </template>
            </InputNumber>
        </div>

        <div
            v-if="page.props.auth.isAdmin"
            class="mb-6 flex items-center gap-4"
        >
            <label class="w-1/2">Points d'aptitude</label>
            <InputNumber
                v-model="accountForm.knowledge_point"
                :min="0"
                buttonLayout="horizontal"
                showButtons
            >
                <template #incrementbuttonicon>
                    <span>+</span>
                </template>
                <template #decrementbuttonicon>
                    <span>-</span>
                </template>
            </InputNumber>
        </div>

        <div v-if="!page.props.auth.isAdmin" class="mb-4 flex">
            <Button
                class="p-button-primary w-full"
                label="Changer le mot de passe"
                @click="openPasswordDialog(selectedParticipant)"
            />
        </div>

        <div class="flex">
            <Button
                class="p-button-primary w-full"
                label="Confirmer"
                @click="submitAccountForm"
            />
        </div>
    </Dialog>

    <!-- Dialog Suppression -->
    <Dialog
        v-model:visible="showDeleteDialog"
        :style="{ width: '30rem' }"
        header="Suppression"
        modal
    >
        <span class="text-surface-500 dark:text-surface-400 mb-6 block">
            La suppression de ce compte est <b>définitive</b> et
            <b>irréversible</b>. Confirmez-vous la suppression de ce participant
            ?
        </span>

        <div class="w-full gap-4">
            <Button
                class="w-full"
                label="Supprimer"
                severity="danger"
                @click="submitDeleteForm"
            />
        </div>
    </Dialog>

    <!-- Dialog Changer Mot de Passe -->
    <Dialog
        v-model:visible="showPasswordDialog"
        :style="{ width: '35rem' }"
        header="Changer le mot de passe"
        modal
    >
        <span class="text-surface-500 dark:text-surface-400 mb-8 block"
        >Mettre un mot de passe sur le participant permet à celui-ci de se
            connecter avec son nom de personnage et le mot de passe. Pour le
            retirer, simplement laisser les champs vides.</span
        >

        <div class="mb-4 flex items-center gap-4">
            <label>Mot de passe du compte principal</label>
            <Password v-model="passwordForm.account_password" :feedback="false" toggleMask />
        </div>
        <div class="mb-4 flex items-center gap-4">
            <label>Nouveau mot de passe du participant</label>
            <Password v-model="passwordForm.new_password" toggleMask>
                <template #footer>
                    <div class="mt-2 text-sm text-gray-600">
                        <p class="mb-1 font-semibold">Le mot de passe doit contenir:</p>
                        <ul class="list-inside list-disc space-y-1">
                            <li>Au moins 8 caractères</li>
                            <li>Une lettre majuscule</li>
                            <li>Une lettre minuscule</li>
                            <li>Un chiffre</li>
                        </ul>
                    </div>
                </template>
            </Password>
        </div>
        <div class="mb-4 flex items-center gap-4">
            <label>Confirmer mot de passe du participant</label>
            <Password v-model="passwordForm.confirm_password" :feedback="false" toggleMask />
        </div>
        <br />
        <div class="flex">
            <Button
                class="p-button-primary w-full"
                label="Confirmer"
                @click="submitPasswordForm"
            />
        </div>
    </Dialog>

    <!-- Dialog Ajout Participant -->
    <AddParticipantDialog
        v-model:visible="showAddParticipantDialog"
        @participant-added="handleParticipantAdded"
    />
</template>

<style scoped>
.p-datatable {
    background-color: white;
}
</style>
