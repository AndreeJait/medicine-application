import './bootstrap';
import { createApp } from "vue";
import App from "@/features/App.vue";
import router from '@/routes';
import stores from '@/stores';
import SideBar from "@/components/side-bar/SideBar.vue";

import "./plugins";
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

const app = createApp(App);

app.use(router);
app.use(stores);

app.component("SideBar", SideBar);
app.component('font-awesome-icon', FontAwesomeIcon)

app.mount("#app");
