<script setup lang="ts">
import Dialog from 'primevue/dialog';
import Message from 'primevue/message';
import InputMask from 'primevue/inputmask';
import { Form } from '@primevue/forms';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import { useForm } from '@inertiajs/vue3';
import { zodResolver } from '@primevue/forms/resolvers/zod';
import { z } from 'zod';
import { watch } from 'vue';
import { User } from '@/interfaces/user';

interface Props {
    visible: boolean,
    user: User,
}

interface Emits {
    (e: 'update:visible', value: boolean): void;
    (e: 'update:user', value: User): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

// --- Form initial ---
const form = useForm({
    given_name: props.user.given_name ?? '',
    family_name: props.user.family_name ?? '',
    email: props.user.email ?? '',
    phone: props.user.phone ?? '',
});

// --- Utility: Sync form with user ---
const syncForm = (user: User) => {
    form.given_name = user.given_name ?? '';
    form.family_name = user.family_name ?? '';
    form.email = user.email ?? '';
    form.phone = user.phone ?? '';
    form.clearErrors();
};

// --- When modal opens: sync with latest user data ---
watch(
    () => props.visible,
    (visible) => {
        if (visible) syncForm(props.user);
    }
);

// --- schéma de validation ---
const resolver = zodResolver(
    z.object({
        given_name: z
            .string({ message: 'Prénom requis' })
            .min(2, { message: 'Minimum 2 caractères' })
            .max(50, { message: 'Maximum 50 caractères' }),

        family_name: z
            .string({ message: 'Nom requis.' })
            .min(2, { message: 'Minimum 2 caractères' })
            .max(50, { message: 'Maximum 50 caractères' }),

        email: z
            .string({ message: 'Email requis' })
            .email({ message: 'Adresse email invalide' }),

        phone: z
            .string({ message: 'Téléphone requis' })
            .regex(/^\(\d{3}\) \d{3}-\d{4}$/, {
                message: 'Format attendu : (819) 201-2013',
            }),
    })
);

// --- soumission du form ---
const handleSubmit = (formData: any) => {
    if (!formData.valid) return;
    form.transform((data) => ({
        phone: data.phone.replace(/\D/g, '').trim(),
        given_name: data.given_name.trim(),
        family_name: data.family_name.trim(),
        email: data.email.toLowerCase().trim(),
    })).post('/profile/update/info', {
        onSuccess: () => {
            emit('update:user', {
                ...props.user,
                given_name: form.given_name,
                family_name: form.family_name,
                email: form.email,
                phone: form.phone,
            });
            emit('update:visible', false);
        },
        onError: () => {
            console.log('Error');
        },
        preserveScroll: true,
    });
};
</script>

<template>
    <Dialog :visible="visible" modal header="Informations" @update:visible="emit('update:visible', $event)">
        <Form
            v-slot="$form"
            :initialValues="form"
            :resolver="resolver"
            :validateOnBlur="true"
            :validateOnValueUpdate="false"
            class="flex flex-col gap-4"
            @submit="handleSubmit"
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
                    <label for="register-givenName" class="font-medium">
                        Prénom
                    </label>
                    <InputText
                        id="register-givenName"
                        v-model="form.given_name"
                        name="given_name"
                        type="text"
                        placeholder="Jean"
                        :disabled="form.processing"
                        :invalid="$form.given_name?.invalid"
                        class="w-full"
                        autocomplete="given-name"
                    />
                    <template v-if="$form.given_name?.invalid">
                        <Message
                            v-for="(error, index) of $form.given_name.errors"
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
                        Nom
                    </label>
                    <InputText
                        id="register-familyName"
                        v-model="form.family_name"
                        name="family_name"
                        type="text"
                        placeholder="Tremblay"
                        :disabled="form.processing"
                        :invalid="$form.family_name?.invalid"
                        class="w-full"
                        autocomplete="family-name"
                    />
                    <template v-if="$form.family_name?.invalid">
                        <Message
                            v-for="(error, index) of $form.family_name.errors"
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

            <div class="flex flex-col gap-2">
                <label for="register-email" class="font-medium"> Email </label>
                <InputText
                    id="register-email"
                    v-model="form.email"
                    name="email"
                    type="email"
                    placeholder="jean.tremblay@exemple.com"
                    :disabled="form.processing"
                    :invalid="$form.email?.invalid"
                    class="w-full"
                    autocomplete="email"
                />
                <template v-if="$form.email?.invalid">
                    <Message
                        v-for="(error, index) of $form.email.errors"
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
                <label for="register-phone" class="font-medium">
                    Téléphone
                </label>
                <InputMask
                    id="register-phone"
                    v-model="form.phone"
                    name="phone"
                    mask="(999) 999-9999"
                    placeholder="(819) 201-2013"
                    :disabled="form.processing"
                    :invalid="$form.phone?.invalid"
                    class="w-full"
                    autocomplete="tel"
                />
                <template v-if="$form.phone?.invalid">
                    <Message
                        v-for="(error, index) of $form.phone.errors"
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

<style scoped></style>
