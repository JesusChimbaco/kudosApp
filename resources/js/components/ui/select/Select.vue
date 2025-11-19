<script setup lang="ts">
import { cn } from '@/lib/utils'
import { ChevronDown } from 'lucide-vue-next'
import { computed, onMounted, onUnmounted, ref, provide, inject, watch } from 'vue'

const SELECT_INJECTION_KEY = Symbol('select')

// Select Root
export interface SelectProps {
  modelValue?: any
  placeholder?: string
  disabled?: boolean
}

const props = withDefaults(defineProps<SelectProps>(), {
  placeholder: 'Selecciona una opción'
})

const emits = defineEmits<{
  'update:modelValue': [value: any]
}>()

const isOpen = ref(false)
const selectedValue = ref(props.modelValue)
const selectedLabel = ref('')

const selectValue = (value: any, label: string) => {
  selectedValue.value = value
  selectedLabel.value = label
  emits('update:modelValue', value)
  isOpen.value = false
}

provide(SELECT_INJECTION_KEY, {
  selectValue,
  selectedValue: computed(() => selectedValue.value),
  isOpen: computed(() => isOpen.value),
  closeSelect: () => { isOpen.value = false }
})

// Watch for external updates
watch(() => props.modelValue, (newValue: any) => {
  selectedValue.value = newValue
})
</script>

<template>
  <div class="relative">
    <slot />
  </div>
</template>