<script setup lang="ts">
import { cn } from '../../Utils/cn';

withDefaults(defineProps<{ class?: string }>(), { class: 'h-4 w-full' });
</script>

<template>
    <div :class="cn('skeleton-shimmer animate-pulse rounded-md bg-muted', $props.class)" />
</template>
