<template>
  <div class="wpgate-admin-wrap" :data-theme="theme" :dir="isRtl ? 'rtl' : 'ltr'">
    <div
      class="wpgate-layout-container"
      :class="{
        'sidebar-collapsed': isSidebarCollapsed,
        'mobile-open': isMobileOpen
      }"
    >
      <!-- Mobile Drawer Backdrop Overlay -->
      <div
        v-if="isMobileOpen"
        class="wpgate-mobile-backdrop"
        @click="isMobileOpen = false"
      ></div>

      <!-- Vertical Sidebar -->
      <AppSidebar
        :current-tab="currentTab"
        :is-collapsed="isSidebarCollapsed"
        :is-mobile-open="isMobileOpen"
        :system="system"
        :stats="stats"
        @select-tab="onSelectTab"
        @toggle-collapse="toggleSidebar"
        @close-mobile="isMobileOpen = false"
      />

      <!-- Main Layout (Header + Scrollable Content) -->
      <div class="wpgate-layout-main">
        <AppHeader
          :current-tab="currentTab"
          :system="system"
          :theme="theme"
          :saving="saving"
          :has-unsaved="hasUnsavedChanges"
          @open-mobile="isMobileOpen = true"
          @toggle-theme="toggleTheme"
          @toggle-lang="toggleLang"
          @save="saveSettings"
        />

        <main class="wpgate-main-scroll-area">
          <div class="wpgate-content-container">
            <div v-if="loadingInitial" class="wpgate-table-loading" style="padding: 100px 0;">
              <div class="wpgate-spinner"></div>
              <span>{{ t('common.loading') }}</span>
            </div>

            <template v-else>
              <!-- Tab 1: Overview Dashboard -->
              <TabOverview
                v-if="currentTab === 'overview'"
                :settings="settings"
                :system="system"
                :stats="stats"
                @switch-tab="onSelectTab"
              />

              <!-- Tab 2: HTTP Control Engine -->
              <TabHttpControl
                v-if="currentTab === 'httpControl'"
                :settings="settings"
                :presets="presets"
                @notify="showNotification"
              />

              <!-- Tab 3: Local CDN & Asset Manager -->
              <TabLocalAssets
                v-if="currentTab === 'localAssets'"
                :settings="settings"
              />

              <!-- Tab 4: WordPress Core Controls -->
              <TabWpControls
                v-if="currentTab === 'wpControls'"
                :settings="settings"
              />

              <!-- Tab 5: Diagnostic Logs -->
              <TabLogs
                v-if="currentTab === 'logs'"
                :settings="settings"
                @notify="showNotification"
                @confirm="openConfirmModal"
              />

              <!-- Tab 6: Backup & System Diagnostics -->
              <TabTools
                v-if="currentTab === 'tools'"
                :settings="settings"
                :system="system"
                @notify="showNotification"
                @confirm="openConfirmModal"
                @reload-settings="loadData"
              />

              <!-- Tab 7: About & Attributions -->
              <TabAbout
                v-if="currentTab === 'about'"
              />
            </template>
          </div>
        </main>
      </div>
    </div>

    <!-- Global Toast Notifications -->
    <ToastNotice ref="toastRef" />

    <!-- Action Confirmation Modal -->
    <ConfirmModal ref="modalRef" />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue';
import { api } from './api';
import { currentLang, isRtl, setLanguage, t } from './i18n';

import AppSidebar from './components/AppSidebar.vue';
import AppHeader from './components/AppHeader.vue';
import TabOverview from './components/TabOverview.vue';
import TabHttpControl from './components/TabHttpControl.vue';
import TabLocalAssets from './components/TabLocalAssets.vue';
import TabWpControls from './components/TabWpControls.vue';
import TabLogs from './components/TabLogs.vue';
import TabTools from './components/TabTools.vue';
import TabAbout from './components/TabAbout.vue';
import ToastNotice from './components/ToastNotice.vue';
import ConfirmModal from './components/ConfirmModal.vue';

// Navigation & State
const currentTab = ref('overview');
const theme = ref(localStorage.getItem('wpgate_theme') || 'light');
const isSidebarCollapsed = ref(localStorage.getItem('wpgate_sidebar_collapsed') === 'true');
const isMobileOpen = ref(false);
const loadingInitial = ref(true);
const saving = ref(false);
const initialSettingsJson = ref('');

// UI Refs
const toastRef = ref(null);
const modalRef = ref(null);

// Reactive Data
const settings = reactive({
  mode: 'disabled',
  whitelist: '',
  blacklist: '',
  local_asset_rewrite: 'yes',
  strict_asset_block: 'no',
  block_mixpanel: 'yes',
  enable_front_vazirmatn: 'no',
  disable_updates: 'no',
  allow_whitelisted_updates: 'yes',
  disable_telemetry: 'yes',
  disable_gravatar: 'no',
  gravatar_url: '',
  enable_timeout_guard: 'yes',
  max_request_timeout: 10,
  enable_logging: 'no',
  log_retention_days: 7,
  log_max_entries: 300,
  log_asset_events: 'no',
});

