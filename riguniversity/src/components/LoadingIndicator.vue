<script setup lang="ts">
import Skeleton from 'primevue/skeleton';

</script>
<template>
    <div class="rounded-lg dark:border-surface-700 bg-surface-0 dark:bg-surface-800 mb-4 p-4">
        <div class="flex mb-4">
            <Skeleton shape="circle" size="4rem" class="mr-2"></Skeleton>
            <div>
                <Skeleton width="10rem" class="mb-2"></Skeleton>
                <Skeleton width="5rem" class="mb-2"></Skeleton>
                <Skeleton height=".5rem"></Skeleton>
            </div>
        </div>
        <Skeleton width="100%" height="150px"></Skeleton>
        <div class="flex justify-between mt-4">
            <Skeleton width="4rem" height="2rem"></Skeleton>
            <Skeleton width="4rem" height="2rem"></Skeleton>
        </div>
    </div>
</template>
<style scoped></style>