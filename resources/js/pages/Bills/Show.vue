<script lang="ts" setup>
import { computed, reactive, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputNumber from 'primevue/inputnumber';
import { useToast, Select } from 'primevue';
import DatePicker from 'primevue/datepicker';
import { IconCreditCard, IconPlus, IconTrash } from '@tabler/icons-vue';
import axios from 'axios';


const page = usePage();
const { bill, inscription, weapons, costumes, payments: initialPayments, statuses, paymentMethods } = page.props as any;
const currentUser = page.props.auth.user;
const payments = ref(initialPayments);

const subtotal = computed(() => inscription.price + weapons.price + costumes.price);
const total = computed(() => subtotal.value - (bill.reduction ?? 0));
let remainingAmount = computed(() => total.value - payments.value.reduce((s, p) => s + Number(p.amount), 0));

const showPaymentDialog = ref(false);
const newPayment = reactive({
    amount: 0,
    date: null as Date | null,
    method: null
});


const toast = useToast();

// --- Supprimer une transaction ---
const deletePayment = async (paymentId: number) => {
    try {
        await axios.delete(`/bills/payments/${paymentId}`);

        // Retirer la transaction du tableau local
        payments.value = payments.value.filter(p => p.id !== paymentId);

        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: 'Transaction supprimée !',
            life: 3000
        });
    } catch (error: any) {
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: error.response?.data?.message || 'Impossible de supprimer la transaction',
            life: 4000
        });
    }
};

// --- Fonction pour ajouter une transaction ---
const submitPayment = async () => {
    try {
        const response = await axios.post(`/bills/${bill.id}/payments`, {
            amount: newPayment.amount,
            date: newPayment.date?.toISOString().split('T')[0],
            method: newPayment.method
        });

        payments.value.push(response.data.payment);
        showPaymentDialog.value = false;

        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: 'Transaction ajoutée !',
            life: 3000
        });

        newPayment.amount = 0;
        newPayment.date = null;
        newPayment.method = null;
    } catch (error: any) {
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: error.response?.data?.message || 'Une erreur est survenue',
            life: 4000
        });
    }
};

// --- Fonction pour changer le statut ---
const changeStatus = async (statusId: number) => {
    try {
        await axios.patch(`/bills/${bill.id}/status`, { status_id: statusId });
        bill.status_id = statusId;
        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: 'Statut mis à jour !',
            life: 3000
        });
    } catch (error: any) {
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: 'Impossible de changer le statut',
            life: 4000
        });
    }
};
</script>

