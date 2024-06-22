<script setup lang="ts">
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import { useForm } from 'vee-validate';
import { createProgramSchema, useProgramStore } from '../../stores/program';
import { useCourseStore } from '../../stores/course';
import { toTypedSchema } from '@vee-validate/yup';
import { onMounted, watch, ref } from 'vue';
import { computed } from 'vue';
import SelectButton from 'primevue/selectbutton';
import MultiSelect from 'primevue/multiselect';

const { updateProgram: updateprogram, currentProgram: currentprogram, getOneProgram: getOneprogram, programId, deleteProgram: deleteprogram } = useProgramStore()

const { errors, defineField, handleSubmit, setValues } = useForm({
    initialValues: {
        name: currentprogram.value?.name,
        drip_method: currentprogram.value?.drip_method,
        subjects: currentprogram.value?.subjects,
        start_date: currentprogram.value?.start_date ? new Date(currentprogram.value?.start_date || "").toISOString().split('T')[0] : "",
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


const submitForm = handleSubmit((values) => {
    console.log(values);
    updateprogram(values as any)

});
watch(currentprogram, (value) => {
    setValues({
        name: value?.name,
        drip_method: value?.drip_method,
        subjects: value?.subjects,
        start_date: new Date(value?.start_date || "").toISOString().split('T')[0],
    } as any)
});

onMounted(() => {
    if (!currentprogram.value) {
        getOneprogram(programId as string)
    }
    fetchCourses()
})


const showDeleteDialog = ref(false)
const toBeDeleted = ref<string | null>(null)
function initDelete(id: string) {
    showDeleteDialog.value = true
    toBeDeleted.value = id
}
function closeDelete() {
    showDeleteDialog.value = false
    toBeDeleted.value = null
}
function deleteAprogram(id: string) {
    deleteprogram(id)
    closeDelete()
}
</script>

<template>

    <form id="myForm" name="Add program">
        <div class="flex flex-col gap-4 mb-4">
            <div class="form-group">
                <label for="name">program Name</label>
                <InputText placeholder="program Name" name="name" v-model="name" class="w-full" v-bind="nameProps" />
                <div>{{ errors.name }}</div>
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
            <Button class="w" type="submit" @click.prevent="submitForm">Save program</Button>
        </div>
    </form>
    <Dialog v-model:visible="showDeleteDialog" modal header="Delete program" :style="{ width: '30rem' }"
        :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
        <p class="mb-5">
            Are you sure you want to delete?
        </p>
        <div class="flex gap-2 justify-end">
            <Button @click="closeDelete">No</Button>
            <Button @click="deleteAprogram(toBeDeleted as string)" outlined severity="danger">Yes</Button>
        </div>
    </Dialog>
</template>

<style scoped></style>
../../stores/program