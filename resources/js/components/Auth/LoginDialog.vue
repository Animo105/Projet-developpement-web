<script setup lang="ts">
import { ref } from 'vue';
import Dialog from 'primevue/dialog';
import SelectButton from 'primevue/selectbutton';
import UserLoginForm from './UserLoginForm.vue';
import CharacterLoginForm from './CharacterLoginForm.vue';

interface Props {
    visible: boolean;
}

interface Emits {
    (e: 'update:visible', value: boolean): void;
    (e: 'forgot-password'): void;
}

defineProps<Props>();
const emit = defineEmits<Emits>();

type LoginType = 'Utilisateur' | 'Personnage';

const selectedLoginType = ref<LoginType>('Utilisateur');
const loginTypeOptions = ref<LoginType[]>(['Utilisateur', 'Personnage']);

</script>

<template>
    <Dialog
        :visible="visible"
        @update:visible="emit('update:visible', $event)"
        modal
        header="Se connecter"
        :style="{ width: '90vw', maxWidth: '500px' }"
        :breakpoints="{ '960px': '75vw', '640px': '90vw' }"
    >
        <SelectButton
            v-model="selectedLoginType"
            :options="loginTypeOptions"
            fluid
            class="mb-6 w-full"
            :allowEmpty="false"
        />

        <UserLoginForm
            v-if="selectedLoginType === 'Utilisateur'"
            @forgot-password="emit('forgot-password')"
            @success="emit('update:visible', false)"
        />
        <CharacterLoginForm
            v-else
            @success="emit('update:visible', false)"
        />
    </Dialog>
</template>
