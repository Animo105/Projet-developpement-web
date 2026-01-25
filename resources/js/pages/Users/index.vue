<script setup lang="ts">
import { ref } from 'vue'
import { IconTrash, IconExternalLink, IconCircleCheck } from '@tabler/icons-vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import { User } from '@/interfaces/user'
import InputText from 'primevue/inputtext'
import { FilterMatchMode } from '@primevue/core/api'
import ConfirmDialog from 'primevue/confirmdialog'
import { useConfirm } from 'primevue/useconfirm'
import { useToast } from 'primevue'
import { router, useForm } from '@inertiajs/vue3'
import Panel from 'primevue/panel'
import Tag from 'primevue/tag'

interface Props {
    users: User[]
}

const confirm = useConfirm();
const toast = useToast();

const props = defineProps<Props>()
const usersRef = ref(props.users)
const expandedRows = ref({});
const loading = ref(false);

const filters = ref({
    family_name: {value: null, matchMode:FilterMatchMode.STARTS_WITH},
    email: {value: null, matchMode:FilterMatchMode.STARTS_WITH}
})

const del_user = (id : number) => {
    confirm.require({
        group: 'confirmDel',
        header: 'Supprimer?',
        message: 'Êtes vous sur de vouloir supprimer ce compte?',
        rejectProps: {
            label: 'Annuler',
            severity: 'secondary',
            outlined: true,
        },
        acceptProps: {
            label: 'Supprimer',
            severity: 'danger',
        },
        accept: () => {
            useForm({ id: id }).post('/admin/users/delete', {
                preserveScroll: true,
                onError: () => {
                    toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: "Une erreur s'est produite",
                        life: 3000,
                    });
                },
                onSuccess: () => {
                    const index = usersRef.value.findIndex(item => item.id === id);
                    if (index !== -1) {
                        usersRef.value.splice(index, 1);
                    }
                    toast.add({
                        summary: 'Succès',
                        detail: 'Le compte à été supprimer',
                        life:3000
                    })
                }
            });
        }
}
    )
}
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
</script>

<template>
    <DataTable v-model:filters="filters" v-model:expandedRows="expandedRows" :value="usersRef" dataKey="id" paginator
               :rows="10" tableStyle="min-width: 60rem" :loading="loading"
                :global-filter-fields="['family_name', 'email']" filter-display="row">
        <template #empty> Aucun compte trouvé. </template>
        <Column expander style="width: 5rem" />
        <Column field="family_name" header="Nom">
            <template #body="slotProps">
                {{slotProps.data.family_name}} {{slotProps.data.given_name}}
            </template>
            <template #filter="{ filterModel, filterCallback }">
                <InputText v-model="filterModel.value" type="text" @input="filterCallback()" placeholder="Chercher par nom" />
            </template>
        </Column>
        <Column field="email" header="Courriel">
            <template #filter="{ filterModel, filterCallback }">
                <InputText v-model="filterModel.value" type="text" @input="filterCallback()" placeholder="Chercher par nom" />
            </template>
        </Column>
        <Column field="phone" header="N° Tel" />
        <Column>
            <template #body="SlotProps">
                <Button @click="del_user(SlotProps.data.id)" severity="danger" variant="text"><IconTrash /></Button>
            </template>
        </Column>
        <template #expansion="slotProps">
            <Panel header="Contactes Secondaires" toggleable class="mt-4">
                <div class="p-4" v-if="slotProps.data.contacts[0] != null">
                    <DataTable :value="slotProps.data.contacts">
                        <Column header="Nom">
                            <template #body="slotProps">
                                {{slotProps.data.family_name}} {{slotProps.data.given_name}}
                            </template>
                        </Column>
                        <Column header="Courriel" field="email"/>
                        <Column header="N° Tel" field="phone"/>
                    </DataTable>
                </div>
                <div v-else>
                    <h5>Aucun contactes secondaires.</h5>
                </div>
            </Panel>
            <Panel header="Participants" toggleable class="mt-4">
                <div class="p-4" v-if="slotProps.data.participants[0] != null">
                    <DataTable :value="slotProps.data.participants">
                        <Column field="name" header="Nom"/>
                        <Column header="Accompagnateur">
                            <template #body="SlotProps">
                                <IconCircleCheck v-if="SlotProps.data.is_adult" />
                            </template>
                        </Column>
                        <Column header="Alias" >
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
                        </Column>
                    </DataTable>
                </div>
                <div v-else>
                    <h5>Aucun participants.</h5>
                </div>
            </Panel>
        </template>
    </DataTable>

    <ConfirmDialog group="confirmDel">
        <template #message="popup">
            <p>
                {{ popup.message.message }} <br />
                Cette action est irréversible.
            </p>
        </template>
    </ConfirmDialog>
</template>

<style scoped>

</style>
