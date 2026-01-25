<script setup lang="ts">
import { ref } from 'vue';
import Button from 'primevue/button';
import { Icon } from '@iconify/vue';
import type { SkillNode } from '@/types/skilltree';
import { useConfirm } from 'primevue/useconfirm';
import { router } from '@inertiajs/vue3';

const confirm = useConfirm();

const { node, nodes, selectedClassId, selectedManaId, currentCardIndex } = defineProps<{
    node: SkillNode;
    nodes: SkillNode[];
    selectedClassId: number;
    selectedManaId: number;
    currentCardIndex: number;
}>()

const emit = defineEmits(['close-dialog']);
const isProcessing = ref(false);

function handleDeleteNode() {
    confirm.require({
        header: 'Supprimer le nœud',
        message: `Êtes-vous sûr de vouloir supprimer ce nœud ? Tous les nœuds enfants, y compris celui-ci, seront également supprimés. Cette action est irréversible.`,
        rejectLabel: 'Annuler',
        acceptLabel: 'Confirmer',
        rejectClass: 'p-button-secondary',
        accept: () => {
            isProcessing.value = true;
            router.delete(
                `/admin/skilltree/${selectedClassId}/${selectedManaId}/${node.nodeId}`,
                {
                    preserveScroll: true,
                    preserveState: true,
                    only: ['tree'],
                    onFinish: () => {
                        isProcessing.value = false;
                    },
                },
            );
        },
    });
}

function handleRemoveCard() {
    if (node.cards.length === 0) return;

    const currentCard = node.cards[currentCardIndex];

    if (!currentCard) return;

    confirm.require({
        header: 'Retirer la carte',
        message: `Êtes-vous sûr de vouloir retirer "${currentCard.title}" de ce nœud ?`,
        rejectLabel: 'Annuler',
        acceptLabel: 'Confirmer',
        rejectClass: 'p-button-secondary',
        accept: () => {
            isProcessing.value = true;
            router.delete(
                `/admin/skilltree/${selectedClassId}/${selectedManaId}/${node.nodeId}/cards/${currentCard.cardId}`,
                {
                    preserveScroll: true,
                    preserveState: true,
                    only: ['tree'],
                    onFinish: () => {
                        isProcessing.value = false;
                        emit('close-dialog');
                    },
                },
            );
        },
    });
}

function handleAttachCard() {
    router.visit(`/cards/attach/${node.nodeId}`);
}

function handleToggleLock() {
    if (node.blockedBy) {
        isProcessing.value = true;
        router.post(
            `/admin/skilltree/${selectedClassId}/${selectedManaId}/${node.nodeId}/unlock`,
            {},
            {
                preserveScroll: true,
                preserveState: true,
                only: ['tree'],
                onFinish: () => {
                    isProcessing.value = false;
                },
            },
        );
        emit('close-dialog');
        return;
    }

    emit('close-dialog');

    const graph = document.querySelector('#skilltree-graph');
    if (!graph) return;

    const nodes = graph.querySelectorAll('[data-node-id]');
    nodes.forEach((n: Element) => {
        const nodeId = n.getAttribute('data-node-id');
        if (nodeId === String(node.nodeId)) return;

        n.classList.add('selectable-node');
        (n as HTMLElement).addEventListener('click', handleSelectLockNode, { once: true });
    });

    async function handleSelectLockNode(e: MouseEvent) {
        const target = e.currentTarget as HTMLElement;
        const lockingNodeId = target.getAttribute('data-node-id');
        if (!lockingNodeId) return;

        isProcessing.value = true;

        nodes.forEach((n: Element) => {
            n.classList.remove('selectable-node');
            (n as HTMLElement).removeEventListener('click', handleSelectLockNode);
        });

        router.post(
            `/admin/skilltree/${selectedClassId}/${selectedManaId}/${node.nodeId}/lock`,
            { locking_node_id: lockingNodeId },
            {
                preserveScroll: true,
                preserveState: true,
                only: ['tree'],
                onFinish: () => {
                    isProcessing.value = false;
                },
            },
        );
    }
}


