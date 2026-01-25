<script setup lang="ts">
import { ref, watch } from 'vue';
import type { SkillNode } from '@/types/skilltree';
import Dialog from 'primevue/dialog';
import Carousel from 'primevue/carousel';
import Image from 'primevue/image';
import Button from 'primevue/button';
import ConfirmDialog from 'primevue/confirmdialog';
import { useConfirm } from 'primevue/useconfirm';
import AdminTools from '@/components/SkillTree/AdminTools.vue';

// --- PROPS & EMITS ---
const props = defineProps<{
    isAdmin: boolean;
    node: SkillNode;
    nodes: SkillNode[];
    skillPoints: number;
    isUnlocking: boolean;
    visible: boolean;
    selectedManaId: number;
    selectedClassId: number;
}>();

const emit = defineEmits(['update:visible', 'unlock', 'highlight-blocker']);

// --- CONFIRMATION LOGIC ---
const confirm = useConfirm();
const currentCardIndex = ref(0);

watch(() => props.visible, (newValue) => {
    if (newValue) {
        currentCardIndex.value = 0;
    }
});

function handleUnlockClick() {
    const pointsRestants = props.skillPoints - 1;

    confirm.require({
        header: 'Confirmer le déblocage',
        message: `Ce nœud va coûter 1 point de compétence.
        Vous avez actuellement ${props.skillPoints} points,
        il vous en restera ${pointsRestants}.
        Une fois débloqué, il ne sera pas possible de revenir sur ce choix.`,
        rejectLabel: 'Annuler',
        acceptLabel: 'Confirmer',
        rejectClass: 'p-button-secondary',
        accept: () => {
            emit('unlock', props.node);
        },
    });
}

function handleCarouselPageChange(page: number) {
    currentCardIndex.value = page;
}

</script>

<template>
    <div>
        <Dialog
            :visible="visible"
            @update:visible="emit('update:visible', $event)"
            modal
            :closable="false"
            dismissableMask
            class="max-w-md w-fit"
        >
            <template v-if="node.cards.length > 0">
                <Carousel
                    :value="node.cards"
                    :circular="node.cards.length > 1"
                    :numVisible="1"
                    :numScroll="1"
                    :showNavigators="node.cards.length > 1"
                    :showIndicators="node.cards.length > 1"
                    @update:page="handleCarouselPageChange"
                >
                    <template #item="slotProps">
                        <Image
                            :src="`/storage/${slotProps.data.img}`"
                            :alt="slotProps.data.title"
                            preview
                            imageClass="rounded-xl"
                        />
                    </template>
                </Carousel>
                <Button
                    v-if="node.state === 'available'"
                    :label="skillPoints <= 0 ? 'Pas assez de points de compétence' : 'Débloquer'"
                    fluid
                    @click="handleUnlockClick"
                    :loading="isUnlocking"
                    :disabled="isUnlocking || skillPoints <= 0"
                />
            </template>
            <div v-else class="text-center py-8 text-gray-500">
                Aucune carte disponible pour ce nœud
            </div>
            <Button
                v-if="node.state === 'locked'"
                label="Voir le Nœud bloquant"
                fluid
                severity="secondary"
                @click="emit('highlight-blocker', node)"
            />
            <AdminTools
                v-if="isAdmin"
                :node="node"
                :nodes="nodes"
                :selected-class-id="selectedClassId"
                :selected-mana-id="selectedManaId"
                :current-card-index="currentCardIndex"
                @close-dialog="emit('update:visible', false)"
            />
        </Dialog>
        <ConfirmDialog class="max-w-xl" />
    </div>
</template>
