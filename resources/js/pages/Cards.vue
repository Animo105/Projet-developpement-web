<script lang="ts" setup>
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { jsPDF } from 'jspdf';
import { debounce } from 'lodash';
import { computed, reactive, ref, watch } from 'vue';

// Prime Vue components
import { Form } from '@primevue/forms';
import { useToast } from 'primevue';
import Button from 'primevue/button';
import ConfirmDialog from 'primevue/confirmdialog';
import Dialog from 'primevue/dialog';
import FileUpload from 'primevue/fileupload';
import InputText from 'primevue/inputtext';
import Paginator from 'primevue/paginator';
import { useConfirm } from 'primevue/useconfirm';

// Self made components
import CardFilterForm from '@/components/Card/CardFilterForm.vue';
import Card from '../components/Card/Card.vue';
import CardCreate from '../components/Card/CardCreate.vue';

const page = usePage();
const adminPerms = page.props.auth.isAdmin;
const character = page.props.auth.character ?? 0;
const props = defineProps({
    cardList: Object,
    filterList: Object,
    searchText: String,
    isAdmin: Boolean,
    characterId: Number,
    nodeId: Number,
    isGiving: Boolean
});
console.log(props.isGiving);

const confirm = useConfirm();
const toast = useToast();

// Search ---------------------------------------------vv
// Stores the search terms
const search = ref(props.searchText || '');

// Delays the search to a bit after typing
const debouncedSearch = ref(search.value);
watch(
    search,
    debounce((val) => {
        debouncedSearch.value = val;
    }, 300)
);

// local updatable card list for filter and search
const cardList = ref(props.cardList || []);

// Show all cards or those matching search terms
const cardFilter = computed(() => {
    const q = debouncedSearch.value.toLowerCase().trim();
    if (!q) return cardList.value;
    return cardList.value.filter((card) =>
        card.title?.toLowerCase().includes(q)
    );
});

// pagination
const pageRef = ref(0);
const rowsPerPage = ref(adminPerms ? 14 : 15); //-1 pour admin for createdCard button, else 15

// manipulates cardFilter and splits the list into seperate pages
const paginatedCards = computed(() => {
    const start = pageRef.value * rowsPerPage.value;
    const end = start + rowsPerPage.value;
    return cardFilter.value.slice(start, end);
});

// updates page selected
function onPageChange(event) {
    pageRef.value = event.page;
    rowsPerPage.value = event.rows;
}

function handleGiveCard() {
    router.visit(`/cards/give/${(props.characterId)}`);
}

watch(cardFilter, () => {
    pageRef.value = 0;
});
// Search ---------------------------------------------^^

// Dialog stuff - on card click
const dialogCardVisible = ref(false);
const selectedCard = ref(null);

// Modification form
const dialogModifyVisible = ref(false);
const formInitialValues = ref(null); // initialized when form opened with previously existing data
const formCostValues = ref<{ [id: number]: number }>({}); // needed to dynamically add costs
const formCosts = ref<any[]>([]); // needed to dynamically add costs
const formPerkValues = ref<{ [id: number]: number }>({}); // needed to dynamically add perks
const formPerks = ref<any[]>([]); // needed to dynamically add Perks
const uploadedImage = ref<File | null>(null); // needed to upload image (not treated automatically)

// Create Form
const dialogCreateVisible = ref(false);

// Filter Form
const dialogFilterVisible = ref(false);
const modelValue = ref({});
const currentEvent = ref(0);
const stockNeeded = ref({});

// opens the dialog for card view
function openCardDialog(card: any) {
    selectedCard.value = card;
    dialogCardVisible.value = true;
}

