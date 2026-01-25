<script setup lang="ts">
import {
    Button,
    CascadeSelect,
    Dialog,
    Galleria,
    IftaLabel,
    Image,
    InputText,
    Select,
    useToast,
} from 'primevue';
import { computed, reactive, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { useConfirm } from 'primevue/useconfirm';
import ConfirmDialog from 'primevue/confirmdialog';
import FileUpload from 'primevue/fileupload';
import { Form } from '@primevue/forms';
import Message from 'primevue/message';

const page = usePage();
const adminPerms = page.props.auth.isAdmin;
const props = defineProps({
    albumImages: Object,
});
const confirm = useConfirm();
const toast = useToast();
const errors = reactive({});
const uploadedImage = ref<File | null>(null); // needed to upload image (not treated automatically)
const checkFileValidity = computed(() => {
    if (!uploadedImage.value) {
        return false;
    }
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    return allowedTypes.includes(uploadedImage.value.type);
});

//// dynamics for updates and display
const imagePerAlbum = ref(props.albumImages || []);
// all images if no year selected
const allImages = computed(() => {
    const array = [];
    for (const album of imagePerAlbum.value) {
        for (const image of album.images) {
            if (image.picture_path) {
                array.push(image);
            }
        }
    }
    return array.reverse() || [];
});
// seperates albums per years
const yearList = computed(() => {
    return Object.values(
        (imagePerAlbum.value || []).reduce((object, album) => {
            if (!object[album.year]) {
                object[album.year] = {
                    label: album.year,
                    value: album.year,
                    albums: [],
                };
            }

            object[album.year].albums.push({
                label: album.name,
                value: album.id,
            });

            return object;
        }, {}),
    );
});
// the selector for the cascadeSelect
const cascadeSelector = computed(() => {
    const reversed = yearList.value.reverse();

    const addAllOption = {
        label: 'Tous',
        value: 0,
    };

    const newArray = [];
    newArray.push(addAllOption);
    for (const year of reversed) {
        newArray.push(year);
    }

    return newArray;
});
// the images that are being displayed in the galleria
const selectedAlbumImages = computed(() => {
    if (!selectedAlbum.value || !imagePerAlbum.value) return [];
    const album = imagePerAlbum.value.find(
        (album) => album.id === selectedAlbum.value,
    );
    return album?.images || [];
});

// current album
const selectedAlbum = ref(0);
const selectedYear = ref(0);
const selectYearList = ref([]);
const galleriaIndex = ref(0);

// fills selectedYearList for album creation.
function getYear() {
    const currentYear = new Date().getFullYear();
    let counter = 2010;
    const yearArray = [];

    while (counter < currentYear +1) {
        const newYearObject = {
            year: counter,
        }
        yearArray.push(newYearObject);
        counter++;
    }

    selectedYear.value = currentYear;

    return yearArray.reverse();
}

function albumLength(){
    const albumIndex = imagePerAlbum.value.findIndex((album) => album.id === selectedAlbum.value);
    return imagePerAlbum.value[albumIndex].images.length;
}

//// Dialog
const dialogImageVisible = ref(false);
const selectedImage = ref(null);

const dialogAddImageVisible = ref(false);
function openImageDialog(image: any) {
    dialogImageVisible.value = true;
    selectedImage.value = image;
}

function newTabImage(image: any) {
    const imageUrl = '/storage/' + image.picture_path;
    window.open(imageUrl);
}

function deleteImage(image: any) {
    const albumArrayIndex = imagePerAlbum.value.findIndex(
        (album) => album.id === image.album_id,
    );

    confirm.require({
        group: 'confirmDel',
        header: "Suppression de l'image?",
        message: "Êtes-vous certain de vouloir supprimer l'image sélectionné?",
        rejectProps: {
            label: 'Annuler',
            severity: 'secondary',
            outlined: true,
        },
        acceptProps: {
            label: 'Supprimer',
            severity: 'danger',
        },
        accept: async () => {
            try {
                const response = await axios.delete(
                    `/gallery/destroy/picture/${image.id}`,
                );
                dialogImageVisible.value = false;

                // otherwise galleria disappears
                if (galleriaIndex.value > 0) {
                    galleriaIndex.value--;
                }
                else {
                    galleriaIndex.value = 0;
                }

                imagePerAlbum.value[albumArrayIndex].images =
                    response.data['images'];

                toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: "L'image a été supprimé",
                    life: 3000,
                });
            } catch (error) {
                console.error('Failed to put image data', error);
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Une erreur est survenue',
                    life: 3000,
                });
            }
        },
    });
}

