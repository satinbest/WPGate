<template>
  <div class="wpgate-tab-pane">
    <div class="wpgate-section-header">
      <h2 class="wpgate-section-title">{{ t('http.title') }}</h2>
      <p class="wpgate-section-desc">{{ t('http.desc') }}</p>
    </div>

    <!-- Interceptor Mode Selector -->
    <div class="wpgate-panel">
      <div class="wpgate-panel-header">
        <h3 class="wpgate-panel-title">{{ t('http.modeLabel') }}</h3>
      </div>
      <div class="wpgate-panel-body">
        <div class="wpgate-radio-group">
          <label class="wpgate-radio-card" :class="{ active: settings.mode === 'disabled' }">
            <input type="radio" v-model="settings.mode" value="disabled" />
            <div class="wpgate-radio-content">
              <strong>{{ t('common.disabled') }}</strong>
              <small>{{ t('http.disabledMode') }}</small>
            </div>
          </label>

          <label class="wpgate-radio-card" :class="{ active: settings.mode === 'whitelist' }">
            <input type="radio" v-model="settings.mode" value="whitelist" />
            <div class="wpgate-radio-content">
              <strong>{{ t('tabs.httpControl') }} — {{ t('common.all') }}</strong>
              <small>{{ t('http.whitelistMode') }}</small>
            </div>
          </label>

          <label class="wpgate-radio-card" :class="{ active: settings.mode === 'blacklist' }">
            <input type="radio" v-model="settings.mode" value="blacklist" />
            <div class="wpgate-radio-content">
              <strong>{{ t('overview.blacklistHosts') }}</strong>
              <small>{{ t('http.blacklistMode') }}</small>
            </div>
          </label>
        </div>
      </div>
    </div>

    <!-- Whitelist Section -->
    <div class="wpgate-panel wpgate-mt-4" v-show="settings.mode === 'whitelist'">
      <div class="wpgate-panel-header">
        <h3 class="wpgate-panel-title">{{ t('http.whitelistTitle') }}</h3>
        <span class="wpgate-badge wpgate-badge-info">{{ parsedWhitelistCount }} domains</span>
      </div>
      <div class="wpgate-panel-body">
        <p class="wpgate-field-help">{{ t('http.whitelistHelp') }}</p>

        <!-- Presets quick-add chips -->
        <div class="wpgate-presets-box">
          <div class="wpgate-presets-label">{{ t('http.presetsTitle') }}</div>
          <p class="wpgate-presets-help">{{ t('http.presetsHelp') }}</p>
          <div class="wpgate-preset-chips">
            <button
              v-for="preset in presets"
              :key="preset.key"
              type="button"
              class="wpgate-chip"
              @click="addPreset(preset)"
              :title="`Add ${preset.domains ? preset.domains.length : 0} domains`"
            >
              <span class="wpgate-chip-plus">+</span>
              <span class="wpgate-chip-text">{{ preset.label }}</span>
              <span class="wpgate-chip-count">{{ preset.domains ? preset.domains.length : 0 }}</span>
            </button>
          </div>
        </div>

        <div class="wpgate-form-group wpgate-mt-3">
          <textarea
            v-model="settings.whitelist"
            rows="8"
            class="wpgate-textarea wpgate-code"
            placeholder="api.zarinpal.com&#10;sep.ir&#10;zhaket.com"
          ></textarea>
        </div>
      </div>
    </div>

    <!-- Blacklist Section -->
    <div class="wpgate-panel wpgate-mt-4" v-show="settings.mode === 'blacklist'">
      <div class="wpgate-panel-header">
        <h3 class="wpgate-panel-title">{{ t('http.blacklistTitle') }}</h3>
        <span class="wpgate-badge wpgate-badge-danger">{{ parsedBlacklistCount }} domains</span>
      </div>
      <div class="wpgate-panel-body">
        <p class="wpgate-field-help">{{ t('http.blacklistHelp') }}</p>
        <div class="wpgate-form-group">
          <textarea
            v-model="settings.blacklist"
            rows="6"
            class="wpgate-textarea wpgate-code"
            placeholder="malicious-site.com&#10;unwanted-tracker.net"
          ></textarea>
        </div>
      </div>
    </div>

    <!-- Request Timeout Section -->
    <div class="wpgate-panel wpgate-mt-4">
      <div class="wpgate-panel-header">
        <h3 class="wpgate-panel-title">{{ t('http.timeoutTitle') }}</h3>
      </div>
      <div class="wpgate-panel-body">
        <p class="wpgate-field-help">{{ t('http.timeoutHelp') }}</p>

        <div class="wpgate-toggle-row">
          <label class="wpgate-switch">
            <input type="checkbox" v-model="settings.enable_timeout_guard" true-value="yes" false-value="no" />
            <span class="wpgate-slider"></span>
          </label>
          <span class="wpgate-toggle-label">{{ t('http.enableTimeout') }}</span>
        </div>

        <div v-if="settings.enable_timeout_guard === 'yes'" class="wpgate-form-group wpgate-mt-3 wpgate-inline-range">
          <label class="wpgate-label">{{ t('http.maxTimeout') }} <strong>{{ settings.max_request_timeout }}s</strong></label>
          <input
            type="range"
            v-model.number="settings.max_request_timeout"
            min="1"
            max="30"
            step="1"
            class="wpgate-range"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { t } from '../i18n';

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
    message: `Added ${added} new domains from "${preset.label}".`,
  });
};
</script>
