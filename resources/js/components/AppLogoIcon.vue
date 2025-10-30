<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <div
        :class="className"
        v-bind="$attrs"
        class="w-full h-full flex items-center justify-center rounded-lg font-bold text-lg"
    >
        K
    </div>
</template>
