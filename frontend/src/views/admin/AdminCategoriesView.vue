<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { Plus, Pencil, Trash2, ChevronRight, FolderTree } from 'lucide-vue-next'
import { useI18n } from 'vue-i18n'
import { adminApi } from '@/api/admin'
import type { AdminCategory } from '@/api/admin'

const { t } = useI18n()

const loading = ref(true)

const tree = ref<AdminCategory[]>([])

const totalCategories = computed(() => {
  const children = tree.value.reduce((acc, c) => acc + (c.children ? c.children.length : 0), 0)
  return t('admin.categories.total_count', { count: tree.value.length + children })
})

const open = ref<Record<string, boolean>>({})

function findNode(list: AdminCategory[], id: number): AdminCategory | undefined {
  for (const c of list) {
    if (c.id === id) return c
    if (c.children) {
      const found = findNode(c.children, id)
      if (found) return found
    }
  }
  return undefined
}

function removeById(list: AdminCategory[], id: number): boolean {
  for (let i = 0; i < list.length; i++) {
    if (list[i].id === id) {
      list.splice(i, 1)
      return true
    }
    const children = list[i].children
    if (children && removeById(children, id)) return true
  }
  return false
}

async function loadCategories() {
  loading.value = true
  try {
    const { data: resp } = await adminApi.listCategories()
    tree.value = resp.data
  } catch {
    showToast(t('admin.categories.toast_load_error'))
  } finally {
    loading.value = false
  }
}

async function removeNode(id: number) {
  const node = findNode(tree.value, id)
  if (!node) return
  try {
    await adminApi.deleteCategory(id)
    removeById(tree.value, id)
    showToast(t('admin.categories.toast_deleted', { name: node.name }))
  } catch {
    showToast(t('admin.categories.toast_delete_error'))
  }
}

const toast = ref('')
let toastTimer: ReturnType<typeof setTimeout> | undefined
function showToast(msg: string) {
  toast.value = msg
  if (toastTimer) clearTimeout(toastTimer)
  toastTimer = setTimeout(() => {
    toast.value = ''
  }, 2500)
}

function toggleOpen(id: string) {
  open.value[id] = !open.value[id]
}

onMounted(loadCategories)
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex items-center gap-3">
        <h1 class="text-2xl font-bold text-ink">{{ $t('admin.categories.title') }}</h1>
        <span class="chip">{{ totalCategories }}</span>
      </div>
      <router-link :to="{ name: 'admin-category-create' }" class="btn-primary btn-sm w-fit">
        <Plus class="h-4 w-4" />
        {{ $t('admin.categories.add_root') }}
      </router-link>
    </div>

    <div class="card overflow-hidden">
      <div class="divide-y divide-border-gray">
        <template v-for="root in tree" :key="root.id">
          <div class="flex items-center gap-3 px-4 py-3">
            <button
              v-if="root.children && root.children.length"
              class="btn-icon h-8 w-8 rotate-0"
              :class="{ 'rotate-90': open[root.id] }"
              type="button"
              @click="toggleOpen(String(root.id))"
            >
              <ChevronRight class="h-4 w-4" />
            </button>
            <span v-else class="h-8 w-8"></span>
            <img
              v-if="root.image"
              :src="root.image"
              :alt="root.name"
              class="h-8 w-8 shrink-0 rounded-lg object-cover"
            />
            <FolderTree v-else class="h-4 w-4 shrink-0 text-primary" />
            <span class="flex-1 text-sm font-medium text-ink">{{ root.name }}</span>
            <span class="chip">{{ root.products_count ?? 0 }}</span>
            <router-link
              class="btn-icon h-8 w-8"
              :title="$t('admin.categories.add_child')"
              :to="{ name: 'admin-category-create', query: { parent: root.id } }"
            >
              <Plus class="h-4 w-4" />
            </router-link>
            <router-link
              class="btn-icon h-8 w-8"
              :title="$t('actions.edit')"
              :to="{ name: 'admin-category-edit', params: { id: root.id } }"
            >
              <Pencil class="h-4 w-4" />
            </router-link>
            <button class="btn-icon h-8 w-8 hover:text-red-600" type="button" :title="$t('actions.delete')" @click="removeNode(root.id)">
              <Trash2 class="h-4 w-4" />
            </button>
          </div>

          <template v-if="root.children && root.children.length && open[root.id]">
            <div
              v-for="child in root.children"
              :key="child.id"
              class="flex items-center gap-3 bg-canvas/40 py-2.5 pl-14 pr-4"
            >
              <ChevronRight class="h-4 w-4 shrink-0 text-gray-400" />
              <FolderTree class="h-4 w-4 shrink-0 text-gray-400" />
              <span class="flex-1 text-sm text-ink">{{ child.name }}</span>
              <span class="chip">{{ child.products_count ?? 0 }}</span>
              <router-link
                class="btn-icon h-8 w-8"
                :title="$t('actions.edit')"
                :to="{ name: 'admin-category-edit', params: { id: child.id } }"
              >
                <Pencil class="h-4 w-4" />
              </router-link>
              <button class="btn-icon h-8 w-8 hover:text-red-600" type="button" :title="$t('actions.delete')" @click="removeNode(child.id)">
                <Trash2 class="h-4 w-4" />
              </button>
            </div>
          </template>
        </template>
      </div>
    </div>

    <transition name="fade">
      <div
        v-if="toast"
        class="fixed bottom-6 right-6 card px-5 py-3 text-sm shadow-popover"
      >
        {{ toast }}
      </div>
    </transition>
  </div>
</template>