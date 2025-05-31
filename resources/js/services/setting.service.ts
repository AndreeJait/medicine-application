// src/services/setting.service.ts
import api from "@/plugins/axios";

export const changeUserPassword = (payload: {
    current_password: string;
    password: string;
    confirm_password: string;
}) => {
    return api.put("/users/me/password", {
        request_header: {
            usecase: "change-password",
            source: "postman",
        },
        ...payload,
    });
};
