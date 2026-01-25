import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
    character : Character;
    impersonating : Impersonate;
    isAdmin: boolean;
    isImpersonating: boolean;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: Auth;
};

export interface User {
    id: number;
    full_name: string,
    family_name: string,
    given_name: string,
    phone: string,
    email: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}
export interface Character {
    id: number;
    name: string,
    lore: string,
    skill_point: number,
    knowledge_point: number,
    participant_id: number,
    created_at: string;
    updated_at: string;
}

export interface Impersonate {
    user_id: number;
    character_id: number;
}

export type BreadcrumbItemType = BreadcrumbItem;
