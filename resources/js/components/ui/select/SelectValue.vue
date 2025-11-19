<script setup lang="ts">
export interface SelectValueProps {
  placeholder?: string
}

const props = withDefaults(defineProps<SelectValueProps>(), {
  placeholder: 'Selecciona una opción'
})
</script>

<template>
  <span class="text-muted-foreground">{{ placeholder }}</span>
</template>