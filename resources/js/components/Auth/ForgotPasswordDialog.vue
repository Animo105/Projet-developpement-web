<script setup lang="ts">
import { ref } from 'vue';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import { Form } from '@primevue/forms';

interface Props {
    visible: boolean;
}

interface Emits {
    (e: 'update:visible', value: boolean): void;
    (e: 'submit', email: string): void;
}

defineProps<Props>();
const emit = defineEmits<Emits>();

const email = ref('');

const handleSubmit = () => {
    emit('submit', email.value);
};
</script>

<template>
    <Dialog
        :visible="visible"
        @update:visible="emit('update:visible', $event)"
        modal
        header="Mot de passe oublié"
        :style="{ width: '90vw', maxWidth: '500px' }"
        :breakpoints="{ '960px': '75vw', '640px': '90vw' }"
    >
        <p class="mb-4 text-surface-600 dark:text-surface-400">
            Entrez votre adresse email et nous vous enverrons un lien pour réinitialiser votre mot de passe.
        </p>

        <Form @submit="handleSubmit" class="flex flex-col gap-4">
            <div class="flex flex-col gap-2">
                <label for="forgot-email" class="font-medium">Email</label>
                <InputText
                    id="forgot-email"
                    v-model="email"
                    name="email"
                    type="email"
                    required
                    aria-required="true"
                    placeholder="exemple@exemple.com"
                    class="w-full"
                />
            </div>

            <Button
                label="Envoyer le lien"
                type="submit"
                class="mt-2 w-full"
            />
        </Form>
    </Dialog>
</template>
