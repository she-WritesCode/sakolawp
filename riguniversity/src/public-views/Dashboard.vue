<script setup lang="ts">
import { ref, onMounted } from 'vue';
import Toast from 'primevue/toast';
import Button from 'primevue/button';
import StudentLayout from './../components/StudentLayout.vue'

const userFirstName = ref('');

onMounted(() => {
  userFirstName.value = skwp_ajax_object.userInfo.user_name;
});

</script>
<template>

<StudentLayout>
    <Toast position="bottom-center" />
    <div class="space-y-10">
        <div class="md:flex justify-between">
            <div class="capitalize">
                <h2 class="text-primary-700 font-bold !mb-1">
                    Hello!  {{ userFirstName }}
                </h2>
                <div>
                    <h5>
                        Welcome Back
                    </h5>
                </div>
            </div>

            <div>
                <Button class=" rounded-xl" label="Take Attendance"></Button>
            </div>

        </div>

        <!-- Assesements -->
        <div class="">
            <h2 class="text-primary-600 font-semibold">
                Upcoming Assesement
            </h2>
    
        <div class="grid lg:grid-cols-3 gap-6">
            <div v-for="assesement in [1, 2, 3, 4, 5, 6]" :key="assesement"
                class="grid grid-cols-5 gap-10 content-center justify-center items-center border-2 bg-white rounded-md p-5">

                <div class="col-span-4">
                    <h4 class="mb-2 font-semibold text-xl">Week {{ assesement }} Assesement</h4>
                    <div class="font-medium capitalize">assesement</div>
                    <small class="font-semibold text-xs">
                        <span><strong>Due</strong></span> <span>Tomorrow 9:30PM</span>
                    </small>
                </div>

                <div class="col-span-1">

                    <img src="./../assets/undone.svg" />

                </div>

            </div>

        </div>

    </div>


        <!-- Meetings -->
        <div class="">
            <h2 class="text-primary-600 font-semibold">Meetings</h2>
            <div class="grid md:grid-cols-2 gap-5">
                <div v-for="meeting in [1, 2]" :key="meeting" class="bg-white border-2 rounded-xl p-4 grid grid-cols-3 md:grid-cols-5 gap-3 md:gap-1">
                    <div class="size-20 flex flex-col items-center justify-center font-medium bg-black text-white rounded-xl p-3">
                            <div>MON</div>
                            <div>0{{ meeting }}</div>
                    </div>

                    <div class="col-span-2 md:col-span-4 lg:flex justify-between items-center w-full">

                        <div>
                            <h5 class="mb-0 font-semibold ">
                                Class Meeting
                            </h5>
                            <div class="font-medium text-sm">Location of Meeting</div>
                            <div class="inline-flex gap-1">
                                <img src="./../assets/clock.svg" />
                                <small>09.00 - 10:00</small>
                            </div>
                        </div>
                        <div class="flex gap-2 font-semibold text-sm"> View  <img src="./../assets/chevron-right.svg" /></div>

                    </div>

                </div>

            </div>
        </div>

        <!-- Program Enrolled -->
        <div class="">
            <h2 class="text-primary-600 font-semibold">Program Enrolled</h2>

            <div class="grid md:grid-cols-2 gap-5">
                <div v-for="meeting in [1, 2]" :key="meeting" class="bg-white border-2 rounded-lg p-5">
                    <div class="space-y-3 w-full">
                        <div class="flex justify-between">
                            <h4 class=" font-semibold text-xl mb-0">RUN LAGOS 2024</h4>
                            <div class="text-orange-500  font-semibold text-xl">45%</div>
                        </div>
                        <div class="w-full rounded-full bg-gray-200">
                            <div class="bg-orange-500 rounded-full w-[45%] h-full p-1"></div>
                        </div>
                        <div>
                            <small class="font-semibold text-sm">started 2 Months ago</small>
                        </div>
                </div>
                </div>

            </div>

        </div>
    </div>
</StudentLayout>

</template>

<style scoped></style>