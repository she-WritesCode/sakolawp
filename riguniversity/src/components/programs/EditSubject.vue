<script setup lang="ts">
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
import { useForm } from 'vee-validate';
import { createSubjectSchema, useSubjectStore } from '../../stores/subject';
import { useUserStore } from '../../stores/user';
import { useprogramStore } from '../../stores/program';
import { toTypedSchema } from '@vee-validate/yup';
import { onMounted, watch } from 'vue';
import { computed } from 'vue';

const { updateSubject, currentSubject } = useSubjectStore()
const { errors, defineField, handleSubmit, setValues } = useForm({
    initialValues: currentSubject.value,
    validationSchema: toTypedSchema(createSubjectSchema),
});

const [name, nameProps] = defineField('name');
const [teacher_id, teacher_idProps] = defineField('teacher_id');
const [class_id, class_idProps] = defineField('class_id');

const { users, filter } = useUserStore()
const teacherOptions = computed(() => users.value.map(u => u.data));

const { programs, fetchprograms } = useprogramStore()
const classOptions = computed(() => programs.value.map(u => u));

const submitForm = handleSubmit((values) => {
    console.log(values);
    updateSubject(values)

});
watch(currentSubject, () => {
    setValues(currentSubject.value as any)
});

onMounted(() => {
    filter.role = "teacher";
    fetchprograms()
    // fetchUsers()
})

</script>

<template>

    <form id="myForm" name="Add Subject">
        <div class="flex flex-col gap-4 mb-4">
            <div class="form-group">
                <label for="name">Subject Name</label>
                <InputText placeholder="Subject Name" name="name" v-model="name" class="w-full" v-bind="nameProps" />
                <div>{{ errors.name }}</div>
            </div>

            <div class="form-group">
                <label for="teacher_id"> Faculty</label>
                <Dropdown name="teacher_id" :options="teacherOptions" optionLabel="display_name" optionValue="ID"
                    placeholder="Select" v-model="teacher_id" class="w-full" v-bind="teacher_idProps" />
                <div>{{ errors.teacher_id }}</div>
            </div>

            <div class="form-group">
                <label for="class_id">Class</label>
                <Dropdown name="class_id" :options="classOptions" optionLabel="name" optionValue="class_id"
                    placeholder="Select" v-model="class_id" class="w-full" v-bind="class_idProps" />
                <div class="p-error text-red-500">{{ errors.class_id }}</div>
            </div>
        </div>
        <div class="flex gap-2 justify-between">
            <Button class="w" type="submit" @click.prevent="submitForm">Save Subject</Button>
        </div>
    </form>
</template>

<style scoped></style>
../../stores/program