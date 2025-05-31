import { AuthState } from "@/types/auth";

export const user = (state: AuthState) => state.user;
export const token = (state: AuthState) => state.token;
export const error = (state: AuthState) => state.error;
export const isLoading = (state: AuthState) => state.isLoading;
