<script lang="ts" setup>
import { computed, ref, watchEffect } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import Menubar from 'primevue/menubar';
import Button from 'primevue/button';
import { IconCalendarEvent, IconCamera, IconCards, IconChevronDown, IconFileInvoice, IconFolders, IconHome, IconInfoSquareRounded, IconLogin2, IconLogout, IconMasksTheater, IconMessage, IconShieldLock, IconSitemap, IconUser, IconUserPlus, IconUsers, IconWorld } from '@tabler/icons-vue';

interface Emits {
    (e: 'open-login'): void;

    (e: 'open-register'): void;
}

const emit = defineEmits<Emits>();

const page = usePage();
const user = computed(() => page.props.auth?.user);
const character = computed(() => page.props.auth?.character);
const isAdmin = computed(() => page.props.auth?.isAdmin);
const isAuthenticated = computed(() => !!user.value || !!character.value);

const navigationItems = ref<any[]>([]);

const makeCharMenu = () => ({
    label: 'Personnage',
    tablerIcon: IconMasksTheater,
    items: [
        { label: 'Profile', tablerIcon: IconUser, route: '/character/profile' },
        { label: 'Arbre de compétences', tablerIcon: IconSitemap, route: '/skilltree/-1/1' },
        { label: 'Cartes', tablerIcon: IconCards, route: '/cards' }
    ]
});

const makeUserMenu = () => ({
    label: 'Mon compte',
    tablerIcon: IconUser,
    items: [
        { label: 'Profil', tablerIcon: IconUser, route: '/profile' },
        { label: 'Participants', tablerIcon: IconFolders, route: '/participants' },
        { label: 'Factures', tablerIcon: IconFileInvoice, route: '/bills' }
    ]
});

const makeAdminMenu = () => ({
    label: 'Admin',
    tablerIcon: IconShieldLock,
    items: [
        { label: 'Comptes', tablerIcon: IconUsers, route: '/accounts' },
        { label: 'Participants', tablerIcon: IconFolders, route: '/participants' },
        { label: 'Factures', tablerIcon: IconFileInvoice, route: '/bills' },
        { label: 'Cartes', tablerIcon: IconCards, route: '/admin/cards' },
        { label: 'Arbre de compétences', tablerIcon: IconSitemap, route: '/admin/skilltree/-1/1' }
    ]
});

watchEffect(() => {
    const publicLinks = [
        { label: 'Accueil', tablerIcon: IconHome, route: '/' },
        { label: 'Événements', tablerIcon: IconCalendarEvent, route: '/events' },
        { label: 'Galerie', tablerIcon: IconCamera, route: '/gallery' },
        { label: 'Informations', tablerIcon: IconInfoSquareRounded, route: '/' },
        { label: 'Contact', tablerIcon: IconMessage, route: '/' }
    ];

    let baseMenu: any[] = [];

    if (!isAuthenticated.value) {
        baseMenu = [...publicLinks];
    } else {
        const explorerMenu = {
            label: 'Explorer',
            tablerIcon: IconWorld,
            items: publicLinks.slice(1)
        };
        baseMenu = [publicLinks[0], explorerMenu];
    }

    const menus: any[] = [...baseMenu];

    if (user.value && !isAdmin.value) menus.push(makeUserMenu());
    if (isAdmin.value) menus.push(makeAdminMenu());
    if (character.value) menus.push(makeCharMenu());

    navigationItems.value = menus;
});

const navigate = (item: any) => {
    if (item.route) router.visit(item.route, { preserveScroll: true });
};

const handleLogout = () => {
    const logoutUrl = user.value ? '/logout' : '/character/logout';
    router.post(logoutUrl, {}, {
        preserveScroll: true,
        onSuccess: () => router.reload({ only: ['auth'] })
    });
};
</script>

<template>
    <Menubar :model="navigationItems">
        <template #item="{ item, props, hasSubmenu }">
            <a
                class="flex items-center gap-2 cursor-pointer"
                v-bind="props.action"
                @click.prevent="navigate(item)"
            >
                <component :is="item.tablerIcon" v-if="item.tablerIcon" class="w-5 h-5" />
                <span>{{ item.label }}</span>
                <IconChevronDown v-if="hasSubmenu" :size="15" class="ml-1" />
            </a>
        </template>

        <template #end>
            <div class="flex items-center gap-2">
                <template v-if="isAuthenticated">
                    <Button
                        label="Déconnexion"
                        severity="secondary"
                        variant="text"
                        @click="handleLogout"
                    >
                        <template #icon>
                            <IconLogout :size="18" />
                        </template>
                    </Button>
                </template>

                <template v-else>
                    <Button
                        label="Se connecter"
                        severity="secondary"
                        variant="text"
                        @click="emit('open-login')"
                    >
                        <template #icon>
                            <IconLogin2 :size="18" />
                        </template>
                    </Button>

                    <Button label="S'inscrire" @click="emit('open-register')">
                        <template #icon>
                            <IconUserPlus :size="18" />
                        </template>
                    </Button>
                </template>
            </div>
        </template>
    </Menubar>
</template>
