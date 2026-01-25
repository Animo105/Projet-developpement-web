<script setup lang="ts">
import DatePicker from 'primevue/datepicker';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import TextArea from 'primevue/textarea';
import Message from 'primevue/message';
import { reactive, ref } from 'vue';
import Select from 'primevue/select'
import { EventShort } from '@/interfaces/event'
interface Props {
    form: any
    events:EventShort[]
}
const props = defineProps<Props>();
const formRef = reactive(props.form);

const date = ref<Date[]>([]);
if (props.form.start_date) {
    date.value.push(props.form.start_date);
}
if (props.form.end_date) {
    date.value.push(props.form.end_date);
}

const updateDate = () => {
    if (date.value) {
        formRef.start_date = date.value[0];
        formRef.end_date = date.value[1];
    }
};

const selected_id = ref()

props.events.forEach((event : EventShort) => {
    if (event.id === props.form.event_linked) {
        selected_id.value = event
    }
})

const updateEvent = () => {
    if (selected_id.value) {
        formRef.event_linked = selected_id.value.id
    }
    else {
        console.log("error while change event_id")
    }
};
</script>

<template>
    <div class="flex gap-5">
        <div class="flex flex-col">
            <label>Date de l'évènement*</label>
            <DatePicker
                v-model="date"
                selection-mode="range"
                :min-date="new Date()"
                class="date-picker"
                inline
                @update:model-value="updateDate"
            />
            <Message
                v-if="formRef.start_date?.invalid"
                severity="error"
                size="small"
                variant="simple"
            >{{ formRef.start_date.error?.message }}</Message
            >
        </div>
        <div class="w-full flex flex-col justify-between">
            <div>
                <label class="mb-1 block font-medium">Titre de l'évènement*</label>
                <InputText v-model="formRef.title" class="w-full" maxlength="50" />
                <Message
                    v-if="formRef.title?.invalid"
                    severity="error"
                    size="small"
                    variant="simple"
                >{{ formRef.title.error?.message }}</Message
                >
            </div>
            <div>
                <label class="mb-1 block font-medium">Description de l'évènement</label>
                <TextArea
                    v-model="formRef.description"
                    class="text-area"
                    maxlength="255"
                />
                <Message
                    v-if="formRef.description?.invalid"
                    severity="error"
                    size="small"
                    variant="simple"
                >{{ formRef.description.error?.message }}</Message
                >
            </div>
            <div class="flex flex-col w-full">
                <label>Date limite pour s'inscrire*</label>
                <DatePicker
                    v-model="formRef.date_registration"
                    :min-date="new Date()"
                    class="date-registration"
                    :manualInput="false"
                    show-icon
                />
            </div>
        </div>
    </div>
    <Message
        v-if="formRef.date_registration?.invalid"
        severity="error"
        size="small"
        variant="simple"
        >{{ formRef.date_registration.error?.message }}</Message
    >

    <!-- Prix -->
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="mb-1 block font-medium">Prix participant*</label>
            <InputNumber
                v-model="formRef.participant_price"
                mode="currency"
                currency="CAD"
                locale="fr-CA"
                class="w-full"
            />
            <Message
                v-if="formRef.participant_price?.invalid"
                severity="error"
                size="small"
                variant="simple"
                >{{ formRef.participant_price.error?.message }}</Message
            >
        </div>

        <div>
            <label class="mb-1 block font-medium">Prix accompagnateur</label>
            <InputNumber
                v-model="formRef.accompagnateur_price"
                mode="currency"
                currency="CAD"
                locale="fr-CA"
                class="w-full"
            />
            <Message
                v-if="formRef.accompagnateur_price?.invalid"
                severity="error"
                size="small"
                variant="simple"
                >{{ formRef.accompagnateur_price.error?.message }}</Message
            >
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="mb-1 block font-medium">Prix arme</label>
            <InputNumber
                v-model="formRef.weapon_price"
                mode="currency"
                currency="CAD"
                locale="fr-CA"
                class="w-full"
            />
            <Message
                v-if="formRef.weapon_price?.invalid"
                severity="error"
                size="small"
                variant="simple"
                >{{ formRef.weapon_price.error?.message }}</Message
            >
        </div>

        <div>
            <label class="mb-1 block font-medium">Prix costume</label>
            <InputNumber
                v-model="formRef.costume_price"
                mode="currency"
                currency="CAD"
                locale="fr-CA"
                class="w-full"
            />
            <Message
                v-if="formRef.costume_price?.invalid"
                severity="error"
                size="small"
                variant="simple"
                >{{ formRef.costume_price.error?.message }}</Message
            >
        </div>
    </div>
    <div>
        <label class="mb-1 block font-medium">Rabais de fratrie</label>
        <InputNumber
            v-model="formRef.rabais_fratrie"
            mode="currency"
            currency="CAD"
            locale="fr-CA"
            class="w-full"
        />
        <Message
            v-if="formRef.rabais_fratrie?.invalid"
            severity="error"
            size="small"
            variant="simple"
        >{{ formRef.rabais_fratrie.error?.message }}</Message
        >
    </div>

    <div class="grid grid-cols-2 gap-4 mt-5">
        <div>
            <label class="mb-1 block font-medium">Évènement lié</label>
            <Select v-model="selected_id" :options="events" optionLabel="name" class="w-full" @change="updateEvent"/>
        </div>
        <div>
            <label class="mb-1 block font-medium">Rabais d'évènement</label>
            <InputNumber
                v-model="formRef.rabais_event"
                mode="currency"
                currency="CAD"
                locale="fr-CA"
                class="w-full"
            />
            <Message
                v-if="formRef.rabais_event?.invalid"
                severity="error"
                size="small"
                variant="simple"
            >{{ formRef.rabais_event.error?.message }}</Message>
        </div>
    </div>
</template>

<style scoped>
.text-area {
    width: 100%;
    resize: none;
    height: 10rem;
}
.date-picker {
    width: 25rem;
}
.date-registration {
    width: 25rem;
}
</style>
