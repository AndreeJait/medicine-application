import { AuthState } from "@/types/auth";

export const SET_BY_KEY = (state: AuthState, param: {key: string, value: any}) => state[param.key] = param.value;
