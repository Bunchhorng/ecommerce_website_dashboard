<script setup lang="ts">
type BadgeVariant = 'success' | 'warning' | 'danger' | 'info' | 'neutral'

const props = withDefaults(
  defineProps<{
    variant?: BadgeVariant
    size?: 'sm' | 'md'
    dot?: boolean
  }>(),
  { variant: 'neutral', size: 'sm', dot: false }
)

const variantClasses: Record<BadgeVariant, string> = {
  success: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
  warning: 'bg-amber-50 text-amber-700 ring-amber-200',
  danger: 'bg-red-50 text-red-600 ring-red-200',
  info: 'bg-blue-50 text-blue-700 ring-blue-200',
  neutral: 'bg-gray-100 text-gray-600 ring-gray-200'
}

const dotClasses: Record<BadgeVariant, string> = {
  success: 'bg-emerald-500',
  warning: 'bg-amber-500',
  danger: 'bg-red-500',
  info: 'bg-blue-500',
  neutral: 'bg-gray-400'
}
</script>

<template>
  <span
    class="inline-flex items-center gap-1 rounded-full font-medium ring-1 ring-inset"
    :class="[
      variantClasses[props.variant],
      props.size === 'sm' ? 'px-2.5 py-0.5 text-xs' : 'px-3 py-1 text-sm'
    ]"
  >
    <span
      v-if="props.dot"
      class="rounded-full"
      :class="[dotClasses[props.variant], props.size === 'sm' ? 'h-1.5 w-1.5' : 'h-2 w-2']"
    ></span>
    <slot />
  </span>
</template>