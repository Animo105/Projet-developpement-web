<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { z } from 'zod';
import { zodResolver } from '@primevue/forms/resolvers/zod';
import { Form } from '@primevue/forms';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import FormField from '@/components/FormField.vue';
import Message from 'primevue/message';

interface Emits {
    (e: 'forgot-password'): void;
    (e: 'success'): void;
}

const emit = defineEmits<Emits>();

const resolver = zodResolver(
    z.object({
        email: z
            .string({ message: 'Email requis' })
            .email({ message: 'Adresse email invalide' }),
        password: z
            .string({ message: 'Mot de passe requis' })
            .min(1, { message: 'Mot de passe requis' }),
        remember: z.boolean().optional().nullable()
    })
);

const form = useForm({
    email: '',
    password: '',
    remember: false
});

const handleSubmit = (formData: any) => {
    if (!formData.valid) return;

    const data = formData.values;

    form.transform(() => ({
        password : data.password,
        email: data.email.toLowerCase().trim(),
    })).post('/login', {
        onSuccess: () => {
            form.reset();
            form.clearErrors();
            router.reload({ only: ['auth'] });
            emit('success');
        },
        preserveScroll: true
    });
};
</script>

<template>
    <Form
        v-slot="$form"
        :resolver="resolver"
        :validateOnBlur="true"
        :validateOnValueUpdate="false"
        @submit="handleSubmit"
        class="flex flex-col gap-4"
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

        <FormField
            name="email"
            label="Email"
            placeholder="exemple@exemple.com"
            type="email"
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
        />

        <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <Checkbox
                    v-model="form.remember"
                    inputId="remember"
                    name="remember"
                    :binary="true"
                    :disabled="form.processing"
                />
                <label for="remember" class="cursor-pointer">Se souvenir de moi</label>
            </div>

            <Button
                label="Mot de passe oublié ?"
                link
                size="small"
                class="p-0"
                :disabled="form.processing"
                @click="emit('forgot-password')"
            />
        </div>

        <Button
            label="Connexion"
            type="submit"
            class="mt-2 w-full"
            :loading="form.processing"
            :disabled="form.processing"
        />
    </Form>
</template>
