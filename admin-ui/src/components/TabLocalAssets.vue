<template>
  <div class="wpgate-tab-pane">
    <div class="wpgate-section-header">
      <div>
        <h2 class="wpgate-section-title">{{ t('assets.title') }}</h2>
        <p class="wpgate-section-desc">{{ t('assets.desc') }}</p>
      </div>
      <StatusBadge :status="settings.local_asset_rewrite === 'yes' ? 'success' : 'neutral'">
        {{ settings.local_asset_rewrite === 'yes' ? t('common.enabled') : t('common.disabled') }}
      </StatusBadge>
    </div>

    <!-- Master Switch Panel -->
    <SettingsPanel :title="t('assets.enableRewrites')">
      <SettingToggle
        v-model="settings.local_asset_rewrite"
        :title="t('assets.enableRewrites')"
        :description="t('assets.enableRewritesDesc')"
      />
    </SettingsPanel>

    <!-- Sub Settings (Shown when master switch is enabled) -->
    <div v-show="settings.local_asset_rewrite === 'yes'" class="wpgate-mt-4">
      <!-- Security & Blocking Policies -->
      <SettingsPanel :title="t('assets.securityPolicyTitle')" class="wpgate-mt-4">
        <!-- Strict Asset Blocking -->
        <SettingToggle
          v-model="settings.strict_asset_block"
          :title="t('assets.strictAssetBlock')"
          :description="t('assets.strictAssetBlockDesc')"
          :warning="t('assets.strictAssetBlockWarning')"
        />

        <hr class="wpgate-divider" />

        <!-- Block Mixpanel Telemetry -->
        <SettingToggle
          v-model="settings.block_mixpanel"
          :title="t('assets.blockMixpanel')"
          :description="t('assets.blockMixpanelDesc')"
        />
      </SettingsPanel>

      <!-- Typography & Font Management -->
      <SettingsPanel :title="t('assets.typographyTitle')" class="wpgate-mt-4">
        <!-- Admin UI Font (Informative / Built-in) -->
        <div class="wpgate-font-info-box">
          <div class="wpgate-font-info-header">
            <div class="wpgate-flex-center wpgate-gap-2">
              <span class="wpgate-font-icon">Aa</span>
              <div>
                <strong>{{ t('assets.dashboardFontTitle') }}</strong>
                <p class="wpgate-text-muted wpgate-text-xs">
                  Locally bundled and inlined into admin assets. Operates 100% offline with zero CDN dependencies and zero font 404s.
                </p>
              </div>
            </div>
            <StatusBadge status="success">Active (Local WOFF2)</StatusBadge>
          </div>
        </div>

        <hr class="wpgate-divider" />

        <!-- Frontend Public Website Vazirmatn -->
        <SettingToggle
          v-model="settings.enable_front_vazirmatn"
          :title="t('assets.enableFrontVazirmatn')"
          :description="t('assets.enableFrontVazirmatnDesc')"
        />
      </SettingsPanel>

      <!-- Bundled Assets Reference Grid -->
      <SettingsPanel
        class="wpgate-mt-4"
        :title="t('assets.libraries')"
        :description="t('assets.librariesDesc')"
      >
        <template #badge>
          <StatusBadge status="info">6 Libraries Bundled</StatusBadge>
        </template>

        <div class="wpgate-lib-grid">
          <div class="wpgate-lib-card">
            <div class="wpgate-lib-card-top">
              <span class="wpgate-lib-status-dot"></span>
              <strong class="wpgate-lib-name">FontAwesome 6 Free</strong>
            </div>
            <span class="wpgate-lib-files">all.min.css &amp; v4-shims.min.css</span>
            <div class="wpgate-lib-card-bottom">
              <span class="wpgate-badge wpgate-badge-neutral">Local CSS &amp; Webfonts</span>
            </div>
          </div>

          <div class="wpgate-lib-card">
            <div class="wpgate-lib-card-top">
              <span class="wpgate-lib-status-dot"></span>
              <strong class="wpgate-lib-name">Swiper 11</strong>
            </div>
            <span class="wpgate-lib-files">swiper-bundle.min.js &amp; .css</span>
            <div class="wpgate-lib-card-bottom">
              <span class="wpgate-badge wpgate-badge-neutral">Touch Slider Engine</span>
            </div>
          </div>

          <div class="wpgate-lib-card">
            <div class="wpgate-lib-card-top">
              <span class="wpgate-lib-status-dot"></span>
              <strong class="wpgate-lib-name">Elementor Icons</strong>
            </div>
            <span class="wpgate-lib-files">eicons.min.css &amp; eicons.woff2</span>
            <div class="wpgate-lib-card-bottom">
              <span class="wpgate-badge wpgate-badge-neutral">Page Builder Iconset</span>
            </div>
          </div>

          <div class="wpgate-lib-card">
            <div class="wpgate-lib-card-top">
              <span class="wpgate-lib-status-dot"></span>
              <strong class="wpgate-lib-name">Dashicons</strong>
            </div>
            <span class="wpgate-lib-files">dashicons.min.css &amp; fonts</span>
            <div class="wpgate-lib-card-bottom">
              <span class="wpgate-badge wpgate-badge-neutral">WP Core Icon Font</span>
            </div>
          </div>

          <div class="wpgate-lib-card">
            <div class="wpgate-lib-card-top">
              <span class="wpgate-lib-status-dot"></span>
              <strong class="wpgate-lib-name">Google Fonts Replacement</strong>
            </div>
            <span class="wpgate-lib-files">Roboto, Vazirmatn local mappings</span>
            <div class="wpgate-lib-card-bottom">
              <span class="wpgate-badge wpgate-badge-neutral">Local Font Stacks</span>
            </div>
          </div>

          <div class="wpgate-lib-card">
            <div class="wpgate-lib-card-top">
              <span class="wpgate-lib-status-dot"></span>
              <strong class="wpgate-lib-name">WordPress Core JavaScript</strong>
            </div>
            <span class="wpgate-lib-files">jQuery, React, Underscore, Lodash</span>
            <div class="wpgate-lib-card-bottom">
              <span class="wpgate-badge wpgate-badge-neutral">Direct WP Script Handles</span>
            </div>
          </div>
        </div>
      </SettingsPanel>
    </div>
  </div>
</template>

<script setup>
import { t } from '../i18n';
import SettingsPanel from './SettingsPanel.vue';
import SettingToggle from './SettingToggle.vue';
import StatusBadge from './StatusBadge.vue';

defineProps({
  settings: { type: Object, required: true },
});
</script>
