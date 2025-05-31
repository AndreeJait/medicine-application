<script setup lang="ts">
import { ref, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import Logo from "@/assets/images/logo-no-text.png";
import {useStore} from "vuex";
import {User} from "@/types/user";

interface SidebarItem {
    name: string;
    icon: [string, string];
    path: string;
    children?: SidebarItem[];
}

const route = useRoute();
const router = useRouter();


const props = defineProps<{
    class?: string;
    user: User,
}>();

const sidebars = computed<SidebarItem[]>(() => {
    const menus: SidebarItem[] = [
        {
            name: "Dashboard",
            icon: ["fas", "home"],
            path: "/admin",
        },
        {
            name: "Medicine",
            icon: ["fas", "pills"],
            path: "/admin/medicine",
            children: [
                { name: "List", icon: ["fas", "list"], path: "/admin/medicine/list" },
                { name: "Stock History", icon: ["fas", "history"], path: "/admin/medicine/history" },
            ],
        },
        {
            name: "Setting",
            icon: ["fas", "cog"],
            path: "/admin/setting",
            children: [
                ...((props.user?.role??"").includes("SuperAdmin")
                    ?  Array<SidebarItem>({
                        name: "User",
                        icon: ["fas", "users"],
                        path: "/admin/setting/users",
                    })
                    : Array<SidebarItem>()),
                {
                    name: "Change My Password",
                    icon: ["fas", "key"],
                    path: "/admin/setting/change-password",
                },
            ],
        },
    ];
    return menus;
});

const expanded = ref<Record<string, boolean>>({});

function toggleExpand(name: string) {
    expanded.value[name] = !expanded.value[name];
}

function navigateTo(path: string) {
    if (path !== route.path) router.push(path);
}

function isActive(path: string): boolean {
    return route.path === path;
}

function isParentActive(item: SidebarItem): boolean {
    if (!item.children) return isActive(item.path);
    return item.children.some(child => isActive(child.path));
}
</script>

<template>
    <aside class="w-full h-screen bg-sky-400 text-white p-4 overflow-y-auto" :class="props.class">
        <!-- Logo -->
        <div class="flex flex-col items-center mb-6 logo-section">
            <img :src="Logo" alt="Logo" class="w-20 h-20 mb-2" />
            <h3 class="text-2xl font-bold">Medicine Management</h3>
        </div>

        <!-- Navigation -->
        <nav class="space-y-4 nav-main">
            <div
                v-for="(item, index) in sidebars"
                :key="`nav-${index}`"
                class="space-y-2 nav-item"
            >
                <!-- Parent -->
                <div
                    class="flex items-center justify-between px-3 py-2 rounded cursor-pointer nav-link transition-all"
                    :class="{
                        'bg-white text-sky-500 font-semibold': isParentActive(item),
                        'hover:bg-white/10': !isParentActive(item),
                    }"
                    @click="item.children ? toggleExpand(item.name) : navigateTo(item.path)"
                >
                    <div class="flex items-center gap-3">
                        <font-awesome-icon :icon="item.icon" />
                        <span class="text-lg">{{ item.name }}</span>
                    </div>
                    <font-awesome-icon
                        v-if="item.children"
                        :icon="(expanded[item.name] || isParentActive(item)) ? ['fas', 'chevron-up'] : ['fas', 'chevron-down']"
                        class="text-sm"
                    />
                </div>

                <!-- Children -->
                <transition name="fade-slide">
                    <div
                        v-if="item.children && (expanded[item.name] || isParentActive(item))"
                        class="ml-8 space-y-1 text-sm text-white/80 sub-nav"
                    >
                        <div
                            v-for="(child, cIndex) in item.children"
                            :key="`child-${index}-${cIndex}`"
                            class="flex items-center gap-2 px-3 py-1 rounded cursor-pointer sub-nav-link transition-all"
                            :class="{
                                'bg-white text-sky-500 font-medium': isActive(child.path),
                                'hover:bg-white/10': !isActive(child.path),
                            }"
                            @click="navigateTo(child.path)"
                        >
                            <font-awesome-icon :icon="child.icon" />
                            <span>{{ child.name }}</span>
                        </div>
                    </div>
                </transition>
            </div>
        </nav>
    </aside>
</template>

<style>
@import url("./side-bar.css");
</style>
