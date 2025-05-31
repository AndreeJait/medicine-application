<template>
    <div class="max-w-md mx-auto bg-white p-6 rounded shadow space-y-4">
        <h2 class="text-xl font-bold text-sky-600 mb-2">Change Password</h2>

        <!-- Current Password -->
        <div>
            <label class="block font-medium">Current Password</label>
            <div class="relative">
                <input
                    :type="showCurrent ? 'text' : 'password'"
                    v-model="form.current_password"
                    class="w-full border px-3 py-2 rounded mt-1 pr-10"
                    required
                />
                <button
                    type="button"
                    class="absolute top-2.5 right-3 text-gray-500"
                    @click="showCurrent = !showCurrent"
                >
                    <font-awesome-icon :icon="showCurrent ? 'eye-slash' : 'eye'" />
                </button>
            </div>
        </div>

        <!-- New Password -->
        <div>
            <label class="block font-medium">New Password</label>
            <div class="relative">
                <input
                    :type="showNew ? 'text' : 'password'"
                    v-model="form.password"
                    class="w-full border px-3 py-2 rounded mt-1 pr-10"
                    required
                />
                <button
                    type="button"
                    class="absolute top-2.5 right-3 text-gray-500"
                    @click="showNew = !showNew"
                >
                    <font-awesome-icon :icon="showNew ? 'eye-slash' : 'eye'" />
                </button>
            </div>
            <p class="text-xs mt-1" :class="passwordTooShort ? 'text-red-500' : 'text-green-600'">
                {{ passwordTooShort ? 'Minimum 8 characters required' : 'Looks good' }}
            </p>
        </div>

        <!-- Confirm Password -->
        <div>
            <label class="block font-medium">Confirm New Password</label>
            <div class="relative">
                <input
                    :type="showConfirm ? 'text' : 'password'"
                    v-model="form.confirm_password"
                    class="w-full border px-3 py-2 rounded mt-1 pr-10"
                    required
                />
                <button
                    type="button"
                    class="absolute top-2.5 right-3 text-gray-500"
                    @click="showConfirm = !showConfirm"
                >
                    <font-awesome-icon :icon="showConfirm ? 'eye-slash' : 'eye'" />
                </button>
            </div>
            <p class="text-xs mt-1" :class="!passwordsMatch ? 'text-red-500' : 'text-green-600'">
                {{ !passwordsMatch ? 'Passwords do not match' : 'Passwords match' }}
            </p>
        </div>

        <!-- Feedback -->
        <div v-if="error" class="text-red-600 text-sm">{{ error }}</div>
        <div v-if="success" class="text-green-600 text-sm">{{ success }}</div>

        <!-- Submit -->
        <button
            @click="submit"
            :disabled="loading || passwordTooShort || !passwordsMatch"
            class="bg-sky-500 text-white px-6 py-2 rounded hover:bg-sky-600 flex items-center gap-2"
        >
            <font-awesome-icon v-if="loading" icon="spinner" spin />
            <span>{{ loading ? 'Updating...' : 'Update Password' }}</span>
        </button>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";
import { changeUserPassword } from "@/services/setting.service";

// form data
const form = ref({
    current_password: "",
    password: "",
    confirm_password: "",
});

const loading = ref(false);
const error = ref("");
const success = ref("");

// visibility toggles
const showCurrent = ref(false);
const showNew = ref(false);
const showConfirm = ref(false);

// validations
const passwordTooShort = computed(() => form.value.password.length < 8);
const passwordsMatch = computed(() => form.value.password === form.value.confirm_password);

// submit handler
const submit = async () => {
    error.value = "";
    success.value = "";

    try {
        loading.value = true;
        await changeUserPassword(form.value);
        success.value = "Password updated successfully.";
        form.value = {
            current_password: "",
            password: "",
            confirm_password: "",
        };
    } catch (err: any) {
        error.value = err?.response?.data?.response_header?.message || "Failed to change password.";
    } finally {
        loading.value = false;
    }
};
</script>