// opens the dialog to modify the card
async function openModifyDialog(cardId: number) {
    try {
        // delete all errors
        Object.keys(errors).forEach((key) => delete errors[key]);

        // prepare data to prefill
        const response = await axios.get(`/admin/cards/${cardId}`);
        selectedCard.value = response.data;

        const cardTitle = selectedCard.value.title;

        // Métier: Prend le id du filterList métier et ressort les compositions valides
        const metierIds = props.filterList.metier.map((m) => m.id);
        const cardMetiers = selectedCard.value.compositions
            .filter((id) => metierIds.includes(id))
            .map((id) => props.filterList.metier.find((m) => m.id === id));

        const manaIds = props.filterList.mana.map((m) => m.id);
        const cardManas = selectedCard.value.compositions
            .filter((id) => manaIds.includes(id))
            .map((id) => props.filterList.mana.find((m) => m.id === id));

        // convert back into readable dictionary
        const cardCostValues = {};
        for (const cost of selectedCard.value.costs) {
            const [id, amount] = Object.entries(cost)[0];
            cardCostValues[id] = amount;
        }

        // attach the cost values with the cost
        const cardCosts = props.filterList.mana.filter((mana) =>
            Object.keys(cardCostValues).includes(mana.id.toString())
        );

        const cardType = props.filterList.type.find(
            (type) => type.id === selectedCard.value.type_id
        );
        const cardLevel = props.filterList.level[selectedCard.value.level_id];

        // convert back into readable dictionary
        const cardPerkValues = {};
        for (const perk of selectedCard.value.perks) {
            const [id, amount] = Object.entries(perk)[0];
            cardPerkValues[id] = amount;
        }

        const cardPerks = props.filterList.perks.filter((perk) =>
            Object.keys(cardPerkValues).includes(perk.id.toString())
        );

        // prefill all fields based on the card
        const initialValues = {
            id: cardId,
            title: cardTitle,
            metier: cardMetiers,
            mana: cardManas,
            type: cardType,
            level: cardLevel
        };
        formInitialValues.value = initialValues;
        formCostValues.value = cardCostValues;
        formCosts.value = cardCosts;
        formPerkValues.value = cardPerkValues;
        formPerks.value = cardPerks;

        dialogCardVisible.value = false;
        dialogModifyVisible.value = true;
    } catch (error) {
        console.error('Failed to fetch card data', error);
    }
}

const errors = reactive({});

function onImage(e: any) {
    uploadedImage.value = e.files[0];
}

function fileToBase64(file: File) {
    return new Promise<string>((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = () => resolve(reader.result as string);
        reader.onerror = (error) => reject(error);
    });
}

// Open the dialog to create a card
function openCreateDialog() {
    // delete all errors
    Object.keys(errors).forEach((key) => delete errors[key]);
    dialogCreateVisible.value = true;
}

function onModifySubmit(form: any) {
    onSubmit(form, '/admin/cards/update');
}

function onCreateSubmit(form: any) {
    onSubmit(form, '/admin/cards/store');
}

// async function for modify and create submits
async function onSubmit(form: any, url: string) {
    const cardId = form.states.id ? form.states.id.value : null;
    const cardTitle = form.states.title ? form.states.title.value : null;

    const metierIds = form.states.metier.value
        ? form.states.metier.value.map((m) => m.id)
        : null;
    const manaIds = form.states.mana.value
        ? form.states.mana.value.map((m) => m.id)
        : null;
    const metierMana = Array.prototype.concat(metierIds, manaIds);

    const costs = Object.entries(formCostValues.value).map(([id, amount]) => ({
        id: Number(id),
        amount
    }));
    const cardType = form.states.type.value ? form.states.type.value.id : null;
    const cardLevel = form.states.level ? form.states.level.value : null;
    const perks = Object.entries(formPerkValues.value).map(([id, amount]) => ({
        id: Number(id),
        amount
    }));

    let imageFile = null;
    if (uploadedImage?.value) {
        imageFile = await fileToBase64(uploadedImage.value);
    }

    const payload = {
        id: cardId,
        title: cardTitle,
        compositions: metierMana,
        costs: costs,
        type_id: cardType,
        level: cardLevel,
        perks: perks,
        image: imageFile
    };
    uploadedImage.value = null;

    try {
        // delete all errors
        console.log(url);
        Object.keys(errors).forEach((key) => delete errors[key]);
        if (url.includes('store')) {
            const response = await axios.post(url, payload);
            cardList.value = response.data.cardList;
            toast.add({
                severity: 'success',
                summary: 'Succès',
                detail: 'La carte a été ajoutée',
                life: 3000
            });
            dialogCreateVisible.value = false;
        } else if (url.includes('update')) {
            const response = await axios.put(url, payload);
            const index = cardList.value.findIndex(
                (i) => i.id === response.data.refreshedCard.id
            );
            cardList.value[index] = response.data.refreshedCard;
            toast.add({
                severity: 'success',
                summary: 'Succès',
                detail: 'La carte a été modifiée',
                life: 3000
            });
            dialogModifyVisible.value = false;
        }
        await router.reload({ only: ['cardList'] });
    } catch (error) {
        if (error.response && error.response.status === 422) {
            Object.assign(errors, error.response.data.errors);

            console.log('Validation errors:', errors);
        } else {
            console.error('Failed to put card data', error);
            toast.add({
                severity: 'error',
                summary: 'Erreur',
                detail: 'Une erreur est survenue',
                life: 3000
            });
        }
    }
}

