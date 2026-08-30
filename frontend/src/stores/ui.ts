import { defineStore } from 'pinia'

export const useUiStore = defineStore('ui', {
  state: () => ({
    cartDrawerOpen: false,
    mobileFiltersOpen: false,
    adminSidebarCollapsed: false,
    adminNotificationsOpen: false,
    globalModalOpen: false
  }),

  actions: {
    openCartDrawer() {
      this.cartDrawerOpen = true
    },
    closeCartDrawer() {
      this.cartDrawerOpen = false
    },
    toggleCartDrawer() {
      this.cartDrawerOpen = !this.cartDrawerOpen
    },
    openMobileFilters() {
      this.mobileFiltersOpen = true
    },
    closeMobileFilters() {
      this.mobileFiltersOpen = false
    },
    toggleAdminSidebar() {
      this.adminSidebarCollapsed = !this.adminSidebarCollapsed
    },
    toggleAdminNotifications() {
      this.adminNotificationsOpen = !this.adminNotificationsOpen
    }
  }
})