<script setup lang="ts">
import { IconTrashX, IconPencil } from '@tabler/icons-vue';
import {ref} from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import Panel from 'primevue/panel'
import Dialog from 'primevue/dialog'
import {Contact} from '@/interfaces/contact'
import Message from 'primevue/message';
import InputMask from 'primevue/inputmask';
import { Form } from '@primevue/forms';
import { z } from 'zod';
import { zodResolver } from '@primevue/forms/resolvers/zod';
import { useForm, usePage } from '@inertiajs/vue3';
import { useConfirm } from "primevue/useconfirm";
import { useToast } from 'primevue';

interface Props {
    contacts : Contact[]
}

const confirm = useConfirm();
const toast = useToast();

const props = defineProps<Props>()
const contactsRef = ref(props.contacts);
const editingRows = ref([]);

const form = useForm({
    id:0,
    given_name: '',
    family_name: '',
    email: '',
    phone: '',
    user_id: usePage().props.auth.user.id,
})

const type = ref('')
const editFormVisible = ref(false)

const zodSchema = z.object({
    given_name: z
        .string({ message: 'Le prénom est requis' })
        .min(2, { message: 'Minimum 2 caractères' })
        .max(50, { message: 'Maximum 50 caractères' }),

    family_name: z
        .string({ message: 'Le nom est requis' })
        .min(2, { message: 'Minimum 2 caractères' })
        .max(50, { message: 'Maximum 50 caractères' }),

    email: z
        .string()
        .email({ message: 'Adresse email invalide' })
        .optional()
        .or(z.literal('')),


    phone: z
        .string({ message: 'Téléphone requis' })
        .regex(/^\(\d{3}\) \d{3}-\d{4}$/, {
            message: 'Format attendu : (819) 201-2013',
        }),
});

const registrationValidationSchema = zodResolver(zodSchema);

const findIndexById = (id : number) => {
    let index = -1;
    for (let i = 0; i < contactsRef.value.length; i++) {
        if (contactsRef.value[i].id === id) {
            index = i;
            break;
        }
    }

    return index;
};

const updateRow = (event : any) => {
    console.log(event);
}

const editContact = (data : Contact) => {
    form.reset()
    form.clearErrors()
    type.value = 'edit'
    form.id = data.id
    form.given_name = data.given_name ?? ''
    form.family_name = data.family_name ?? ''
    form.email = data.email ?? ''
    form.phone = data.phone ?? ''
    editFormVisible.value = true
}
const createContact = () => {
    type.value = 'create'
    form.reset();
    form.clearErrors()
    editFormVisible.value = true
    console.log(form)
}

const confirmDeleteProduct = (data : any) => {
    confirm.require({
        group: 'confirmDel',
        header: 'Supprimer ' + data.family_name + ' ' + data.given_name + '?',
        message: 'Êtes vous sur de vouloir supprimer ce contacte secondaire?',
        rejectProps: {
            label: 'Annuler',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Supprimer',
            severity: 'danger'
        },
        accept: () => {
            useForm({id:data.id, user_id:data.user_id}).post('/contact/destroy', {
                onSuccess: () => {
                    contactsRef.value = contactsRef.value.filter(val => val.id !== data.id);
                    toast.add({
                        severity: 'success', summary: 'Succès', detail: 'le contact à été supprimer', life: 3000
                    })
                },
                onError: () => {
                    toast.add({
                        severity: 'error', summary: 'Erreur', detail: 'une erreur s\'est produite', life: 3000
                    })
                }
            })
        },
    });
}

const HandleEditSubmit = (formData : any) => {
    if (!formData.valid) return

    form.transform((data) => ({
        ...data,
        id: data.id,
        given_name: data.given_name.trim(),
        family_name: data.family_name.trim(),
        email: data.email.trim(),
        phone: data.phone.trim()
    }))

    if (type.value == 'create') {
        form.post('/contact/create', {
            onSuccess: (response) => {
                const flash = response.props.flash as { success: { id: number } }
                const newContact : Contact = {
                    id:flash.success.id,
                    given_name:form.given_name,
                    family_name:form.family_name,
                    email:form.email,
                    phone:form.phone
                }
                contactsRef.value.push(newContact)
                editFormVisible.value = false
                form.reset()
                toast.add({
                    severity: 'success', summary:"Succès", life:3000, detail:'Le contacte secondaire à été créer'
                })
            },
            onError: () => {
                toast.add({
                    severity: 'error', summary:"Erreur", life:3000, detail:'Une erreur s\'est produite lors de la création'
                })
            }
        })
    }
    else if (type.value == 'edit') {
        form.post('/contact/update', {
            onSuccess: () => {
                const contact : Contact = {
                    id:form.id,
                    given_name:form.given_name,
                    family_name:form.family_name,
                    email:form.email,
                    phone:form.phone
                }
                contactsRef.value[findIndexById(contact.id)] = contact;
                editFormVisible.value = false
                form.reset()
                toast.add({
                    severity: 'success', summary:"Succès", life:3000, detail:'Le contacte secondaire à été créer'
                })
            },
            onError: () => {
                toast.add({
                    severity: 'error', summary:"Erreur", life:3000, detail:'Une erreur s\'est produite lors de la modification'
                })
            }
        });
    }
}

