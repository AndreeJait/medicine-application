<script lang="ts">
import { defineComponent, ref, computed, onMounted, onBeforeUnmount } from "vue";
import { useStore } from "vuex";
import { useRoute, useRouter } from "vue-router";

export default defineComponent({
    name: "Admin",
    setup() {
        const store = useStore();
        const route = useRoute();
        const router = useRouter();

        const user = computed(() => store.getters["auth/user"]);

        // Map path to title
        const routeTitles: Record<string, string> = {
            "/admin": "Dashboard",
            "/admin/medicine/list": "Medicine List",
            "/admin/medicine/stock": "Manage Stock",
            "/admin/medicine/history": "Stock History",
            "/admin/setting/users": "User Management",
            "/admin/setting/change-password": "Change My Password",
        };

        const pageTitle = computed(() => {
            const path = route.path;
            return (
                routeTitles[path] ||
                Object.entries(routeTitles).find(([key]) => path.startsWith(key))?.[1] ||
                "Admin"
            );
        });

        // Dropdown toggle
        const showDropdown = ref(false);

        const toggleDropdown = () => {
            showDropdown.value = !showDropdown.value;
        };

        const closeDropdown = (event: MouseEvent) => {
            const target = event.target as HTMLElement;
            if (!target.closest("#user-menu")) {
                showDropdown.value = false;
            }
        };

        onMounted(() => {
            document.addEventListener("click", closeDropdown);
        });

        onBeforeUnmount(() => {
            document.removeEventListener("click", closeDropdown);
        });

       const logout = async () => {
            // Optional: confirm dialog
            if (!confirm("Are you sure you want to logout?")) return;

            await store.dispatch("auth/logoutAsync");
            await router.push("/");
        };

        return {
            user,
            pageTitle,
            showDropdown,
            toggleDropdown,
            logout,
        };
    },
});
</script>

<template>
    <div class="admin-layout flex h-screen">
        <!-- Sidebar -->
        <aside class="bg-sky-400 text-white max-w-[250px] w-full h-full overflow-y-auto">
            <side-bar :user="user"/>
        </aside>

        <!-- Main Area (Navbar + Content) -->
        <div class="flex-1 flex flex-col">
            <!-- Top Navbar -->
            <nav class="h-16 bg-white shadow-md flex items-center px-6 justify-between">
                <!-- Left: Page Title -->
                <h1 class="text-xl font-semibold text-sky-600">{{ pageTitle }}</h1>

                <!-- Right: User Dropdown -->
                <div id="user-menu" class="relative">
                    <button
                        @click="toggleDropdown"
                        class="flex items-center gap-2 bg-sky-100 hover:bg-sky-200 px-4 py-2 rounded-md text-sky-600 font-semibold"
                    >
                        <span v-if="user">{{ user.name }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Dropdown menu -->
                    <div
                        v-if="showDropdown"
                        class="absolute right-0 mt-2 w-40 bg-white border border-gray-200 rounded-md shadow-lg z-50"
                    >
                        <button class="w-full text-left px-4 py-2 hover:bg-gray-100" @click="logout">
                            Logout
                        </button>
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="flex-1 overflow-auto p-6 bg-gray-100">
                <router-view />
            </main>
        </div>
    </div>
</template>

<style scoped>
.admin-layout {
    font-family: sans-serif;
}
</style>
