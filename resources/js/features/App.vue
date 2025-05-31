<script lang="ts" setup>
import {defineComponent, onMounted} from 'vue';
import {useStore} from "vuex";
const store = useStore();

onMounted(() => {
    store.dispatch('auth/initAuthAsync');
});
</script>

<template>
    <router-view/>
</template>


<style lang="scss">
@import url('https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap');

* {
    box-sizing: border-box;
    font-family: "Nunito", sans-serif;
    font-optical-sizing: auto;
    font-style: normal;
}

body {
    margin: 0;
    padding: 0;
}

#app {
    min-height: 100vh;
}

.bg-medicine {
    background-image: url("../assets/images/bg/login_bg.png");
}
</style>
