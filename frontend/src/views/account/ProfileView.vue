<script setup lang="ts">
import { CheckCircle2 } from 'lucide-vue-next'
import { accountApi } from '@/api/account'
import { useAuthStore } from '@/stores/auth'
import { computed, reactive, ref } from 'vue'

const authStore = useAuthStore()
const activeUser = computed(() => authStore.user)

const form = reactive({
  name: activeUser.value?.name ?? '',
  phone: activeUser.value?.phone ?? ''
})

const avatarFileName = ref('')
const saved = ref(false)
const saving = ref(false)
let timer: ReturnType<typeof setTimeout> | null = null

const initials = computed(() =>
  (activeUser.value?.name ?? 'U')
    .split(' ')
    .map((word) => word[0])
    .join('')
)

function chooseAvatar() {
  avatarFileName.value = 'avatar-alex-morgan.png'
}

async function saveProfile() {
  saving.value = true
  try {
    const res = await accountApi.updateProfile({
      name: form.name,
      phone: form.phone || undefined
    })
    if (res.data) {
      const updated = res.data
      authStore.user = {
        ...authStore.user!,
        name: updated.name,
        phone: updated.phone ?? undefined
      }
      localStorage.setItem('shopverse_user', JSON.stringify(authStore.user))
    }
    saved.value = true
    if (timer) clearTimeout(timer)
    timer = setTimeout(() => {
      saved.value = false
    }, 3000)
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="card mx-auto max-w-xl space-y-6 p-6">
    <div>
      <h2 class="text-xl font-bold text-ink dark:text-gray-100">{{ $t('account.profile_info') }}</h2>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $t('account.manage_account') }}</p>
    </div>

    <div class="flex items-center gap-4">
      <div
        class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-primary text-lg font-bold text-white"
      >
        {{ initials }}
      </div>
      <div>
        <button type="button" class="btn-secondary btn-sm" @click="chooseAvatar">{{ $t('account.upload') }}</button>
        <p v-if="avatarFileName" class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ avatarFileName }}</p>
      </div>
    </div>

    <form class="space-y-4" @submit.prevent="saveProfile">
      <div>
        <label class="label" for="profile-name">{{ $t('checkout.full_name') }}</label>
        <input id="profile-name" v-model="form.name" type="text" class="input" />
      </div>
      <div>
        <label class="label" for="profile-email">{{ $t('auth.email') }}</label>
        <input id="profile-email" :value="activeUser?.email" type="email" class="input bg-gray-50 dark:bg-gray-800" disabled />
      </div>
      <div>
        <label class="label" for="profile-phone">{{ $t('checkout.phone') }}</label>
        <input id="profile-phone" v-model="form.phone" type="tel" class="input" />
      </div>
      <button type="submit" class="btn-primary w-full" :disabled="saving">
        {{ saving ? $t('common.saving') : $t('actions.save_changes') }}
      </button>
    </form>

    <p v-if="saved" class="flex items-center gap-2 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 p-3 text-sm font-medium text-emerald-700 dark:text-emerald-400">
      <CheckCircle2 class="h-4 w-4 shrink-0" />
      {{ $t('account.profile_updated') }}
    </p>
  </div>
</template>
