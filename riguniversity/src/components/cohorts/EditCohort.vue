<script setup lang="ts">
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import { useForm } from 'vee-validate';
import { createCohortSchema, useCohortStore } from '../../stores/cohort';
import { useSubjectStore } from '../../stores/subject';
import { toTypedSchema } from '@vee-validate/yup';
import { onMounted, watch, ref } from 'vue';
import { computed } from 'vue';
import SelectButton from 'primevue/selectbutton';
import MultiSelect from 'primevue/multiselect';

const { updateCohort, currentCohort, getOneCohort, cohortId, deleteCohort } = useCohortStore()

const { errors, defineField, handleSubmit, setValues } = useForm({
    initialValues: {
        name: currentCohort.value?.name,
        drip_method: currentCohort.value?.drip_method,
        subjects: currentCohort.value?.subjects,
        start_date: currentCohort.value?.start_date ? new Date(currentCohort.value?.start_date || "").toISOString().split('T')[0] : "",
    },
    validationSchema: toTypedSchema(createCohortSchema),
});

const [name, nameProps] = defineField('name');
const [drip_method, drip_methodProps] = defineField('drip_method');
const [start_date, start_dateProps] = defineField('start_date');
const [subjects, subjectsProps] = defineField('subjects');

const options = [
    { label: "Days after course starts", value: "days_after_release" },
    { label: "Specific dates", value: "specific_dates" },
]

const { subjects: allSubjects, fetchSubjects } = useSubjectStore()
const subjectOptions = computed(() => allSubjects.value.map((s) => ({ label: s.name, value: s.subject_id })))


const submitForm = handleSubmit((values) => {
    console.log(values);
    updateCohort(values as any)

});
watch(currentCohort, (value) => {
    setValues({
        name: value?.name,
        drip_method: value?.drip_method,
        subjects: value?.subjects,
        start_date: new Date(value?.start_date || "").toISOString().split('T')[0],
    } as any)
});

onMounted(() => {
    if (!currentCohort.value) {
        getOneCohort(cohortId as string)
    }
    fetchSubjects()
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
function deleteACohort(id: string) {
    deleteCohort(id)
    closeDelete()
}
</script>

<template>

    <form id="myForm" name="Add Cohort">
        <div class="flex flex-col gap-4 mb-4">
            <div class="form-group">
                <label for="name">Cohort Name</label>
                <InputText placeholder="Cohort Name" name="name" v-model="name" class="w-full" v-bind="nameProps" />
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
                    :options="subjectOptions" optionLabel="label" optionValue="value" />
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
            <Button class="w" type="submit" @click.prevent="submitForm">Save Cohort</Button>
        </div>
    </form>
    <Dialog v-model:visible="showDeleteDialog" modal header="Delete Cohort" :style="{ width: '30rem' }"
        :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
        <p class="mb-5">
            Are you sure you want to delete?
        </p>
        <div class="flex gap-2 justify-end">
            <Button @click="closeDelete">No</Button>
            <Button @click="deleteACohort(toBeDeleted as string)" outlined severity="danger">Yes</Button>
        </div>
    </Dialog>
</template>

<style scoped></style>
../../stores/cohort