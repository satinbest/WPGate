<template>
  <div class="wpgate-tab-pane">
    <div class="wpgate-section-header">
      <div>
        <h2 class="wpgate-section-title">{{ t('tools.title') }}</h2>
        <p class="wpgate-section-desc">Configuration portability, runtime diagnostics, and disaster recovery controls.</p>
      </div>
    </div>

    <!-- Backup & Restore Grid -->
    <div class="wpgate-grid-2col">
      <!-- Export Configuration Card -->
      <SettingsPanel :title="t('common.export')" description="Download all active WPGate rules and policies as a clean JSON backup file.">
        <div class="wpgate-tool-action-box">
          <button type="button" class="wpgate-btn wpgate-btn-secondary" @click="exportJson">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="wpgate-icon"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            <span>{{ t('common.export') }} JSON</span>
          </button>
          <span class="wpgate-text-muted wpgate-text-xs">Portable across all staging and production environments</span>
        </div>
      </SettingsPanel>

      <!-- Import Configuration Card -->
      <SettingsPanel :title="t('common.import')" description="Restore or migrate WPGate settings from a previously saved JSON backup file.">
        <div class="wpgate-tool-action-box">
          <input
            type="file"
            ref="fileInput"
            accept=".json"
            class="wpgate-file-input"
            @change="importJson"
          />
          <button type="button" class="wpgate-btn wpgate-btn-secondary" @click="$refs.fileInput.click()">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="wpgate-icon"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
            <span>{{ t('common.import') }} JSON</span>
          </button>
          <span class="wpgate-text-muted wpgate-text-xs">Validates format before applying</span>
        </div>
      </SettingsPanel>
    </div>

    <!-- Emergency Kill-Switch Panel -->
    <SettingsPanel title="Emergency Kill-Switch (wp-config.php)" class="wpgate-mt-4" badge="Zero-Risk Recovery" badge-status="warning">
      <p class="wpgate-field-help">
        If you ever need to instantly bypass all WPGate filters and network interceptions without accessing the WordPress admin, add this constant anywhere in your <code>wp-config.php</code> file:
      </p>
      <div class="wpgate-code-snippet-box">
        <pre class="wpgate-code-snippet" dir="ltr"><code>define('WPGATE_DISABLED', true);</code></pre>
        <button type="button" class="wpgate-btn wpgate-btn-secondary wpgate-btn-sm" @click="copyKillSwitch">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
          <span>{{ t('common.copy') }}</span>
        </button>
      </div>
      <p class="wpgate-text-muted wpgate-text-xs wpgate-mt-2">
        When defined, WPGate halts execution on the first line of its bootstrap routine with zero overhead.
      </p>
    </SettingsPanel>

    <!-- System Diagnostics Information -->
    <SettingsPanel :title="t('tools.sysInfo')" class="wpgate-mt-4">
      <template #badge>
        <StatusBadge status="success">Operational</StatusBadge>
      </template>
      <div class="wpgate-sysinfo-grid">
        <div class="wpgate-sysinfo-item">
          <span class="wpgate-sysinfo-label">{{ t('tools.wpgateVersion') }}</span>
          <span class="wpgate-sysinfo-value">v{{ system.version || '0.1.3' }}</span>
        </div>
        <div class="wpgate-sysinfo-item">
          <span class="wpgate-sysinfo-label">{{ t('tools.loadingMethod') }}</span>
          <span class="wpgate-sysinfo-value"><code>{{ system.loading_method || 'mu-plugin' }}</code></span>
        </div>
        <div class="wpgate-sysinfo-item">
          <span class="wpgate-sysinfo-label">{{ t('tools.phpVersion') }}</span>
          <span class="wpgate-sysinfo-value">PHP {{ system.php_version }}</span>
        </div>
        <div class="wpgate-sysinfo-item">
          <span class="wpgate-sysinfo-label">{{ t('tools.wpVersion') }}</span>
          <span class="wpgate-sysinfo-value">WordPress {{ system.wp_version }}</span>
        </div>
        <div class="wpgate-sysinfo-item">
          <span class="wpgate-sysinfo-label">{{ t('tools.multisite') }}</span>
          <span class="wpgate-sysinfo-value">{{ system.is_multisite ? 'Yes' : 'No' }}</span>
        </div>
        <div class="wpgate-sysinfo-item">
          <span class="wpgate-sysinfo-label">{{ t('tools.diskWrites') }}</span>
          <span class="wpgate-sysinfo-value text-success">
            <strong>0 Writes (Pure Memory)</strong>
          </span>
        </div>
      </div>
    </SettingsPanel>

    <!-- Danger Zone: Factory Reset -->
    <SettingsPanel title="Danger Zone" class="wpgate-mt-4" badge="Irreversible" badge-status="danger">
      <div class="wpgate-danger-zone-box">
        <div>
          <strong class="text-danger">{{ t('common.reset') }}</strong>
          <p class="wpgate-text-muted wpgate-text-xs">{{ t('tools.resetDesc') }}</p>
        </div>
        <button type="button" class="wpgate-btn wpgate-btn-danger" @click="onResetDefaults">
          {{ t('common.reset') }}
        </button>
      </div>
    </SettingsPanel>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { api } from '../api';
import { t } from '../i18n';
import SettingsPanel from './SettingsPanel.vue';
import StatusBadge from './StatusBadge.vue';

const props = defineProps({
  settings: { type: Object, required: true },
  system: { type: Object, required: true },
});

const emit = defineEmits(['notify', 'confirm', 'reload-settings']);
const fileInput = ref(null);

const exportJson = () => {
  const exportPayload = {
    generator: 'WPGate',
    version: props.system.version || '0.1.3',
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

const copyKillSwitch = () => {
  if (navigator.clipboard) {
    navigator.clipboard.writeText("define('WPGATE_DISABLED', true);");
    emit('notify', { type: 'success', message: t('common.copied') });
  }
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
        emit('notify', { type: 'success', message: 'WPGate settings reset to default.' });
        emit('reload-settings');
      } catch (err) {
        emit('notify', { type: 'error', message: 'Failed to reset settings.' });
      }
    },
  });
};
</script>
