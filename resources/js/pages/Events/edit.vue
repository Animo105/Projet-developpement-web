<script setup lang="ts">
import Button from 'primevue/button'
import {useForm } from '@inertiajs/vue3';
import { Form } from '@primevue/forms';
import { z } from 'zod';
import { zodResolver } from '@primevue/forms/resolvers/zod';
import Message from 'primevue/message';
import FormContent from '@/components/Events/FormContent.vue'
import { EventFull, Tarif, EventShort } from '@/interfaces/event'
import { useToast } from 'primevue';

interface Props {
    event:EventFull
    linkableEvents:EventShort[]
}

type EventForm = {
    title: string | null;
    description: string | null;
    start_date: Date | null;
    end_date: Date | null;
    date_registration: Date | null;
    participant_price: number | null;
    accompagnateur_price: number | null;
    weapon_price: number | null;
    costume_price: number | null;
    rabais_fratrie: number | null;
    rabais_event: number | null;
    event_linked: number | null;
};

const props = defineProps<Props>()
const toast = useToast()
console.log(props.event)
const zodSchema = z.object({
    title: z
        .string({ message: 'Titre de l\'évènement requis' })
        .min(2, { message: 'Minimum 2 caractères' })
        .max(50, { message: 'Maximum 50 caractères' }),

    description: z
        .string({message:'La description doit etre du texte'}),
    start_date: z
        .string({message: 'date de début et de fin requis'}),
    date_registration: z
        .string({message: 'date limite pour l\'inscription requis'})

});
const resolver = zodResolver(zodSchema);

const form = useForm<EventForm>({
    title:props.event.title,
    description:props.event.description,
    start_date:new Date(props.event.start_date),
    end_date:new Date(props.event.end_date),
    date_registration:new Date(props.event.end_date_registration),
    participant_price:null,
    accompagnateur_price:null,
    weapon_price:null,
    costume_price:null,
    rabais_fratrie:null,
    rabais_event:null,
    event_linked:props.event.event_linked ?? null,
});
props.event.tarifs.forEach((tarif : Tarif) => {
    if (tarif.type == "participant") {
        form.participant_price = tarif.amount
    }
    else if (tarif.type == "accompagnateur") {
        form.accompagnateur_price = tarif.amount
    }
    else if (tarif.type == "arme") {
        form.weapon_price = tarif.amount
    }
    else if (tarif.type == "costume") {
        form.costume_price = tarif.amount
    }
    else if (tarif.type == "rfratrie") {
        form.rabais_fratrie = tarif.amount
    }
    else if (tarif.type == "revent") {
        form.rabais_event = tarif.amount
    }
})
console.log(props.event)
function handleSubmit() {
    form.post(`/events/update/${props.event.id}`, {
        onSuccess: () => {
            // Message de succès
        },
        onError: () => {
            toast.add({severity:'error', summary:'Erreur', detail:'une erreur est surevenu lors de la modification'});
        }
    });
}

</script>

<template>
    <Form
        :validateOnBlur="true"
        :validateOnValueUpdate="false"
        :validate-on-mount="true"
        :resolver="resolver"
        @submit="handleSubmit"
        class="flex flex-col gap-4 mt-8"
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
        <Button type="submit">Modifier l'évènement</Button>
    </Form>
</template>

<style scoped>

</style>
