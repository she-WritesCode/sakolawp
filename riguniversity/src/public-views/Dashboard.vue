<script setup lang="ts">
import { ref, onMounted } from 'vue';
import Toast from 'primevue/toast';
import Button from 'primevue/button';
import StudentLayout from '@/components/StudentLayout.vue';
import { useDashboardStore } from '@/stores/dashboard';
import { DateHelper } from '@/utils/date';
import Skeleton from 'primevue/skeleton';
import { useProgramMeetingStore } from '@/stores/program-meeting';


const userFirstName = ref('');

const { enrolledPrograms, fetchEnrolledPrograms, fetchNews, news : events, filter, loading, studentId } = useDashboardStore();
const { fetchProgramMeetings, programMeetings, goToViewProgramMeeting } = useProgramMeetingStore();
onMounted(() => {
  userFirstName.value = skwp_ajax_object.userInfo.user_name;
  fetchEnrolledPrograms();
  fetchProgramMeetings();
  fetchNews()
  //console.log(programMeetings, 'here');
} 

);


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
                <Button class="w ring-primary text-red-300 heds"  outlined  label="Take Attendance"></Button>
            </div>

        </div>

        <!-- Assesements -->
        <div class="">
            <h2 class="font-semibold">
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
            <h2 class="font-semibold">Meetings</h2>
            <div class="grid md:grid-cols-2 gap-5">
                <div v-for="meeting in programMeetings" :key="meeting.ID" class="bg-white border-2 rounded-xl p-4 grid grid-cols-3 md:grid-cols-5 gap-3 md:gap-1">
                    <div class="size-20 flex flex-col items-center justify-center font-medium bg-black text-white rounded-xl p-3">
                            <div class="uppercase">{{ DateHelper.getFormattedDateInfo(meeting!.date).dayOfWeek }}</div>
                            <div>{{ DateHelper.getFormattedDateInfo(meeting!.date).dayOfMonth }}</div>
                    </div>

                    <div class="col-span-2 md:pl-5 md:col-span-4 lg:flex justify-between items-center w-full">

                        <div>
                            <h5 class="mb-0 font-semibold ">
                                {{  meeting.title }}
                            </h5>
                            <div class="font-medium text-sm">{{ meeting.content }}</div>
                            <div class="inline-flex gap-1">
                                <img src="./../assets/clock.svg" />
                                <small>{{ meeting.meta._sakolawp_event_date_clock[0] }}</small>
                            </div>
                        </div>
                        <div @click="goToViewProgramMeeting(meeting.ID)" class="flex gap-2 font-semibold text-sm cursor-pointer hover:underline"> View  <img src="./../assets/chevron-right.svg" /></div>

                    </div>

                </div>

            </div>
        </div>

        <!-- Program Enrolled -->
        <div class="">
            <h2 class="font-semibold">Programs Enrolled</h2>

            <div class="grid md:grid-cols-2 gap-5">
                <!-- loader -->
                <template v-if="loading.list">
                        <div v-for="i in [1, 2,]" :key="i" class="space-y-3 w-full bg-white border-2 rounded-lg p-5">
                            <div class="flex justify-between">
                                <Skeleton  width="10rem"></Skeleton>
                                <Skeleton  width="5rem"></Skeleton>
                            </div>
                            <div class="w-full rounded-full bg-gray-200">
                                <Skeleton class="rounded-full p-1"></Skeleton>
                            </div>
                            <div>
                                <Skeleton width="10rem" class="text-sm"></Skeleton>
                            </div>
                        </div>
                </template>
                <template v-else>
                    <div v-for="program in enrolledPrograms" :key="program.id" class="bg-white border-2 rounded-lg p-5">
                        <div class="space-y-3 w-full">
                            <div class="flex justify-between">
                                <h4 class="font-semibold text-xl mb-0 uppercase">{{ program.class_name }}</h4>
                                <div class="text-orange-500 font-semibold text-xl">10%</div>
                            </div>
                            <div class="w-full rounded-full bg-gray-200">
                                <!-- <div :style="{ width: program.progress + '%' }" class="bg-orange-500 rounded-full h-full p-1"></div> -->
                                <div class="bg-orange-500 rounded-full w-[45%] h-full p-1"></div>
                            </div>
                            <div>
                                <small class="font-semibold text-sm">Started {{ DateHelper.formatMonthsAgo(program.date_added)  }}</small>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

        </div>


            <!-- Latest News -->
            <div class="">
            <h2 class="font-semibold">Lastest News</h2>
            <div class="grid md:grid-cols-3 gap-5">
                <div v-for="event in events" :key="event.ID.toString()" class="bg-white border-2 rounded-xl p-2 flex items-center gap-3">
                    <div class="w-[45%] flex flex-col items-center justify-center font-medium bg-black text-white rounded-xl">
                            <img src="./../assets/news-default.png" alt="" class="w-full h-full object-fill" /> 
                    </div>

                    <div class="">
                            <h5 class="mb-0 font-semibold ">

                                {{ event.title }}
                            </h5>

                            <div class="text-sm flex w-full justify-between">
                                    <small> {{  DateHelper.relativeTime(event.date)  }}</small>
                                    <small> NEWS RUN</small>
                            </div>
                        

                    </div>

                </div>

            </div>
        </div>

       
    </div>
</StudentLayout>

</template>

<style scoped>
.heds {
    border-color: transparent!important;
}
</style>