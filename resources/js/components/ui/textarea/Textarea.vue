<script setup lang="ts">
import { cn } from '@/lib/utils'

export interface TextareaProps {
  class?: any
  placeholder?: string
  rows?: number | string
  cols?: number | string
  disabled?: boolean
  readonly?: boolean
  required?: boolean
  id?: string
  name?: string
  modelValue?: string
}

const props = withDefaults(defineProps<TextareaProps>(), {
  rows: 3,
})

const emits = defineEmits<{
  'update:modelValue': [value: string]
}>()

function onInput(event: Event) {
  const target = event.target as HTMLTextAreaElement
  emits('update:modelValue', target.value)
}
</script>

<template>
  <textarea
    :id="id"
    :name="name"
    :class="cn(
      'flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
      props.class
    )"
    :placeholder="placeholder"
    :rows="rows"
    :cols="cols"
    :disabled="disabled"
    :readonly="readonly"
    :required="required"
    :value="modelValue"
    @input="onInput"
  />
</template>