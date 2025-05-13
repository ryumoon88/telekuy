<script setup>
import { onBeforeMount, onMounted, onUnmounted, ref } from 'vue';
import Chat from './Chat.vue';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const chat = ref(null);

const settings = ref({
    isListened: false,
});

const channel = Echo.private(`user.${page.props.auth.user.id}`);

const fetchChat = () => {
    axios.get(route('chats.latest'))
    .then((response) => {
        const res = response.data;
        if(!res.success){
            if(!settings.value.isListened){
                console.log('listening')
                channel.listen(
                    "Client\\ChatAccepted",
                    (event) => {
                        fetchChat();
                    }
                );
                settings.value.isListened = true; // Mark as listened
            }
        }
        chat.value = res.data;
    });
};

if(!chat.value){
    fetchChat();
}

onUnmounted(() => {
    if (settings.value.isListened) {
        channel.stopListening("Client\\ChatAccepted"); // Stop listening when the component is unmounted
    }
});
</script>

<template>
    <div class="fixed bottom-[25px] right-[25px] z-[999]">
        <Chat v-if="chat != null" :chat="chat" @chat-closed="chat = null" />
    </div>
</template>
