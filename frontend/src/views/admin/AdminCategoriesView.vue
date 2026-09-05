<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'
import { Plus, Pencil, Trash2, ChevronRight, FolderTree, ImagePlus, UploadCloud } from 'lucide-vue-next'
import { useI18n } from 'vue-i18n'
import BaseModal from '@/components/BaseModal.vue'
import { adminApi } from '@/api/admin'
import { mediaApi } from '@/api/uploads'
import type { AdminCategory } from '@/api/admin'

const { t } = useI18n()

const loading = ref(true)

const tree = ref<AdminCategory[]>([])

const totalCategories = computed(() => {
  const children = tree.value.reduce((acc, c) => acc + (c.children ? c.children.length : 0), 0)
  return t('admin.categories.total_count', { count: tree.value.length + children })
})

const open = ref<Record<string, boolean>>({})

const modalOpen = ref(false)
const modalMode = ref<'root' | 'child' | 'edit'>('root')
const targetId = ref<number | null>(null)
const form = reactive({ name: '', slug: '', imageUrl: null as string | null })
const imageFile = ref<File | null>(null)
const imageInput = ref<HTMLInputElement | null>(null)

function resetImage() {
  imageFile.value = null
  if (imageInput.value) imageInput.value.value = ''
}

function onImagePicked(e: Event) {
  const input = e.target as HTMLInputElement
  const file = input.files?.[0]
  if (!file || !file.type.startsWith('image/')) return
  imageFile.value = file
  form.imageUrl = URL.createObjectURL(file)
}

function removeImagePreview() {
  imageFile.value = null
  form.imageUrl = null
  if (imageInput.value) imageInput.value.value = ''
}

const modalTitle = computed(() => {
  if (modalMode.value === 'edit') return t('admin.categories.edit_category')
  if (modalMode.value === 'child') return t('admin.categories.add_child_category')
  return t('admin.categories.add_category')
})

function openAddRoot() {
  modalMode.value = 'root'
  targetId.value = null
  form.name = ''
  form.slug = ''
  form.imageUrl = null
  resetImage()
  modalOpen.value = true
}

function openAddChild(parent: AdminCategory) {
  modalMode.value = 'child'
  targetId.value = parent.id
  form.name = ''
  form.slug = ''
  form.imageUrl = null
  resetImage()
  modalOpen.value = true
}

function openEdit(node: AdminCategory) {
  modalMode.value = 'edit'
  targetId.value = node.id
  form.name = node.name
  form.slug = node.slug
  form.imageUrl = node.image
  resetImage()
  modalOpen.value = true
}

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

async function saveNode() {
  if (!form.name.trim()) {
    showToast(t('admin.categories.toast_enter_name'))
    return
  }
  const name = form.name.trim()
  const slug = form.slug.trim() || name.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '')

  try {
    await saveCategoryRecord(name, slug)
    modalOpen.value = false
  } catch {
    showToast(t('admin.categories.toast_save_error'))
  }
}

async function saveCategoryRecord(name: string, slug: string): Promise<void> {
  if (modalMode.value === 'edit' && targetId.value != null) {
    await adminApi.updateCategory(targetId.value, { name, slug })
    if (imageFile.value) {
      await mediaApi.uploadCategoryImage(targetId.value, imageFile.value)
    }
    const node = findNode(tree.value, targetId.value)
    if (node) {
      node.name = name
      node.slug = slug
    }
    showToast(t('admin.categories.toast_updated', { name }))
  } else {
    const parentId = modalMode.value === 'child' && targetId.value != null ? targetId.value : undefined
    const { data: resp } = await adminApi.createCategory({ name, slug, ...(parentId ? { parent_id: parentId } : {}) })
    if (imageFile.value) {
      await mediaApi.uploadCategoryImage(resp.data.id, imageFile.value)
    }
    await loadCategories()
    if (parentId) {
      showToast(t('admin.categories.toast_added_child', { name }))
    } else {
      showToast(t('admin.categories.toast_added', { name }))
    }
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
      <button class="btn-primary btn-sm" @click="openAddRoot()">
        <Plus class="h-4 w-4" />
        {{ $t('admin.categories.add_root') }}
      </button>
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
            <button class="btn-icon h-8 w-8" type="button" :title="$t('admin.categories.add_child')" @click="openAddChild(root)">
              <Plus class="h-4 w-4" />
            </button>
            <button class="btn-icon h-8 w-8" type="button" :title="$t('actions.edit')" @click="openEdit(root)">
              <Pencil class="h-4 w-4" />
            </button>
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
              <span class="flex-1 text-sm text-gray-700">{{ child.name }}</span>
              <span class="chip">{{ child.products_count ?? 0 }}</span>
              <button class="btn-icon h-8 w-8" type="button" :title="$t('actions.edit')" @click="openEdit(child)">
                <Pencil class="h-4 w-4" />
              </button>
              <button class="btn-icon h-8 w-8 hover:text-red-600" type="button" :title="$t('actions.delete')" @click="removeNode(child.id)">
                <Trash2 class="h-4 w-4" />
              </button>
            </div>
          </template>
        </template>
      </div>
    </div>

    <BaseModal v-model="modalOpen" :title="modalTitle" size="md">
      <div class="space-y-4">
        <div>
          <label class="label" for="cat-name">{{ $t('admin.categories.name_label') }}</label>
          <input id="cat-name" v-model="form.name" class="input" :placeholder="$t('admin.categories.name_placeholder')" />
        </div>
        <div>
          <label class="label" for="cat-slug">{{ $t('admin.categories.slug_label') }}</label>
          <input id="cat-slug" v-model="form.slug" class="input" :placeholder="$t('admin.categories.slug_placeholder')" />
        </div>
        <div>
          <label class="label">{{ $t('admin.categories.image_label') }}</label>
          <div class="flex items-center gap-4">
            <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-canvas">
              <img v-if="form.imageUrl" :src="form.imageUrl" alt="Category" class="h-full w-full object-cover" />
              <UploadCloud v-else class="h-6 w-6 text-gray-300" />
            </div>
            <div class="flex gap-2">
              <button type="button" class="btn-secondary btn-sm" @click="imageInput?.click()">
                <ImagePlus class="h-4 w-4" />
                {{ form.imageUrl ? $t('admin.categories.change_image') : $t('admin.categories.upload_image') }}
              </button>
              <button v-if="form.imageUrl" type="button" class="btn-ghost btn-sm" @click="removeImagePreview()">
                {{ $t('admin.categories.remove_image') }}
              </button>
            </div>
            <input ref="imageInput" type="file" accept="image/*" class="hidden" @change="onImagePicked" />
          </div>
        </div>
      </div>
      <template #footer>
        <button class="btn-secondary btn-sm" type="button" @click="modalOpen = false">{{ $t('actions.cancel') }}</button>
        <button class="btn-primary btn-sm" type="button" @click="saveNode()">{{ $t('admin.categories.save') }}</button>
      </template>
    </BaseModal>

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
