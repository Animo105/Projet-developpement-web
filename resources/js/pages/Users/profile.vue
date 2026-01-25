<script lang="ts" setup>
import ChangePasswordDialog from '@/components/Profile/ChangePasswordDialog.vue';
import ContactsSection from '@/components/Profile/ContactsSection.vue';
import EditProfilDialog from '@/components/Profile/EditProfilDialog.vue';
import { User } from '@/interfaces/user';
import { useForm, router } from '@inertiajs/vue3';
import { IconEdit, IconKey, IconTrashX } from '@tabler/icons-vue';
import ConfirmDialog from 'primevue/confirmdialog';
import Divider from 'primevue/divider';
import Panel from 'primevue/panel';
import { useConfirm } from 'primevue/useconfirm';
import { computed, ref } from 'vue';
import { useToast } from 'primevue';
import Button from 'primevue/button';


interface Props {
    user: User;
}
const props = defineProps<Props>();
const userref = ref(props.user);

const username = computed(
    () => userref.value.given_name + ' ' + userref.value.family_name,
);
const edit_profile_visible = ref(false);
const password_form_visible = ref(false);
const toast = useToast();
const confirm = useConfirm();

const confirmDelete = () => {
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
            useForm({ id: props.user.id }).post('/users/delete', {
                onError: () => {
                    toast.add({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: "Une erreur s'est produite",
                        life: 3000,
                    });
                },
            });
        },
    });
};
</script>

<template>
    <div>
        <!-- Panel du profile général (user, email, phone) -->
        <Panel
            :header="username"
            class="clickable"
            @click="edit_profile_visible = true"
        >
            <template #icons>
                <IconEdit class="icon" size="1.5rem" />
            </template>

            <small class="text-secondary">{{ userref.email }}</small
            ><br />
            <small class="text-secondary">{{ userref.phone }}</small>
        </Panel>

        <!-- Update password / delete account -->
        <div class="mt-3 mb-3 flex w-full justify-center gap-4">
            <Button
                class="w-full"
                raised
                severity="secondary"
                variant="outlined"
                @click="password_form_visible = true"
            >
                <div class="flex justify-center gap-3">
                    <IconKey />
                    <p>Changer le mot de passe</p>
                </div>
            </Button>

            <ConfirmDialog />
            <Button
                class="w-full"
                severity="danger"
                variant="text"
                @click="confirmDelete"
            >
                <div class="flex justify-center gap-3">
                    <IconTrashX />
                    <p>Supprimer le compte</p>
                </div>
            </Button>
        </div>

        <!-- bouton pour aller au participant -->
        <Divider />
        <Button @click="router.visit('/participants');">Inscrire un participant</Button>

        <!-- Contacts secondaire -->
        <Divider />
        <ContactsSection v-model:contacts="userref.contacts" />
    </div>

    <ConfirmDialog group="confirmDel">
        <template #message="popup">
            <p>
                {{ popup.message.message }} <br />
                Cette action est irréversible.
            </p>
        </template>
    </ConfirmDialog>

    <EditProfilDialog
        v-model:user="userref"
        v-model:visible="edit_profile_visible"
    />
    <ChangePasswordDialog v-model:visible="password_form_visible" />
</template>

<style scoped>
.clickable:hover {
    cursor: pointer;
    background-color: var(--secondary);
}
.icon:hover {
    cursor: pointer;
}
small {
    color: var(--text-secondary);
}
</style>
