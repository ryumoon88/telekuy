<script setup>
import Navbar from "@/Components/Header/Navbar.vue";
import Footer from "@/Components/Footer/Footer.vue";
import ChatContainer from "@/Components/Chat/ChatContainer.vue";
import { Toast } from "primevue";
</script>

<template>
    <div
        class="flex flex-col justify-between items-center min-h-screen bg-gradient-to-t from-[#041740] via-[#1B4773] to-[#1B4773] sm:pt-0 dark:bg-gray-900"
    >
        <header
            class="fixed top-0 left-0 w-full bg-[#04163D] text-[#2C2F35] z-[999]"
        >
            <Navbar></Navbar>
        </header>

        <main
            class="flex flex-col items-center w-full max-w-6xl min-h-[calc(100vh-93px-209px)] pt-24 pb-12"
        >
            <slot />
        </main>

        <Footer />
        <Toast />
        <ChatContainer v-if="$page.props.auth.user" />
    </div>
</template>