function openAddImageDialog() {
    // delete all errors
    Object.keys(errors).forEach((key) => delete errors[key]);

    dialogAddImageVisible.value = true;
}

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

async function onAddImageSubmit() {
    const albumArrayIndex = imagePerAlbum.value.findIndex(
        (album) => album.id === selectedAlbum.value,
    );

    let imageFile = null;
    if (uploadedImage?.value) {
        imageFile = await fileToBase64(uploadedImage.value);
    }

    const payload = {
        image: imageFile,
    };
    uploadedImage.value = null;

    try {
        const response = await axios.post(
            `gallery/store/${selectedAlbum.value}`,
            payload,
        );

        dialogAddImageVisible.value = false;
        imagePerAlbum.value[albumArrayIndex].images = response.data['images'];

        galleriaIndex.value = albumLength() -1;

        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: "L'image a été ajouté",
            life: 3000,
        });
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
                life: 3000,
            });
        }
    }
}

const dialogCreateAlbumVisible = ref(false);
function openCreateAlbumDialog() {
    // delete all errors
    Object.keys(errors).forEach((key) => delete errors[key]);
    selectYearList.value = getYear();

    dialogCreateAlbumVisible.value = true;
}

async function onCreateAlbumSubmit(form: any) {
    const payload = {
        year: Number(selectedYear.value),
        name: form.states.name.value,
    };

    try {
        const response = await axios.post('/gallery/newAlbum', payload);
        imagePerAlbum.value = response.data['albumImages'];
        selectedAlbum.value = response.data['album_id'];
        dialogCreateAlbumVisible.value = false;

        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: "L'album a été ajouté",
            life: 3000,
        });
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
                life: 3000,
            });
        }
    }
}

function deleteAlbum(id : number) {
    confirm.require({
        group: 'confirmDel',
        header: "Suppression de l'album?",
        message: "Êtes-vous certain de vouloir supprimer l'album sélectionné?",
        rejectProps: {
            label: 'Annuler',
            severity: 'secondary',
            outlined: true,
        },
        acceptProps: {
            label: 'Supprimer',
            severity: 'danger',
        },
        accept: async () => {
            try {
                const response = await axios.delete(
                    `/gallery/destroy/${id}`,
                );
                selectedAlbum.value = 0;
                imagePerAlbum.value = imagePerAlbum.value.filter(album => album.id !== id);
                toast.add({
                    severity: 'success',
                    summary: 'Succès',
                    detail: "L'album a été supprimé",
                    life: 3000,
                });
            } catch (error) {
                console.error('Failed to put image data', error);
                toast.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Une erreur est survenue',
                    life: 3000,
                });
            }
        },
    });
}
</script>

