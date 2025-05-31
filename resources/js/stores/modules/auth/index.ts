import state from "./state";
import { error, isLoading, token, user } from "./getters";
import * as mutations from "./mutations";
import * as actions from "./actions";

export default {
    namespaced: true,
    state,
    getters: {
        user,
        isLoading,
        token,
        error,
    },
    mutations,
    actions
}
