<template>
  <div class="wpgate-tab-pane">
    <div class="wpgate-section-header">
      <div>
        <h2 class="wpgate-section-title">{{ t('http.title') }}</h2>
        <p class="wpgate-section-desc">{{ t('http.desc') }}</p>
      </div>
      <StatusBadge :status="settings.mode === 'disabled' ? 'neutral' : (settings.mode === 'whitelist' ? 'success' : 'danger')">
        {{ settings.mode === 'disabled' ? t('common.disabled') : (settings.mode === 'whitelist' ? t('overview.whitelistStrict') : t('overview.blacklistBlock')) }}
      </StatusBadge>
    </div>

    <!-- Mode Selector Panel -->
    <SettingsPanel :title="t('http.modeLabel')" :badge="t('common.operationalMode')" badge-status="primary">
      <div class="wpgate-mode-selector-grid">
        <!-- Mode: Disabled -->
        <div
          class="wpgate-mode-card"
          :class="{ active: settings.mode === 'disabled' }"
          @click="settings.mode = 'disabled'"
        >
          <div class="wpgate-mode-card-radio">
            <span class="wpgate-radio-indicator"></span>
          </div>
          <div class="wpgate-mode-card-content">
            <div class="wpgate-mode-card-header">
              <span class="wpgate-mode-icon neutral">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>
              </span>
              <strong class="wpgate-mode-title">{{ t('common.disabled') }}</strong>
            </div>
            <p class="wpgate-mode-desc">{{ t('http.disabledMode') }}</p>
          </div>
        </div>

        <!-- Mode: Whitelist (Allowlist) -->
        <div
          class="wpgate-mode-card"
          :class="{ active: settings.mode === 'whitelist' }"
          @click="settings.mode = 'whitelist'"
        >
          <div class="wpgate-mode-card-radio">
            <span class="wpgate-radio-indicator"></span>
          </div>
          <div class="wpgate-mode-card-content">
            <div class="wpgate-mode-card-header">
              <span class="wpgate-mode-icon success">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><polyline points="9 12 11 14 15 10"></polyline></svg>
              </span>
              <strong class="wpgate-mode-title">{{ t('http.whitelistTitle') }}</strong>
            </div>
            <p class="wpgate-mode-desc">{{ t('http.whitelistMode') }}</p>
          </div>
        </div>

        <!-- Mode: Blacklist (Blocklist) -->
        <div
          class="wpgate-mode-card"
          :class="{ active: settings.mode === 'blacklist' }"
          @click="settings.mode = 'blacklist'"
        >
          <div class="wpgate-mode-card-radio">
            <span class="wpgate-radio-indicator"></span>
          </div>
          <div class="wpgate-mode-card-content">
            <div class="wpgate-mode-card-header">
              <span class="wpgate-mode-icon danger">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
              </span>
              <strong class="wpgate-mode-title">{{ t('http.blacklistTitle') }}</strong>
            </div>
            <p class="wpgate-mode-desc">{{ t('http.blacklistMode') }}</p>
          </div>
        </div>
      </div>
    </SettingsPanel>

    <!-- Whitelist Configuration -->
    <SettingsPanel
      v-show="settings.mode === 'whitelist'"
      class="wpgate-mt-4"
      :title="t('http.whitelistTitle')"
      :description="t('http.whitelistHelp')"
    >
      <template #badge>
        <StatusBadge status="info">
          {{ parsedWhitelistCount }} {{ t('common.entries') }}
        </StatusBadge>
      </template>

      <!-- Presets Quick Add Box -->
      <div class="wpgate-presets-wrapper">
        <div class="wpgate-presets-header">
          <div class="wpgate-presets-title-wrap">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            <strong>{{ t('http.presetsTitle') }}</strong>
          </div>
          <span class="wpgate-presets-subtitle">{{ t('http.presetsHelp') }}</span>
        </div>

        <div class="wpgate-preset-chips">
          <button
            v-for="preset in presets"
            :key="preset.key"
            type="button"
            class="wpgate-chip"
            @click="addPreset(preset)"
            :title="t('http.addPresetTitle').replace('{count}', preset.domains ? preset.domains.length : 0)"
          >
            <span class="wpgate-chip-plus">+</span>
            <span class="wpgate-chip-text">{{ preset.label }}</span>
            <span class="wpgate-chip-count">{{ preset.domains ? preset.domains.length : 0 }}</span>
          </button>
        </div>
      </div>

      <!-- Domain List Textarea (Forced LTR for URLs/domains) -->
      <div class="wpgate-form-group wpgate-mt-4">
        <div class="wpgate-form-label-row">
          <label class="wpgate-label">{{ t('http.whitelistTitle') }}</label>
          <span class="wpgate-text-muted wpgate-text-xs">{{ t('http.domainFormatHelp') }}</span>
        </div>
        <textarea
          v-model="settings.whitelist"
          rows="10"
          dir="ltr"
          class="wpgate-textarea wpgate-code-editor"
          placeholder="api.zarinpal.com&#10;sep.ir&#10;*.shaparak.ir&#10;zhaket.com"
          spellcheck="false"
        ></textarea>
      </div>
    </SettingsPanel>

    <!-- Blacklist Configuration -->
    <SettingsPanel
      v-show="settings.mode === 'blacklist'"
      class="wpgate-mt-4"
      :title="t('http.blacklistTitle')"
      :description="t('http.blacklistHelp')"
    >
      <template #badge>
        <StatusBadge status="danger">
          {{ parsedBlacklistCount }} {{ t('common.entries') }}
        </StatusBadge>
      </template>

      <div class="wpgate-form-group">
        <div class="wpgate-form-label-row">
          <label class="wpgate-label">{{ t('http.blacklistTitle') }}</label>
          <span class="wpgate-text-muted wpgate-text-xs">One blocked domain or pattern per line</span>
        </div>
        <textarea
          v-model="settings.blacklist"
          rows="8"
          dir="ltr"
          class="wpgate-textarea wpgate-code-editor"
          placeholder="unwanted-telemetry.com&#10;*.third-party-tracker.net"
          spellcheck="false"
        ></textarea>
      </div>
    </SettingsPanel>

    <!-- Request Timeout Guard Panel -->
    <SettingsPanel
      class="wpgate-mt-4"
      :title="t('http.timeoutTitle')"
      :description="t('http.timeoutHelp')"
    >
      <SettingToggle
        v-model="settings.enable_timeout_guard"
        :title="t('http.enableTimeout')"
        :description="t('http.timeoutHelp')"
      >
        <template #extra v-if="settings.enable_timeout_guard === 'yes'">
          <div class="wpgate-timeout-slider-box">
            <div class="wpgate-slider-label-row">
              <span class="wpgate-slider-title">{{ t('http.maxTimeout') }}</span>
              <span class="wpgate-slider-badge">{{ settings.max_request_timeout }}s</span>
            </div>
            <div class="wpgate-slider-control-row">
              <span class="wpgate-slider-bound">1s</span>
              <input
                type="range"
                v-model.number="settings.max_request_timeout"
                min="1"
                max="30"
                step="1"
                class="wpgate-range"
              />
              <span class="wpgate-slider-bound">30s</span>
            </div>
            <p class="wpgate-slider-hint">
              Recommended: <strong>5s – 10s</strong>. Prevents outbound requests from freezing PHP workers and causing 504 Gateway Timeouts.
            </p>
          </div>
        </template>
      </SettingToggle>
    </SettingsPanel>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { t } from '../i18n';
