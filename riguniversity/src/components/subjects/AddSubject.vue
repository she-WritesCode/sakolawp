<script setup lang="ts">
import { ref } from 'vue';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import InputNumber from 'primevue/inputnumber';
import Button from 'primevue/button';
import { reactive } from 'vue';

const formValues = reactive({
    name: "",
    class: "",
    section: "",
    teacher: "",
    labs: 0
})

const classOptions = [
    { id: '', name: 'Select' },
    { id: 1, name: 'RUN LAGOS 2024' }
];

const sectionOptions = [
    { id: '', name: 'Select' }
];

const teacherOptions = [
    { id: '', name: 'Select' },
    { id: 175, name: 'Teacher Pangolo' }
];

const submitForm = () => {
    // Handle form submission logic here
    console.log(formValues);
};
</script>

<template>
    <form id="myForm" name="Add Subject">
        <div class="flex flex-col gap-4 mb-4">
            <div class="form-group">
                <label for=""> Subject Name</label>
                <InputText placeholder="Subject Name" name="name" :required="true" v-model="formValues.name"
                    class="w-full" />

            </div>

            <!-- <div class="form-group">
                <label for=""> Class</label>
                <Dropdown name="class_id" :options="classOptions" optionLabel="name" optionValue="id"
                    placeholder="Select" v-model="formValues.class_id" class="w-full" />

            </div>

            <div class="form-group">
                <label for=""> Parent Group</label>
                <Dropdown name="section_id" :options="sectionOptions" optionLabel="name" optionValue="id"
                    placeholder="Select" v-model="formValues.section" class="w-full" />

            </div> -->

            <div class="form-group">
                <label for=""> Faculty</label>
                <Dropdown name="teacher_id" :options="teacherOptions" optionLabel="name" optionValue="id"
                    placeholder="Select" v-model="formValues.teacher" class="w-full" />
            </div>

            <div class="form-group">
                <label for=""> Total Lab</label>
                <InputNumber placeholder="Total Lab" name="total_lab" :min="1" :max="24" v-model="formValues.labs"
                    class="w-full" />
            </div>
        </div>
        <div class="">
            <Button class="w-full" type="submit" @click.prevent="submitForm"> Add Subject</Button>
        </div>
    </form>
</template>

<style scoped></style>
