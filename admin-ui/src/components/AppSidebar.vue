<template>
  <aside
    class="wpgate-sidebar"
    :class="{ 'is-collapsed': isCollapsed, 'is-mobile-open': isMobileOpen }"
    role="navigation"
    :aria-label="t('sidebar.coreGroup')"
  >
    <!-- Brand / Logo Header -->
    <div class="sidebar-header">
      <div class="sidebar-brand" @click="$emit('select-tab', 'overview')">
        <div class="brand-logo-icon">
          <svg width="28" height="28" viewBox="0 0 100 100" fill="none">
            <rect width="100" height="100" rx="22" fill="#0f172a" />
            <path d="M50 14L82 32V68L50 86L18 68V32L50 14Z" stroke="#38bdf8" stroke-width="5" fill="none" />
            <path d="M50 26L72 38.5V61.5L50 74L28 61.5V38.5L50 26Z" fill="#1e293b" stroke="#0ea5e9" stroke-width="3" />
            <line x1="50" y1="26" x2="50" y2="74" stroke="#38bdf8" stroke-width="2.5" stroke-dasharray="3 3" />
            <circle cx="50" cy="50" r="7" fill="#38bdf8" />
          </svg>
        </div>
        <div v-show="!isCollapsed" class="brand-text-wrap">
          <span class="brand-name">WPGate</span>
          <span class="brand-subtitle">{{ t('sidebar.securityCore') }}</span>
        </div>
      </div>

      <!-- Close button on Mobile Drawer -->
      <button
        type="button"
        class="mobile-close-btn"
        @click="$emit('close-mobile')"
        :aria-label="t('common.closeNav')"
      >
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
      </button>
    </div>

    <!-- Navigation Groups -->
    <div class="sidebar-nav-container">
      <!-- Section 1: Core Navigation -->
      <div class="nav-group">
        <div v-show="!isCollapsed" class="nav-group-title">
          {{ t('sidebar.coreGroup') }}
        </div>
        <ul class="nav-list">
          <li v-for="item in coreNavItems" :key="item.id">
            <button
              type="button"
              class="nav-item-btn"
              :class="{ 'is-active': currentTab === item.id }"
              @click="onSelect(item.id)"
              :title="isCollapsed ? t(`tabs.${item.id}`) : ''"
            >
              <span class="nav-icon" v-html="item.icon"></span>
              <span v-show="!isCollapsed" class="nav-label">{{ t(`tabs.${item.id}`) }}</span>
              <span
                v-if="item.badge && !isCollapsed"
                class="nav-badge"
                :class="`nav-badge-${item.badgeType || 'neutral'}`"
              >
                {{ item.badge }}
              </span>
            </button>
          </li>
        </ul>
      </div>

      <!-- Section 2: System & Insights -->
      <div class="nav-group">
        <div v-show="!isCollapsed" class="nav-group-title">
          {{ t('sidebar.systemGroup') }}
        </div>
        <ul class="nav-list">
          <li v-for="item in systemNavItems" :key="item.id">
            <button
              type="button"
              class="nav-item-btn"
              :class="{ 'is-active': currentTab === item.id }"
              @click="onSelect(item.id)"
              :title="isCollapsed ? t(`tabs.${item.id}`) : ''"
            >
              <span class="nav-icon" v-html="item.icon"></span>
              <span v-show="!isCollapsed" class="nav-label">{{ t(`tabs.${item.id}`) }}</span>
            </button>
          </li>
        </ul>
      </div>
    </div>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
      <div v-show="!isCollapsed" class="footer-info-row">
        <span class="footer-version-tag">
          v{{ system.version || '0.1.4' }}
        </span>
        <span class="footer-mode-pill">
          {{ system.loading_method === 'theme' ? t('sidebar.themeMode') : t('sidebar.muMode') }}
        </span>
      </div>

      <!-- Desktop Collapse Toggle -->
      <button
        type="button"
        class="collapse-toggle-btn"
        @click="$emit('toggle-collapse')"
        :title="isCollapsed ? t('sidebar.expand') : t('sidebar.collapse')"
      >
        <svg v-if="isCollapsed" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="collapse-icon-expand">
          <polyline points="13 17 18 12 13 7"></polyline>
          <polyline points="6 17 11 12 6 7"></polyline>
        </svg>
        <svg v-else width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="collapse-icon-collapse">
          <polyline points="11 17 6 12 11 7"></polyline>
          <polyline points="18 17 13 12 18 7"></polyline>
        </svg>
        <span v-show="!isCollapsed" class="collapse-btn-text">{{ t('sidebar.collapse') }}</span>
      </button>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue';
import { t } from '../i18n';

