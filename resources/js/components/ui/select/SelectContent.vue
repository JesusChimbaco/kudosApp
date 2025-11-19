<script setup lang="ts">
import { cn } from '@/lib/utils'
import { inject, onMounted, onUnmounted, ref } from 'vue'

const SELECT_INJECTION_KEY = Symbol('select')

export interface SelectContentProps {
  class?: any
}

const props = defineProps<SelectContentProps>()

const selectContext = inject(SELECT_INJECTION_KEY) as any
const contentRef = ref<HTMLElement>()

const handleClickOutside = (event: MouseEvent) => {
  if (contentRef.value && !contentRef.value.contains(event.target as Node)) {
    selectContext.closeSelect()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<template>
  <div
    v-if="selectContext.isOpen.value"
    ref="contentRef"
    :class="cn(
      'absolute top-full z-50 mt-1 max-h-96 min-w-[8rem] overflow-hidden rounded-md border bg-popover text-popover-foreground shadow-md animate-in fade-in-0 zoom-in-95',
      props.class
    )"
  >
    <div class="p-1">
      <slot />
    </div>
  </div>
</template>