function onDeleteSubmit(card: any) {
    console.log(card);
    confirm.require({
        group: 'confirmDel',
        header: 'Suppression d\'une carte?',
        message: `Êtes-vous certain de vouloir supprimer la carte "${card.title}" ?`,
        rejectProps: {
            label: 'Annuler',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Supprimer',
            severity: 'danger'
        },
        accept: async () => {
            try {
                const response = await axios.delete(
                    `/admin/cards/destroy/${card.id}`
                );
                dialogModifyVisible.value = false;
                cardList.value = response.data['cardList'];
                toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'La carte a été supprimé',
                    life: 3000
                });
            } catch (error) {
                console.error('Failed to put card data', error);
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Une erreur est survenue',
                    life: 3000
                });
            }
        }
    });
}

function updateNewValues({ costs, perks }) {
    formCostValues.value = costs;
    formPerkValues.value = perks;
}

function openFilterDialog() {
    dialogFilterVisible.value = true;
}

async function submitFilters(form: any) {
    const metierMana = Array.prototype.concat(
        form.metier || [],
        form.mana || []
    );

    const payload = {
        characterId: character.id,
        event: form.events,
        compositions: metierMana,
        types: form.type,
        levels: form.level,
        perks: form.perk
    };
    try {
        const response = await axios.post('/cards/filters', payload);
        cardList.value = response.data['cardList'];
        currentEvent.value = response.data['currentEvent'];
        stockNeeded.value = response.data['stockNeeded'];

        dialogFilterVisible.value = false;

        // resets search
        search.value = '';
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: 'Une erreur est survenue',
            life: 3000
        });
    }
}

function generateCardPDF() {
    // get missing cards, their images and their missing counts
    const missingCards = [];

    for (const card of cardFilter.value) {
        if (card.stock < stockNeeded.value[card.id]) {
            const printNeeded = {
                id: card.id,
                title: card.title,
                image_path: '/storage/' + card.image_path,
                printCount: stockNeeded.value[card.id] - card.stock
            };
            missingCards.push(printNeeded);
        }
    }

    const doc = new jsPDF({
        unit: 'in'
    });

    let count = 0;
    let totalPrint = 0;
    for (const card of missingCards) {
        totalPrint += card.printCount;
    }

    const x = [
        0.4, 2.89031, 5.37562, 0.4, 2.89031, 5.37562, 0.4, 2.89031, 5.37562
    ];
    const y = [
        0.5, 0.5, 0.5, 3.97531, 3.97531, 3.97531, 7.44988, 7.44988, 7.44988
    ];
    const width = 2.48031;
    const height = 3.46457;

    for (const card of missingCards) {
        for (let i = 0; i < card.printCount; i++) {
            doc.addImage(
                card.image_path,
                'png',
                x[count % 9],
                y[count % 9],
                width,
                height,
                card.title,
                undefined,
                0
            );

            if (count % 9 === 8 && totalPrint > count + 1) {
                doc.addPage();
            }
            count++;
        }
    }

    const date = new Date().toLocaleDateString();
    doc.save('event_' + currentEvent.value + '_missingCards_' + date);
}

