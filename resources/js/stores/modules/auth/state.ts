import { AuthState } from "@/types/auth";


const state: AuthState = {
    user: {
        profile: "",
        id: "",
        name: "Andree Panjaitan",
        email: "panjaitanandree@gmail.com",
        active: true,
    },
    isLoading: false,
    token: null,
    refreshToken: null,
    error: null,
};

export default state;
