<script setup lang="ts">
import {reactive, ref, watch} from "vue";
import { useStore } from "vuex";
import { useRouter } from "vue-router";
import { AuthState } from "@/types/auth";

interface FormLogin {
    email: string;
    password: string;
}

interface ErrorForm {
    email: string;
    password: string;
}

const store = useStore<{ auth: AuthState }>();
const router = useRouter();

const formLogin = reactive<FormLogin>({
    email: "",
    password: "",
});

const errorForm = reactive<ErrorForm>({
    email: "",
    password: "",
});

const loading = ref(false);

function validate(target: keyof FormLogin | "all" = "all") {
    const regexEmail = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    const regexPassword = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/;

    if (target === "all" || target === "email") {
        if (!formLogin.email.trim()) {
            errorForm.email = "Email is required";
        } else if (!regexEmail.test(formLogin.email)) {
            errorForm.email = "Invalid email format";
        } else {
            errorForm.email = "";
        }
    }

    if (target === "all" || target === "password") {
        if (!formLogin.password.trim()) {
            errorForm.password = "Password is required";
        } else if (!regexPassword.test(formLogin.password)) {
            errorForm.password =
                "Min. 8 characters incl. uppercase, lowercase, number, special char.";
        } else {
            errorForm.password = "";
        }
    }
}

async function login() {
    validate("all");

    if (errorForm.email || errorForm.password) return;

    loading.value = true;

    const result = await store.dispatch("auth/loginAsync", {
        email: formLogin.email,
        password: formLogin.password,
    });

    loading.value = false;

    if (result.success) {
        await router.push("/admin");
    } else {
        if(result.code === "401001") {
            errorForm.email = "invalid email or password"
            errorForm.password = "invalid email or password"
        }else {
            alert(result.message)
        }
    }
}

watch(() => formLogin.email, () => {
    if (errorForm.email === "invalid email or password") {
        errorForm.email = "";
        errorForm.password = "";
    }
});

watch(() => formLogin.password, () => {
    if (errorForm.password === "invalid email or password") {
        errorForm.email = "";
        errorForm.password = "";
    }
});

</script>

<template>
    <div class="bg-medicine w-screen h-screen flex items-center justify-center px-4">
        <div class="w-full max-w-md bg-white rounded-lg shadow-lg p-8">
            <form @submit.prevent="login">
                <h1 class="text-center text-4xl font-bold text-sky-500 mb-6">Login</h1>

                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 font-medium mb-1">Email</label>
                    <input
                        v-model="formLogin.email"
                        @input="validate('email')"
                        id="email"
                        type="email"
                        placeholder="you@example.com"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-sky-300"
                        :class="{ 'border-red-500': errorForm.email }"
                    />
                    <p v-if="errorForm.email" class="text-red-500 text-sm mt-1">
                        {{ errorForm.email }}
                    </p>
                </div>

                <!-- Password -->
                <div class="mb-6">
                    <label for="password" class="block text-gray-700 font-medium mb-1">Password</label>
                    <input
                        v-model="formLogin.password"
                        @input="validate('password')"
                        id="password"
                        type="password"
                        placeholder="********"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-sky-300"
                        :class="{ 'border-red-500': errorForm.password }"
                    />
                    <p v-if="errorForm.password" class="text-red-500 text-sm mt-1">
                        {{ errorForm.password }}
                    </p>
                </div>

                <!-- Buttons -->
                <div class="flex items-center justify-between">
                    <button
                        type="submit"
                        :disabled="loading || errorForm.email.length > 0 || errorForm.password.length > 0"
                        class="bg-sky-500 hover:bg-sky-600 text-white font-semibold py-2 px-4 rounded disabled:opacity-50"
                    >
                        {{ loading ? "Signing in..." : "Sign In" }}
                    </button>
                    <router-link
                        to="/forgot-password"
                        class="text-sm text-sky-500 hover:underline"
                    >
                        Forgot Password?
                    </router-link>
                </div>
            </form>

            <p class="text-center text-gray-500 text-xs mt-6">
                &copy; 2025 Medicine Manager. All rights reserved.
            </p>
        </div>
    </div>
</template>

<style scoped>
.bg-medicine {
    background: linear-gradient(to right, #dfefff, #e8f7ff);
}
</style>
