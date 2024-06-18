<script setup lang="ts">
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import { useForm } from 'vee-validate';
import { createCohortSchema, useCohortStore } from '../../stores/cohort';
import { toTypedSchema } from '@vee-validate/yup';
import { onMounted } from 'vue';

const { errors, defineField, handleSubmit } = useForm({
    initialValues: {
        name: "",
    },
    validationSchema: toTypedSchema(createCohortSchema),
});

const [name, nameProps] = defineField('name');

const { createCohort, closeAddForm } = useCohortStore()

const submitForm = handleSubmit((values) => {
    console.log(values);
    createCohort(values)
});

onMounted(() => {
    // fetchCohorts()
    // fetchUsers()
})
</script>

<template>
    <form id="myForm" name="Add Cohort">
        <div class="flex flex-col gap-4 mb-4">
            <div class="form-group">
                <label for="name">Cohort Name</label>
                <InputText placeholder="Cohort Name" name="name" v-model="name" class="w-full" v-bind="nameProps" />
                <div class="p-error text-red-500">{{ errors.name }}</div>
            </div>
        </div>
        <div class="flex gap-2 justify-between">
            <Button class="w" outlined severity='secondary' type="submit" @click.prevent="closeAddForm">Cancel
            </Button>
            <Button class="w" type="submit" @click.prevent="submitForm">Add Cohort</Button>
        </div>
    </form>
</template>

<style scoped></style>
