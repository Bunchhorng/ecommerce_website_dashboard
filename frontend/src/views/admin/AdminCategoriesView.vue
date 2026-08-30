<script setup lang="ts">
import { computed, reactive, ref } from 'vue'
import { Plus, Pencil, Trash2, ChevronRight, FolderTree } from 'lucide-vue-next'
import BaseModal from '@/components/BaseModal.vue'
import type { Category } from '@/types'
import { CATEGORIES, PRODUCTS } from '@/data/mock'
import { randomId } from '@/utils/format'

const categoryCount = (slug: string) => PRODUCTS.filter((p) => p.category.slug === slug).length

function slugify(v: string): string {
  return v
    .trim()
    .toLowerCase()
    .replace(/\s+/g, '-')
    .replace(/[^a-z0-9-]/g, '')
}

const customChildren: Record<string, Category[]> = {
  'cat-electronics': [{ id: 'c-e-audio', name: 'Headphones & Audio', slug: 'headphones-audio' }],
  'cat-fashion': [
    { id: 'c-f-men', name: 'Men', slug: 'fashion-men' },
    { id: 'c-f-women', name: 'Women', slug: 'fashion-women' }
  ],
  'cat-home': [{ id: 'c-h-lighting', name: 'Lighting', slug: 'home-lighting' }]
}

const tree = reactive<Category[]>(
  CATEGORIES.map((c) => (customChildren[c.id] ? { ...c, children: customChildren[c.id] } : c))
)

const totalCategories = computed(() => {
  const children = tree.reduce((acc, c) => acc + (c.children ? c.children.length : 0), 0)
  return `${tree.length + children} total`
})

const open = ref<Record<string, boolean>>({})

const modalOpen = ref(false)
const modalMode = ref<'root' | 'child' | 'edit'>('root')
const targetId = ref('')
const form = reactive({ name: '', slug: '' })

const modalTitle = computed(() => {
  if (modalMode.value === 'edit') return 'Edit Category'
  if (modalMode.value === 'child') return 'Add Child Category'
  return 'Add Category'
})

function openAddRoot() {
  modalMode.value = 'root'
  targetId.value = ''
  form.name = ''
  form.slug = ''
  modalOpen.value = true
}

function openAddChild(parent: Category) {
  modalMode.value = 'child'
  targetId.value = parent.id
  form.name = ''
  form.slug = ''
  modalOpen.value = true
}

function openEdit(node: Category) {
  modalMode.value = 'edit'
  targetId.value = node.id
  form.name = node.name
  form.slug = node.slug
  modalOpen.value = true
}

function findNode(list: Category[], id: string): Category | undefined {
  for (const c of list) {
    if (c.id === id) return c
    if (c.children) {
      const found = findNode(c.children, id)
      if (found) return found
    }
  }
  return undefined
}

function removeById(list: Category[], id: string): boolean {
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

function saveNode() {
  if (!form.name.trim()) {
    showToast('Please enter a category name')
    return
  }
  const slug = form.slug.trim() || slugify(form.name)
  const name = form.name.trim()
  if (modalMode.value === 'edit') {
    const node = findNode(tree, targetId.value)
    if (node) {
      node.name = name
      node.slug = slug
      showToast(`Updated category "${name}"`)
    }
  } else if (modalMode.value === 'child') {
    const parent = findNode(tree, targetId.value)
    if (parent) {
      if (!parent.children) parent.children = []
      parent.children.push({ id: randomId('cat'), name, slug })
      showToast(`Added child "${name}"`)
    }
  } else {
    const node = { id: randomId('cat'), name, slug }
    tree.push(node)
    showToast(`Added category "${name}"`)
  }
  modalOpen.value = false
}

function removeNode(id: string) {
  const node = findNode(tree, id)
  if (node) {
    removeById(tree, id)
    showToast(`Deleted category "${node.name}"`)
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
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex items-center gap-3">
        <h1 class="text-2xl font-bold text-ink">Categories</h1>
        <span class="chip">{{ totalCategories }}</span>
      </div>
      <button class="btn-primary btn-sm" @click="openAddRoot()">
        <Plus class="h-4 w-4" />
        Add Root
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
              @click="toggleOpen(root.id)"
            >
              <ChevronRight class="h-4 w-4" />
            </button>
            <span v-else class="h-8 w-8"></span>
            <FolderTree class="h-4 w-4 shrink-0 text-primary" />
            <span class="flex-1 text-sm font-medium text-ink">{{ root.name }}</span>
            <span class="chip">{{ categoryCount(root.slug) }}</span>
            <button class="btn-icon h-8 w-8" type="button" title="Add child" @click="openAddChild(root)">
              <Plus class="h-4 w-4" />
            </button>
            <button class="btn-icon h-8 w-8" type="button" title="Edit" @click="openEdit(root)">
              <Pencil class="h-4 w-4" />
            </button>
            <button class="btn-icon h-8 w-8 hover:text-red-600" type="button" title="Delete" @click="removeNode(root.id)">
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
              <span class="chip">{{ categoryCount(child.slug) }}</span>
              <button class="btn-icon h-8 w-8" type="button" title="Edit" @click="openEdit(child)">
                <Pencil class="h-4 w-4" />
              </button>
              <button class="btn-icon h-8 w-8 hover:text-red-600" type="button" title="Delete" @click="removeNode(child.id)">
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
          <label class="label" for="cat-name">Name</label>
          <input id="cat-name" v-model="form.name" class="input" placeholder="Category name" />
        </div>
        <div>
          <label class="label" for="cat-slug">Slug</label>
          <input id="cat-slug" v-model="form.slug" class="input" placeholder="auto-generated if empty" />
        </div>
      </div>
      <template #footer>
        <button class="btn-secondary btn-sm" type="button" @click="modalOpen = false">Cancel</button>
        <button class="btn-primary btn-sm" type="button" @click="saveNode()">Save</button>
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
