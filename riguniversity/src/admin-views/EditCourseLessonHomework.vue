<script setup lang="ts">
import { ref } from "vue";
import SingleCourse from '../components/courses/SingleCourse.vue'
import Button from 'primevue/button'

const expand = ref(new URL(window.location.href).searchParams.get('expanded') == '1')

const expandView = () => {
    if (!expand.value) {
        expand.value = true
        const url = new URL(window.location.href)
        url.searchParams.set('expanded', '1')
        window.history.replaceState({}, document.title, url.toString())
        return;
    }
    expand.value = false
    const url = new URL(window.location.href)
    url.searchParams.delete('expanded')
    window.history.replaceState({}, document.title, url.toString())
}

</script>

<template>
    <div :class="expand ? 'fixed inset-0 bg-white z-[10000] pt-16 overflow-y-auto' : ''">
        <Toast position="bottom-center" />
        <!-- Loading Indicator -->
        <div class="flex justify-end"><Button @click="expandView" text icon="pi pi-expand" label="Expand"></Button>
        </div>
        <SingleCourse :class="`bg-white ${expand ? 'max-w-4xl mx-auto' : ''}`"></SingleCourse>
    </div>
</template>

<style scoped></style>
