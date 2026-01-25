<script setup lang="ts">
import Dialog from 'primevue/dialog'
import { Form } from '@primevue/forms';
import Button from 'primevue/button';
import { useForm } from '@inertiajs/vue3';

import { zodResolver } from '@primevue/forms/resolvers/zod';
import { z } from 'zod';
import { watch } from 'vue';
import Message from 'primevue/message';
import Password from 'primevue/password';
import { useToast } from 'primevue';

interface Props {
    visible: boolean;
}

interface Emits {
    (e: 'update:visible', value: boolean): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();
const toast = useToast();

watch(() => props.visible, (newVal) => {
    if (!newVal) {
        form.reset();
        form.clearErrors();
    }
});

const form = useForm({
    old_password: '',
    new_password: '',
    new_password_confirmation: '',
});

const registrationValidationSchema = zodResolver(
    z.object({
        old_password: z
            .string({message: 'Mot de passe acctuel requis'})
            .min(1, { message: 'Mot de passe requis' }),

        new_password: z
            .string({ message: 'Mot de passe requis' })
            .regex(
                /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,50}$/,
                { message: 'Mot de passe invalide ou trop faible' }
            ),

        new_password_confirmation: z
            .string({ message: 'Confirmation requise' })
    })
        .refine((data) => data.new_password === data.new_password_confirmation, {
            message: 'Les mots de passe ne correspondent pas',
            path: ['password_confirmation']
        })
);

const handleSubmit = (formData: any) => {
    if (!formData.valid) return;
    form.transform((data) => ({
        old_password: data.old_password.trim(),
        new_password: data.new_password.trim(),
        new_password_confirmation: data.new_password_confirmation.trim()

    })).post('/profile/update/password', {
        onSuccess: () => {
            emit('update:visible', false)
            toast.add({ severity: 'success', summary: 'Succès!', detail: 'Mot de passe modifier', life: 3000 })
        },
    })
};

</script>

<template>
    <Dialog :visible="visible" modal header="Mot de passe" @update:visible="emit('update:visible', $event)" class="w-md">
        <Form
            v-slot="$form"
            :validate-on-value-update="false"
            :validate-on-blur="true"
            :resolver="registrationValidationSchema"
            class="flex flex-col gap-4"
            @submit="handleSubmit">

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

            <div class="flex flex-col gap-2">
                <label for="user-password" class="font-medium">Mot de passe actuel</label>
                <Password
                    id="user-password"
                    v-model="form.old_password"
                    name="current_password"
                    toggleMask
                    :feedback="false"
                    placeholder="Votre mot de passe"
                    :disabled="form.processing"
                    :invalid="$form.old_password?.invalid"
                    fluid
                    class="w-full"
                />
                <template v-if="$form.old_password?.invalid">
                    <Message
                        v-for="(error, index) of $form.old_password.errors"
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
                <label for="modify-password" class="font-medium">
                    Mot de passe
                </label>
                <Password
                    id="modify-password"
                    v-model="form.new_password"
                    name="password"
                    placeholder="Mot de passe"
                    :feedback="true"
                    toggleMask
                    :disabled="form.processing"
                    :invalid="$form.new_password?.invalid"
                    fluid
                    class="w-full"
                    autocomplete="new-password"
                >
                    <template #footer>
                        <div class="text-xs text-gray-600 mt-2">
                            <p class="font-semibold mb-1">Le mot de passe doit contenir:</p>
                            <ul class="list-disc list-inside space-y-1">
                                <li>Au moins 8 caractères</li>
                                <li>Une lettre majuscule</li>
                                <li>Une lettre minuscule</li>
                                <li>Un chiffre</li>
                            </ul>
                        </div>
                    </template>
                </Password>

                <template v-if="$form.new_password?.invalid">
                    <Message
                        v-for="(error, index) of $form.new_password.errors"
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
                <label for="modify-password-confirmation" class="font-medium">
                    Confirmer le mot de passe
                </label>
                <Password
                    id="modify-password-confirmation"
                    v-model="form.new_password_confirmation"
                    name="password_confirmation"
                    placeholder="Confirmez le mot de passe"
                    :feedback="false"
                    toggleMask
                    :disabled="form.processing"
                    :invalid="$form.new_password_confirmation?.invalid"
                    fluid
                    class="w-full"
                    autocomplete="new-password"
                />
                <template v-if="$form.new_password_confirmation?.invalid">
                    <Message
                        v-for="(error, index) of $form.new_password_confirmation.errors"
                        :key="index"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ error.message }}
                    </Message>
                </template>
            </div>

            <div class="flex justify-end gap-2">
                <Button
                    type="button"
                    severity="secondary"
                    label="Annuler"
                    @click="emit('update:visible', false)"
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

</style>
