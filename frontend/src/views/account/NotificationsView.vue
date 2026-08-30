<script setup lang="ts">
import { Bell, Check, Package, ShieldCheck } from 'lucide-vue-next'
import { NOTIFICATIONS } from '@/data/mock'
import { formatDateTime } from '@/utils/format'
import { computed, ref } from 'vue'
import type { Component } from 'vue'
import type { AppNotification, NotificationType } from '@/types'

const typeMeta: Record<NotificationType, { icon: Component; tint: string }> = {
  order: { icon: Package, tint: 'bg-primary/10 text-primary' },
  promo: { icon: Bell, tint: 'bg-accent/10 text-accent' },
  system: { icon: ShieldCheck, tint: 'bg-gray-100 text-gray-600' }
}

const notifs = ref<AppNotification[]>([...NOTIFICATIONS])

const unreadCount = computed(() => notifs.value.filter((n) => !n.read).length)

function markAllRead() {
  notifs.value.forEach((n) => {
    n.read = true
  })
}

function toggleRead(id: string) {
  const index = notifs.value.findIndex((n) => n.id === id)
  if (index >= 0) {
    notifs.value[index].read = !notifs.value[index].read
  }
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-ink">Notifications</h1>
        <p v-if="unreadCount" class="mt-1 text-sm text-gray-500">{{ unreadCount }} unread</p>
      </div>
      <button type="button" class="btn-secondary btn-sm w-fit" @click="markAllRead">
        <Check class="h-4 w-4" />
        Mark all as read
      </button>
    </div>

    <div v-if="notifs.length" class="card divide-y divide-border-gray">
      <button
        v-for="n in notifs"
        :key="n.id"
        type="button"
        class="relative flex w-full cursor-pointer gap-4 p-4 text-left transition-colors hover:bg-canvas"
        @click="toggleRead(n.id)"
      >
        <div :class="['flex h-10 w-10 shrink-0 items-center justify-center rounded-full', typeMeta[n.type].tint]">
          <component :is="typeMeta[n.type].icon" class="h-5 w-5" />
        </div>
        <div class="min-w-0 flex-1 pr-4">
          <p :class="n.read ? 'font-medium text-gray-600' : 'font-semibold text-ink'">{{ n.title }}</p>
          <p class="mt-0.5 text-sm text-gray-500">{{ n.message }}</p>
          <p class="mt-1 text-xs text-gray-400">{{ formatDateTime(n.date) }}</p>
        </div>
        <span v-if="!n.read" class="absolute right-4 top-4 h-2 w-2 rounded-full bg-primary"></span>
      </button>
    </div>

    <div v-else class="card p-10 text-center">
      <p class="text-sm text-gray-500">You're all caught up.</p>
    </div>
  </div>
</template>