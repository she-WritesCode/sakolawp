<!-- Layout.vue -->
<template>
    <div>
        <!-- Top Bar -->

        <div class="flex justify-end items-center p-4 gap-x-5">
            <div class="flex items-center" @click="toggleActionMenu"  aria-haspopup="true"
            aria-controls="action_menu">
                <!-- <Avatar image="./../assets/Avatar.png" style="width: 32px; height: 32px" /> -->


                <img src="./../assets/Avatar.png" alt="User Profile" class="w-10 h-10 rounded-full cursor-pointer" />
                <!-- <i class="pi pi-chevron-down ml-2 cursor-pointer" @click="toggleProfileMenu"></i> -->
                <i class="pi pi-chevron-down ml-2 cursor-pointer" @click="toggleActionMenu" aria-haspopup="true"
                    aria-controls="action_menu"></i>
                <Menu ref="actionMenu" id="action_menu" :model="items" :popup="true" />
            </div>

            <div class="relative">
                <i class="pi pi-bell cursor-pointer text-xl" @click="toggleNotifications" aria-haspopup="true"
                    aria-controls="notification_menu"></i>
                <Menu ref="notificationsMenu" id="notification_menu" :model="notifications" :popup="true" />
                <div v-if="notificationsVisible"
                    class="absolute right-0 mt-2 w-64 bg-white border border-gray-200 rounded-md shadow-lg z-50">
                    <ul>
                        <li v-for="notification in notifications" :key="notification.id"
                            class="px-4 py-2 hover:bg-gray-100 cursor-pointer">
                            {{ notification.message }}
                        </li>
                    </ul>
                </div>
                <!-- <div class="card flex justify-center">
                            <Button type="button" @click="toggle" aria-haspopup="true"
                                aria-controls="overlay_menu">
                                <i class="pi pi-bell cursor-pointer text-xl"></i>
                            </Button>
                            <Menu ref="menu" id="overlay_menu" :model="itemz" :popup="true" />
                        </div> -->
            </div>
        </div>


        <!-- Main Content -->
        <main class="p-4">
            <slot></slot>
        </main>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import SplitButton from 'primevue/splitbutton'
import Avatar from 'primevue/avatar'
import Menu from 'primevue/menu'
import Button from 'primevue/button'

const notificationsVisible = ref(false);


const toggleNotifications = (event) => {
    notificationsMenu.value.toggle(event);
};

const toggleActionMenu = (event) => {
    actionMenu.value.toggle(event);
};


const notificationsMenu = ref();
const notifications = ref([
    { id: 1, label: 'You have a new message' },
    { id: 2, label: 'Your assignment is due tomorrow' },
    { id: 3, label: 'Meeting scheduled for Monday' },
]);


const actionMenu = ref();
const items = ref([
    {
        label: 'Profile',
        icon: 'pi pi-user'
    },
    {
        label: 'Settings',
        icon: 'pi pi-wrench'
    }
])


const menu = ref();


const toggle = (event) => {
    menu.value.toggle(event);
};
</script>

<style scoped>
.size-20 {
    width: 20px;
    height: 20px;
}
</style>
