<template>
  <div class="wpgate-app-container" :data-theme="theme" :dir="isRtl ? 'rtl' : 'ltr'">
    <!-- Top Header -->
    <header class="wpgate-header">
      <div class="wpgate-header-left">
        <div class="wpgate-logo-box">
          <svg width="34" height="34" viewBox="0 0 100 100" fill="none">
            <rect width="100" height="100" rx="20" fill="#0f172a" />
            <path d="M50 15L80 32.5V67.5L50 85L20 67.5V32.5L50 15Z" stroke="#38bdf8" stroke-width="6" fill="none"/>
            <path d="M50 28L70 40V60L50 72L30 60V40L50 28Z" fill="#1e293b" stroke="#0ea5e9" stroke-width="3"/>
            <line x1="50" y1="28" x2="50" y2="72" stroke="#38bdf8" stroke-width="3" stroke-dasharray="3 3"/>
            <circle cx="50" cy="50" r="7" fill="#38bdf8"/>
          </svg>
          <div>
            <h1 class="wpgate-app-title">
              {{ t('appName') }}
              <span class="wpgate-version-pill">v{{ system.version || '0.1.1' }}</span>
            </h1>
          </div>
        </div>
      </div>

      <div class="wpgate-header-right">
        <!-- Language Switcher -->
        <button
          type="button"
          class="wpgate-btn wpgate-btn-secondary"
          @click="toggleLang"
          :title="currentLang === 'fa' ? 'Switch to English' : 'تغییر به فارسی'"
        >
          <span>{{ currentLang === 'fa' ? 'English' : 'فارسی' }}</span>
        </button>

        <!-- Theme Switcher -->
        <button
          type="button"
          class="wpgate-btn-icon"
          @click="toggleTheme"
          :title="theme === 'dark' ? 'Switch to Light Theme' : 'Switch to Dark Theme'"
        >
          <svg v-if="theme === 'dark'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
          <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
        </button>

        <!-- Save Button -->
        <button
          type="button"
          class="wpgate-btn wpgate-btn-primary"
          @click="saveSettings"
          :disabled="saving"
        >
          <svg v-if="saving" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="wpgate-spin"><line x1="12" y1="2" x2="12" y2="6"></line><line x1="12" y1="18" x2="12" y2="22"></line><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line><line x1="2" y1="12" x2="6" y2="12"></line><line x1="18" y1="12" x2="22" y2="12"></line><line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line><line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line></svg>
          <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
          <span>{{ saving ? t('common.saving') : t('common.save') }}</span>
        </button>
      </div>
    </header>

    <!-- Navigation Tabs -->
    <nav class="wpgate-nav-tabs">
      <button
        v-for="tab in tabList"
        :key="tab.id"
        type="button"
        class="wpgate-nav-tab"
        :class="{ active: currentTab === tab.id }"
        @click="currentTab = tab.id"
      >
        <span class="wpgate-tab-icon" v-html="tab.icon"></span>
        <span>{{ t(`tabs.${tab.id}`) }}</span>
      </button>
    </nav>

    <!-- Main Content Tab Area -->
    <main class="wpgate-main-content">
      <div v-if="loadingInitial" class="wpgate-table-loading" style="padding: 60px 0;">
        <div class="wpgate-spinner"></div>
        <span>{{ t('common.loading') }}</span>
      </div>

      <template v-else>
        <!-- Tab 1: Overview -->
        <TabOverview
          v-if="currentTab === 'overview'"
          :settings="settings"
          :system="system"
          :stats="stats"
          @switch-tab="currentTab = $event"
        />

        <!-- Tab 2: HTTP Control -->
        <TabHttpControl
          v-if="currentTab === 'httpControl'"
          :settings="settings"
          :presets="presets"
          @notify="showNotification"
        />

        <!-- Tab 3: Local Assets -->
        <TabLocalAssets
          v-if="currentTab === 'localAssets'"
          :settings="settings"
        />

        <!-- Tab 4: WordPress Controls -->
        <TabWpControls
          v-if="currentTab === 'wpControls'"
          :settings="settings"
        />

        <!-- Tab 5: Logs -->
        <TabLogs
          v-if="currentTab === 'logs'"
          :settings="settings"
          @notify="showNotification"
          @confirm="openConfirmModal"
        />

        <!-- Tab 6: Tools & Settings -->
        <TabTools
          v-if="currentTab === 'tools'"
          :settings="settings"
          :system="system"
          @notify="showNotification"
          @confirm="openConfirmModal"
          @reload-settings="loadData"
        />

        <!-- Tab 7: About WPGate -->
        <TabAbout
          v-if="currentTab === 'about'"
        />
      </template>
    </main>

    <!-- Notifications Toast -->
    <ToastNotice ref="toastRef" />

    <!-- Confirmation Modal -->
    <ConfirmModal ref="modalRef" />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { api } from './api';
