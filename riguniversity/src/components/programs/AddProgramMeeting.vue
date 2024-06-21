<script setup lang="ts">
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import { useForm } from 'vee-validate';
import { createprogramMeetingSchema, useprogramMeetingStore, type CreateprogramMeeting } from '../../stores/program-meeting';
import { DateHelper } from '../../utils/date';
import { toTypedSchema } from '@vee-validate/yup';
import { onMounted } from 'vue';
import { watch } from 'vue';

const props = defineProps<{
    programId: string;
    initialValues?: CreateprogramMeeting & { ID: number };
}>()
const { errors, defineField, handleSubmit, setValues, values } = useForm<CreateprogramMeeting & { ID?: number }>({
    initialValues: {
        title: "",
        content: "",
        meta: {
            _sakolawp_event_date: DateHelper.toSimpleBackendDateString(new Date()),
            _sakolawp_event_date_clock: DateHelper.toSimpleBackendTimeString(new Date()),
            _sakolawp_event_class_id: props.programId,
            _sakolawp_event_location: '',
        },
        ...props.initialValues,
    },
    validationSchema: toTypedSchema(createprogramMeetingSchema),
});

const [title, titleProps] = defineField('title');
const [content, contentProps] = defineField('content');
const [date, dateProps] = defineField('meta._sakolawp_event_date');
const [time, timeProps] = defineField('meta._sakolawp_event_date_clock');
const [location, locationProps] = defineField('meta._sakolawp_event_location');
// we set this from props
// const [class_id, class_idProps] = defineField('meta._sakolawp_event_class_id');

const { createprogramMeeting, closeAddForm, updateprogramMeeting } = useprogramMeetingStore()

const submitForm = handleSubmit((values) => {
    console.log(values);
    if (props.initialValues && props.initialValues?.ID) {
        updateprogramMeeting({ ...values, ID: props.initialValues!.ID })
    } else {

        createprogramMeeting(values)
    }
});
watch(() => props.initialValues, () => {
    setValues({ ...values, ...props.initialValues })
})
onMounted(() => {
    //
})
</script>

<template>
    <form id="myForm" name="Add program Meeting">
        <div class="flex flex-col gap-4 mb-4">
            <div class="form-group">
                <label for="name">Meeting Title</label>
                <InputText placeholder="Meeting Title" name="title" v-model="title" class="w-full"
                    v-bind="titleProps" />
                <div class="p-error text-red-500">{{ errors.title }}</div>
            </div>

            <div class="form-group">
                <label for="content">Content</label>
                <Textarea name="content" placeholder="Write about this event" v-model="content" class="w-full"
                    v-bind="contentProps" :rows='5'></Textarea>
                <div class="p-error text-red-500">{{ errors.content }}</div>
            </div>
            <div class="flex gap-2 items-center">
                <div class="form-group">
                    <label for="class_id">Date</label>
                    <InputText name="date" type="date" v-model="date" class="w-full" v-bind="dateProps" />
                    <div class="p-error text-red-500">{{ errors['meta._sakolawp_event_date'] }}</div>
                </div>

                <div class="form-group">
                    <label for="class_id">Time</label>
                    <InputText name="time" type="time" v-model="time" class="w-full" v-bind="timeProps" />
                    <div class="p-error text-red-500">{{ errors['meta._sakolawp_event_date_clock'] }}</div>
                </div>
            </div>
            <div class="form-group">
                <label for="class_id">Location</label>
                <InputText name="location" placeholder="Enter address" v-model="location" class="w-full"
                    v-bind="locationProps" />
                <div class="p-error text-red-500">{{ errors['meta._sakolawp_event_location'] }}</div>
            </div>
        </div>
        <div class="flex gap-2 justify-between">
            <Button class="w" outlined severity='secondary' type="submit" @click.prevent="closeAddForm">Cancel
            </Button>
            <Button class="w" type="submit" @click.prevent="submitForm">
                {{ initialValues?.ID ? 'Save' : 'Add' }} Meeting
            </Button>
        </div>
    </form>
</template>

<style scoped></style>
