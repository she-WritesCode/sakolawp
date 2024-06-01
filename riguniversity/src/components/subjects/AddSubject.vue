<script setup lang="ts">
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import InputNumber from 'primevue/inputnumber';
import Button from 'primevue/button';
import { useForm } from 'vee-validate';
import { createSubjectSchema } from '../../stores/subject';
import { toTypedSchema } from '@vee-validate/yup';

const { values, errors, defineField, handleSubmit } = useForm({
    initialValues: {
        name: "",
        // class_id: "",
        // section_id: "",
        teacher_id: "",
    },
    validationSchema: toTypedSchema(createSubjectSchema),
    // submi
});

const [name, nameProps] = defineField('name');
const [teacher_id, teacher_idProps] = defineField('teacher_id');
const [labs, labsProps] = defineField('labs');


const teacherOptions = [
    { id: '', name: 'Select' },
    { id: 175, name: 'Teacher Pangolo' }
];

const submitForm = handleSubmit((values) => {
    if (Object.keys(errors).length) {
        console.log("validation errors", errors)
        return
    }
    console.log(values);
});
</script>

<template>
    <form id="myForm" name="Add Subject">
        <div class="flex flex-col gap-4 mb-4">
            <div class="form-group">
                <label for=""> Subject Name</label>
                <InputText placeholder="Subject Name" name="name" v-model="name" class="w-full" v-bind="nameProps" />
                <div>{{ errors.name }}</div>
            </div>

            <!-- <div class="form-group">
                <label for=""> Class</label>
                <Dropdown name="class_id" :options="classOptions" optionLabel="name" optionValue="id"
                    placeholder="Select" v-model="values.class_id" class="w-full" />

            </div>

            <div class="form-group">
                <label for=""> Parent Group</label>
                <Dropdown name="section_id" :options="sectionOptions" optionLabel="name" optionValue="id"
                    placeholder="Select" v-model="values.section_id" class="w-full" />
            </div> -->

            <div class="form-group">
                <label for=""> Faculty</label>
                <Dropdown name="teacher_id" :options="teacherOptions" optionLabel="name" optionValue="id"
                    placeholder="Select" v-model="teacher_id" class="w-full" v-bind="teacher_idProps" />
                <div>{{ errors.teacher_id }}</div>
            </div>
        </div>
        <div class="">
            <Button class="w-full" type="submit" @click.prevent="submitForm"> Add Subject</Button>
        </div>
    </form>
</template>

<style scoped></style>
