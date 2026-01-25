<script setup lang="ts">
import { ref, computed, toRefs, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import type { SkillTree, SkillNode } from '@/types/skilltree';
import Card from 'primevue/card';
import SkillTreeHeader from '@/components/SkillTree/Header.vue';
import SkillTreeGraph from '@/components/SkillTree/Graph.vue';
import SkillNodeDialog from '@/components/SkillTree/NodeDialog.vue';

// --- PROPS ---
const props = defineProps<{
    tree: SkillTree;
    manas: { id: number; name: string; icon: string }[];
    classes: { id: number; name: string; icon: string }[];
    selectedManaId: number;
    selectedClassId: number;
}>();

const page = usePage();
const character = computed(() => page.props.auth?.character)

const { tree, manas, classes, selectedManaId, selectedClassId } = toRefs(props);

// --- STATE MANAGEMENT ---
const selectedNode = ref<SkillNode | null>(null);
const dialogVisible = ref(false);
const isUnlocking = ref(false);

const selectedMana = computed(
    () =>
        manas.value.find((m) => m.id === selectedManaId.value) ??
        manas.value[0],
);
const selectedClass = computed(
    () =>
        classes.value.find((c) => c.id === selectedClassId.value) ??
        classes.value[0],
);

// --- EVENT HANDLERS ---
function handleSelectionChange({ mana, cls }: { mana: any; cls: any }) {
    dialogVisible.value = false;
    selectedNode.value = null;

    router.get(
        `${!character.value ? '/admin' : ''}/skilltree/${cls.id}/${mana.id}`,
        {},
        {
            preserveScroll: true,
            replace: true,
            preserveState: true,
            only: ['tree', 'selectedManaId', 'selectedClassId'],
        },
    );
}

function handleNodeClick(node: SkillNode) {
    const graph = document.querySelector('#skilltree-graph');

    const el = graph?.querySelector(`[data-node-id="${node.nodeId}"]`);
    if (el?.classList.contains('selectable-node')) {
        return;
    }

    selectedNode.value = node;
    dialogVisible.value = true;
}

function handleUnlockNode(node: SkillNode) {
    isUnlocking.value = true;
    router.post(
        `/skilltree/${selectedClass.value.id}/${selectedMana.value.id}/${node.nodeId}/unlock`,
        {},
        {
            preserveScroll: true,
            preserveState: true,
            only: ['tree'],
            onFinish: () => {
                isUnlocking.value = false;
                dialogVisible.value = false;
            },
        },
    );
}

function handleHighlightBlockingNode(node: SkillNode) {
    if (!node.blockedBy) return;

    document
        .querySelectorAll('.node-target')
        .forEach((el) => el.classList.remove('node-target'));

    const selector = `[data-node-id="${node.blockedBy}"]`;
    const blockingEl = document.querySelector(selector);
    if (!blockingEl) return;

    blockingEl.classList.add('node-target');
    blockingEl.scrollIntoView({
        behavior: 'smooth',
        block: 'center',
        inline: 'center',
    });

    const removeHighlight = () => blockingEl.classList.remove('node-target');
    blockingEl.addEventListener('mouseenter', removeHighlight, { once: true });
    dialogVisible.value = false;
}

// --- WATCHERS ---
// Close dialog if tree data changes from external sources
watch(tree, () => {
    dialogVisible.value = false;
    selectedNode.value = null;
});
</script>

<template>
    <Card>
        <template #title>
            <SkillTreeHeader
                :skill_point="tree.skill_point"
                :title="tree.title"
                :classes="classes"
                :manas="manas"
                :selected-class="selectedClass"
                :selected-mana="selectedMana"
                @selection-change="handleSelectionChange"
            />
        </template>
        <template #content>
            <SkillTreeGraph :tree="tree" @node-click="handleNodeClick" />
        </template>
    </Card>

    <SkillNodeDialog
        v-if="selectedNode"
        v-model:visible="dialogVisible"
        :node="selectedNode"
        :nodes="tree.nodes"
        :skill-points="tree.skill_point"
        :is-unlocking="isUnlocking"
        :is-admin="!character"
        :selected-mana-id="selectedManaId"
        :selected-class-id="selectedClassId"
        @unlock="handleUnlockNode"
        @highlight-blocker="handleHighlightBlockingNode"
    />
</template>
