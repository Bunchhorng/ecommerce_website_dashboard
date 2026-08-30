<script setup lang="ts">
import { CheckCircle2, Eye, EyeOff } from 'lucide-vue-next'
import { clamp } from '@/utils/format'
import { computed, reactive, ref } from 'vue'

const form = reactive({
  current: '',
  password: '',
  confirm: ''
})

const errors = reactive({
  current: '',
  password: '',
  confirm: ''
})

const success = ref(false)
const showNew = ref(false)
const showConfirm = ref(false)

const strengthPercent = computed(() => clamp((form.password.length / 12) * 100, 0, 100))

const strengthBar = computed(() => {
  const length = form.password.length
  if (length === 0) return 'bg-gray-200'
  if (length < 5) return 'bg-red-500'
  if (length < 8) return 'bg-accent'
  return 'bg-emerald-500'
})

const strengthLabel = computed(() => {
  const length = form.password.length
  if (length === 0) return ''
  if (length < 5) return 'Weak'
  if (length < 8) return 'Fair'
  if (length < 12) return 'Good'
  return 'Strong'
})

function submit() {
  errors.current = form.current.length ? '' : 'Current password is required.'
  errors.password = form.password.length >= 8 ? '' : 'New password must be at least 8 characters.'
  errors.confirm = form.confirm && form.confirm === form.password ? '' : 'Passwords do not match.'
  success.value = false
  if (errors.current || errors.password || errors.confirm) return
  success.value = true
}
</script>

<template>
  <div class="mx-auto w-full max-w-xl">
    <div class="card space-y-6 p-6">
      <div>
        <h2 class="text-xl font-bold text-ink">Change Password</h2>
        <p class="mt-1 text-sm text-gray-500">Update the password you use to sign in.</p>
      </div>

      <form class="space-y-4" @submit.prevent="submit">
        <div>
          <label class="label" for="cp-current">Current password</label>
          <input
            id="cp-current"
            v-model="form.current"
            type="password"
            class="input"
            :class="{ 'input-error': Boolean(errors.current) }"
          />
          <p v-if="errors.current" class="mt-1 text-xs text-red-500">{{ errors.current }}</p>
        </div>

        <div>
          <label class="label" for="cp-new">New password</label>
          <div class="relative">
            <input
              id="cp-new"
              v-model="form.password"
              :type="showNew ? 'text' : 'password'"
              class="input pr-10"
              :class="{ 'input-error': Boolean(errors.password) }"
            />
            <button
              type="button"
              class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
              @click="showNew = !showNew"
            >
              <Eye v-if="!showNew" class="h-4 w-4" />
              <EyeOff v-else class="h-4 w-4" />
            </button>
          </div>
          <p v-if="errors.password" class="mt-1 text-xs text-red-500">{{ errors.password }}</p>
          <div class="mt-2">
            <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100">
              <div
                class="h-full rounded-full transition-all duration-300"
                :class="strengthBar"
                :style="{ width: `${strengthPercent}%` }"
              ></div>
            </div>
            <p v-if="strengthLabel" class="mt-1 text-xs text-gray-500">
              Password strength: {{ strengthLabel }}
            </p>
          </div>
        </div>

        <div>
          <label class="label" for="cp-confirm">Confirm new password</label>
          <div class="relative">
            <input
              id="cp-confirm"
              v-model="form.confirm"
              :type="showConfirm ? 'text' : 'password'"
              class="input pr-10"
              :class="{ 'input-error': Boolean(errors.confirm) }"
            />
            <button
              type="button"
              class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
              @click="showConfirm = !showConfirm"
            >
              <Eye v-if="!showConfirm" class="h-4 w-4" />
              <EyeOff v-else class="h-4 w-4" />
            </button>
          </div>
          <p v-if="errors.confirm" class="mt-1 text-xs text-red-500">{{ errors.confirm }}</p>
        </div>

        <button type="submit" class="btn-primary w-full">Update Password</button>
      </form>

      <p v-if="success" class="flex items-start gap-2 rounded-lg bg-emerald-50 p-3 text-sm font-medium text-emerald-700">
        <CheckCircle2 class="h-4 w-4 shrink-0" />
        Password changed successfully. We've sent a confirmation email.
      </p>
    </div>
  </div>
</template>