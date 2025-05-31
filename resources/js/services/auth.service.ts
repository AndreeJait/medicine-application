// src/services/auth.service.ts
import api from "@/plugins/axios";

export const login = (email: string, password: string) => {
    return api.post("/login", {
        request_header: {
            usecase: "login",
            source: "web",
        },
        identifier: email,
        password,
    });
};

export const getMe = () => {
    return api.get("/me", {
        params: {
            source: "usecase",
            usecase: "get user",
        },
    });
};

export const logout = () => {
    return api.post("/logout", {
        request_header: {
            usecase: "logout",
            source: "web",
        },
    });
};
