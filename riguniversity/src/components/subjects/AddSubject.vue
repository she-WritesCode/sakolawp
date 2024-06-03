<script setup lang="ts">
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
import { useForm } from 'vee-validate';
import { createSubjectSchema, useSubjectStore } from '../../stores/subject';
import { useUserStore } from '../../stores/user';
import { toTypedSchema } from '@vee-validate/yup';
import { onMounted } from 'vue';
import { computed } from 'vue';

const { errors, defineField, handleSubmit } = useForm({
    initialValues: {
        name: "",
        teacher_id: "",
    },
    validationSchema: toTypedSchema(createSubjectSchema),
});

const [name, nameProps] = defineField('name');
const [teacher_id, teacher_idProps] = defineField('teacher_id');

const { users, filter, fetchUsers } = useUserStore()
const teacherOptions = computed(() => users.value.map(u => u.data));
const { createSubject, closeAddForm } = useSubjectStore()

const submitForm = handleSubmit((values) => {
    console.log(values);
    createSubject(values)
});

onMounted(() => {
    filter.role = "teacher";
    fetchUsers()
})
</script>

<template>
    <form id="myForm" name="Add Subject">
        <div class="flex flex-col gap-4 mb-4">
            <div class="form-group">
                <label for="name">Subject Name</label>
                <InputText placeholder="Subject Name" name="name" v-model="name" class="w-full" v-bind="nameProps" />
                <div class="p-error text-red-500">{{ errors.name }}</div>
            </div>

            <div class="form-group">
                <label for="teacher_id"> Faculty</label>
                <Dropdown name="teacher_id" :options="teacherOptions" optionLabel="display_name" optionValue="ID"
                    placeholder="Select" v-model="teacher_id" class="w-full" v-bind="teacher_idProps" />
                <div class="p-error text-red-500">{{ errors.teacher_id }}</div>
            </div>
        </div>
        <div class="flex gap-2 justify-between">
            <Button class="w" outline severity='secondary' type="submit" @click.prevent="closeAddForm">Cancel
            </Button>
            <Button class="w" type="submit" @click.prevent="submitForm">Add Subject</Button>
        </div>
    </form>
</template>

<style scoped></style>
