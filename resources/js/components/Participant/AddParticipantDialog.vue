<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { watch, computed } from 'vue';
import Dialog from 'primevue/dialog';
import FormField from '@/components/FormField.vue';
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
    (e: 'participant-added'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const maxBirthdate = computed(() => {
    const date = new Date();
    date.setFullYear(date.getFullYear() - 5);
    return date.toISOString().split('T')[0];
});

const resolver = zodResolver(
    z.object({
        givenName: z.string({ message: 'Prénom requis' })
            .min(2, { message: 'Minimum 2 caractères' })
            .max(50, { message: 'Maximum 50 caractères' }),
        familyName: z.string({ message: 'Nom requis' })
            .min(2, { message: 'Minimum 2 caractères' })
            .max(50, { message: 'Maximum 50 caractères' }),
        birthdate: z.string({ message: 'Date de naissance requise' })
            .min(1, { message: 'Date de naissance requise' }),
        condition: z.string().nullable().optional(),
        characterUsername: z.string({ message: 'Nom du personnage requis' })
            .min(2, { message: 'Minimum 2 caractères' })
            .max(50, { message: 'Maximum 50 caractères' }),
    })
);

const form = useForm({
    givenName: '',
    familyName: '',
    birthdate: '',
    condition: '',
    characterUsername: '',
});

const handleSubmit = (formData: any) => {
    if (!formData.valid) return;

    const data = formData.values;

    form.transform(() => ({
        given_name: data.givenName.trim(),
        family_name: data.familyName.trim(),
        birthdate: data.birthdate,
        condition: data.condition?.trim() || '',
        character_username: data.characterUsername.trim(),
    })).post('/participants', {
        onSuccess: () => {
            emit('update:visible', false);
            emit('participant-added');
        },
        onError: (errors) => {
            console.error('Validation errors:', errors);
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

defineExpose({
    form
});
</script>

<template>
    <Dialog
        :visible="visible"
        @update:visible="closeDialog"
        modal
        header="Ajouter un participant"
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
                name="birthdate"
                label="Date de naissance"
                type="date"
                :form="$form"
                :disabled="form.processing"
                :maxDate="maxBirthdate"
            />

            <FormField
                name="condition"
                label="Allergies & conditions"
                placeholder="Indiquez les allergies ou conditions médicales..."
                type="textarea"
                :form="$form"
                :disabled="form.processing"
                :rows="3"
            />

            <FormField
                name="characterUsername"
                label="Nom du personnage"
                placeholder="Nom du joueur"
                :form="$form"
                :disabled="form.processing"
            />

            <Button
                label="Ajouter le participant"
                type="submit"
                class="mt-3"
                :loading="form.processing"
                :disabled="form.processing"
            />
        </Form>
    </Dialog>
</template>
