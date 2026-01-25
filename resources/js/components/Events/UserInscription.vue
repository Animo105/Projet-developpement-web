<script setup lang="ts">
import {Tarif} from '@/interfaces/event'
import {Participant} from '@/interfaces/participant'
import Checkbox from 'primevue/checkbox'
import Button from 'primevue/button'
import {useForm} from '@inertiajs/vue3'
import { ref} from "vue";
import { useToast } from 'primevue'
import {Bill} from '@/interfaces/bill'

const toast = useToast()

interface Inscription {
    participant_id:number
    inscrit:boolean
    accompagnateur:boolean
    location_arme:boolean
    location_costume:boolean
}
interface Preview {
    amount:number
    price_per:number
    total:number
}

interface Props {
    event_id:number
    tarifs: Tarif[]
    participants?:Participant[]
    bill?:Bill
}

const props = defineProps<Props>()
console.log(props.participants)
const tarifArme : boolean = !!props.tarifs.find(t => t.type == 'arme')
const tarifCostume : boolean = !!props.tarifs.find(t => t.type == 'costume')
const tarifAccompagnateur : boolean = !!props.tarifs.find(t => t.type == 'accompagnateur')
const tarifFratrie : boolean = !!props.tarifs.find(t => t.type == 'rfratrie')
const tarifEvent : boolean = !!props.tarifs.find(t => t.type == 'revent')

const bilant = ref<Record<number, Inscription>>({})
const total = ref<Record<string, Preview>>({})
const total_amount = ref<number>(0)

if (props.tarifs) {
    props.tarifs.forEach(tarif => {
        total.value[tarif.type] = {
            amount:0,
            price_per:tarif.amount,
            total:0
        }
    })
}
if (props.participants) {
    Object.values(props.participants).forEach((participant) => {
        bilant.value[participant.id] = {
            participant_id: participant.id,
            inscrit: props.bill?.entries?.[participant.id]?.['inscrit'] ?? false,
            accompagnateur: participant.is_adult,
            location_arme: props.bill?.entries?.[participant.id]?.['arme'] ?? false,
            location_costume: props.bill?.entries?.[participant.id]?.['costume'] ?? false
        }
    });
}
const updateInscription = (p_id : number) => {
    const inscription = bilant.value[p_id];
    if (inscription && !inscription.inscrit) {
        inscription.location_arme = false
        inscription.location_costume = false
    }
    updatePreview()
}
const updatePreview = () => {
    let participant_amount = 0
    let accompagnateur_amount = 0
    let arme_amount = 0
    let costume_amount = 0
    total_amount.value = 0
    Object.values(bilant.value).forEach( inscription => {
        if (inscription.inscrit) {
            if (inscription.accompagnateur) {
                accompagnateur_amount += 1
            }
            else {
                participant_amount += 1
            }
            if (inscription.location_arme) {
                arme_amount += 1
            }
            if (inscription.location_costume) {
                costume_amount += 1
            }
        }
    })

    total.value['participant'].amount = participant_amount
    total.value['participant'].total = total.value['participant'].price_per * participant_amount
    total_amount.value += total.value['participant'].total

    if (tarifFratrie) {
        total.value['rfratrie'].amount = (participant_amount -1)
        total.value['rfratrie'].total = total.value['rfratrie'].price_per * (participant_amount -1)
        total_amount.value += total.value['rfratrie'].total
    }

    if (tarifAccompagnateur) {
        total.value['accompagnateur'].amount = accompagnateur_amount
        total.value['accompagnateur'].total = total.value['accompagnateur'].price_per * accompagnateur_amount
        total_amount.value += total.value['accompagnateur'].total
    }

    if (tarifArme) {
        total.value['arme'].amount = arme_amount
        total.value['arme'].total = total.value['arme'].price_per * arme_amount
        total_amount.value += total.value['arme'].total
    }

    if (tarifCostume) {
        total.value['costume'].amount = costume_amount
        total.value['costume'].total = total.value['costume'].price_per * costume_amount
        total_amount.value += total.value['costume'].total
    }
}
const handleInscription = () => {
    loading.value = true
    const inscs : Record<number, string[]> = {}
    Object.values(bilant.value).forEach(insc => {
        if (insc.inscrit) {
            const temp = []
            if (insc.accompagnateur) {
                temp.push('accompagnateur')
            }
            else {
                temp.push('participant')
            }
            if (insc.location_arme) {
                temp.push('arme')
            }
            if (insc.location_costume) {
                temp.push('costume')
            }
            inscs[insc.participant_id] = temp
        }
    })
    const form = useForm(inscs);
    form.post('/events/inscription/'+props.event_id, {
        preserveScroll:true,
        onError: () => {
            toast.add({
                severity:"error",
                detail:"Erreur lors de l'inscription.",
                summary:"Erreur",
                life:5000
            })
        }
    })
    loading.value = false
}
const loading = ref(false)
</script>

