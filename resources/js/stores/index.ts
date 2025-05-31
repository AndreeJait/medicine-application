import { createStore } from "vuex";
import auth from "./modules/auth";
import medicine from "./modules/medicine";


export default createStore({
    modules: {
        auth,
        medicine
    }
});
