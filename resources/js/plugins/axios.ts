import axios from "axios";
import store from "@/stores";

const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL || "http://localhost:8000", // default fallback
});

// Attach token from Vuex
api.interceptors.request.use((config) => {
    const token = store.getters["auth/token"];
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

export default api;