const props = defineProps({
  currentTab: {
    type: String,
    required: true,
  },
  isCollapsed: {
    type: Boolean,
    default: false,
  },
  isMobileOpen: {
    type: Boolean,
    default: false,
  },
  system: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits(['select-tab', 'toggle-collapse', 'close-mobile']);

const coreNavItems = [
  {
    id: 'overview',
    icon: '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>',
  },
  {
    id: 'httpControl',
    icon: '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1 4-10z"></path></svg>',
  },
  {
    id: 'localAssets',
    icon: '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>',
  },
  {
    id: 'wpControls',
    icon: '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>',
  },
];

const systemNavItems = [
  {
    id: 'logs',
    icon: '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>',
  },
  {
    id: 'tools',
    icon: '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>',
  },
  {
    id: 'about',
    icon: '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>',
  },
];

const onSelect = (tabId) => {
  emit('select-tab', tabId);
  emit('close-mobile');
};
</script>

<style scoped>
.wpgate-sidebar {
  width: 232px;
  background-color: var(--wpgate-surface);
  border-inline-end: 1px solid var(--wpgate-border);
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
  transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1), transform 0.25s ease;
  position: relative;
  z-index: 100;
  min-height: 100%;
}

.wpgate-sidebar.is-collapsed {
  width: 68px;
}

/* Header Brand */
.sidebar-header {
  padding: 16px 14px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid var(--wpgate-border);
}

.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  user-select: none;
}

.brand-logo-icon {
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.brand-text-wrap {
  display: flex;
  flex-direction: column;
  line-height: 1.15;
}

.brand-name {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--wpgate-text);
  letter-spacing: -0.01em;
}

.brand-subtitle {
  font-size: 0.68rem;
  font-weight: 500;
  color: var(--wpgate-text-muted);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.mobile-close-btn {
  display: none;
  background: none;
  border: none;
  color: var(--wpgate-text-muted);
  cursor: pointer;
  padding: 4px;
}

/* Navigation List */
.sidebar-nav-container {
  flex-grow: 1;
  padding: 14px 8px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  overflow-y: auto;
}

.nav-group {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.nav-group-title {
  font-size: 0.68rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--wpgate-text-muted);
  padding: 0 10px 4px;
}

.nav-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.nav-item-btn {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 12px;
  border: none;
  background: transparent;
  color: var(--wpgate-text-muted);
  font-size: 13px;
  font-weight: 500;
  border-radius: var(--wpgate-radius-sm);
  cursor: pointer;
  transition: all 0.15s ease;
  position: relative;
  text-align: start;
}

.nav-item-btn:hover {
  background-color: var(--wpgate-surface-alt);
  color: var(--wpgate-text);
}

.nav-item-btn.is-active {
  background-color: rgba(37, 99, 235, 0.09);
  color: var(--wpgate-primary);
  font-weight: 600;
}

[data-theme="dark"] .nav-item-btn.is-active {
  background-color: rgba(59, 130, 246, 0.15);
  color: var(--wpgate-primary);
}

.nav-item-btn.is-active::before {
  content: "";
  position: absolute;
  inset-inline-start: 0;
  top: 6px;
  bottom: 6px;
  width: 3px;
  border-radius: 4px;
  background-color: var(--wpgate-primary);
}

.nav-icon {
  width: 18px;
  height: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.nav-label {
  flex-grow: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-size: 13px;
}

/* Footer & Collapse */
.sidebar-footer {
  padding: 12px 10px;
  border-top: 1px solid var(--wpgate-border);
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.footer-info-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 4px;
}

.footer-version-tag {
  font-size: 11px;
  font-weight: 600;
  color: var(--wpgate-text-muted);
}

.footer-mode-pill {
  font-size: 11px;
  font-weight: 600;
  padding: 2px 7px;
  border-radius: 9999px;
  background-color: var(--wpgate-surface-alt);
  color: var(--wpgate-text-muted);
  border: 1px solid var(--wpgate-border);
}

.collapse-toggle-btn {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 6px 8px;
  border: 1px solid var(--wpgate-border);
  background-color: var(--wpgate-surface);
  color: var(--wpgate-text-muted);
  border-radius: var(--wpgate-radius-sm);
  cursor: pointer;
  font-size: 12px;
  font-weight: 500;
  transition: all 0.15s ease;
}

.collapse-toggle-btn:hover {
  background-color: var(--wpgate-surface-alt);
  color: var(--wpgate-text);
  border-color: var(--wpgate-border-focus);
}

[dir="rtl"] .collapse-icon-collapse {
  transform: rotate(180deg);
}

[dir="rtl"] .collapse-icon-expand {
  transform: rotate(180deg);
}

/* Mobile responsive drawer */
@media (max-width: 1024px) {
  .wpgate-sidebar {
    position: fixed;
    top: 0;
    bottom: 0;
    inset-inline-start: 0;
    height: 100vh;
    z-index: 100000;
    box-shadow: var(--wpgate-shadow-lg);
    transform: translateX(-100%);
  }

  [dir="rtl"] .wpgate-sidebar {
    transform: translateX(100%);
  }

  .wpgate-sidebar.is-mobile-open {
    transform: translateX(0);
  }

  .mobile-close-btn {
    display: flex;
  }

  .collapse-toggle-btn {
    display: none;
  }
}
</style>
