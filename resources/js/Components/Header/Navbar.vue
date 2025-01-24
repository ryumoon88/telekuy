<script setup>
// import {
//     Disclosure,
//     DisclosureButton,
//     DisclosurePanel,
//     Menu,
//     MenuButton,
//     MenuItem,
//     MenuItems,
// } from "@headlessui/vue";
import { Bars3Icon, BellIcon, XMarkIcon } from "@heroicons/vue/24/outline";
import { Avatar, Button, Menu, Menubar } from "primevue";
import { ref } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import UserBalance from "@/Components/UserBalance.vue";
import Cart from "@/Components/Cart.vue";
import LatestOrders from "../Order/LatestOrders.vue";
import NavLink from "../NavLink.vue";

const navItems = [
    {
        label: "Account",
        href: route("products.index", { type: "account" }),
        route: true,
        active: true,
    },
    {
        label: "Bot",
        href: route("products.index", { type: "bot" }),
        route: true,
    },
    {
        label: "Referral",
        href: route("products.index", { type: "referral" }),
        route: true,
    },
    {
        label: "Products",
        href: route("home"),
        route: true,
    },
];

function getNavItems() {
    const { url } = usePage();
    return navItems.map((item) => ({
        ...item,
        active: item.href.endsWith(url), // Check if the current URL matches or starts with the nav item URL
    }));
}

const profileItems = [
    { label: "Profile", url: route("profile.edit") },
    { label: "Top-Up", url: route("top-up.index") },
    { label: "Setting" },
    {
        label: "Sign out",
        command: () => {
            router.post(route("logout"));
        },
    },
];

const menuToggle = ref();

function toggleMenu(e) {
    menuToggle.value.toggle(e);
}

console.log(getNavItems());
</script>

<style>
.profile-menu-button {
    @apply hover:bg-none;
}
</style>

