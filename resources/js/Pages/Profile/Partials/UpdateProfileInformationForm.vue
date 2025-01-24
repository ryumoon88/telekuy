<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Link, useForm, usePage } from "@inertiajs/vue3";
import { Button, InputText, Message } from "primevue";

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section>
        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="mt-6 space-y-6"
        >
            <div>
                <InputLabel for="name" value="Name" />

                <InputText
                    id="name"
                    type="text"
                    class="block w-full mt-1"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <Message
                variant="simple"
                    severity="error"
                    class="mt-2"
                    :message="form.errors.name"
                />
            </div>

            <div>
                <InputLabel for="email" value="Email" />

                <InputText
                    id="email"
                    type="email"
                    class="block w-full mt-1"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <Message
                    severity="error"
                    variant="simple"
                    class="mt-2"
                    :message="form.errors.email"
                    
                />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <Message severity="danger" variant="simple">
                    Your email address is unverified.
                    <Button
                        severity="secondary"
                        variant="link"
                        :href="route('verification.send')"
                        method="post"
                        :as="Link"
                    >
                        Click here to re-send the verification email.
                    </Button>
                </Message>

                <Message
                    v-show="status === 'verification-link-sent'"
                    variant="simple"
                    severity="success"
                >
                    A new verification link has been sent to your email address.
                </Message>
            </div>

            <div class="flex items-center gap-4">
                <Button size="small" type="submit" :disabled="form.processing"
                    >Save</Button
                >

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <Message
                        v-if="form.recentlySuccessful"
                        severity="success"
                        variant="simple"
                    >
                        Saved.
                    </Message>
                </Transition>
            </div>
        </form>
    </section>
</template>