import SettingsPanel from './SettingsPanel.vue';
import SettingToggle from './SettingToggle.vue';
import StatusBadge from './StatusBadge.vue';

const props = defineProps({
  settings: { type: Object, required: true },
  presets: { type: Array, default: () => [] },
});

const emit = defineEmits(['notify']);

const parsedWhitelistCount = computed(() => {
  if (!props.settings.whitelist) return 0;
  return props.settings.whitelist.split(/\r\n|\r|\n/).filter(line => line.trim().length > 0).length;
});

const parsedBlacklistCount = computed(() => {
  if (!props.settings.blacklist) return 0;
  return props.settings.blacklist.split(/\r\n|\r|\n/).filter(line => line.trim().length > 0).length;
});

const addPreset = (preset) => {
  if (!preset || !preset.domains || !preset.domains.length) return;
  const currentLines = (props.settings.whitelist || '').split(/\r\n|\r|\n/).map(s => s.trim().toLowerCase()).filter(Boolean);
  const currentSet = new Set(currentLines);
  let added = 0;

  for (const domain of preset.domains) {
    const clean = domain.trim().toLowerCase();
    if (clean && !currentSet.has(clean)) {
      currentSet.add(clean);
      added++;
    }
  }

  props.settings.whitelist = Array.from(currentSet).join('\n');
  emit('notify', {
    type: 'success',
    message: t('http.domainsAdded').replace('{count}', added).replace('{label}', preset.label),
  });
};
</script>
