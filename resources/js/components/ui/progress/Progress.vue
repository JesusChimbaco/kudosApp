<script setup lang="ts">
import { cn } from '@/lib/utils'

export interface ProgressProps {
  value?: number
  max?: number
  class?: any
}

const props = withDefaults(defineProps<ProgressProps>(), {
  value: 0,
  max: 100
})

import { computed } from 'vue'

const percentage = computed(() => {
  if (props.max === 0) return 0
  return Math.min(Math.max((props.value / props.max) * 100, 0), 100)
})
</script>

<template>
  <div
    :class="cn(
      'relative h-4 w-full overflow-hidden rounded-full bg-secondary',
      props.class
    )"
  >
    <div
      class="h-full w-full flex-1 bg-primary transition-all duration-300 ease-in-out"
      :style="{ transform: `translateX(-${100 - percentage}%)` }"
    />
  </div>
</template>