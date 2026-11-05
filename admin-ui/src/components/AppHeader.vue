<template>
  <header class="wpgate-header">
    <div class="header-start-col">
      <!-- Mobile Drawer Hamburger Toggle -->
      <button
        type="button"
        class="header-hamburger-btn"
        @click="$emit('open-mobile')"
        aria-label="Open Navigation"
      >
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="3" y1="12" x2="21" y2="12"></line>
          <line x1="3" y1="6" x2="21" y2="6"></line>
          <line x1="3" y1="18" x2="21" y2="18"></line>
        </svg>
      </button>

      <!-- Active Page Title / Breadcrumb -->
      <div class="header-title-wrap">
        <span class="header-breadcrumb-root">WPGate</span>
        <span class="header-breadcrumb-sep">/</span>
        <h2 class="header-active-title">{{ pageTitle }}</h2>
      </div>
    </div>

    <!-- Right Controls: Status, Language, Theme, Save -->
    <div class="header-end-col">
      <!-- System Status Badge -->
      <StatusBadge
        :type="statusBadgeType"
        :text="statusBadgeText"
        class="header-status-badge"
      />

      <!-- Language Toggle Button -->
      <button
        type="button"
        class="wpgate-btn-header"
        @click="$emit('toggle-lang')"
        :title="currentLang === 'fa' ? 'Switch to English' : 'تغییر به فارسی'"
      >
        <span class="lang-pill-text">{{ currentLang === 'fa' ? 'English' : 'فارسی' }}</span>
      </button>

      <!-- Theme Switcher Button -->
      <button
        type="button"
        class="wpgate-btn-icon-header"
        @click="$emit('toggle-theme')"
        :title="theme === 'dark' ? 'Switch to Light Theme' : 'Switch to Dark Theme'"
        aria-label="Toggle Theme"
      >
        <svg v-if="theme === 'dark'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="5"></circle>
          <line x1="12" y1="1" x2="12" y2="3"></line>
          <line x1="12" y1="21" x2="12" y2="23"></line>
          <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
          <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
          <line x1="1" y1="12" x2="3" y2="12"></line>
          <line x1="21" y1="12" x2="23" y2="12"></line>
          <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
          <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
        </svg>
        <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
        </svg>
      </button>

      <!-- Primary Save Changes Button -->
      <button
        type="button"
        class="wpgate-btn-save-header"
        @click="$emit('save')"
        :disabled="saving"
      >
        <svg v-if="saving" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="wpgate-spin">
          <line x1="12" y1="2" x2="12" y2="6"></line>
          <line x1="12" y1="18" x2="12" y2="22"></line>
          <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line>
          <line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line>
          <line x1="2" y1="12" x2="6" y2="12"></line>
          <line x1="18" y1="12" x2="22" y2="12"></line>
          <line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line>
          <line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line>
        </svg>
        <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
          <polyline points="17 21 17 13 7 13 7 21"></polyline>
          <polyline points="7 3 7 8 15 8"></polyline>
        </svg>
        <span>{{ saving ? t('common.saving') : t('common.save') }}</span>
      </button>
    </div>
  </header>
</template>

<script setup>
import { computed } from 'vue';
import StatusBadge from './StatusBadge.vue';
import { currentLang, t } from '../i18n';

const props = defineProps({
  currentTab: {
    type: String,
    required: true,
  },
  system: {
    type: Object,
    default: () => ({}),
  },
  theme: {
    type: String,
    default: 'light',
  },
  saving: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['open-mobile', 'toggle-theme', 'toggle-lang', 'save']);

const pageTitle = computed(() => {
  return t(`tabs.${props.currentTab}`) || props.currentTab;
});

const statusBadgeType = computed(() => {
  if (props.system.disabled_by_constant) return 'danger';
  return 'success';
});

const statusBadgeText = computed(() => {
  if (props.system.disabled_by_constant) return t('common.disabled');
  return t('sidebar.statusProtected');
});
</script>

<style scoped>
.wpgate-header {
  height: 64px;
  background-color: var(--wpgate-surface);
  border-bottom: 1px solid var(--wpgate-border);
  padding: 0 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  position: sticky;
  top: 0;
  z-index: 50;
  transition: background-color 0.2s ease, border-color 0.2s ease;
}

.header-start-col {
  display: flex;
  align-items: center;
  gap: 14px;
}

.header-hamburger-btn {
  display: none;
  background: none;
  border: none;
  padding: 6px;
  color: var(--wpgate-text);
  cursor: pointer;
  border-radius: var(--wpgate-radius-sm);
}

.header-title-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
}

.header-breadcrumb-root {
  font-size: 0.88rem;
  font-weight: 500;
  color: var(--wpgate-text-muted);
}

.header-breadcrumb-sep {
  font-size: 0.88rem;
  color: var(--wpgate-border);
}

.header-active-title {
  margin: 0;
  font-size: 1.15rem;
  font-weight: 700;
  color: var(--wpgate-text);
  line-height: 1.2;
}

.header-end-col {
  display: flex;
  align-items: center;
  gap: 12px;
}

.header-status-badge {
  display: inline-flex;
}

.wpgate-btn-header {
  height: 36px;
  padding: 0 14px;
  border: 1px solid var(--wpgate-border);
  background-color: var(--wpgate-surface);
  color: var(--wpgate-text);
  font-size: 0.85rem;
  font-weight: 600;
  border-radius: var(--wpgate-radius-sm);
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s ease;
}

.wpgate-btn-header:hover {
  background-color: var(--wpgate-surface-alt);
  border-color: var(--wpgate-border-focus);
}

.wpgate-btn-icon-header {
  width: 36px;
  height: 36px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 1px solid var(--wpgate-border);
  background-color: var(--wpgate-surface);
  color: var(--wpgate-text);
  border-radius: var(--wpgate-radius-sm);
  cursor: pointer;
  transition: all 0.15s ease;
}

.wpgate-btn-icon-header:hover {
  background-color: var(--wpgate-surface-alt);
  border-color: var(--wpgate-border-focus);
}

.wpgate-btn-save-header {
  height: 36px;
  padding: 0 18px;
  background-color: var(--wpgate-primary);
  color: #ffffff;
  border: 1px solid transparent;
  border-radius: var(--wpgate-radius-sm);
  font-size: 0.88rem;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  box-shadow: 0 1px 2px rgba(37, 99, 235, 0.2);
  transition: all 0.15s ease;
}

.wpgate-btn-save-header:hover:not(:disabled) {
  background-color: var(--wpgate-primary-hover);
  box-shadow: 0 2px 4px rgba(37, 99, 235, 0.3);
}

.wpgate-btn-save-header:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

@media (max-width: 1024px) {
  .header-hamburger-btn {
    display: flex;
  }
}

@media (max-width: 640px) {
  .header-status-badge {
    display: none;
  }
  .wpgate-header {
    padding: 0 14px;
  }
}
</style>
