<script setup lang="ts">
import { CheckCircle2 } from 'lucide-vue-next'
import { CURRENT_USER } from '@/data/mock'
import { computed, reactive, ref } from 'vue'

const form = reactive({
  name: CURRENT_USER.name,
  email: CURRENT_USER.email,
  phone: CURRENT_USER.phone ?? ''
})

const avatarFileName = ref('')
const saved = ref(false)
let timer: ReturnType<typeof setTimeout> | null = null

const initials = computed(() =>
  CURRENT_USER.name
    .split(' ')
    .map((word) => word[0])
    .join('')
)

function chooseAvatar() {
  avatarFileName.value = 'avatar-alex-morgan.png'
}

function saveProfile() {
  saved.value = true
  if (timer) clearTimeout(timer)
  timer = setTimeout(() => {
    saved.value = false
  }, 3000)
}
</script>

<template>
  <div class="card mx-auto max-w-xl space-y-6 p-6">
    <div>
      <h2 class="text-xl font-bold text-ink">Profile Information</h2>
      <p class="mt-1 text-sm text-gray-500">Manage your account details.</p>
    </div>

    <div class="flex items-center gap-4">
      <div
        class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-primary text-lg font-bold text-white"
      >
        {{ initials }}
      </div>
      <div>
        <button type="button" class="btn-secondary btn-sm" @click="chooseAvatar">Upload</button>
        <p v-if="avatarFileName" class="mt-1 text-xs text-gray-500">{{ avatarFileName }}</p>
      </div>
    </div>

    <form class="space-y-4" @submit.prevent="saveProfile">
      <div>
        <label class="label" for="profile-name">Full name</label>
        <input id="profile-name" v-model="form.name" type="text" class="input" />
      </div>
      <div>
        <label class="label" for="profile-email">Email</label>
        <input id="profile-email" v-model="form.email" type="email" class="input" />
      </div>
      <div>
        <label class="label" for="profile-phone">Phone</label>
        <input id="profile-phone" v-model="form.phone" type="tel" class="input" />
      </div>
      <button type="submit" class="btn-primary w-full">Save Changes</button>
    </form>

    <p v-if="saved" class="flex items-center gap-2 rounded-lg bg-emerald-50 p-3 text-sm font-medium text-emerald-700">
      <CheckCircle2 class="h-4 w-4 shrink-0" />
      Profile updated successfully.
    </p>

    <p class="text-xs text-gray-400">This is a demo. Data is not persisted.</p>
  </div>
</template>