<template>
    <Head :title="` | ${$page.component}`" />

    <div class="grid grid-cols-2 gap-2 py-3">
        <IftaLabel>
            <CascadeSelect
                class="w-full"
                v-model="selectedAlbum"
                id="csAlbum"
                :options="cascadeSelector"
                optionLabel="label"
                optionValue="value"
                optionGroupLabel="label"
                :optionGroupChildren="['albums']"
                placeholder="Choisir un album"
            />
            <label for="csAlbum">Album</label>
            <!-- must be after element -->
        </IftaLabel>
        <Button
            v-if="adminPerms"
            label="Création d'un album"
            class="w-full"
            @click="openCreateAlbumDialog"
        />
    </div>

    <div
        v-if="adminPerms && selectedAlbum !== 0"
        class="grid grid-cols-2 gap-2 py-3"
    >
        <Button label="Supprimer l'album" severity="danger" class="w-full" @click="deleteAlbum(selectedAlbum)"/>
        <Button
            label="Ajouter une image"
            class="w-full"
            @click="openAddImageDialog"
        />
    </div>
    <div v-else-if="adminPerms && selectedAlbum === 0" class="py-3 text-center">
        <i
            >Veuillez choisir un album pour ajouter une image ou supprimer un
            album</i
        >
    </div>

    <div v-if="selectedAlbum !== 0 && albumLength() > 0" class="flex justify-center">
        <div class="w-full">
            <!-- Key seems needed otherwise changing album too fast breaks?-->
            <Galleria
                v-if="selectedAlbumImages.length"
                :key="selectedAlbum"
                :value="selectedAlbumImages"
                v-model:active-index="galleriaIndex"
                :numVisible="5"
                containerStyle="max-width: 100%"
                :showThumbnails="false"
                :showIndicators="true"
                :changeItemOnIndicatorHover="true"
                :showIndicatorsOnItem="true"
                indicatorsPosition="top"
            >
                <template #item="slotProps">
                    <img
                        v-if="slotProps.item && slotProps.item.picture_path"
                        :src="slotProps.item.picture_path"
                        :alt="slotProps.item.alt"
                        style="width: 100%; display: block"
                        @click="openImageDialog(slotProps.item)"
                    />
                </template>
            </Galleria>
        </div>
    </div>
    <div v-else-if="selectedAlbum !==0 && albumLength() === 0" class="text-center"> <i>Aucun image dans l'album.</i></div>
    <div v-else-if="selectedAlbum === 0" class="columns-3 gap-2">
        <div v-for="image in allImages" :key="image.id" class="mb-2">
            <Image
                :src="
                    image.picture_path ? '/storage/' + image.picture_path : ''
                "
                @click="openImageDialog(image)"
            />
        </div>
    </div>

    <Dialog
        v-model:visible="dialogCreateAlbumVisible"
        header="Création d'un album"
        modal
        :closable="true"
        :dismissable-mark="true"
    >
        <Form v-slot="$form" @submit="onCreateAlbumSubmit">
            <div class="items-center py-1">
                <IftaLabel>
                    <Select
                        id="yearSelector"
                        name="year"
                        v-model="selectedYear"
                        :options="selectYearList"
                        option-label="year"
                        option-value="year"
                        class="w-full"
                    />
                    <label for="yearSelector">Année</label>
                    <Message
                        v-if="errors?.year"
                        severity="error"
                        size="small"
                        variant="simple"
                    >{{ errors.year[0] }}</Message
                    >
                </IftaLabel>
            </div>
            <div class="items-center py-1">
                <IftaLabel>
                    <InputText name="name" type="text" id="name" class="w-full"/>
                    <label for="name">Nom de l'album</label>
                    <Message
                        v-if="errors?.name"
                        severity="error"
                        size="small"
                        variant="simple"
                    >{{ errors.name[0] }}</Message
                    >
                </IftaLabel>

            </div>
            <Button class="w-full" label="Ajouter" type="submit" />
        </Form>
    </Dialog>

    <Dialog
        v-model:visible="dialogAddImageVisible"
        header="Ajout d'une image"
        modal
        :closable="true"
        :dismissable-mask="true"
    >
        <Form @submit="onAddImageSubmit">
            <div class="w-full pt-3">
                <FileUpload
                    mode="basic"
                    url="/api/upload"
                    accept=".png,.jpg.,jpeg"
                    :maxFileSize="5000000"
                    :auto="false"
                    @select="onImage"
                />
            </div>

            <Button
                class="w-full"
                label="Ajouter"
                type="submit"
                :disabled="!checkFileValidity"
            />
        </Form>
    </Dialog>

    <Dialog
        v-model:visible="dialogImageVisible"
        :show-header="false"
        modal
        :closable="true"
        :dismissable-mask="true"
    >
        <div v-if="selectedImage" class="py-3">
            <Image
                class="mx-auto w-full rounded sm:max-w-xs md:max-w-sm lg:max-w-md"
                :src="
                    selectedImage.picture_path
                        ? '/storage/' + selectedImage.picture_path
                        : ''
                "
            />
        </div>

        <div class="grid grid-cols-2 gap-3">
            <Button
                v-if="adminPerms"
                label="Supprimer l'image"
                severity="danger"
                class="w-full"
                @click="deleteImage(selectedImage)"
            />
            <Button
                label="Ouvrir dans un nouvelle onglet"
                :class="adminPerms ? 'w-full' : 'col-span-2 w-full'"
                @click="newTabImage(selectedImage)"
            />
        </div>
    </Dialog>

    <ConfirmDialog group="confirmDel">
        <template #message="popup">
            <p>
                {{ popup.message.message }} <br />
                Cette action est irréversible.
            </p>
        </template>
    </ConfirmDialog>
</template>

<style scoped></style>
