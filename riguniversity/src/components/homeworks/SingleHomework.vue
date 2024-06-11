<script setup lang="ts">
import { useHomeworkStore } from '../../stores/homework';


const { loading, homeworkId } = useHomeworkStore()

</script>
<template>
    <div>Hello {{ homeworkId }}</div>
</template>

<style></style>