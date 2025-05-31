import { AuthState } from "@/types/auth";
import { RootState } from "@/types/store";
import { ActionContext } from "vuex";
import Cookies from "js-cookie";
import { Commit } from "vuex";
import {getMe, login, logout} from "@/services/auth.service.ts";

type AuthContext = ActionContext<AuthState, RootState>;

export const loginAsync = async (
    { commit }: { commit: Commit },
    payload: { email: string; password: string }
) => {
    commit("SET_BY_KEY", { key: "isLoading", value: true });

    try {
        const res = await login(payload.email, payload.password);
        const { token, user } = res.data.data;
        commit("SET_BY_KEY", { key: "token", value: token });
        commit("SET_BY_KEY", { key: "user", value: user });

        // Simpan token ke cookie
        Cookies.set("token", token, { expires: 7, sameSite: "Lax" }); // 7 hari

        return { success: true };
    } catch (err: any) {
        const message = err.response?.data?.response_header?.message || "Login failed";
        return {
            success: false,
            message: message.split(":")[1] || "internal server error",
            code: err.response?.data?.response_header?.code ?? 0,
        };
    } finally {
        commit("SET_BY_KEY", { key: "isLoading", value: false });
    }
}

export const logoutAsync = async ({ commit }: { commit: Commit }) => {
    try {
        // Optional: hit API logout endpoint
        await logout();
    } catch (err) {
        console.warn("Logout API failed silently:", err);
        // Tetap lanjut reset session meskipun logout API gagal
    } finally {
        // Hapus data dari store dan cookie
        commit("SET_BY_KEY", { key: "token", value: null });
        commit("SET_BY_KEY", { key: "user", value: null });
        Cookies.remove("token");
    }
};

export const initAuthAsync = async ({ commit, state }: { commit: Commit; state: any }) => {
    const token = Cookies.get("token");
    if (!token) return;

    // Simpan token dari cookie
    commit("SET_BY_KEY", { key: "token", value: token });

    // Jika user sudah ada (tidak perlu getMe)
    if (state.user?.id) return;

    try {
        const res = await getMe();
        commit("SET_BY_KEY", { key: "user", value: res.data.data });
    } catch {
        // Jika gagal ambil user, reset token
        commit("SET_BY_KEY", { key: "token", value: null });
        Cookies.remove("token");
    }
};