</script>

<template>
    <Panel header="Contactes Secondaires">
        <div class="w-full flex justify-end">
            <Button @click="createContact">Ajouter un contact</Button>
        </div>
        <DataTable v-model:editing-rows="editingRows" :value="contactsRef" dataKey="id"
                   editMode="row" tableStyle="min-width: 50rem" @row-edit-save="updateRow">

            <template #empty> Aucun contact secondaire définit. </template>

            <Column header="Nom">
                <template #body="slotProps">
                    {{slotProps.data.family_name}} {{slotProps.data.given_name}}
                </template>
            </Column>
            <Column field="email" header="Courriel">
            </Column>
            <Column field="phone" header="N° Tel">
                <template #editor="{ data, field }">
                    <InputText v-model="data[field]" class="w-36"/>
                </template>
            </Column>
            <Column class="w-36">
                <template #body="slotProps">
                    <div class="flex justify-end gap-0 w-full">
                        <Button rounded variant="text" @click="editContact(slotProps.data)" >
                            <IconPencil />
                        </Button>
                        <Button rounded variant="text" severity="danger" @click="confirmDeleteProduct(slotProps.data)" >
                                <IconTrashX />
                        </Button>
                    </div>
                </template>
            </Column>
        </DataTable>
    </Panel>





    <Dialog modal header="Informations" v-model:visible="editFormVisible">
        <Form
            v-slot="$form"
            :initialValues="form"
            :validateOnBlur="true"
            :validateOnValueUpdate="false"
            :resolver="registrationValidationSchema"
            class="flex flex-col gap-4"
            @submit="HandleEditSubmit"
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

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="flex flex-col gap-2">
                    <label for="register-givenName" class="font-medium">
                        Prénom
                    </label>
                    <InputText
                        id="register-givenName"
                        v-model="form.given_name"
                        name="given_name"
                        type="text"
                        :disabled="form.processing"
                        :invalid="$form.given_name?.invalid"
                        class="w-full"
                        autocomplete="given-name"
                    />
                    <template v-if="$form.given_name?.invalid">
                        <Message
                            v-for="(error, index) of $form.given_name.errors"
                            :key="index"
                            severity="error"
                            size="small"
                            variant="simple"
                        >
                            {{ error.message }}
                        </Message>
                    </template>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="register-familyName" class="font-medium">
                        Nom
                    </label>
                    <InputText
                        id="register-familyName"
                        v-model="form.family_name"
                        name="family_name"
                        type="text"
                        :disabled="form.processing"
                        :invalid="$form.family_name?.invalid"
                        class="w-full"
                        autocomplete="family-name"
                    />
                    <template v-if="$form.family_name?.invalid">
                        <Message
                            v-for="(error, index) of $form.family_name.errors"
                            :key="index"
                            severity="error"
                            size="small"
                            variant="simple"
                        >
                            {{ error.message }}
                        </Message>
                    </template>
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <label for="register-email" class="font-medium"> Email </label>
                <InputText
                    id="register-email"
                    v-model="form.email"
                    name="email"
                    type="email"
                    :disabled="form.processing"
                    :invalid="$form.email?.invalid"
                    class="w-full"
                    autocomplete="email"
                />
                <template v-if="$form.email?.invalid">
                    <Message
                        v-for="(error, index) of $form.email.errors"
                        :key="index"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ error.message }}
                    </Message>
                </template>
            </div>

            <div class="flex flex-col gap-2">
                <label for="register-phone" class="font-medium">
                    Téléphone
                </label>
                <InputMask
                    id="register-phone"
                    v-model="form.phone"
                    name="phone"
                    mask="(999) 999-9999"
                    placeholder="(819) 201-2013"
                    :disabled="form.processing"
                    :invalid="$form.phone?.invalid"
                    class="w-full"
                    autocomplete="tel"
                />
                <template v-if="$form.phone?.invalid">
                    <Message
                        v-for="(error, index) of $form.phone.errors"
                        :key="index"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ error.message }}
                    </Message>
                </template>
            </div>
            <div class="flex justify-end gap-2">
                <Button
                    type="button"
                    severity="secondary"
                    label="Annuler"
                    @click="editFormVisible = false"
                    :loading="form.processing"
                    :disabled="form.processing"
                />
                <Button
                    type="submit"
                    label="Sauvegarder"
                    :loading="form.processing"
                    :disabled="form.processing"
                />
            </div>
        </Form>
    </Dialog>

</template>

<style scoped>

</style>
