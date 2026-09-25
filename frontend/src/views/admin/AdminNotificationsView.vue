<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { Bell, BellRing, CheckCheck, Trash2 } from 'lucide-vue-next'
import BasePagination from '@/components/BasePagination.vue'
import EmptyState from '@/components/EmptyState.vue'
import { adminApi } from '@/api/admin'
import type { AdminNotification } from '@/api/admin'
import { formatDateTime } from '@/utils/format'

const { t } = useI18n()

const loading = ref(true)
const notifications = ref<AdminNotification[]>([])
const totalCount = ref(0)
const unreadCount = ref(0)
const filter = ref('all')
const page = ref(1)
const perPage = 20

const filters = [
  { key: 'all', value: undefined },
  { key: 'unread', value: 'unread' },
  { key: 'read', value: 'read' }
]

const pageCount = computed(() => Math.max(1, Math.ceil(totalCount.value / perPage)))

const toast = ref('')
let toastTimer: ReturnType<typeof setTimeout> | undefined
function showToast(msg: string) {
  toast.value = msg
  if (toastTimer) clearTimeout(toastTimer)
  toastTimer = setTimeout(() => {
    toast.value = ''
  }, 2500)
}

async function loadNotifications() {
  loading.value = true
  try {
    const { data: resp } = await adminApi.listNotifications({
      filter: filter.value === 'all' ? undefined : filter.value,
      page: page.value
    })
    notifications.value = resp.data
    totalCount.value = resp.meta.total
    unreadCount.value = resp.meta.unread_count
  } catch {
    showToast(t('admin.notifications.toast.load_error'))
  } finally {
    loading.value = false
  }
}

async function refreshUnreadCount() {
  try {
    const { data: resp } = await adminApi.getNotificationUnreadCount()
    unreadCount.value = resp.data.unread_count
  } catch {
    // Ignore background poll failures
  }
}

async function switchFilter(value?: string) {
  filter.value = value ?? 'all'
  page.value = 1
  await loadNotifications()
}

async function markRead(id: string | 'all') {
  try {
    await adminApi.markNotificationRead(id)
    if (id === 'all') {
      for (const n of notifications.value) n.read_at = new Date().toISOString()
      unreadCount.value = 0
    } else {
      const target = notifications.value.find((n) => n.id === id)
      if (target) target.read_at = new Date().toISOString()
      await refreshUnreadCount()
    }
  } catch {
    showToast(t('admin.notifications.toast.action_failed'))
  }
}

async function removeNotification(id: string) {
  try {
    await adminApi.deleteNotification(id)
    notifications.value = notifications.value.filter((n) => n.id !== id)
    totalCount.value = Math.max(0, totalCount.value - 1)
    await refreshUnreadCount()
  } catch {
    showToast(t('admin.notifications.toast.action_failed'))
  }
}

function onPageChange(p: number) {
  page.value = p
  void loadNotifications()
}

let pollTimer: ReturnType<typeof setInterval> | undefined

onMounted(() => {
  loadNotifications()
  pollTimer = setInterval(() => {
    refreshUnreadCount()
  }, 30000)
})

onUnmounted(() => {
  if (pollTimer) clearInterval(pollTimer)
})
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex items-center gap-3">
        <h1 class="text-2xl font-bold text-ink">{{ $t('admin.notifications.title') }}</h1>
        <span class="chip">{{ $t('admin.notifications.total_count', { count: totalCount }) }}</span>
        <span
          v-if="unreadCount > 0"
          class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-2 py-0.5 text-xs font-semibold text-primary"
        >
          <BellRing class="h-3.5 w-3.5" />
          {{ $t('admin.notifications.unread_count', { count: unreadCount }) }}
        </span>
      </div>
      <button
        type="button"
        class="btn-outline btn-sm"
        :disabled="unreadCount === 0"
        @click="markRead('all')"
      >
        <CheckCheck class="h-4 w-4" />
        {{ $t('admin.notifications.mark_all_read') }}
      </button>
    </div>

    <div class="flex flex-wrap items-center gap-2">
      <button
        v-for="f in filters"
        :key="f.key"
        type="button"
        :class="filter === f.key ? 'bg-primary text-white' : 'bg-surface text-gray-600 hover:bg-surface-hover dark:text-muted'"
        class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors"
        @click="switchFilter(f.value)"
      >
        {{ $t(`admin.notifications.filter.${f.key}`) }}
      </button>
    </div>

    <div v-if="loading" class="card p-10 text-center">
      <p class="text-sm text-gray-500 dark:text-muted">{{ $t('common.loading') }}</p>
    </div>

    <div v-else-if="notifications.length === 0" class="card">
      <EmptyState :title="$t('admin.notifications.no_notifications')" cta-label="">
        <template #icon>
          <Bell class="h-10 w-10 text-gray-300" />
        </template>
      </EmptyState>
    </div>

    <div v-else class="card overflow-hidden">
      <ul class="divide-y divide-border-gray">
        <li
          v-for="n in notifications"
          :key="n.id"
          class="flex items-start gap-3 px-5 py-4 transition-colors hover:bg-canvas/50"
          :class="!n.read_at ? 'bg-primary/[0.03]' : ''"
        >
          <div
            class="mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-full"
            :class="n.type === 'order'
              ? 'bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400'
              : n.type === 'review'
                ? 'bg-purple-50 text-purple-600 dark:bg-purple-500/15 dark:text-purple-400'
                : n.type === 'inventory'
                  ? 'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400'
                  : 'bg-gray-100 text-gray-600 dark:bg-surface-hover dark:text-muted'"
          >
            <Bell class="h-4 w-4" />
          </div>

          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center justify-between gap-2">
              <p class="text-sm font-semibold text-ink dark:text-ink">{{ n.title ?? t('admin.notifications.untitled') }}</p>
              <div class="flex items-center gap-2">
                <span v-if="!n.read_at" class="h-2 w-2 rounded-full bg-primary"></span>
                <span class="text-xs text-gray-400 dark:text-gray-500">{{ formatDateTime(n.created_at) }}</span>
              </div>
            </div>
            <p v-if="n.message" class="mt-0.5 text-sm text-gray-600 dark:text-muted">{{ n.message }}</p>
          </div>

          <div class="flex shrink-0 items-center gap-1">
            <button
              v-if="!n.read_at"
              type="button"
              class="btn-icon h-8 w-8"
              :title="$t('admin.notifications.mark_read')"
              @click="markRead(n.id)"
            >
              <CheckCheck class="h-4 w-4" />
            </button>
            <button
              type="button"
              class="btn-icon h-8 w-8 hover:!text-red-500"
              :title="$t('admin.notifications.delete')"
              @click="removeNotification(n.id)"
            >
              <Trash2 class="h-4 w-4" />
            </button>
          </div>
        </li>
      </ul>

      <div class="flex items-center justify-between gap-3 border-t border-border-gray p-4">
        <span class="text-xs text-gray-500 dark:text-muted">
          {{ $t('admin.table.showing_range', { from: (page - 1) * perPage + 1, to: Math.min(page * perPage, totalCount), total: totalCount }) }}
        </span>
        <BasePagination :page="page" :page-count="pageCount" :total-items="totalCount" :page-size="perPage" @update:page="onPageChange" />
      </div>
    </div>

    <transition name="fade">
      <div v-if="toast" class="fixed bottom-6 right-6 card px-5 py-3 text-sm shadow-popover">
        {{ toast }}
      </div>
    </transition>
  </div>
</template>