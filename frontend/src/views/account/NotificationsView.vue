<script setup lang="ts">
import { Bell, Check, Package, ShieldCheck } from 'lucide-vue-next'
import { accountApi, type ApiNotification } from '@/api/account'
import { formatDateTime } from '@/utils/format'
import { computed, onMounted, ref } from 'vue'
import type { Component } from 'vue'
import type { NotificationType } from '@/types'

const typeMeta: Record<NotificationType, { icon: Component; tint: string }> = {
  order: { icon: Package, tint: 'bg-primary/10 text-primary' },
  promo: { icon: Bell, tint: 'bg-accent/10 text-accent' },
  system: { icon: ShieldCheck, tint: 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300' }
}

interface NotifItem {
  id: string
  type: NotificationType
  title: string
  message: string
  date: string
  read: boolean
}

const notifs = ref<NotifItem[]>([])
const loading = ref(true)

const unreadCount = computed(() => notifs.value.filter((n) => !n.read).length)

function mapNotification(n: ApiNotification): NotifItem {
  const typeKey: NotificationType = ['order', 'promo', 'system'].includes(n.type) ? n.type as NotificationType : 'system'
  return {
    id: n.id,
    type: typeKey,
    title: n.title,
    message: n.message,
    date: n.created_at,
    read: n.read_at !== null
  }
}

async function markAllRead() {
  if (!unreadCount.value) return
  await accountApi.markAllNotificationsRead()
  notifs.value.forEach((n) => {
    n.read = true
  })
}

async function toggleRead(n: NotifItem) {
  if (!n.read) {
    await accountApi.markNotificationRead(n.id)
    n.read = true
  }
}

onMounted(async () => {
  loading.value = true
  try {
    const res = await accountApi.getNotifications()
    notifs.value = (res.data.data ?? []).map(mapNotification)
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-ink dark:text-gray-100">{{ $t('nav.notifications') }}</h1>
        <p v-if="unreadCount" class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $t('account.unread_count', { count: unreadCount }) }}</p>
      </div>
      <button v-if="unreadCount" type="button" class="btn-secondary btn-sm w-fit" @click="markAllRead">
        <Check class="h-4 w-4" />
        {{ $t('account.mark_all_read') }}
      </button>
    </div>

    <div v-if="loading" class="card p-10 text-center">
      <p class="text-sm text-gray-500 dark:text-gray-400">{{ $t('common.loading') }}</p>
    </div>

    <div v-else-if="notifs.length" class="card divide-y divide-border-gray dark:divide-gray-700">
      <button
        v-for="n in notifs"
        :key="n.id"
        type="button"
        class="relative flex w-full cursor-pointer gap-4 p-4 text-left transition-colors hover:bg-canvas dark:hover:bg-gray-900"
        @click="toggleRead(n)"
      >
        <div :class="['flex h-10 w-10 shrink-0 items-center justify-center rounded-full', typeMeta[n.type].tint]">
          <component :is="typeMeta[n.type].icon" class="h-5 w-5" />
        </div>
        <div class="min-w-0 flex-1 pr-4">
          <p :class="n.read ? 'font-medium text-gray-600 dark:text-gray-300' : 'font-semibold text-ink dark:text-gray-100'">{{ n.title }}</p>
          <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ n.message }}</p>
          <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ formatDateTime(n.date) }}</p>
        </div>
        <span v-if="!n.read" class="absolute right-4 top-4 h-2 w-2 rounded-full bg-primary"></span>
      </button>
    </div>

    <div v-else class="card p-10 text-center">
      <p class="text-sm text-gray-500 dark:text-gray-400">{{ $t('account.all_caught_up') }}</p>
    </div>
  </div>
</template>
