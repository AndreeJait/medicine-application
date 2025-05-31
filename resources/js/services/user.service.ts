import axios from "@/plugins/axios";

export const getUsers = (
    page: number,
    per_page: number,
    search: string = ""
) => {
    return axios.get("/users", {
        params: {
            source: "web",
            usecase: "get-users",
            page,
            per_page,
            search,
        },
    });
};

export const getUserDetail = (id: number) => {
    return axios.get(`/users/${id}`, {
        params: {
            source: "web",
            usecase: "get-user",
        },
    });
};

export const createUser = (data: {
    name: string;
    email: string;
    nik: string;
    position: string;
    password: string;
    role: string;
    is_active?: boolean;
}) => {
    return axios.post("/users", {
        request_header: {
            usecase: "create user",
            source: "web",
        },
        ...data,
    });
};

export const updateUser = (
    id: number,
    data: {
        name: string;
        email: string;
        position: string;
        is_active: boolean;
    }
) => {
    return axios.put(`/users/${id}`, {
        request_header: {
            usecase: "update user",
            source: "web",
        },
        ...data,
    });
};

export const updateUserRole = (id: number, role: string) => {
    return axios.put(`/users/${id}/role`, {
        request_header: {
            usecase: "update role",
            source: "web",
        },
        role,
    });
};

export const changeUserPassword = (
    id: number,
    password: string,
    confirm_password: string
) => {
    return axios.put(`/users/${id}/password`, {
        request_header: {
            usecase: "change-password",
            source: "web",
        },
        password,
        confirm_password,
    });
};

export const deleteUser = (id: number) => {
    return axios.delete(`/users/${id}`, {
        data: {
            request_header: {
                usecase: "delete user",
                source: "web",
            },
        },
    });
};
