<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
    name: "NotFound",
    data() {
        return {
        };
    }
});
</script>

<template>
    <div class="flex items-center justify-center w-screen h-screen bg-not-found">
    </div>
</template>

<style>
    .bg-not-found {
        background: url("../../assets/images/bg/not_found.png") no-repeat center center;
        background-size: cover;
    }
</style>
