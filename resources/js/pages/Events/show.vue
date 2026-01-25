<script setup lang="ts">
import {EventFull} from '@/interfaces/event';
import {Participant} from '@/interfaces/participant'
import { usePage, useForm, router } from '@inertiajs/vue3'
import Card from 'primevue/card'
import Panel from 'primevue/panel'
import Button from 'primevue/button'
import { computed } from 'vue';
import AdminList from '@/components/Events/AdminList.vue'
import UserInscription from '@/components/Events/UserInscription.vue'
import { Bill } from '@/interfaces/bill'
import { jsPDF } from 'jspdf';
import autoTable from 'jspdf-autotable';

const page = usePage()
const isAdmin = computed(() => page.props.auth?.isAdmin)
interface Props {
    event: EventFull
    participants:Participant[]
    bill?:Bill
}
const props = defineProps<Props>();
const delEvent = () => {
    useForm({id:props.event.id}).post('/events/destroy')
}

function generateParticipantPDF() {
    const doc = new jsPDF();

    const eventName = props.event.title;
    const start = new Date(props.event.start_date);
    const end = new Date(props.event.end_date);

    const formatDate = {
        timezone: 'UTC',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }
    const eventDuration = `${start.toLocaleString('fr-FR', formatDate)} - ${end.toLocaleString('fr-FR', formatDate)}`;

    // event name print
    doc.setFontSize(12);
    doc.text(eventName, 14,9);
    doc.setFontSize(8);
    doc.text(eventDuration, 14, 13);
    // set table
    const header = ['Nom', 'Personnage', 'Téléphone', 'Arrivé', 'Diner', 'DépartJ'];
    const table = props.event.entries.map((entry) => [entry.participant_name, entry.character_name, entry.telephone_number, '', '', '']);

    // generate table
    autoTable(doc, {
        head: [header],
        body: table,
        willDrawCell: function(data) {
            doc.setLineWidth(0.2); // set the border width
            if(data.row.section === 'head'){
                doc.setFillColor(120,120,120); // sets cell color
                doc.setDrawColor(255,255,255); // sets border color
            }
            if(data.row.section === 'body') {
                doc.setFillColor(255,255,255); // sets cell color
                doc.setDrawColor(0, 0, 0); // set the border color
            }

            // required for borders
            // bottom border
            doc.line(
                data.cell.x,
                data.cell.y + data.cell.height,
                data.cell.x + data.cell.width,
                data.cell.y + data.cell.height
            );
            // top border
            doc.line(
                data.cell.x + data.cell.width,
                data.cell.y,
                data.cell.x,
                data.cell.y
            );
            // left border
            doc.line(
                data.cell.x,
                data.cell.y + data.cell.height,
                data.cell.x,
                data.cell.y
            );
            // right border
            doc.line(
                data.cell.x + data.cell.width,
                data.cell.y,
                data.cell.x + data.cell.width,
                data.cell.y + data.cell.height
            );
        },
    })

    const date = new Date().toLocaleDateString();
    doc.save('event_' + props.event.title + '_participantList_' + date);
}
</script>

<template>
    <Card>
        <template #title>{{event.title}}</template>
        <template #subtitle>{{event.date}}</template>
        <template #content><p class="description">{{event.description}}</p></template>
    </Card>
    <div v-if="isAdmin" class="w-full flex justify-end gap-3 mt-3">
        <div>
            <Button
                class="w-full"
                severity="info"
                label="Génération d'un PDF pour la présence des participants"
                @click="generateParticipantPDF"
            ></Button>
        </div>
        <Button v-if="new Date(props.event.start_date).getTime() > new Date().getTime()" @click="router.visit('/events/edit/'+event.id)" label="Modifier l'évènement" />
        <Button v-else disabled label="L'évènement est passé"></Button>
        <Button @click="delEvent" severity="danger" label="Supprimer l'évènement" />
    </div>
    <Panel class="mt-5" header="Voir les tarifs" :toggleable="true" :collapsed="true">
        <div v-for="tarif in event.tarifs" :key="tarif.id">
            <p>{{tarif.label}} : <b class="font-bold">{{tarif.amount}}$</b></p>
        </div>
    </Panel>

    <div v-if="isAdmin"  class="mt-5">
        <AdminList :event="event" :spendings="event.spendings" :entries="event.entries" />
    </div>
    <div v-else class="mt-5">
        <UserInscription :bill="bill" :event_id="event.id" :participants="participants" :tarifs="event.tarifs"/>
    </div>
</template>

<style scoped>
.description {
    white-space: pre-line;
}
</style>
