<script setup lang="ts">
import { onMounted } from "vue";
import { useprogramParentGroupStore } from "../../stores/program-parent-group";
import { useprogramAccountabilityGroupStore } from "../../stores/program-accountability-group";
import { useProgramStore } from "../../stores/program";
import DataTable from 'primevue/datatable';
import TreeTable from 'primevue/treetable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
// import AddMeeting from './AddMeeting.vue'
import LoadingIndicator from '../LoadingIndicator.vue'
import { ref, computed } from "vue";


const {
    programParentGroups,
    filter,
    goToViewprogramParentGroup,
    programParentGroupId,
    loading,
    showAddParentGroupForm,
    goToAddParentGroupForm, deleteprogramParentGroup, goToEditprogramParentGroup,
} = useprogramParentGroupStore();
const {
    filter: accountabilityFilter,
    goToViewprogramAccountabilityGroup,
    programAccountabilityGroupId,
    loading: accountabilityLoading,
    showAddAccountabilityGroupForm,
    goToAddAccountabilityGroupForm, deleteprogramAccountabilityGroup, goToEditprogramAccountabilityGroup
} = useprogramAccountabilityGroupStore();
const {
    programId
} = useProgramStore();

onMounted(() => {
    if (!programParentGroupId) {
        filter.class_id = programId || ''
    }
    if (!programAccountabilityGroupId) {
        accountabilityFilter.class_id = programId || ''
    }
});

const treeNodes = computed(() => programParentGroups.value.map((item) => ({
    key: `${item.section_id}`,
    label: item.name,
    data: item,
    children: item.accountabilities.map((child) => ({
        key: `${child.accountability_id}`,
        label: child.name,
        data: child
    }))
})))
const expandedKeys = ref<Record<string, boolean>>({});
const toggleRow = (key: string) => {
    let _expandedKeys = { ...expandedKeys.value };

    if (_expandedKeys[key]) delete _expandedKeys[key];
    else _expandedKeys[key] = true;

    expandedKeys.value = _expandedKeys;
}


const showDeleteParentGroupDialog = ref(false)
const toBeDeletedParentGroup = ref<string | null>(null)
function initDeleteParentGroup(id: string) {
    showDeleteParentGroupDialog.value = true
    toBeDeletedParentGroup.value = id
}
function closeDeleteParentGroup() {
    showDeleteParentGroupDialog.value = false
    toBeDeletedParentGroup.value = null
}
function deleteParentGroup(id: string) {
    deleteprogramParentGroup(id)
    closeDeleteParentGroup()
}
const showDeleteAccountabilityGroupDialog = ref(false)
const toBeDeletedAccountabilityGroup = ref<string | null>(null)
function initDeleteAccountabilityGroup(id: string) {
    showDeleteAccountabilityGroupDialog.value = true
    toBeDeletedAccountabilityGroup.value = id
}
function closeDeleteAccountabilityGroup() {
    showDeleteAccountabilityGroupDialog.value = false
    toBeDeletedAccountabilityGroup.value = null
}
function deleteAccountabilityGroup(id: string) {
    deleteprogramAccountabilityGroup(id)
    closeDeleteAccountabilityGroup()
}
</script>

<template>
    <!-- Loading Indicator -->
    <div v-if="loading.list">
        <LoadingIndicator></LoadingIndicator>
    </div>
    <template v-else>
        <!-- Parent Group List -->
        <div class="border-0">
            <div class="px-2 pb-4">
                <h3 class="text-xl text-surface-900 dark:text-surface-0 font-bold">Parent Groups</h3>
            </div>
            <TreeTable autoLayout rowHover :value="treeNodes" tableStyle="min-width: 10rem" class="border-0" paginator
                :rows="10" :rowsPerPageOptions="[5, 10, 20, 50]" v-model:expandedKeys="expandedKeys">
                <template #header>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <InputText v-model="filter.search" placeholder="Search Meetings" class="font-normal" />
                        </div>
                        <div class="">
                            <Button @click="goToAddParentGroupForm" size="small" label="Add Meeting"></Button>
                        </div>
                    </div>
                </template>

                <Column field="name" header="Name" expander></Column>
                <Column field="accountability_count" header="Groups"></Column>
                <Column field="teacher_name" header="Faculty"></Column>
                <Column header="Actions">
                    <template #body="slotProps">
                        <div v-if="slotProps.node.children?.length" class="flex gap-2 text-sm">
                            <Button @click="toggleRow(slotProps.node.key)" text plain label="+ Add"></Button>
                            <Button text size="small" @click="goToViewprogramParentGroup(slotProps.node.key)"
                                label="View"></Button>
                            <Button text size="small" @click="goToEditprogramParentGroup(slotProps.node.key)"
                                label="Edit"></Button>
                            <Button size="small" @click="initDeleteParentGroup(slotProps.node.key)" text
                                severity="danger" label="Delete"></Button>
                        </div>
                        <div v-else class="flex gap-2 text-sm items-center justify-end">
                            <Button size="small" @click="goToViewprogramAccountabilityGroup(slotProps.node.key)" text
                                label="View"></Button>
                            <Button size="small" @click="goToEditprogramAccountabilityGroup(slotProps.node.key)" text
                                label="Edit"></Button>
                            <Button size="small" @click="initDeleteAccountabilityGroup(slotProps.node.key)" text
                                severity="danger" label="Delete"></Button>
                        </div>
                    </template>
                </Column>
            </TreeTable>

            <Dialog v-model:visible="showAddParentGroupForm" modal header="Add Meeting" :style="{ width: '30rem' }"
                :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
                <!-- <AddMeeting></AddMeeting> -->
                add meeting form
            </Dialog>
            <Dialog v-model:visible="showDeleteParentGroupDialog" modal header="Remove Meeting from program"
                :style="{ width: '30rem' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
                <p class="mb-5">
                    Are you sure you want to remove this meeting from this program? All student progress would be lost.
                </p>
                <div class="flex gap-2 justify-end">
                    <Button @click="closeDeleteParentGroup">No</Button>
                    <Button @click="deleteParentGroup(toBeDeletedParentGroup as string)" outlined
                        severity="danger">Yes</Button>
                </div>
            </Dialog>
        </div>
    </template>
</template>

<style scoped></style>
