<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import Dialog from 'primevue/dialog';
import FormField from '@/components/FormField.vue'
import Message from 'primevue/message';
import Button from 'primevue/button';
import { Form } from '@primevue/forms';
import { zodResolver } from '@primevue/forms/resolvers/zod';
import { z } from 'zod';

interface Props {
    visible: boolean;
}
interface Emits {
    (e: 'update:visible', value: boolean): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const resolver = zodResolver(
    z.object({
        givenName: z.string({ message: 'Prénom requis' })
            .min(2, { message: 'Minimum 2 caractères' })
            .max(50, { message: 'Maximum 50 caractères' }),
        familyName: z.string({ message: 'Nom requis.' })
            .min(2, { message: 'Minimum 2 caractères' })
            .max(50, { message: 'Maximum 50 caractères' }),
        email: z.string({ message: 'Email requis' })
            .email({ message: 'Adresse email invalide' }),
        phone: z.string({ message: 'Téléphone requis' })
            .regex(/^\(\d{3}\) \d{3}-\d{4}$/, {
                message: 'Format attendu : (819) 201-2013',
            }),
        password: z.string({ message: 'Mot de passe requis' })
            .regex(/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,50}$/, {
                message: 'Mot de passe invalide ou trop faible',
            }),
        password_confirmation: z.string({ message: 'Confirmation requise' }),
    })
        .refine((data) => data.password === data.password_confirmation, {
            message: 'Les mots de passe ne correspondent pas',
            path: ['password_confirmation'],
        }),
);

const form = useForm({
    givenName: '',
    familyName: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
});

const handleSubmit = (formData: any) => {
    if (!formData.valid) return;

    const data = formData.values;

    form.transform(() => ({
        givenName: data.givenName.trim(),
        familyName: data.familyName.trim(),
        email: data.email.toLowerCase().trim(),
        phone: data.phone,
        password: data.password,
        password_confirmation: data.password_confirmation,
    })).post('/register', {
        onSuccess: () => {

            //reload pour connecter, c'est préférable d'amener directement vers le profile
            //router.reload({ only: ['auth'] });
            router.visit('/profile');
            emit('update:visible', false);
        },
        preserveScroll: true
    });
};

watch(() => props.visible, (visible) => {
    if (!visible) {
        form.reset();
        form.clearErrors();
    }
});

const closeDialog = () => {
    if (!form.processing) emit('update:visible', false);
};
</script>

<template>
    <Dialog
        :visible="visible"
        @update:visible="closeDialog"
        modal
        header="S'inscrire"
        :style="{ width: '90vw', maxWidth: '600px' }"
        :breakpoints="{ '960px': '75vw', '640px': '90vw' }"
        :closable="!form.processing"
    >
        <Form
            v-slot="$form"
            :resolver="resolver"
            :validateOnBlur="true"
            :validateOnValueUpdate="false"
            class="flex flex-col gap-5"
            @submit="handleSubmit"
        >
            <Message
                class="mt-1"
                v-if="form.errors && Object.keys(form.errors).length"
                severity="error"
                :closable="false"
            >
                <ul class="list-disc list-inside">
                    <li v-for="(msg, field) in form.errors" :key="field">{{ msg }}</li>
                </ul>
            </Message>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <FormField
                    name="givenName"
                    label="Prénom"
                    placeholder="Jean"
                    :form="$form"
                    :disabled="form.processing"
                />

                <FormField
                    name="familyName"
                    label="Nom"
                    placeholder="Tremblay"
                    :form="$form"
                    :disabled="form.processing"
                />
            </div>

            <FormField
                name="email"
                label="Email"
                placeholder="jean.tremblay@exemple.com"
                type="email"
                :form="$form"
                :disabled="form.processing"
            />

            <FormField
                name="phone"
                label="Téléphone"
                placeholder="(819) 201-2013"
                mask="(999) 999-9999"
                :form="$form"
                :disabled="form.processing"
            />

            <FormField
                name="password"
                label="Mot de passe"
                placeholder="Votre mot de passe"
                type="password"
                :form="$form"
                :disabled="form.processing"
                showStrength
            >
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
            </FormField>

            <FormField
                name="password_confirmation"
                label="Confirmez le mot de passe"
                placeholder="Votre mot de passe"
                type="password"
                :form="$form"
                :disabled="form.processing"
            />

            <Button
                label="S'inscrire"
                type="submit"
                class="mt-3"
                :loading="form.processing"
                :disabled="form.processing"
            />
        </Form>
    </Dialog>
</template>
