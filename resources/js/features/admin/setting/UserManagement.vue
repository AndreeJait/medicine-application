<script setup lang="ts">
import { ref, onMounted } from "vue";
import {
    getUsers,
    createUser,
    updateUser,
    deleteUser,
    changeUserPassword,
    updateUserRole,
} from "@/services/user.service";
import Modal from "@/components/modal/Modal.vue";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { User } from "@/types/user";

const users = ref<User[]>([]);
const isLoading = ref(false);
const isProcessing = ref(false);
const currentModal = ref<"add" | "edit" | "password" | "role" | null>(null);
const selectedUser = ref<User | null>(null);
const currentPage = ref(1);
const totalPages = ref(1);
const perPage = 10;
const search = ref("");

const form = ref({
    name: "",
    email: "",
    nik: "",
    position: "",
    password: "",
    confirm_password: "",
    role: "Admin",
    is_active: true,
});

const errors = ref<Record<string, string>>({});

const validateForm = () => {
    errors.value = {};
    if (!form.value.name) errors.value.name = "Name is required.";
    if (!form.value.email) errors.value.email = "Email is required.";
    if (!form.value.nik) errors.value.nik = "NIK is required.";
    if (!form.value.position) errors.value.position = "Position is required.";
    if (currentModal.value === "add" && !form.value.password)
        errors.value.password = "Password is required.";
    if (form.value.password !== form.value.confirm_password)
        errors.value.confirm_password = "Passwords do not match.";
    return Object.keys(errors.value).length === 0;
};

const loadUsers = async () => {
    isLoading.value = true;
    const res = await getUsers(currentPage.value, perPage, search.value);
    users.value = res.data.data.items;
    totalPages.value = res.data.data.total_pages;
    currentPage.value = res.data.data.page;
    isLoading.value = false;
};

const openModal = (type: typeof currentModal.value, user?: User) => {
    selectedUser.value = user || null;
    currentModal.value = type;

    form.value = {
        name: user?.name || "",
        email: user?.email || "",
        nik: user?.nik || "",
        position: user?.position || "",
        password: "",
        confirm_password: "",
        role: user?.role || "Admin",
        is_active: user?.is_active ?? true,
    };

    errors.value = {};
};

const closeModal = () => {
    currentModal.value = null;
    selectedUser.value = null;
    form.value = {
        name: "",
        email: "",
        nik: "",
        position: "",
        password: "",
        confirm_password: "",
        role: "Admin",
        is_active: true,
    };
    errors.value = {};
};

const handleSubmit = async () => {
    if (!validateForm()) return;
    isProcessing.value = true;

    try {
        if (currentModal.value === "add") {
            await createUser(form.value);
        } else if (currentModal.value === "edit" && selectedUser.value) {
            await updateUser(selectedUser.value.id, form.value);
        }
        await loadUsers();
        closeModal();
    } finally {
        isProcessing.value = false;
    }
};

const handleDelete = async (user: User) => {
    if (!confirm(`Delete ${user.name}?`)) return;
    isProcessing.value = true;
    try {
        await deleteUser(user.id);
        await loadUsers();
    } finally {
        isProcessing.value = false;
    }
};

const handleChangePassword = async () => {
    if (!form.value.password || form.value.password !== form.value.confirm_password) {
        errors.value.confirm_password = "Passwords do not match or empty.";
        return;
    }
    isProcessing.value = true;
    try {
        await changeUserPassword(selectedUser.value!.id, form.value.password, form.value.confirm_password);
        closeModal();
    } finally {
        isProcessing.value = false;
    }
};

const handleChangeRole = async () => {
    isProcessing.value = true;
    try {
        await updateUserRole(selectedUser.value!.id, form.value.role);
        await loadUsers();
        closeModal();
    } finally {
        isProcessing.value = false;
    }
};