function handleAddNode() {
    emit('close-dialog');

    const graph = document.querySelector('#skilltree-graph');
    if (!graph) return;

    // Remove old arrows
    graph.querySelectorAll('.node-arrow').forEach((n: Element) => n.remove());

    const nodeElement = graph.querySelector(`[data-node-id="${node.nodeId}"]`);
    if (!nodeElement) return;

    const occupiedPositions = new Set(nodes.map(n => `${n.position.x},${n.position.y}`));

    const radius = 16;
    const pointsCount = 8;
    const angleStep = (2 * Math.PI) / pointsCount;

    for (let i = 0; i < pointsCount; i++) {
        const angle = i * angleStep;
        const dx = Math.round(Math.cos(angle));
        const dy = Math.round(Math.sin(angle));

        const targetX = node.position.x + dx;
        const targetY = node.position.y + dy;
        if (occupiedPositions.has(`${targetX},${targetY}`)) continue;

        const ax = radius * Math.cos(angle);
        const ay = radius * Math.sin(angle);

        const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        circle.setAttribute('r', '4');
        circle.classList.add('node-arrow');
        circle.setAttribute('transform', `translate(${ax}, ${ay})`);

        circle.addEventListener('click', async e => {
            e.stopPropagation();
            isProcessing.value = true;

            graph.querySelectorAll('.node-arrow').forEach(a => a.remove());

            router.post(
                `/admin/skilltree/${selectedClassId}/${selectedManaId}/${node.nodeId}/children`,
                { dx, dy },
                {
                    preserveScroll: true,
                    preserveState: true,
                    only: ['tree'],
                    onFinish: () => {
                        isProcessing.value = false;
                    },
                },
            );
        });

        nodeElement.appendChild(circle);
    }
}


</script>

<template>
    <div class="flex justify-center pt-3 gap-4">
        <Button
            v-if="node.parentId"
            severity="danger"
            rounded
            size="large"
            v-tooltip.top="'Suppression du nœud'"
            :disabled="isProcessing"
            @click="handleDeleteNode"
        >
            <template #icon>
                <Icon icon="tabler:trash" />
            </template>
        </Button>
        <Button
            v-if="node.cards.length > 0"
            severity="secondary"
            rounded
            size="large"
            v-tooltip.top="'Retrait de la carte'"
            :disabled="isProcessing"
            @click="handleRemoveCard"
        >
            <template #icon>
                <Icon icon="tabler:minus" />
            </template>
        </Button>

        <Button
            severity="secondary"
            rounded
            size="large"
            v-tooltip.top="'Ajout d\'une carte'"
            :disabled="isProcessing"
            @click="handleAttachCard"
        >
            <template #icon>
                <Icon icon="tabler:plus" />
            </template>
        </Button>

        <Button
            severity="secondary"
            rounded
            size="large"
            v-tooltip.top="
                node.blockedBy ? 'Débloquer le nœud' : 'Bloquer le nœud'
            "
            :disabled="isProcessing"
            @click="handleToggleLock"
        >
            <template #icon>
                <Icon v-if="node.blockedBy" icon="tabler:lock-open" />
                <Icon v-else icon="tabler:lock" />
            </template>
        </Button>

        <Button
            severity="secondary"
            rounded
            size="large"
            v-tooltip.top="'Ajouter un nœud enfant'"
            :disabled="isProcessing"
            @click="handleAddNode"
        >
            <template #icon>
                <Icon icon="tabler:binary-tree-2-filled" />
            </template>
        </Button>
    </div>
</template>

<style>
.node-arrow {
    fill: currentColor;
    cursor: pointer;
    stroke-dasharray: none;
    transition: stroke-width 150ms;
}
.node-arrow:hover {
    stroke-width: 4px;

}
</style>
