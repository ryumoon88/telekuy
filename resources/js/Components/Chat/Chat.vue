<script>
import { useForm } from "@inertiajs/vue3";
import {
    Button,
    Card,
    ConfirmDialog,
    Divider,
    InputGroup,
    InputGroupAddon,
    InputText,
    useConfirm,
} from "primevue";
import { defineComponent, ref } from "vue";
import ChatBubble from "./ChatBubble.vue";
import axios from "axios";
import moment from "moment";

export default defineComponent({
    components: {
        Button,
        Card,
        InputGroup,
        InputGroupAddon,
        InputText,
        Divider,
        ChatBubble,
        ConfirmDialog,
    },
    props: {
        chat: Object,
    },
    setup() {
        const confirm = useConfirm();
        return {
            confirm,
        };
    },
    data() {
        return {
            isOpen: false,
            button: {
                icon: "pi pi-comment",
            },
            messageToSend: null,
            messages: [],
            processing: false,
        };
    },
    mounted() {
        this.loadMessage();
        Echo.private(`chat.${this.chat.id}`).listen(
            "Client\\MessageSent",
            (event) => {
                this.messageReceived(event.message);
            }
        );
    },

    methods: {
        loadMessage() {
            axios
                .get(route("chats.messages", { chat: this.chat.id }))
                .then((response) => {
                    this.messages = response.data.data;
                    this.scrollToBottom();
                });
        },
        toggleChat() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.button.icon = "pi pi-times";
                this.scrollToBottom();
            } else this.button.icon = "pi pi-comment";
        },
        send() {
            if (this.messageToSend) {
                this.processing = true;
                axios
                    .post(
                        route("chats.send", { chat: this.chat.id }),
                        {
                            message: this.messageToSend,
                        },
                        {
                            headers: {
                                "X-Socket-ID": window.Echo.socketId(),
                            },
                        }
                    )
                    .then((response) => {})
                    .finally(() => {
                        this.messageSent(this.messageToSend);
                        this.processing = false;
                    });
            }
        },
        messageSent(message) {
            this.messages.push({
                sender: this.$page.props.auth.user.name,
                you: true,
                message: message,
                sent_at: moment(),
            });
            this.messageToSend = null;
            this.scrollToBottom();
        },

        messageReceived(message) {
            this.messages.push(message);
            this.scrollToBottom();
        },
        scrollToBottom() {
            this.$nextTick(() => {
                console.log(
                    this.$refs.bottomChat.scrollIntoView({ behavior: "smooth" })
                );
            });
        },
        confirmCloseChat() {
            this.confirm.require({
                header: "Close chat?",
                message: "This action can't be undone",
                icon: "pi pi-exclamation-triangle",
                rejectProps: {
                    label: "Cancel",
                    severity: "secondary",
                    outlined: true,
                },
                acceptProps: {
                    label: "Close",
                    severity: "danger",
                },
                accept: () => {
                    axios
                        .delete(
                            route("chats.close", { chat: this.$props.chat.id }),
                            {
                                headers: {
                                    "X-Socket-ID": window.Echo.socketId(),
                                },
                            }
                        )
                        .then((response) => {
                            this.$emit("chatClosed");
                        });
                },
            });
        },
    },
});
</script>
<style>
.p-card-body {
    @apply h-full;
}
.p-card-content {
    @apply h-full max-h-full overflow-y-hidden;
}
.p-card-footer {
    @apply items-end;
}
</style>
<template>
    <div class="">
        <Button
            @click="toggleChat"
            v-if="!isOpen"
            :icon="button.icon"
            size="large"
            class="w-14 h-14"
            rounded
            aria-label="Star"
        />
        <ConfirmDialog />
        <Card class="w-[400px] h-[600px] border" v-show="isOpen">
            <template #title>
                <div class="flex justify-between">
                    <span>{{ chat.title }}</span>
                    <div class="flex">
                        <Button
                            size="small"
                            icon="pi pi-minus"
                            variant="text"
                            @click="toggleChat"
                        />
                        <Button
                            size="small"
                            icon="pi pi-times"
                            variant="text"
                            @click="confirmCloseChat()"
                        />
                    </div>
                </div>
            </template>
            <template #subtitle>#{{ chat.description }} <Divider /></template>

            <template #content>
                <div
                    class="flex flex-col h-full max-h-full gap-3 overflow-y-auto scrollbar-thin"
                    ref="chatContainer"
                >
                    <ChatBubble
                        v-for="(message, index) in messages"
                        :key="index"
                        :message="message"
                    />
                    <div ref="bottomChat"></div>
                </div>
            </template>

            <template #footer>
                <Divider />
                <form @submit.prevent="send">
                    <InputGroup>
                        <InputText v-model="messageToSend"></InputText>
                        <InputGroupAddon>
                            <Button
                                icon="pi pi-send"
                                type="submit"
                                :disabled="processing"
                            />
                        </InputGroupAddon>
                    </InputGroup>
                </form>
            </template>
        </Card>
    </div>
</template>
