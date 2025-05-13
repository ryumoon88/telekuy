<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DeleteUserForm from "./Partials/DeleteUserForm.vue";
import UpdatePasswordForm from "./Partials/UpdatePasswordForm.vue";
import UpdateProfileInformationForm from "./Partials/UpdateProfileInformationForm.vue";
import { Head } from "@inertiajs/vue3";
import Default from "@/Layouts/Default.vue";
import { Card } from "primevue";

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profile" />
    <div class="w-full py-12">
        <div class="mx-auto space-y-6 max-w-7xl sm:px-6 lg:px-8">
            <Card class="">
                <template #title> Profile Information </template>
                <template #subtitle>
                    Update your account's profile information and email address.
                </template>
                <template #content>
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </template>
            </Card>

            <Card>
                <template #title> Update Password </template>
                <template #subtitle>
                    Ensure your account is using a long, random password to stay
                    secure.
                </template>
                <template #content>
                    <UpdatePasswordForm class="max-w-xl" />
                </template>
            </Card>

            <!-- <div
                    class="p-4 bg-white shadow sm:rounded-lg sm:p-8 dark:bg-gray-800"
                >
                    <DeleteUserForm class="max-w-xl" />
                </div> -->
        </div>
    </div>
</template>
