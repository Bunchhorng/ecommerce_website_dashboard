<script setup lang="ts">
import { useI18n } from 'vue-i18n';

const { locale } = useI18n();

const changeLanguage = (newLocale: string) => {
  locale.value = newLocale;
  localStorage.setItem('user_locale', newLocale);
};
</script>

<template>
  <div class="flex items-center gap-1 bg-gray-100 dark:bg-gray-800 p-1 rounded-lg border border-gray-200 dark:border-gray-700">
    <button
      @click="changeLanguage('km')"
      :class="[
        'px-2.5 py-1 text-xs font-medium rounded-md transition-all',
        locale === 'km' 
          ? 'bg-blue-600 text-white shadow-sm' 
          : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
      ]"
    >
      🇰🇭 ខ្មែរ
    </button>
    <button
      @click="changeLanguage('en')"
      :class="[
        'px-2.5 py-1 text-xs font-medium rounded-md transition-all',
        locale === 'en' 
          ? 'bg-blue-600 text-white shadow-sm' 
          : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
      ]"
    >
      🇬🇧 EN
    </button>
  </div>
</template>