function handleAttachCard(cardId: number) {
    router.post(`/admin/skilltree/attach/${cardId}/${props.nodeId}`);
}

async function handleGivenCard(cardId: number) {
    dialogCardVisible.value = false;
    await axios.post(`/admin/cards/give/${props.characterId}/${cardId}`, []);
    localStorage.setItem(
        'toast-success',
        'La carte a été donnée au personnage'
    );
    window.location.href = '/cards';
}
</script>

<template>
    <Head :title="` | ${$page.component}`" />

    <div class="w-full">
        <div class="grid grid-cols-2 gap-4 pt-2 pb-4">
            <!-- Filter Button -->
            <div class="flex justify-start">
                <button
                    class="w-full rounded-md bg-gray-600 px-4 py-2 font-semibold text-white shadow-sm transition duration-150 ease-in-out hover:bg-blue-700 focus:ring-2 focus:ring-blue-400 focus:ring-offset-1 focus:outline-none"
                    @click="openFilterDialog"
                >
                    Filtrer
                </button>
            </div>

            <!-- Search Input -->
            <div class="flex justify-end">
                <input
                    v-model="search"
                    class="w-full rounded-md border border-gray-300 px-4 py-2 text-center placeholder-gray-400 shadow-sm transition duration-150 ease-in-out focus:border-blue-400 focus:ring-1 focus:ring-blue-400 focus:outline-none"
                    placeholder="Rechercher"
                    type="search"
                />
            </div>
            <!-- Give Card Button -->
            <div
                v-if="adminPerms && characterId != 0"
                class="col-span-2 flex w-full justify-start"
            >
                <button
                    v-if="props.isGiving == false"
                    class="w-full rounded-md bg-gray-600 px-4 py-2 font-semibold text-white shadow-sm transition duration-150 ease-in-out hover:bg-blue-700 focus:ring-2 focus:ring-blue-400 focus:ring-offset-1 focus:outline-none"
                    @click="handleGiveCard"
                >
                    Donner une carte
                </button>
            </div>

            <div v-if="currentEvent != 0" class="col-span-2">
                <Button
                    class="w-full"
                    label="Génération d'un PDF des cartes manquantes pour l'évènement sélectionné"
                    severity="info"
                    @click="generateCardPDF"
                ></Button>
            </div>
        </div>

        <div class="pb-3">
            <Paginator
                :first="pageRef * rowsPerPage"
                :rows="rowsPerPage"
                :totalRecords="cardFilter.length"
                class="mt-4"
                @page="onPageChange"
            />
        </div>
        <div class="cards grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-5">
            <span
                v-if="adminPerms && props.characterId === 0 && currentEvent === 0"
                class="w-full"
                @click="openCreateDialog"
            >
                <Button class="h-full w-full" severity="secondary">
                    Création d'une carte
                </Button>
            </span>
            <Card
                v-for="card in paginatedCards"
                :key="card.id"
                :adminCheck="adminPerms"
                :card="card"
                :characterId="props.characterId"
                :currentEvent="currentEvent"
                :stockNeeded="stockNeeded"
                @click="openCardDialog(card)"
            />
        </div>

        <div class="pt-3">
            <Paginator
                :first="pageRef * rowsPerPage"
                :rows="rowsPerPage"
                :totalRecords="cardFilter.length"
                class="mt-4"
                @page="onPageChange"
            />
        </div>

        <Dialog
            v-model:visible="dialogCardVisible"
            :closable="true"
            :dismissable-mask="true"
            :header="selectedCard?.title"
            modal
        >
            <div v-show="selectedCard">
                <img
                    :src="
                        selectedCard.image_path
                            ? '/storage/' + selectedCard.image_path
                            : ''
                    "
                    class="mx-auto w-full rounded sm:max-w-xs md:max-w-sm lg:max-w-md"
                />
                <p class="mt-2">{{ selectedCard.description }}</p>
                <Button
                    v-if="props.nodeId"
                    class="w-full"
                    label="Attacher au noeud"
                    rounded
                    severity="secondary"
                    @click="handleAttachCard(selectedCard.id)"
                />
                <Button
                    v-if="props.isGiving == true"
                    class="w-full"
                    label="Donner au personnage"
                    rounded
                    @click="handleGivenCard(selectedCard.id)"
                />
                <Button
                    v-if="props.isGiving === false && (props.nodeId === undefined || props.nodeId === 0)"
                    class="w-full"
                    label="Voir le noeud"
                    rounded
                />
                <Button
                    v-if="adminPerms && props.isGiving === false && (props.nodeId === undefined || props.nodeId === 0)"
                    class="w-full"
                    label="Modifier"
                    rounded
                    severity="warn"
                    @click="openModifyDialog(selectedCard.id)"
                />
            </div>
        </Dialog>

        <Dialog
            v-model:visible="dialogModifyVisible"
            :closable="true"
            :dismissable-mask="true"
            header="Modification"
            modal
        >
            <div v-if="selectedCard">
                <Form
                    v-if="formInitialValues"
                    v-slot="$form"
                    :cardId="selectedCard.id"
                    :initial-values="formInitialValues"
                    @submit="onModifySubmit"
                >
                    <InputText name="id" style="display: none" />

                    <CardCreate
                        v-model:costValues="formCostValues"
                        v-model:costs="formCosts"
                        v-model:perkValues="formPerkValues"
                        v-model:perks="formPerks"
                        :errors="errors"
                        :filterList="filterList"
                        :form="$form"
                        @update-values="updateNewValues"
                    />

                    <div>
                        <span>Image de la carte</span>
                        <FileUpload
                            :auto="false"
                            :maxFileSize="5000000"
                            accept=".png,.jpg.,jpeg"
                            mode="basic"
                            url="/api/upload"
                            @select="onImage"
                        />
                    </div>

                    <Button class="w-full" label="Modifier" type="submit" />
                </Form>
                <Form @submit="onDeleteSubmit(selectedCard)">
                    <i class="form__info"
                    >La suppression de la carte est irréversible.</i
                    >
                    <Button
                        class="w-full"
                        label="Supprimer"
                        severity="danger"
                        type="submit"
                    />
                </Form>
            </div>
        </Dialog>

        <Dialog
            v-model:visible="dialogCreateVisible"
            :closable="true"
            :dismissable-mask="true"
            header="Création"
            modal
        >
            <Form v-slot="$form" @submit="onCreateSubmit">
                <CardCreate
                    :errors="errors"
                    :filterList="filterList"
                    :form="$form"
                    @update-values="updateNewValues"
                />

                <div class="w-full pt-3">
                    <span>Image de la carte</span>
                    <FileUpload
                        :auto="false"
                        :maxFileSize="5000000"
                        accept=".png,.jpg.,jpeg"
                        mode="basic"
                        url="/api/upload"
                        @select="onImage"
                    />
                </div>

                <Button class="mt-3 w-full" label="Créer" type="submit" />
            </Form>
        </Dialog>

        <Dialog
            v-model:visible="dialogFilterVisible"
            :closable="true"
            :dismissable-mask="true"
            header="Filtrer"
            modal
        >
            <CardFilterForm
                :adminCheck="adminPerms"
                :filterList="props.filterList"
                :characterId="props.characterId||0"
                :modelValue="modelValue"
                @submit="submitFilters"
                @update:modelValue="(val) => (modelValue = val)"
            />
        </Dialog>
    </div>

    <ConfirmDialog group="confirmDel">
        <template #message="popup">
            <p>
                {{ popup.message.message }} <br />
                Cette action est irréversible.
            </p>
        </template>
    </ConfirmDialog>
</template>