<template>
    <section class="p-6 min-h-screen">
        <div class="max-w-7xl flex flex-col lg:flex-row gap-6 items-start justify-center">

            <!-- Facture -->
            <div class="rounded-xl shadow p-6 flex flex-col gap-4"
                 style="background-color: #18181b; border: 1px solid #3f3f46;">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-bold mr-5">{{ bill.inscriptions[0]?.tarif?.event?.name ?? 'Événement' }}</h2>
                    <div>
                        <span v-if="!page.props.auth.isAdmin" class="px-3 py-1 rounded-full font-semibold text-sm">
                            {{ bill.status.name }}
                        </span>
                        <Select
                            v-else
                            v-model="bill.status_id"
                            :options="statuses"
                            class="w-40"
                            optionLabel="name"
                            optionValue="id"
                            placeholder="Changer le statut"
                            @change="changeStatus($event.value)"
                        />
                    </div>
                </div>

                <div>
                    <p class="font-medium text-white">{{ currentUser.given_name }} {{ currentUser.family_name }}
                        <span class="block text-sm text-gray-300">{{ currentUser.email }}</span>
                    </p>
                    <p class="mt-2 font-medium text-white">Nombre de participants: {{ bill.participants.length }}</p>
                </div>

                <div class="overflow-x-auto mt-4">
                    <table class="w-full text-left border-collapse">
                        <tr style="border-bottom: 1px solid #3f3f46;">
                            <td class="py-2 font-medium text-white">Inscriptions:</td>
                            <td class="py-2 italic text-gray-300">
                                {{ inscription.amountChild > 1 ? `${inscription.childBasePrice}$ x ${inscription.amountChild}` : inscription.childBasePrice + '$' }}
                                +
                                {{ inscription.amountAdult > 1 ? `${inscription.adultBasePrice}$ x ${inscription.amountAdult}` : inscription.adultBasePrice + '$' }}
                            </td>
                            <td class="py-2 font-semibold text-white">{{ inscription.price }}$</td>
                        </tr>
                        <tr v-if="weapons.amount > 0" style="border-bottom: 1px solid #3f3f46;">
                            <td class="py-2 font-medium text-white">Armes ({{ weapons.amount }}):</td>
                            <td class="py-2 italic text-gray-300">{{ weapons.amount > 1 ? `${weapons.basePrice}$ x ${weapons.amount}` : weapons.basePrice + '$' }}</td>
                            <td class="py-2 font-semibold text-white">{{ weapons.price }}$</td>
                        </tr>
                        <tr v-if="costumes.amount > 0" style="border-bottom: 1px solid #3f3f46;">
                            <td class="py-2 font-medium text-white">Costumes ({{ costumes.amount }}):</td>
                            <td class="py-2 italic text-gray-300">{{ costumes.amount > 1 ? `${costumes.basePrice}$ x ${costumes.amount}` : costumes.basePrice + '$' }}</td>
                            <td class="py-2 font-semibold text-white">{{ costumes.price }}$</td>
                        </tr>
                    </table>
                </div>

                <div class="mt-4 space-y-1 text-white">
                    <div v-if="bill.reduction != 0 && bill.reduction != null" class="flex justify-between pb-1" style="border-bottom: 1px solid #3f3f46;">
                        <span>Sous-total:</span>
                        <span>{{ subtotal }}$</span>
                    </div>
                    <div v-if="bill.reduction != 0 && bill.reduction != null" class="flex justify-between pb-1" style="border-bottom: 1px solid #3f3f46;">
                        <span>Rabais:</span>
                        <span>- {{ bill.reduction }}$</span>
                    </div>
                    <div class="flex justify-between font-bold text-lg pt-1">
                        <span>Total:</span>
                        <span>{{ total }}$</span>
                    </div>
                </div>
            </div>

            <!-- Transactions -->
            <div class="rounded-xl shadow p-6 flex flex-col gap-4 min-w-sm"
                 style="background-color: #18181b; border: 1px solid #3f3f46;">
                <div class="flex justify-between items-center mb-2 text-white">
                    <h3 class="text-xl font-bold">Transactions</h3>
                    <Button
                        v-if="!page.props.auth.isAdmin"
                        class="p-button-rounded p-button-primary"
                        label="Payer"
                        @click="showPaymentDialog = true"
                    >
                        <template #icon>
                            <IconCreditCard class="w-5 h-5" />
                        </template>
                    </Button>
                    <Button
                        v-else
                        label="Ajouter"
                        @click="showPaymentDialog = true"
                    >
                        <template #icon>
                            <IconPlus class="w-5 h-5" />
                        </template>
                    </Button>
                </div>

                <DataTable
                    :sortOrder=1
                    :value="payments"
                    class="text-white"
                    responsiveLayout="scroll"
                    sortField="date"
                >
                    <Column field="amount" header="Montant">
                        <template #body="{ data }">
                            {{ data.amount }}$
                        </template>
                    </Column>
                    <Column field="date" header="Date">
                        <template #body="{ data }">
                            {{ data.date ? new Date(data.date).toLocaleDateString('fr-FR') : '-' }}
                        </template>
                    </Column>
                    <Column header="Supprimer" v-if="page.props.auth.isAdmin">
                        <template #body="{ data }">
                            <Button
                                class="p-0 m-0"
                                severity="danger"
                                text
                                @click="deletePayment(data.id)"
                            >
                                <template #icon>
                                    <IconTrash class="w-5 h-5" />
                                </template>
                            </Button>
                        </template>
                    </Column>
                </DataTable>

                <div class="text-right font-semibold mt-2 text-white">
                    Montant restant: {{ remainingAmount }}$
                </div>
            </div>

            <!-- Dialog Ajouter Paiement -->
            <Dialog v-model:visible="showPaymentDialog" class="w-xs" header="Ajouter une transaction" modal>
                <div class="space-y-3">
                    <div>
                        <label class="block font-medium mb-1">Montant</label>
                        <InputNumber v-model="newPayment.amount" :min="0" class="w-full" currency="CAD" locale="fr-CA" mode="currency" />
                    </div>
                    <div>
                        <label class="block font-medium mb-1">Date</label>
                        <DatePicker
                            v-model="newPayment.date"
                            date-format="dd/mm/yy"
                            show-icon
                        />
                    </div>
                    <div>
                        <label class="block font-medium mb-1">Méthode</label>
                        <Select v-model="newPayment.method" :options="paymentMethods" class="w-full border rounded" optionLabel="name" optionValue="id" />
                    </div>
                    <div class="flex justify-end gap-2 mt-3">
                        <Button class="p-button-secondary" label="Annuler" @click="showPaymentDialog = false" />
                        <Button class="p-button-primary" label="Confirmer" @click="submitPayment" />
                    </div>
                </div>
            </Dialog>

        </div>
    </section>
</template>
