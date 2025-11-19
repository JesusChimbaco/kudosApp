<script setup lang="ts">
import { cn } from '@/lib/utils'
import { ChevronDown } from 'lucide-vue-next'
import { inject, computed } from 'vue'

const SELECT_INJECTION_KEY = Symbol('select')

export interface SelectTriggerProps {
  class?: any
  disabled?: boolean
}

const props = defineProps<SelectTriggerProps>()

const selectContext = inject(SELECT_INJECTION_KEY) as any

const toggleOpen = () => {
  if (!props.disabled) {
    selectContext.isOpen.value = !selectContext.isOpen.value
  }
}
</script>

<template>
  <button
    type="button"
    :class="cn(
      'flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
      props.class
    )"
    :disabled="disabled"
    @click="toggleOpen"
  >
    <slot />
    <ChevronDown class="h-4 w-4 opacity-50" />
  </button>
</template>