const system = reactive({
  version: '0.1.4',
  php_version: '',
  wp_version: '',
  loading_method: 'mu-plugin',
  is_multisite: false,
  disabled_by_constant: false,
});

const stats = reactive({
  total_intercepted: 0,
  total_blocked: 0,
  total_allowed: 0,
  total_rewritten: 0,
  whitelist_count: 0,
  blacklist_count: 0,
});

const presets = ref([]);

// Dirty state tracking
const hasUnsavedChanges = computed(() => {
  if (!initialSettingsJson.value) return false;
  return JSON.stringify(settings) !== initialSettingsJson.value;
});

// Sidebar & Tab handlers
const onSelectTab = (tabId) => {
  currentTab.value = tabId;
  isMobileOpen.value = false;
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const toggleSidebar = () => {
  isSidebarCollapsed.value = !isSidebarCollapsed.value;
  localStorage.setItem('wpgate_sidebar_collapsed', isSidebarCollapsed.value ? 'true' : 'false');
};

const toggleTheme = () => {
  theme.value = theme.value === 'dark' ? 'light' : 'dark';
  localStorage.setItem('wpgate_theme', theme.value);
};

const toggleLang = () => {
  const nextLang = currentLang.value === 'fa' ? 'en' : 'fa';
  setLanguage(nextLang);
};

// Data Loading
const loadData = async () => {
  loadingInitial.value = true;
  try {
    // 1. Primary Contract: Load settings and system info from /settings
    const settingsRes = await api.getSettings();

    if (settingsRes && settingsRes.success && settingsRes.data) {
      const incomingSettings = settingsRes.data.settings || settingsRes.data;
      if (incomingSettings && typeof incomingSettings === 'object') {
        Object.assign(settings, incomingSettings);
        initialSettingsJson.value = JSON.stringify(incomingSettings);
      }
      if (settingsRes.data.system) {
        Object.assign(system, settingsRes.data.system);
      }
    }

    // 2. Secondary Contracts: Load stats and presets gracefully in parallel (failure must not crash settings)
    const [statsResult, presetsResult] = await Promise.allSettled([
      api.getStats(),
      api.getPresets(),
    ]);

    if (statsResult.status === 'fulfilled' && statsResult.value?.success && statsResult.value?.data) {
      Object.assign(stats, statsResult.value.data);
    }
    if (presetsResult.status === 'fulfilled' && presetsResult.value?.success && presetsResult.value?.data) {
      presets.value = presetsResult.value.data;
    }
  } catch (err) {
    console.error('WPGate loadData error:', err);
    showNotification({
      type: 'error',
      message: t('common.apiError') + (err.message ? ' (' + err.message + ')' : ''),
    });
  } finally {
    loadingInitial.value = false;
  }
};

// Save Settings
const saveSettings = async () => {
  saving.value = true;
  try {
    const res = await api.saveSettings(settings);
    if (res && res.success) {
      showNotification({
        type: 'success',
        message: t('common.saved'),
      });
      const updatedSettings = res.data?.settings || settings;
      Object.assign(settings, updatedSettings);
      initialSettingsJson.value = JSON.stringify(settings);
      // Refresh stats gracefully
      try {
        const statsRes = await api.getStats();
        if (statsRes && statsRes.success && statsRes.data) {
          Object.assign(stats, statsRes.data);
        }
      } catch (e) {
        // Non-critical telemetry refresh
      }
    } else {
      showNotification({
        type: 'error',
        message: res?.message || t('common.saveFailed'),
      });
    }
  } catch (err) {
    showNotification({
      type: 'error',
      message: t('common.saveFailed') + (err.message ? ' (' + err.message + ')' : ''),
    });
  } finally {
    saving.value = false;
  }
};

// Toast notification helper
const showNotification = ({ message, type = 'success', duration = 4000 }) => {
  if (toastRef.value) {
    toastRef.value.show(message, type, duration);
  }
};

// Confirmation modal helper
const openConfirmModal = async (options) => {
  if (modalRef.value) {
    const confirmed = await modalRef.value.open(options);
    if (confirmed && typeof options.action === 'function') {
      options.action();
    }
  }
};

// Keyboard shortcuts (Ctrl+S / Cmd+S, Escape)
const handleKeydown = (e) => {
  // Ctrl+S / Cmd+S to save
  if ((e.ctrlKey || e.metaKey) && (e.key === 's' || e.key === 'S')) {
    e.preventDefault();
    if (!saving.value) {
      saveSettings();
    }
  }
  // Escape closes mobile menu
  if (e.key === 'Escape') {
    if (isMobileOpen.value) {
      isMobileOpen.value = false;
    }
  }
};

onMounted(() => {
  loadData();
  window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown);
});
</script>
