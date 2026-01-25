<script setup lang="ts">
import Button from 'primevue/button'
import Card from 'primevue/card'
import { EventSummary } from '@/interfaces/event';
import { computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

const page = usePage()
const isAdmin = computed(() => page.props.auth?.isAdmin)
interface Props {
    future_events: EventSummary[],
    past_events: EventSummary[]
}
defineProps<Props>()
</script>

<template>
    <div v-if="future_events.length == 0" class="flex justify-center border-3 mt-5 mb-5 p-3">
        Aucun évènement n'est prévu pour le moment.
    </div>

    <Card v-for="event in future_events" :key="event.id" class="w-2xl mt-3">
        <template #title>{{event.title}}</template>
        <template #subtitle>{{event.date}}</template>
        <template #content><p class="description">{{event.description}}</p></template>
        <template #footer>
            <div class="flex w-full">
                <div v-if="isAdmin" class="w-full p-card-subtitle">
                    Participant: {{event.participants}} <br>
                    profit:0
                </div>
                <div class="flex justify-end w-full">
                    <Button v-if="isAdmin" label="Voir l'évènement" @click="router.visit('/events/show/'+event.id)"/>
                    <Button v-else label="S'inscrire" @click="router.visit('/events/show/'+event.id)"/>
                </div>
            </div>
        </template>
    </Card>

    <div v-if="isAdmin">
        <Button label="Créer un évènement" @click="router.visit('/events/create')" />

        <h2 class="mt-10 font-bold text-xl">Évènements passés</h2>

        <Card v-for="event in past_events" :key="event.id" class="w-2xl mt-3">
            <template #title>{{event.title}}</template>
            <template #subtitle>{{event.date}}</template>
            <template #content><p>{{event.description}}</p></template>
            <template #footer>
                <div class="flex w-full">
                    <div v-if="isAdmin" class="w-full p-card-subtitle">
                        Participant: {{event.participants}} <br>
                        profit:0
                    </div>
                    <div class="flex justify-end w-full">
                        <Button label="Voir l'évènement" @click="router.visit(`/events/show/${event.id}`)" />
                    </div>
                </div>
            </template>
        </Card>
    </div>

</template>

<style scoped>
.description {
    white-space: pre-line;
}
</style>
