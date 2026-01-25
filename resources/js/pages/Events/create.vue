<script setup lang="ts">
import Button from 'primevue/button'
import {useForm } from '@inertiajs/vue3';
import { Form } from '@primevue/forms';
import { z } from 'zod';
import { zodResolver } from '@primevue/forms/resolvers/zod';
import Message from 'primevue/message';
import FormContent from '@/components/Events/FormContent.vue'
import {EventShort} from '@/interfaces/event'

interface Props {
    linkableEvents:EventShort[]
}
defineProps<Props>()

const zodSchema = z.object({
    title: z
        .string({ message: 'Titre de l\'évènement requis' })
        .min(2, { message: 'Minimum 2 caractères' })
        .max(50, { message: 'Maximum 50 caractères' }),

    description: z
        .string({message:'La description doit etre du texte'})
        .nullable(),
    start_date: z
        .date({message: 'date de début et de fin requis'}),
    date_registration: z
        .date({message: 'date limite pour l\'inscription requis'})

});
const resolver = zodResolver(zodSchema);

const form = useForm({
    title:null,
    description:null,
    start_date:null,
    end_date:null,
    date_registration:null,
    participant_price:null,
    accompagnateur_price:null,
    weapon_price:null,
    costume_price:null,
    rabais_fratrie:null,
    rabais_event:null,
    event_id:null,
});

function handleSubmit() {
    form.post('/events/store');
}

</script>

<template>
    <Form
        :validateOnBlur="true"
        :validateOnValueUpdate="false"
        :validate-on-mount="true"
        :resolver="resolver"
        @submit="handleSubmit"
        class="flex flex-col gap-4"
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

        <FormContent :events="linkableEvents" v-model:form="form"></FormContent>
        <Button type="submit">Créer l'évènement</Button>
    </Form>
</template>

<style scoped>

</style>
