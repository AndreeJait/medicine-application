import { createRouter, createWebHistory, RouteRecordRaw } from "vue-router";
import stores from "@/stores/index.ts";

const routes: Array<RouteRecordRaw> = [
    {
        path: "/",
        component: ()=> import("@/features/auth/Index.vue"),
        meta: { guestOnly: true }, // hanya tampil kalau belum login
    },
    {
        path: "/admin",
        component: ()=> import("@/features/admin/Admin.vue"),
        children: [
            {
                path: "",
                component: ()=> import("@/features/admin/Dashboard.vue"),
            },
            {
                path: "medicine/list",
                component: ()=> import("@/features/admin/medicine/MedicineList.vue"),
            },
            {
                path: "medicine/add",
                component: ()=> import("@/features/admin/medicine/Add.vue"),
            },
            {
                path: "medicine/edit/:id",
                component: ()=> import("@/features/admin/medicine/Edit.vue"),
            },
            {
                path: "medicine/stock/:id",
                component: ()=> import("@/features/admin/medicine/Stock.vue"),
            },
            {
                path: "medicine/history",
                component: ()=> import("@/features/admin/medicine/StockHistory.vue"),
            },
            {
                path: "setting/change-password",
                component: ()=> import("@/features/admin/setting/ChangeOwnPassword.vue"),
            },
            {
                path: "setting/users",
                component: ()=> import("@/features/admin/setting/UserManagement.vue"),
            }

        ],
        meta: { requiresAuth: true },
    },
    {
        path: "/:pathMatch(.*)*",
        name: "not-found",
        component: ()=>import("@/features/not-found/NotFound.vue"),
    },
];


const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const token = stores.getters["auth/token"];
    const isLoggedIn = !!token;

    if (to.meta.requiresAuth && !isLoggedIn) {
        return next({ path: "/" });
    }

    if (to.meta.guestOnly && isLoggedIn) {
        return next({ path: "/admin" });
    }

    return next();
});

export default router;