onMounted(loadUsers);
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex gap-2">
                <input
                    v-model="search"
                    @keyup.enter="loadUsers"
                    placeholder="Search user..."
                    class="border px-3 py-2 rounded w-64"
                />
                <button class="bg-sky-500 text-white px-4 py-2 rounded" @click="loadUsers">
                    Search
                </button>
            </div>
            <button @click="openModal('add')" class="bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700">
                <font-awesome-icon icon="plus" class="mr-2" /> Add User
            </button>
        </div>

        <div class="overflow-x-auto bg-white shadow rounded">
            <table class="min-w-full text-sm text-gray-700">
                <thead class="bg-gray-100 text-left">
                <tr>
                    <th class="py-2 px-4">Name</th>
                    <th class="py-2 px-4">Email</th>
                    <th class="py-2 px-4">NIK</th>
                    <th class="py-2 px-4">Position</th>
                    <th class="py-2 px-4">Role</th>
                    <th class="py-2 px-4">Active</th>
                    <th class="py-2 px-4 text-center">Actions</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="user in users" :key="user.id" class="border-t hover:bg-gray-50">
                    <td class="py-2 px-4">{{ user.name }}</td>
                    <td class="py-2 px-4">{{ user.email }}</td>
                    <td class="py-2 px-4">{{ user.nik }}</td>
                    <td class="py-2 px-4">{{ user.position }}</td>
                    <td class="py-2 px-4">{{ user.role }}</td>
                    <td class="py-2 px-4">{{ user.is_active ? 'Yes' : 'No' }}</td>
                    <td class="py-2 px-4 text-center flex justify-center gap-2">
                        <button @click="openModal('edit', user)" class="text-blue-600">
                            <font-awesome-icon icon="pen-square" />
                        </button>
                        <button @click="openModal('password', user)" class="text-yellow-600">
                            <font-awesome-icon icon="key" />
                        </button>
                        <button @click="openModal('role', user)" class="text-purple-600">
                            <font-awesome-icon icon="user-shield" />
                        </button>
                        <button @click="handleDelete(user)" class="text-red-600" :disabled="isProcessing">
                            <font-awesome-icon :icon="isProcessing ? 'spinner' : 'trash'" :spin="isProcessing" />
                        </button>
                    </td>
                </tr>
                <tr v-if="!isLoading && users.length === 0">
                    <td colspan="7" class="text-center text-gray-400 p-4">No users found</td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="flex justify-center items-center gap-4 mt-4">
            <button :disabled="currentPage === 1" class="px-4 py-2 border rounded" @click="() => { currentPage--; loadUsers(); }">
                Prev
            </button>
            <span>{{ currentPage }} / {{ totalPages }}</span>
            <button :disabled="currentPage === totalPages" class="px-4 py-2 border rounded" @click="() => { currentPage++; loadUsers(); }">
                Next
            </button>
        </div>

        <!-- Add/Edit Modal -->
        <Modal v-if="currentModal === 'add' || currentModal === 'edit'" @close="closeModal">
            <template #default>
                <h3 class="text-lg font-semibold mb-4">{{ currentModal === 'add' ? 'Add New User' : 'Edit User' }}</h3>
                <div class="space-y-3">
                    <input v-model="form.name" placeholder="Name" class="w-full border px-3 py-2 rounded" />
                    <span class="text-red-500 text-sm">{{ errors.name }}</span>
                    <input v-model="form.email" placeholder="Email" class="w-full border px-3 py-2 rounded" />
                    <span class="text-red-500 text-sm">{{ errors.email }}</span>
                    <input v-model="form.nik" placeholder="NIK" class="w-full border px-3 py-2 rounded" />
                    <span class="text-red-500 text-sm">{{ errors.nik }}</span>
                    <input v-model="form.position" placeholder="Position" class="w-full border px-3 py-2 rounded" />
                    <span class="text-red-500 text-sm">{{ errors.position }}</span>
                    <div v-if="currentModal === 'add'">
                        <input v-model="form.password" placeholder="Password" type="password" class="w-full border px-3 py-2 rounded" />
                        <span class="text-red-500 text-sm">{{ errors.password }}</span>
                    </div>
                    <label class="inline-flex items-center mt-2">
                        <input v-model="form.is_active" type="checkbox" class="form-checkbox" />
                        <span class="ml-2 text-sm">Active</span>
                    </label>
                </div>
                <div class="flex justify-end gap-2 mt-4">
                    <button @click="closeModal" class="px-4 py-2 border rounded">Cancel</button>
                    <button @click="handleSubmit" :disabled="isProcessing" class="bg-sky-500 text-white px-4 py-2 rounded">
                        <font-awesome-icon icon="spinner" v-if="isProcessing" spin />
                        <span v-else>Save</span>
                    </button>
                </div>
            </template>
        </Modal>

        <!-- Password Modal -->
        <Modal v-if="currentModal === 'password'" @close="closeModal">
            <template #default>
                <h3 class="text-lg font-semibold mb-4">Change Password</h3>
                <input v-model="form.password" placeholder="New Password" type="password" class="w-full border px-3 py-2 rounded" />
                <input v-model="form.confirm_password" placeholder="Confirm Password" type="password" class="w-full border px-3 py-2 rounded mt-2" />
                <span class="text-red-500 text-sm">{{ errors.confirm_password }}</span>
                <div class="flex justify-end gap-2 mt-4">
                    <button @click="closeModal" class="px-4 py-2 border rounded">Cancel</button>
                    <button @click="handleChangePassword" :disabled="isProcessing" class="bg-sky-500 text-white px-4 py-2 rounded">
                        <font-awesome-icon icon="spinner" v-if="isProcessing" spin />
                        <span v-else>Update</span>
                    </button>
                </div>
            </template>
        </Modal>

        <!-- Role Modal -->
        <Modal v-if="currentModal === 'role'" @close="closeModal">
            <template #default>
                <h3 class="text-lg font-semibold mb-4">Update Role</h3>
                <select v-model="form.role" class="w-full border px-3 py-2 rounded">
                    <option value="Admin">Admin</option>
                    <option value="SuperAdmin">SuperAdmin</option>
                </select>
                <div class="flex justify-end gap-2 mt-4">
                    <button @click="closeModal" class="px-4 py-2 border rounded">Cancel</button>
                    <button @click="handleChangeRole" :disabled="isProcessing" class="bg-sky-500 text-white px-4 py-2 rounded">
                        <font-awesome-icon icon="spinner" v-if="isProcessing" spin />
                        <span v-else>Update</span>
                    </button>
                </div>
            </template>
        </Modal>
    </div>
</template>
