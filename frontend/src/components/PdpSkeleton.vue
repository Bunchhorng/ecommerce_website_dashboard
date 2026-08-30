<script setup lang="ts">
</script>

<template>
  <div class="grid gap-8 lg:grid-cols-2">
    <div class="animate-pulse">
      <div class="aspect-square rounded-xl bg-gray-200"></div>
      <div class="mt-4 grid grid-cols-4 gap-3">
        <div v-for="n in 4" :key="n" class="aspect-square rounded-lg bg-gray-200"></div>
      </div>
    </div>
    <div class="animate-pulse">
      <div class="h-3 w-24 rounded bg-gray-200"></div>
      <div class="mt-3 h-6 w-3/4 rounded bg-gray-200"></div>
      <div class="mt-3 h-4 w-32 rounded bg-gray-200"></div>
      <div class="mt-8 flex items-center gap-2">
        <div v-for="n in 3" :key="n" class="h-10 w-10 rounded-full bg-gray-200"></div>
        <div v-for="n in 2" :key="`s-${n}`" class="h-8 w-14 rounded bg-gray-200"></div>
      </div>
      <div class="mt-8 h-8 w-48 rounded bg-gray-200"></div>
      <div class="mt-6 flex gap-3">
        <div class="h-11 flex-1 rounded-lg bg-gray-200"></div>
        <div class="h-11 flex-1 rounded-lg bg-gray-200"></div>
      </div>
      <div class="mt-8 space-y-2">
        <div v-for="n in 4" :key="n" class="h-3 rounded bg-gray-200" :class="n % 2 === 0 ? 'w-5/6' : 'w-full'"></div>
      </div>
    </div>
  </div>
</template>