<template>
    <h1>Formulaire d'inscription</h1>
    <p v-if="!participants || participants.length == 0">Vous n'avez pas de participant à inscrire</p>
    <table v-else class="insc_table">
        <thead>
            <tr class="font-bold">
                <td>Participant</td>
                <td>Inscrire</td>
                <td v-if="tarifArme">Louer une armes</td>
                <td v-if="tarifCostume">Louer un costume</td>
            </tr>
        </thead>
        <tbody>
            <tr v-for="participant in participants" :key="participant.id">
                <td>
                    <p>{{participant.name}}</p>
                    <small>{{participant.character_name}}</small>
                </td>
                <td>
                    <Checkbox binary :true-value="true" :false-value="false"
                      v-model="bilant[participant.id].inscrit"
                        @change="updateInscription(participant.id)"
                        :disabled="bill?.locked?.[participant.id]?.['inscrit'] ?? false"/>
                </td>
                <td v-if="tarifArme">
                    <Checkbox binary :true-value="true" :false-value="false"
                        @change="updatePreview" v-model="bilant[participant.id].location_arme"
                              :disabled="!bilant[participant.id].inscrit || (bill?.locked?.[participant.id]?.['arme'] ?? false)"/>
                </td>
                <td v-if="tarifCostume">
                    <Checkbox binary :true-value="true" :false-value="false"
                        @change="updatePreview"     v-model="bilant[participant.id].location_costume"
                              :disabled="!bilant[participant.id].inscrit || (bill?.locked?.[participant.id]?.['costume'] ?? false)"/>
                </td>
            </tr>
        </tbody>
    </table>
    <!-- TODO : Refaire estimation de la facture, manque rabais d'event, rabais de fraternité  -->
    <h2 class="font-bold">Estimation de la facture</h2>
    <table class="preview">
        <tbody>
            <tr>
                <td>Participant</td>
                <td>{{total['participant'].amount}}</td>
                <td>X {{total['participant'].price_per}}</td>
                <td>= {{total['participant'].total}}$</td>
            </tr>
            <tr v-if="tarifFratrie">
                <td>Rabais de fratrie</td>
                <td>{{total['rfratrie'].amount}}</td>
                <td>X {{total['rfratrie'].price_per}}</td>
                <td>= {{total['rfratrie'].total}}$</td>

            </tr>
            <tr v-if="tarifAccompagnateur">
                <td>Accomagnateur</td>
                <td>{{total['accompagnateur'].amount}}</td>
                <td>X {{total['accompagnateur'].price_per}}</td>
                <td>= {{total['accompagnateur'].total}}$</td>
            </tr>
            <tr v-if="tarifArme">
                <td>Arme</td>
                <td>{{total['arme'].amount}}</td>
                <td>X {{total['arme'].price_per}}</td>
                <td>= {{total['arme'].total}}$</td>
            </tr>
            <tr v-if="tarifCostume">
                <td>Costume</td>
                <td>{{total['costume'].amount}}</td>
                <td>X {{total['costume'].price_per}}</td>
                <td>= {{total['costume'].total}}$</td>
            </tr>
            <tr class="border-t-secondary">
                <td>Total</td>
                <td colspan="2"></td>
                <td>{{total_amount}}$</td>
            </tr>
        </tbody>
    </table>

        <Button @click="handleInscription" :disabled="loading">
        Inscrire les participants
    </Button>
</template>

<style scoped>
h1 {
    font-weight: 700;
    font-size: 2rem;
    line-height: 2;
    margin: 0;
}
.insc_table {
    width: 100%;
}
.insc_table td {
    width: 25%;
}
small {
    color: var(--text-secondary);
}
.insc_table tr td {
    padding-bottom: 1rem;
}
.preview td {
    padding-right: 1rem;
}
</style>
