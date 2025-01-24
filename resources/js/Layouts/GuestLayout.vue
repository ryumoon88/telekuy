<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import Navbar from "@/Components/Header/Navbar.vue";
import Footer from "@/Components/Footer/Footer.vue";
import NavLink from "@/Components/NavLink.vue";
import { Link } from "@inertiajs/vue3";
</script>

<template>
    <div
        class="flex flex-col items-center min-h-screen pt-6 bg-gradient-to-t from-[#041740] via-[#1B4773]  to-[#1B4773] sm:justify-center sm:pt-0 dark:bg-gray-900"
    >
    <!-- <div
        class="flex flex-col items-center min-h-screen pt-6 bg-gray-100 sm:justify-center sm:pt-0 dark:bg-gray-900"
    > -->
        <Navbar />

        <div
            class="w-full px-6 py-4 mt-6 overflow-hidden bg-[#0E275E] shadow-md sm:max-w-md sm:rounded-lg dark:bg-gray-800"
        >
            <slot />
        </div>

        <Footer />
    </div>
</template>
