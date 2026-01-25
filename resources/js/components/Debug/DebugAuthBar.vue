<script setup lang="ts">
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import Chip from 'primevue/chip'
import Badge from 'primevue/badge'
import {
    IconUser,
    IconMasksTheater,
    IconArrowRightCircleFilled,
    IconAlertTriangle,
    IconCircleCheck,
    IconCircleX
} from '@tabler/icons-vue'

const page = usePage()

const user = computed(() => page.props.auth?.user)
const character = computed(() => page.props.auth?.character)
const isAdmin = computed(() => page.props.auth?.isAdmin)
const isImpersonating = computed(() => page.props.auth?.isImpersonating)

const authStatus = computed(() => {
    if (isImpersonating.value) {
        return {
            icon: IconAlertTriangle,
            label: 'Mode Impersonation'
        }
    } else if (character.value && !user.value) {
        return {
            icon: IconMasksTheater,
            label: 'Connecté comme personnage'
        }
    } else if (user.value) {
        return {
            icon: IconCircleCheck,
            label: 'Connecté comme utilisateur'
        }
    } else {
        return {
            icon: IconCircleX,
            label: 'Non connecté'
        }
    }
})
</script>

<template>
        <div class="container mx-auto px-4 py-2">
            <div class="flex items-center justify-between gap-4 flex-wrap text-sm">
                <div class="flex items-center gap-3">
                    <Chip
                        :label="authStatus.label"
                        class="font-semibold"
                    >
                        <template #icon>
                            <component
                                :is="authStatus.icon"
                                :size="16"
                                class="mr-2"
                            />
                        </template>
                    </Chip>

                    <div class="flex items-center gap-2">
                        <Badge
                            :value="user ? 'web ✓' : 'web ✗'"
                            :severity="user ? 'success' : 'danger'"
                        />
                        <Badge
                            :value="character ? 'character ✓' : 'character ✗'"
                            :severity="character ? 'success' : 'danger'"
                        />
                    </div>
                </div>

                <div class="flex items-center gap-4 flex-1 justify-center flex-wrap">
                    <div v-if="user" class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-lg border border-gray-200 shadow-sm">
                        <IconUser :size="16" class="text-blue-500" />
                        <span class="text-gray-700 font-medium">
                            {{ user.full_name }}
                        </span>
                        <Badge
                            :value="`#${user.id}`"
                            severity="secondary"
                            size="small"
                        />
                        <Badge
                            v-if="isAdmin"
                            value="ADMIN"
                            severity="danger"
                            size="small"
                        />
                    </div>

                    <div v-if="isImpersonating">
                        <IconArrowRightCircleFilled />
                    </div>

                    <div v-if="character" class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-lg border border-purple-200 shadow-sm">
                        <IconMasksTheater :size="16" class="text-purple-500" />
                        <span class="text-gray-700 font-medium">
                            {{ character.name }}
                        </span>
                        <Badge
                            :value="`#${character.id}`"
                            severity="secondary"
                            size="small"
                        />
                    </div>
                </div>

            </div>
        </div>
</template>
