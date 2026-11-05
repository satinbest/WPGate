<template>
  <div class="wpgate-tab-pane">
    <div class="wpgate-section-header">
      <h2 class="wpgate-section-title">{{ t('wpControls.title') }}</h2>
      <p class="wpgate-section-desc">{{ t('wpControls.desc') }}</p>
    </div>

    <!-- WordPress Core Controls -->
    <div class="wpgate-panel">
      <div class="wpgate-panel-body">
        <!-- Disable Updates -->
        <div class="wpgate-toggle-row">
          <label class="wpgate-switch">
            <input type="checkbox" v-model="settings.disable_updates" true-value="yes" false-value="no" />
            <span class="wpgate-slider"></span>
          </label>
          <div class="wpgate-toggle-info">
            <div class="wpgate-toggle-title">{{ t('wpControls.disableUpdates') }}</div>
            <div class="wpgate-toggle-desc">{{ t('wpControls.disableUpdatesDesc') }}</div>
          </div>
        </div>

        <hr class="wpgate-divider" />

        <!-- Allow Whitelisted Updates -->
        <div class="wpgate-toggle-row">
          <label class="wpgate-switch">
            <input type="checkbox" v-model="settings.allow_whitelisted_updates" true-value="yes" false-value="no" />
            <span class="wpgate-slider"></span>
          </label>
          <div class="wpgate-toggle-info">
            <div class="wpgate-toggle-title">{{ t('wpControls.allowWhitelistedUpdates') }}</div>
            <div class="wpgate-toggle-desc">{{ t('wpControls.allowWhitelistedUpdatesDesc') }}</div>
          </div>
        </div>

        <hr class="wpgate-divider" />

        <!-- Disable Telemetry -->
        <div class="wpgate-toggle-row">
          <label class="wpgate-switch">
            <input type="checkbox" v-model="settings.disable_telemetry" true-value="yes" false-value="no" />
            <span class="wpgate-slider"></span>
          </label>
          <div class="wpgate-toggle-info">
            <div class="wpgate-toggle-title">{{ t('wpControls.disableTelemetry') }}</div>
            <div class="wpgate-toggle-desc">{{ t('wpControls.disableTelemetryDesc') }}</div>
          </div>
        </div>

        <hr class="wpgate-divider" />

        <!-- Disable Gravatar -->
        <div class="wpgate-toggle-row">
          <label class="wpgate-switch">
            <input type="checkbox" v-model="settings.disable_gravatar" true-value="yes" false-value="no" />
            <span class="wpgate-slider"></span>
          </label>
          <div class="wpgate-toggle-info">
            <div class="wpgate-toggle-title">{{ t('wpControls.disableGravatar') }}</div>
            <div class="wpgate-toggle-desc">{{ t('wpControls.disableGravatarDesc') }}</div>
          </div>
        </div>

        <!-- Custom Gravatar URL (if gravatar disabled) -->
        <div v-show="settings.disable_gravatar === 'yes'" class="wpgate-form-group wpgate-mt-3 wpgate-pl-offset">
          <label class="wpgate-label">{{ t('wpControls.gravatarUrl') }}</label>
          <input
            type="url"
            v-model="settings.gravatar_url"
            class="wpgate-input"
            :placeholder="defaultGravatarUrl"
          />
          <small class="wpgate-field-help">{{ t('wpControls.gravatarUrlHelp') }}</small>
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
});

const defaultGravatarUrl = computed(() => {
  return window.wpgateApiSettings?.root ? `${window.wpgateApiSettings.root}/wpgate/user.jpg` : 'Default local bundled avatar';
});
</script>
