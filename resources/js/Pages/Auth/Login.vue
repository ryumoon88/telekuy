<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import Checkbox from "@/Components/Checkbox.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Button, Card, InputText, Message } from "primevue";
import Default from "@/Layouts/Default.vue";

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        remember: form.remember ? "on" : "",
    })).post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <Head title="Log in" />

    <Default>
        <Card class="w-full max-w-2xl m-auto">
            <template #content>
                <img src="internal/telekuy-primary.png" class="object-contain py-10 mx-auto w-52"/>

                <div
                    v-if="status"
                    class="mb-4 text-sm font-medium text-green-600 dark:text-green-400"
                >
                    {{ status }}
                </div>

                <form @submit.prevent="submit">
                    <div>
                        <InputLabel for="email" value="Email" />
                        <InputText
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="block w-full mt-1"
                            required
                            autofocus
                            autocomplete="username"
                        />
                        <Message
                            severity="error"
                            variant="simple"
                            class="mt-2"
                            :message="form.errors.email"
                        />
                    </div>

                    <div class="mt-4">
                        <InputLabel for="password" value="Password" />
                        <TextInput
                            id="password"
                            v-model="form.password"
                            type="password"
                            class="block w-full mt-1"
                            required
                            autocomplete="current-password"
                        />
                        <Message
                            severity="error"
                            variant="simple"
                            class="mt-2"
                            :message="form.errors.password"
                        />
                    </div>

                    <div class="block mt-4">
                        <label class="flex items-center">
                            <Checkbox
                                v-model:checked="form.remember"
                                name="remember"
                            />
                            <span class="text-sm ms-2">Remember me</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <Button
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            variant="link"
                            severity="contrast"
                            :as="Link"
                        >
                            Forgot your password?
                        </Button>

                        <Button
                            size="small"
                            class="ms-4"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                            type="submit"
                        >
                            Log in
                        </Button>
                    </div>
                </form>
            </template>
        </Card>
    </Default>
</template>