import { currentLang, t } from './i18n';
import TabOverview from './components/TabOverview.vue';
import TabHttpControl from './components/TabHttpControl.vue';
import TabLocalAssets from './components/TabLocalAssets.vue';
import TabWpControls from './components/TabWpControls.vue';
import TabLogs from './components/TabLogs.vue';
import TabTools from './components/TabTools.vue';
import TabAbout from './components/TabAbout.vue';
import ToastNotice from './components/ToastNotice.vue';
import ConfirmModal from './components/ConfirmModal.vue';

const currentTab = ref('overview');
const theme = ref(localStorage.getItem('wpgate_theme') || 'light');
const loadingInitial = ref(true);
const saving = ref(false);

const settings = reactive({});
const system = reactive({});
const stats = reactive({});
const presets = ref([]);

const toastRef = ref(null);
const modalRef = ref(null);

const isRtl = computed(() => currentLang.value === 'fa');

const tabList = [
  { id: 'overview', icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>' },
  { id: 'httpControl', icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>' },
  { id: 'localAssets', icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>' },
  { id: 'wpControls', icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>' },
  { id: 'logs', icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>' },
  { id: 'tools', icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>' },
  { id: 'about', icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>' },
];

const toggleTheme = () => {
  theme.value = theme.value === 'dark' ? 'light' : 'dark';
  localStorage.setItem('wpgate_theme', theme.value);
};

const toggleLang = () => {
  currentLang.value = currentLang.value === 'fa' ? 'en' : 'fa';
};

const showNotification = (event) => {
  if (toastRef.value) {
    toastRef.value.show(event.message, event.type || 'success');
  }
};

const openConfirmModal = async (options) => {
  if (modalRef.value) {
    const confirmed = await modalRef.value.open(options);
    if (confirmed && typeof options.action === 'function') {
      options.action();
    }
  }
};

const loadData = async () => {
  loadingInitial.value = true;
  try {
    const [settingsRes, statsRes, presetsRes] = await Promise.all([
      api.getSettings(),
      api.getStats(),
      api.getPresets(),
    ]);

    if (settingsRes.success && settingsRes.data) {
      Object.assign(settings, settingsRes.data.settings || {});
      Object.assign(system, settingsRes.data.system || {});
    }

    if (statsRes.success && statsRes.data) {
      Object.assign(stats, statsRes.data || {});
    }

    if (presetsRes.success && presetsRes.data) {
      presets.value = presetsRes.data || [];
    }
  } catch (err) {
    showNotification({ type: 'error', message: 'Failed to initialize WPGate data from WordPress REST API.' });
  } finally {
    loadingInitial.value = false;
  }
};

const saveSettings = async () => {
  saving.value = true;
  try {
    const res = await api.saveSettings(settings);
    if (res.success) {
      showNotification({ type: 'success', message: t('common.saved') });
      const statsRes = await api.getStats();
      if (statsRes.success) {
        Object.assign(stats, statsRes.data);
      }
    }
  } catch (err) {
    showNotification({ type: 'error', message: `Save error: ${err.message}` });
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  loadData();
});
</script>
