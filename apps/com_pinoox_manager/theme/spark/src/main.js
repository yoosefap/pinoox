import { createApp } from "vue";
import 'dockbar';
import App from "./App.vue";
import store from "@/stores";
import router from "./router";
import "@/assets/styles/tailwind-config.css";
import "@/assets/styles/main.scss";
import { createModal } from '@kolirt/vue-modal'
import Notifications, { notify } from '@kyvg/vue3-notification';
import { bindNotify } from '@utils/helpers/toastHelper.js';

const app = createApp(App);

// ---------------------------- Plugins ----------------------------
app.use(store);
app.use(router);
app.use(Notifications);
bindNotify(notify);
app.use(createModal({
    groups: {
        default: {
            disableCloseOnInteractOutside: true,
        },
    },
}))

//---------------------------- Mixin ----------------------------

import {saxIcon} from '@/const/icons.js';

app.mixin({data: () => ({saxIcon})});


// ---------------------------- Mount ----------------------------
app.mount("#app");
