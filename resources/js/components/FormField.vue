<script setup lang="ts">
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import InputMask from 'primevue/inputmask';
import Textarea from 'primevue/textarea';
import Message from 'primevue/message';

interface Props {
    name: string;
    label: string;
    form: any;
    type?: string;
    placeholder?: string;
    mask?: string;
    disabled?: boolean;
    showStrength?: boolean;
    rows?: number;
    maxDate?: string;
}

const props = defineProps<Props>();

const getComponent = () => {
    if (props.mask) return InputMask;
    if (props.type === 'password') return Password;
    if (props.type === 'textarea') return Textarea;
    return InputText;
};
</script>

<template>
    <div class="flex flex-col gap-1">
        <label :for="`input-${name}`" class="font-medium">{{ label }}</label>

        <component
            :is="getComponent()"
            :id="`input-${name}`"
            :name="name"
            :placeholder="placeholder"
            :mask="mask"
            :rows="rows"
            :max="maxDate"
            :feedback="showStrength || false"
            :toggleMask="type === 'password'"
            :disabled="disabled"
            :invalid="form[name]?.invalid"
            :type="type !== 'password' && type !== 'textarea' ? type : undefined"
            fluid
            autocomplete="off"
        >
            <template v-for="(_, slotName) in $slots" #[slotName]>
                <slot :name="slotName" />
            </template>
        </component>

        <Message
            v-for="(err, i) in form[name]?.errors"
            :key="i"
            severity="error"
            size="small"
            variant="simple"
        >
            {{ err.message }}
        </Message>
    </div>
</template>