<template>
    <Menubar
        :model="getNavItems()"
        class="px-4 py-2"
        :pt="{
            root: {
                class: 'max-w-7xl mx-auto',
            },
            rootList: {
                class: 'w-full justify-center',
            },
        }"
    >
        <template #start>
            <Link :href="route('home')" class="flex items-center shrink-0">
                <img
                    class="w-auto h-8"
                    src="/internal/telekuy-navbar.png"
                    alt="Telekuy"
                />
            </Link>
        </template>
        <template #item="{ item, props, hasSubmenu }">
            <NavLink v-if="item.route" v-bind="{ ...props.action, ...item }">
                <span v-ripple>
                    <span :class="item.icon" />
                    <span>{{ item.label }}</span>
                </span>
            </NavLink>
        </template>
        <template #end>
            <div class="flex gap-4" v-if="$page.props.auth.user">
                <UserBalance />
                <Cart />
                <LatestOrders />
                <Button
                    id="profile-menu-button"
                    type="button"
                    icon="pi pi-ellipsis-v"
                    @click="toggleMenu"
                    aria-haspopup="true"
                    aria-controls="overlay_menu"
                    variant="link"
                >
                    <Avatar
                        image="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"
                        shape="circle"
                    />
                </Button>
                <Menu
                    ref="menuToggle"
                    id="overlay_menu"
                    :model="profileItems"
                    :popup="true"
                ></Menu>
            </div>
            <div class="flex gap-4" v-else>
                <Button
                    as="a"
                    :href="route('register')"
                    label="Register"
                    size="small"
                />
                <Button
                    as="a"
                    :href="route('login')"
                    label="Login"
                    severity="secondary"
                    size="small"
                />
            </div>
        </template>
    </Menubar>

    <!-- <Disclosure as="nav" class="bg-gray-800" v-slot="{ open }">
        <div class="px-2 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="relative flex items-center justify-between h-16">
                <div
                    class="absolute inset-y-0 left-0 flex items-center sm:hidden"
                >
                    <DisclosureButton
                        class="relative inline-flex items-center justify-center p-2 text-gray-400 rounded-md hover:bg-gray-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
                    >
                        <span class="absolute -inset-0.5" />
                        <span class="sr-only">Open main menu</span>
                        <Bars3Icon
                            v-if="!open"
                            class="block size-6"
                            aria-hidden="true"
                        />
                        <XMarkIcon
                            v-else
                            class="block size-6"
                            aria-hidden="true"
                        />
                    </DisclosureButton>
                </div>
                <div
                    class="flex items-center justify-center flex-1 sm:items-stretch sm:justify-between"
                >
                    <a :href="route('home')" class="flex items-center shrink-0">
                        <img
                            class="w-auto h-8"
                            src="/internal/telekuy-navbar.png"
                            alt="Telekuy"
                        />
                    </a>
                    <div class="hidden sm:ml-6 sm:block">
                        <div class="flex space-x-4">
                            <a
                                v-for="item in navigation"
                                :key="item.name"
                                :href="item.href"
                                :class="[
                                    item.current
                                        ? 'bg-gray-900 text-white'
                                        : 'text-gray-300 hover:bg-gray-700 hover:text-white',
                                    'rounded-md px-3 py-2 text-sm font-medium',
                                ]"
                                :aria-current="
                                    item.current ? 'page' : undefined
                                "
                                >{{ item.name }}</a
                            >
                        </div>
                    </div>
                    <div
                        class="absolute inset-y-0 right-0 flex items-center pr-2 sm:static sm:inset-auto sm:ml-6 sm:pr-0"
                    >
                        <template v-if="$page.props.auth.user">
                            <button
                                type="button"
                                class="relative p-1 text-gray-400 bg-gray-800 rounded-full hover:text-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800"
                            >
                                <span class="absolute -inset-1.5" />
                                <span class="sr-only">View notifications</span>
                                <BellIcon class="size-6" aria-hidden="true" />
                            </button>

                            <Menu as="div" class="relative ml-3">
                                <div>
                                    <MenuButton
                                        class="relative flex text-sm bg-gray-800 rounded-full focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800"
                                    >
                                        <span class="absolute -inset-1.5" />
                                        <span class="sr-only"
                                            >Open user menu</span
                                        >
                                        <img
                                            class="rounded-full size-8"
                                            src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"
                                            alt=""
                                        />
                                    </MenuButton>
                                </div>
                                <transition
                                    enter-active-class="transition duration-100 ease-out"
                                    enter-from-class="transform scale-95 opacity-0"
                                    enter-to-class="transform scale-100 opacity-100"
                                    leave-active-class="transition duration-75 ease-in"
                                    leave-from-class="transform scale-100 opacity-100"
                                    leave-to-class="transform scale-95 opacity-0"
                                >
                                    <MenuItems
                                        class="absolute right-0 z-10 w-48 py-1 mt-2 origin-top-right bg-white rounded-md shadow-lg ring-1 ring-black/5 focus:outline-none"
                                    >
                                        <MenuItem v-slot="{ active }">
                                            <a
                                                href="#"
                                                :class="[
                                                    active
                                                        ? 'bg-gray-100 outline-none'
                                                        : '',
                                                    'block px-4 py-2 text-sm text-gray-700',
                                                ]"
                                                >Your Profile</a
                                            >
                                        </MenuItem>
                                        <MenuItem v-slot="{ active }">
                                            <a
                                                href="#"
                                                :class="[
                                                    active
                                                        ? 'bg-gray-100 outline-none'
                                                        : '',
                                                    'block px-4 py-2 text-sm text-gray-700',
                                                ]"
                                                >Settings</a
                                            >
                                        </MenuItem>
                                        <MenuItem v-slot="{ active }">
                                            <a
                                                href="#"
                                                :class="[
                                                    active
                                                        ? 'bg-gray-100 outline-none'
                                                        : '',
                                                    'block px-4 py-2 text-sm text-gray-700',
                                                ]"
                                                >Sign out</a
                                            >
                                        </MenuItem>
                                    </MenuItems>
                                </transition>
                            </Menu>
                        </template>

                        <template v-else>
                            <a
                                :href="route('register')"
                                class="px-3 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg focus:outline-none hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700"
                            >
                                Register
                            </a>

                            <a
                                :href="route('login')"
                                class="ms-2 focus:outline-none bg-[#FAA11A] hover:bg-yellow-500 focus:ring-1 focus:ring-yellow-300 font-medium rounded-lg text-sm px-3 py-2 dark:focus:ring-yellow-900"
                            >
                                Login
                            </a>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <DisclosurePanel class="sm:hidden">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <DisclosureButton
                    v-for="item in navigation"
                    :key="item.name"
                    as="a"
                    :href="item.href"
                    :class="[
                        item.current
                            ? 'bg-gray-900 text-white'
                            : 'text-gray-300 hover:bg-gray-700 hover:text-white',
                        'block rounded-md px-3 py-2 text-base font-medium',
                    ]"
                    :aria-current="item.current ? 'page' : undefined"
                    >{{ item.name }}</DisclosureButton
                >
            </div>
        </DisclosurePanel>
    </Disclosure> -->
</template>
