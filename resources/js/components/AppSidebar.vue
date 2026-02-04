<script setup lang="ts">
import { computed } from 'vue';
import { App, Link, usePage } from '@inertiajs/vue3';
import { FolderOpen, AppWindow, Airplay } from 'lucide-vue-next';

import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { apply as licenseApply, status as licenseStatus } from '@/routes/license';
import { type NavItem } from '@/types';

import AppLogo from './AppLogo.vue';

const page = usePage();
const isAdmin = computed(() => page.props.auth?.user?.role === 'admin');
const hasLicenseApplication = computed(
    () => Boolean(page.props.hasLicenseApplication),
);

const mainNavItems = computed<NavItem[]>(() => {
    if (isAdmin.value) {
        return [
            {
                title: 'Dashboard',
                href: dashboard(),
                icon: Airplay,
            },
            {
                title: 'Senarai Permohonan',
                href: '/admin/license-applications',
                icon: FolderOpen,
            },
        ];
    }

    return [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: Airplay,
        },
        {
            title: hasLicenseApplication.value
                ? 'Status Lesen Penginapan'
                : 'Mohon Lesen Penginapan',
            href: hasLicenseApplication.value ? licenseStatus() : licenseApply(),
            icon: AppWindow,
        },
    ];
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
