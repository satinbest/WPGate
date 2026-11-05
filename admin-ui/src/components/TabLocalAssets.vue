<template>
  <div class="wpgate-tab-pane">
    <div class="wpgate-section-header">
      <h2 class="wpgate-section-title">{{ t('assets.title') }}</h2>
      <p class="wpgate-section-desc">{{ t('assets.desc') }}</p>
    </div>

    <!-- Master Switch -->
    <div class="wpgate-panel">
      <div class="wpgate-panel-body">
        <div class="wpgate-toggle-row">
          <label class="wpgate-switch">
            <input type="checkbox" v-model="settings.local_asset_rewrite" true-value="yes" false-value="no" />
            <span class="wpgate-slider"></span>
          </label>
          <div class="wpgate-toggle-info">
            <div class="wpgate-toggle-title">{{ t('assets.enableRewrites') }}</div>
            <div class="wpgate-toggle-desc">{{ t('assets.enableRewritesDesc') }}</div>
          </div>
        </div>
      </div>
    </div>

    <div v-show="settings.local_asset_rewrite === 'yes'" class="wpgate-mt-4">
      <!-- Strict Asset Blocking -->
      <div class="wpgate-panel">
        <div class="wpgate-panel-body">
          <div class="wpgate-toggle-row">
            <label class="wpgate-switch">
              <input type="checkbox" v-model="settings.strict_asset_block" true-value="yes" false-value="no" />
              <span class="wpgate-slider"></span>
            </label>
            <div class="wpgate-toggle-info">
              <div class="wpgate-toggle-title">{{ t('assets.strictAssetBlock') }}</div>
              <div class="wpgate-toggle-desc">{{ t('assets.strictAssetBlockDesc') }}</div>
            </div>
          </div>

          <hr class="wpgate-divider" />

          <!-- Block Mixpanel -->
          <div class="wpgate-toggle-row">
            <label class="wpgate-switch">
              <input type="checkbox" v-model="settings.block_mixpanel" true-value="yes" false-value="no" />
              <span class="wpgate-slider"></span>
            </label>
            <div class="wpgate-toggle-info">
              <div class="wpgate-toggle-title">{{ t('assets.blockMixpanel') }}</div>
              <div class="wpgate-toggle-desc">{{ t('assets.blockMixpanelDesc') }}</div>
            </div>
          </div>

          <hr class="wpgate-divider" />

          <!-- Frontend Vazirmatn -->
          <div class="wpgate-toggle-row">
            <label class="wpgate-switch">
              <input type="checkbox" v-model="settings.enable_front_vazirmatn" true-value="yes" false-value="no" />
              <span class="wpgate-slider"></span>
            </label>
            <div class="wpgate-toggle-info">
              <div class="wpgate-toggle-title">{{ t('assets.enableFrontVazirmatn') }}</div>
              <div class="wpgate-toggle-desc">{{ t('assets.enableFrontVazirmatnDesc') }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Bundled Assets Reference Card -->
      <div class="wpgate-panel wpgate-mt-4">
        <div class="wpgate-panel-header">
          <h3 class="wpgate-panel-title">{{ t('assets.libraries') }}</h3>
        </div>
        <div class="wpgate-panel-body">
          <p class="wpgate-field-help">{{ t('assets.librariesDesc') }}</p>
          <div class="wpgate-lib-grid">
            <div class="wpgate-lib-item">
              <span class="wpgate-lib-icon">&check;</span>
              <div>
                <strong>FontAwesome 6</strong>
                <small>all.min.css &amp; v4-shims</small>
              </div>
            </div>
            <div class="wpgate-lib-item">
              <span class="wpgate-lib-icon">&check;</span>
              <div>
                <strong>Swiper 11</strong>
                <small>swiper-bundle.min.js/css</small>
              </div>
            </div>
            <div class="wpgate-lib-item">
              <span class="wpgate-lib-icon">&check;</span>
              <div>
                <strong>Elementor Icons</strong>
                <small>eicons.min.css &amp; woff2</small>
              </div>
            </div>
            <div class="wpgate-lib-item">
              <span class="wpgate-lib-icon">&check;</span>
              <div>
                <strong>Dashicons</strong>
                <small>dashicons.min.css &amp; fonts</small>
              </div>
            </div>
            <div class="wpgate-lib-item">
              <span class="wpgate-lib-icon">&check;</span>
              <div>
                <strong>Google Fonts</strong>
                <small>Roboto &amp; Vazirmatn</small>
              </div>
            </div>
            <div class="wpgate-lib-item">
              <span class="wpgate-lib-icon">&check;</span>
              <div>
                <strong>WP Core Libraries</strong>
                <small>jQuery, React, Underscore</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { t } from '../i18n';

defineProps({
  settings: { type: Object, required: true },
});
</script>
