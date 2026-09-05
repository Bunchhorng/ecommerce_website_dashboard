<script setup lang="ts">
import { CheckCircle2, Mail, Send } from 'lucide-vue-next'
import { computed, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const route = useRoute()
const auth = useAuthStore()

const verified = computed(() => route.query.verified === '1')
const needsVerification = computed(
  () => Boolean(auth.user) && !verified.value && !auth.user?.email_verified
)

const sending = ref(false)
const sent = ref(false)
const error = ref('')

async function resend() {
  sending.value = true
  error.value = ''
  sent.value = false
  try {
    await auth.sendVerificationEmail()
    await auth.refreshUser()
    sent.value = true
  } catch {
    error.value = 'verify.resend_failed'
  } finally {
    sending.value = false
  }
}
</script>

<template>
  <div class="w-full max-w-md">
    <div class="card p-8 text-center">
      <div
        class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full"
        :class="verified ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40' : 'bg-primary/10 text-primary'"
      >
        <CheckCircle2 v-if="verified" class="h-8 w-8" />
        <Mail v-else class="h-8 w-8" />
      </div>

      <template v-if="verified">
        <h1 class="text-2xl font-bold text-ink dark:text-ink">{{ $t('verify.title') }}</h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-muted">{{ $t('verify.verified_message') }}</p>
        <RouterLink v-if="auth.isAuthenticated" to="/account" class="btn-primary mt-6 w-full">
          {{ $t('verify.go_to_dashboard') }}
        </RouterLink>
        <RouterLink v-else to="/" class="btn-primary mt-6 w-full">
          {{ $t('verify.continue_shopping') }}
        </RouterLink>
      </template>

      <template v-else-if="needsVerification">
        <h1 class="text-2xl font-bold text-ink dark:text-ink">{{ $t('verify.check_your_inbox') }}</h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-muted">
          {{ $t('verify.pending_message', { email: auth.user?.email }) }}
        </p>

        <button class="btn-primary mt-6 w-full" :disabled="sending" @click="resend">
          <Send v-if="!sending" class="mr-2 h-4 w-4" />
          {{ sending ? $t('verify.sending') : $t('verify.resend_email') }}
        </button>
        <p v-if="sent" class="mt-3 text-sm font-medium text-emerald-600 dark:text-success">
          {{ $t('verify.email_sent') }}
        </p>
        <p v-else-if="error" class="mt-3 text-sm font-medium text-red-500">
          {{ $t(error) }}
        </p>
      </template>

      <template v-else>
        <h1 class="text-2xl font-bold text-ink dark:text-ink">{{ $t('verify.title') }}</h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-muted">{{ $t('verify.verified_message') }}</p>
        <RouterLink to="/" class="btn-primary mt-6 w-full">
          {{ $t('verify.continue_shopping') }}
        </RouterLink>
      </template>
    </div>
  </div>
</template>