<script setup lang="ts">
import { onMounted, ref, reactive } from 'vue';
import { useHomeworkStore, type Homework } from '../../stores/homework';
import Dialog from 'primevue/dialog';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import { useCourseStore } from '../../stores/course';
import AddHomework from '../homeworks/AddHomework.vue';
import ViewHomework from '../homeworks/ViewHomework.vue';

const { homeworks, goToAddForm, filter, goToViewHomework, goToEditHomework, homeworkId, showAddForm, duplicate, closeEditHomework, closeViewHomework, closeAddForm, getOneHomework, currentHomework, showViewScreen, loading, deleteHomework } = useHomeworkStore();
const { courseId } = useCourseStore();

onMounted(() => {
    if (homeworkId) {
        getOneHomework(homeworkId)
    } else {
        filter.subject_id = `${courseId!}` ?? ""
        // fetchHomeworks();
    }
});


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
function deleteAHomework(id: string) {
    deleteHomework(id)
    closeDelete()
}


const showDuplicateDialog = ref(false)
const toBeDuplicated = reactive<{ homework_id?: string, title?: string }>({})
function initDuplicate(homework: Homework) {
    showDuplicateDialog.value = true
    toBeDuplicated.homework_id = homework.homework_id
    toBeDuplicated.title = homework.title + " - Copy"
}
function closeDuplicate() {
    showDuplicateDialog.value = false
    toBeDuplicated.homework_id = ''
    toBeDuplicated.title = ''
}
function duplicateAHomework() {
    duplicate(toBeDuplicated.homework_id!, toBeDuplicated.title).then(() => closeDuplicate())

}

</script>
<template>
    <div v-if="showAddForm">
        <div class="md:px-2 max-w-2xl mx-auto">
            <div class="flex gap-2 mb-8 items-center">
                <div>
                    <Button @click="homeworkId ? closeEditHomework() : closeAddForm()" label="Back" size="small" outline
                        severity="secondary"></Button>
                </div>
                <div class="md:text-center w-full">
                    <h3 class="px-2 text-xl font-semibold">{{ homeworkId ? "Edit" : "Add" }} Homework</h3>
                </div>
            </div>
            <AddHomework :initialValues="currentHomework" :courseId="courseId!"></AddHomework>
        </div>
    </div>
    <div v-else-if="showViewScreen">
        <div class="md:px-2 max-w-2xl mx-auto">
            <div class="flex gap-2 mb-8 items-center">
                <div>
                    <Button @click="closeViewHomework" label="Back" size="small" outlined severity="secondary"></Button>
                </div>
                <div class="md:text-center w-full">
                    <h2 class="px-2 text-xl font-semibold">Homework</h2>
                </div>
            </div>
            <ViewHomework></ViewHomework>
        </div>
    </div>
    <DataTable v-else :loading="loading.list" :value="homeworks" paginatorPosition="both" tableStyle="min-width: 10rem"
        paginator :rows="10" :rowsPerPageOptions="[5, 10, 20, 50]">
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <InputText v-model="filter.search" placeholder="Search Homeworks" class="font-normal" />
                </div>
                <div class="">
                    <Button @click="goToAddForm" size="small" label="Add Homework"></Button>
                </div>
            </div>
        </template>
        <Column field="title" header="Title"></Column>
        <Column header="Questions" class="text-center">
            <template #body="slotProps">
                <Tag :value="`${slotProps.data.questions?.length || 0}`" severity="secondary" />
            </template>
        </Column>
        <Column header="Submissions" class="text-center">
            <template #body="slotProps">
                <Tag :value="slotProps.data.delivery_count" severity="secondary" />
            </template>
        </Column>
        <Column field="teacher_name" header="Faculty"></Column>
        <Column header="">
            <template #body="slotProps">
                <div class="flex gap-2">
                    <Button :disabled="loading.duplicate" size="small" outlined
                        @click="goToViewHomework(slotProps.data.homework_id)" label="Details"></Button>
                    <Button :disabled="loading.duplicate" icon="pi pi-edit" size="small" text
                        @click="goToEditHomework(slotProps.data.homework_id)" label="edit"></Button>
                    <Button :loading="loading.duplicate" icon="pi pi-copy" size="small" text
                        @click="initDuplicate(slotProps.data)"></Button>
                    <Button :disabled="loading.duplicate" size="small" icon="pi pi-trash"
                        @click="initDelete(slotProps.data.homework_id)" text severity="danger"></Button>
                </div>
            </template>
        </Column>
    </DataTable>
    <Dialog z v-model:visible="showDeleteDialog" modal header="Delete Homework" :style="{ width: '30rem' }"
        :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
        <p class="mb-5">
            Are you sure you want to delete?
        </p>
        <div class="flex gap-2 justify-end">
            <Button @click="closeDelete">No</Button>
            <Button @click="deleteAHomework(toBeDeleted as string)" outlined severity="danger">Yes</Button>
        </div>
    </Dialog>
    <Dialog z v-model:visible="showDuplicateDialog" modal header="Duplicate Homework" :style="{ width: '30rem' }"
        :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
        <div class="form-group mb-4">
            <label>Homework Title</label>
            <InputText v-model="toBeDuplicated.title" placeholder="New Homework Title" class="w-full" />
        </div>
        <div class="flex gap-2 justify-end">
            <Button @click="closeDuplicate" text>Cancel</Button>
            <Button @click="duplicateAHomework()" outlined>Duplicate</Button>
        </div>
    </Dialog>
</template>

<style scoped></style>