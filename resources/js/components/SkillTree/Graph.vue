<script setup lang="ts">
import { ref, computed } from 'vue';
import type { SkillTree } from '@/types/skilltree';
import { Icon } from '@iconify/vue';
import Button from 'primevue/button';

// --- CONSTANTS ---
const SIZE = 150;
const GAP = 64;
const PADDING = GAP / 2;

// --- PROPS & EMITS ---
const props = defineProps<{
    tree: SkillTree;
}>();

const emit = defineEmits(['node-click']);

// --- STATE ---
const zoom = ref(1); // 1 = 100%
const MIN_ZOOM = 0.5;
const MAX_ZOOM = 2;

// --- COMPUTED ---
const nodeMap = computed(() => new Map(props.tree.nodes.map(n => [n.nodeId, n])));

const links = computed(() =>
    props.tree.nodes.flatMap(n => {
        if (n.parentId == null) return [];
        const from = nodeMap.value.get(n.parentId);
        return from ? [{ from, to: n }] : [];
    })
);

const locked_links = computed(() =>
    props.tree.nodes.flatMap(n => {
        if (!n.blockedBy) return [];
        const blocker = nodeMap.value.get(n.blockedBy);
        if (!blocker || blocker.state === 'acquired') return [];
        return [{ from: n, to: blocker }];
    })
);

const viewBox = computed(() => {
    const { minX, minY, width, height } = props.tree.canvas;
    const x = minX * GAP - PADDING;
    const y = minY * GAP - PADDING;
    const w = width * GAP + PADDING * 2;
    const h = height * GAP + PADDING * 2;
    return `${x} ${y} ${w} ${h}`;
});

const svgWidth = computed(() => {
    const { width } = props.tree.canvas;
    return (width + 1) * SIZE * zoom.value;
});

const svgHeight = computed(() => {
    const { height } = props.tree.canvas;
    return (height + 1) * SIZE * zoom.value;
});

// --- METHODS ---
function zoomIn() {
    zoom.value = Math.min(MAX_ZOOM, zoom.value + 0.1);
}

function zoomOut() {
    zoom.value = Math.max(MIN_ZOOM, zoom.value - 0.1);
}
</script>


<template>
    <div class="relative">
        <!-- Zoom Controls -->
        <div class="top-2 left-2 absolute flex flex-col gap-2">
            <Button
                v-tooltip.right="'Zoom in'"
                rounded
                severity="secondary"
                @click="zoomIn"
            >
                <template #icon>
                    <Icon icon="tabler:zoom-in"/>
                </template>
            </Button>

            <Button
                v-tooltip.right="'Zoom out'"
                rounded
                severity="secondary"
                @click="zoomOut"
            >
                <template #icon>
                    <Icon icon="tabler:zoom-out"/>
                </template>
            </Button>
        </div>

        <!-- SVG Container -->
        <div class="overflow-auto w-full bg-[#0a0a0a] rounded-xl">
            <svg
                class="mx-auto transition-all duration-300"
                xmlns="http://www.w3.org/2000/svg"
                :viewBox="viewBox"
                :width="svgWidth"
                :height="svgHeight"
                preserveAspectRatio="xMidYMid meet"
                id="skilltree-graph"
            >
                <defs>
                    <circle id="circle" r="16" />
                    <polygon id="diamond" points="0,16 16,0 0,-16 -16,0" />
                </defs>

                <mask id="nodeMask">
                    <rect
                        fill="white"
                        :x="tree.canvas.minX * GAP - PADDING"
                        :y="tree.canvas.minY * GAP - PADDING"
                        width="100%"
                        height="100%"
                    />
                    <g>
                        <use
                            v-for="n in tree.nodes"
                            :key="'mask-' + n.nodeId"
                            :href="n.parentId ? '#circle' : '#diamond'"
                            :x="n.position.x * GAP"
                            :y="n.position.y * GAP"
                            fill="black"
                        />
                    </g>
                </mask>

                <g id="links" mask="url(#nodeMask)">
                    <line
                        v-for="link in links"
                        :key="'link-' + link.to.nodeId"
                        :x1="link.from.position.x * GAP"
                        :y1="link.from.position.y * GAP"
                        :x2="link.to.position.x * GAP"
                        :y2="link.to.position.y * GAP"
                        :stroke="link.to.color"
                        class="stroke"
                        :class="link.to.state === 'locked' ? 'state-neutral' : `state-${link.to.state}`"
                    />
                    <line
                        v-for="link in locked_links"
                        :key="'locked-' + link.from.nodeId"
                        :x1="link.from.position.x * GAP"
                        :y1="link.from.position.y * GAP"
                        :x2="link.to.position.x * GAP"
                        :y2="link.to.position.y * GAP"
                        :stroke="link.from.color"
                        class="locked-line stroke state-neutral"
                    />
                </g>

                <g id="nodes">
                    <g
                        v-for="n in tree.nodes"
                        :key="'node-' + n.nodeId"
                        class="node"
                        :data-node-id="n.nodeId"
                        :style="{ color: n.color }"
                        :class="`state-${n.state}`"
                        :stroke="n.color"
                        :transform="`translate(${n.position.x * GAP}, ${n.position.y * GAP})`"
                        @click="emit('node-click', n)"
                    >
                        <use
                            class="stroke"
                            :href="n.parentId ? '#circle' : '#diamond'"
                            fill="transparent"
                        />
                        <use
                            v-if="n.state === 'acquired' || n.state === 'neutral-acquired'"
                            class="inner"
                            :href="n.parentId ? '#circle' : '#diamond'"
                            :fill="n.color"
                        />
                    </g>
                </g>
            </svg>
        </div>
    </div>
</template>


<style scoped>
.node {
    cursor: pointer;
}
.node > .stroke {
    transition: transform 150ms ease-in-out;
}
.node:hover > .stroke {
    transform: scale(105%);
}
.stroke {
    stroke-width: 2px;
}
.inner {
    transform: scale(0.5);
}
.state-neutral-acquired,
.state-locked,
.state-neutral {
    filter: grayscale(50%) brightness(40%);
}

.locked-line,
.state-locked {
    stroke-dasharray: 5;
    stroke-linecap: round;
}

.selectable-node > .stroke,
.node-target > .stroke {
    filter: drop-shadow(0 0 0.1rem currentColor);
}

.selectable-node,
.state-acquired,
.node-target {
    filter: none;
}

</style>
