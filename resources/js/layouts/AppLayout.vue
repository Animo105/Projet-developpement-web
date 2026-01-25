<script lang="ts" setup>
import { onMounted, ref } from 'vue';
import { useToast } from 'primevue/usetoast';

import AppNavigation from '@/components/Navigation/AppNavigation.vue';
import LoginDialog from '@/components/Auth/LoginDialog.vue';
import RegisterDialog from '@/components/Auth/RegisterDialog.vue';
import ForgotPasswordDialog from '@/components/Auth/ForgotPasswordDialog.vue';
import DebugAuthBar from '@/components/Debug/DebugAuthBar.vue';
import Toast from 'primevue/toast';

const toast = useToast(); // ⬅️ IMPORTANT

const isLoginDialogVisible = ref(false);
const isRegisterDialogVisible = ref(false);
const isForgotPasswordDialogVisible = ref(false);

const handleForgotPassword = () => {
    isLoginDialogVisible.value = false;
    isForgotPasswordDialogVisible.value = true;
};

// 🔥 Afficher le toast après reload
onMounted(() => {
    const msg = localStorage.getItem('toast-success');
    if (msg) {
        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: msg,
            life: 3000
        });
        localStorage.removeItem('toast-success');
    }
});
</script>

<template>
    <div class="mx-auto max-w-6xl p-2">
        <Toast />

        <!--<DebugAuthBar />-->

        <AppNavigation
            @open-login="isLoginDialogVisible = true"
            @open-register="isRegisterDialogVisible = true"
        />

        <LoginDialog
            v-model:visible="isLoginDialogVisible"
            @forgot-password="handleForgotPassword"
        />

        <RegisterDialog
            v-model:visible="isRegisterDialogVisible"
        />

        <ForgotPasswordDialog
            v-model:visible="isForgotPasswordDialogVisible"
        />

        <main class="mt-2">
            <slot />
        </main>
    </div>
</template>
