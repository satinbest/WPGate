<template>
  <div class="wpgate-tab-pane">
    <div class="wpgate-section-header">
      <h2 class="wpgate-section-title">{{ t('tools.title') }}</h2>
    </div>

    <!-- Backup & Restore Grid -->
    <div class="wpgate-grid-2col">
      <!-- Export Card -->
      <div class="wpgate-panel">
        <div class="wpgate-panel-header">
          <h3 class="wpgate-panel-title">{{ t('common.export') }}</h3>
        </div>
        <div class="wpgate-panel-body">
          <p class="wpgate-field-help">{{ t('tools.exportDesc') }}</p>
          <button type="button" class="wpgate-btn wpgate-btn-secondary" @click="exportJson">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="wpgate-icon"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            {{ t('common.export') }}
          </button>
        </div>
      </div>

      <!-- Import Card -->
      <div class="wpgate-panel">
        <div class="wpgate-panel-header">
          <h3 class="wpgate-panel-title">{{ t('common.import') }}</h3>
        </div>
        <div class="wpgate-panel-body">
          <p class="wpgate-field-help">{{ t('tools.importDesc') }}</p>
          <input
            type="file"
            ref="fileInput"
            accept=".json"
            class="wpgate-file-input"
            @change="importJson"
          />
          <button type="button" class="wpgate-btn wpgate-btn-secondary" @click="$refs.fileInput.click()">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="wpgate-icon"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
            {{ t('common.import') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Reset to Factory Defaults Card -->
    <div class="wpgate-panel wpgate-mt-4">
      <div class="wpgate-panel-header">
        <h3 class="wpgate-panel-title text-danger">{{ t('common.reset') }}</h3>
      </div>
      <div class="wpgate-panel-body">
        <p class="wpgate-field-help">{{ t('tools.resetDesc') }}</p>
        <button type="button" class="wpgate-btn wpgate-btn-danger" @click="onResetDefaults">
          {{ t('common.reset') }}
        </button>
      </div>
    </div>

    <!-- System Diagnostics Information -->
    <div class="wpgate-panel wpgate-mt-4">
      <div class="wpgate-panel-header">
        <h3 class="wpgate-panel-title">{{ t('tools.sysInfo') }}</h3>
      </div>
      <div class="wpgate-panel-body">
        <div class="wpgate-info-table">
          <div class="wpgate-info-row">
            <span class="wpgate-info-key">{{ t('tools.wpgateVersion') }}:</span>
            <span class="wpgate-info-val">v{{ system.version || '0.1.0' }}</span>
          </div>
          <div class="wpgate-info-row">
            <span class="wpgate-info-key">{{ t('tools.loadingMethod') }}:</span>
            <span class="wpgate-info-val"><code>{{ system.loading_method || 'mu-plugin' }}</code></span>
          </div>
          <div class="wpgate-info-row">
            <span class="wpgate-info-key">{{ t('tools.phpVersion') }}:</span>
            <span class="wpgate-info-val">{{ system.php_version }}</span>
          </div>
          <div class="wpgate-info-row">
            <span class="wpgate-info-key">{{ t('tools.wpVersion') }}:</span>
            <span class="wpgate-info-val">{{ system.wp_version }}</span>
          </div>
          <div class="wpgate-info-row">
            <span class="wpgate-info-key">{{ t('tools.multisite') }}:</span>
            <span class="wpgate-info-val">{{ system.is_multisite ? 'Yes' : 'No' }}</span>
          </div>
          <div class="wpgate-info-row">
            <span class="wpgate-info-key">{{ t('tools.diskWrites') }}:</span>
            <span class="wpgate-info-val text-success"><strong>{{ t('tools.diskWritesVal') }}</strong></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { api } from '../api';
import { t } from '../i18n';

const props = defineProps({
  settings: { type: Object, required: true },
  system: { type: Object, required: true },
});

const emit = defineEmits(['notify', 'confirm', 'reload-settings']);
const fileInput = ref(null);

const exportJson = () => {
  const exportPayload = {
    generator: 'WPGate',
    version: props.system.version || '0.1.0',
    export_date: new Date().toISOString(),
    settings: props.settings,
  };
  const jsonBlob = new Blob([JSON.stringify(exportPayload, null, 2)], { type: 'application/json' });
  const downloadUrl = URL.createObjectURL(jsonBlob);
  const a = document.createElement('a');
  a.href = downloadUrl;
  a.download = `wpgate-settings-${new Date().toISOString().slice(0, 10)}.json`;
  a.click();
  URL.revokeObjectURL(downloadUrl);
  emit('notify', { type: 'success', message: 'Configuration exported successfully.' });
};

const importJson = (event) => {
  const file = event.target.files[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onload = async (e) => {
    try {
      const parsed = JSON.parse(e.target.result);
      const incoming = parsed.settings || parsed;
      if (typeof incoming !== 'object' || incoming === null) {
        throw new Error('Invalid settings JSON structure.');
      }
      const res = await api.saveSettings(incoming);
      if (res.success) {
        emit('notify', { type: 'success', message: 'Settings imported and saved successfully.' });
        emit('reload-settings');
      }
    } catch (err) {
      emit('notify', { type: 'error', message: `Import failed: ${err.message}` });
    } finally {
      if (fileInput.value) fileInput.value.value = '';
    }
  };
  reader.readAsText(file);
};

const onResetDefaults = () => {
  emit('confirm', {
    title: t('common.reset'),
    message: t('tools.resetConfirm'),
    confirmText: t('common.confirm'),
    cancelText: t('common.cancel'),
    isDanger: true,
    action: async () => {
      try {
        await api.resetSettings();
        emit('notify', { type: 'success', message: 'Settings reset to factory defaults.' });
        emit('reload-settings');
      } catch (err) {
        emit('notify', { type: 'error', message: 'Failed to reset settings.' });
      }
    },
  });
};
</script>
