import state from "./state";
import { medicines } from "./getters";
import * as mutations from "./mutations";
import * as actions from "./actions";

export default {
    namespaced: true,
    state,
    getters: {
        medicines
    },
    mutations,
    actions
}
