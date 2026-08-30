<script setup lang="ts">
withDefaults(
  defineProps<{
    rows?: number
    columns?: number
  }>(),
  { rows: 8, columns: 5 }
)
</script>

<template>
  <div class="card overflow-hidden">
    <div class="animate-pulse overflow-x-auto">
      <table class="w-full">
        <thead>
          <tr class="border-b border-border-gray">
            <th v-for="n in columns" :key="n" class="px-4 py-3">
              <div class="h-4 rounded bg-gray-200"></div>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="r in rows" :key="r" class="border-b border-border-gray/60">
            <td v-for="c in columns" :key="c" class="px-4 py-3">
              <div class="h-8 rounded bg-gray-100"></div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>