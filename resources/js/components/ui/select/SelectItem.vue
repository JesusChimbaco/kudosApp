<script setup lang="ts">
import { cn } from '@/lib/utils'
import { inject, computed } from 'vue'

const SELECT_INJECTION_KEY = Symbol('select')

export interface SelectItemProps {
  value: any
  class?: any
  disabled?: boolean
}

const props = defineProps<SelectItemProps>()

const selectContext = inject(SELECT_INJECTION_KEY) as any

const handleSelect = () => {
  if (!props.disabled) {
    const label = (document.querySelector(`[data-value="${props.value}"]`) as HTMLElement)?.textContent || String(props.value)
    selectContext.selectValue(props.value, label)
  }
}

const isSelected = computed(() => selectContext.selectedValue.value === props.value)
</script>

<template>
  <div
    :data-value="value"
    :class="cn(
      'relative flex w-full cursor-default select-none items-center rounded-sm py-1.5 pl-8 pr-2 text-sm outline-none hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50',
      isSelected && 'bg-accent text-accent-foreground',
      props.class
    )"
    :data-disabled="disabled"
    @click="handleSelect"
  >
    <slot />
  </div>
</template>