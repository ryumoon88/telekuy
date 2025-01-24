<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, useForm } from "@inertiajs/vue3";
import Default from "@/Layouts/Default.vue";
import { Button, Card, InputText, Message } from "primevue";

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: "",
    password_confirmation: "",
});

const submit = () => {
    form.post(route("password.store"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <Default>
        <Head title="Reset Password" />

        <Card class="w-full max-w-lg m-auto">
            <template #content>
                <form @submit.prevent="submit">
                    <div>
                        <InputLabel for="email" value="Email" />

                        <InputText
                            id="email"
                            type="email"
                            class="block w-full mt-1"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                        />

                        <Message 
                            variant="simple"
                            severity="error"
                        >
                            {{ form.errors.email }}
                        </Message>
                    </div>

                    <div class="mt-4">
                        <InputLabel for="password" value="Password" />

                        <InputText
                            id="password"
                            type="password"
                            class="block w-full mt-1"
                            v-model="form.password"
                            required
                            autocomplete="new-password"
                        />

                        <Message 
                            variant="simple"
                            severity="error"
                        >
                            {{ form.errors.password }}
                        </Message>
                    </div>

                    <div class="mt-4">
                        <InputLabel
                            for="password_confirmation"
                            value="Confirm Password"
                        />

                        <InputText
                            id="password_confirmation"
                            type="password"
                            class="block w-full mt-1"
                            v-model="form.password_confirmation"
                            required
                            autocomplete="new-password"
                        />

                        <Message 
                            variant="simple"
                            severity="error"
                        >
                            {{ form.errors.password_confirmation }}
                        </Message>
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <Button
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                            type="submit"
                            size="small"
                        >
                            Reset Password
                        </Button>
                    </div>
                </form>
            </template>
        </Card>
    </Default>
</template>
