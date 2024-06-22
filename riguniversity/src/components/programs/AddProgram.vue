<script setup lang="ts">
import InputText from 'primevue/inputtext';
import SelectButton from 'primevue/selectbutton';
import MultiSelect from 'primevue/multiselect';
import Button from 'primevue/button';
import { useForm } from 'vee-validate';
import { createProgramSchema, useProgramStore } from '../../stores/program';
import { useCourseStore } from '../../stores/course';
import { toTypedSchema } from '@vee-validate/yup';
import { onMounted } from 'vue';
import { computed } from 'vue';

const { errors, defineField, handleSubmit } = useForm({
    initialValues: {
        name: "",
        drip_method: "days_after_release",
        subjects: [],
        start_date: ''
    },
    validationSchema: toTypedSchema(createProgramSchema),
});

const [name, nameProps] = defineField('name');
const [drip_method, drip_methodProps] = defineField('drip_method');
const [start_date, start_dateProps] = defineField('start_date');
const [subjects, subjectsProps] = defineField('subjects');

const options = [
    { label: "Days after course starts", value: "days_after_release" },
    { label: "Specific dates", value: "specific_dates" },
]

const { courses: allCourses, fetchCourses } = useCourseStore()
const courseOptions = computed(() => allCourses.value.map((s) => ({ label: s.title, value: s.ID })))

const { createProgram: createProgram, closeAddForm } = useProgramStore()

const submitForm = handleSubmit((values) => {
    console.log(values);
    createProgram(values as any)
});

onMounted(() => {
    fetchCourses()
})
</script>

<template>
    <form id="myForm" name="Add program">
        <div class="flex flex-col gap-4 mb-4">
            <div class="form-group">
                <label for="name">program Name</label>
                <InputText placeholder="program Name" name="name" v-model="name" class="w-full" v-bind="nameProps" />
                <div class="p-error text-red-500">{{ errors.name }}</div>
            </div>
            <div class="form-group">
                <label for="name">Start Date</label>
                <InputText type="date" placeholder="Start Date" name="start_date" v-model="start_date" class="w-full"
                    v-bind="start_dateProps" />
                <div class="p-error text-red-500">{{ errors.start_date }}</div>
            </div>
            <div class="form-group">
                <label for="name">Subjects</label>
                <MultiSelect name="subjects" v-model="subjects" class="w-full text-base" v-bind="subjectsProps"
                    :options="courseOptions" optionLabel="label" optionValue="value" />
                <div class="p-error text-red-500">{{ errors.subjects }}</div>
            </div>
            <div class="form-group">
                <label for="name">Release Contents On?</label>
                <SelectButton name="drip_method" v-model="drip_method" class="w-full text-base"
                    v-bind="drip_methodProps" :options="options" optionLabel="label" optionValue="value" />
                <div class="p-error text-red-500">{{ errors.drip_method }}</div>
            </div>
        </div>
        <div class="flex gap-2 justify-between">
            <Button class="w" outlined severity='secondary' type="submit" @click.prevent="closeAddForm">Cancel
            </Button>
            <Button class="w" type="submit" @click.prevent="submitForm">Add program</Button>
        </div>
    </form>
</template>

<style scoped></style>
