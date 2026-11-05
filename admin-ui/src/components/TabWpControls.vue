<template>
  <div class="wpgate-tab-pane">
    <div class="wpgate-section-header">
      <div>
        <h2 class="wpgate-section-title">{{ t('wpControls.title') }}</h2>
        <p class="wpgate-section-desc">{{ t('wpControls.desc') }}</p>
      </div>
    </div>

    <!-- Update Policy Management -->
    <SettingsPanel :title="t('wpControls.updatesManagementTitle')" :badge="t('common.coreProtection')" badge-status="primary">
      <!-- Disable Updates -->
      <SettingToggle
        v-model="settings.disable_updates"
        :title="t('wpControls.disableUpdates')"
        :description="t('wpControls.disableUpdatesDesc')"
        :warning="t('wpControls.disableUpdatesWarning')"
      />

      <hr class="wpgate-divider" />

      <!-- Allow Whitelisted Updates -->
      <SettingToggle
        v-model="settings.allow_whitelisted_updates"
        :title="t('wpControls.allowWhitelistedUpdates')"
        :description="t('wpControls.allowWhitelistedUpdatesDesc')"
      />
    </SettingsPanel>

    <!-- Telemetry & Network Pingbacks -->
    <SettingsPanel :title="t('wpControls.telemetryTrackingTitle')" class="wpgate-mt-4">
      <SettingToggle
        v-model="settings.disable_telemetry"
        :title="t('wpControls.disableTelemetry')"
        :description="t('wpControls.disableTelemetryDesc')"
      />
    </SettingsPanel>

    <!-- Gravatar Management -->
    <SettingsPanel :title="t('wpControls.avatarPrivacyTitle')" class="wpgate-mt-4">
      <SettingToggle
        v-model="settings.disable_gravatar"
        :title="t('wpControls.disableGravatar')"
        :description="t('wpControls.disableGravatarDesc')"
      >
        <template #extra v-if="settings.disable_gravatar === 'yes'">
          <div class="wpgate-avatar-config-box">
            <div class="wpgate-form-group">
              <label class="wpgate-label">{{ t('wpControls.gravatarUrl') }}</label>
              <input
                type="url"
                v-model="settings.gravatar_url"
                dir="ltr"
                class="wpgate-input"
                :placeholder="t('wpControls.defaultGravatarUrl')"
              />
              <small class="wpgate-field-help">{{ t('wpControls.gravatarUrlHelp') }}</small>
            </div>
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

const props = defineProps({
  settings: { type: Object, required: true },
});

const defaultGravatarUrl = computed(() => {
  return window.wpgateApiSettings?.root ? `${window.wpgateApiSettings.root}/wpgate/user.jpg` : 'Default local bundled avatar';
});
</script>
