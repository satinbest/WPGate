<template>
  <div class="wpgate-tab-pane">
    <!-- Brand Hero Banner -->
    <div class="wpgate-panel wpgate-hero-panel">
      <div class="wpgate-about-hero">
        <div class="wpgate-about-logo">
          <svg width="68" height="68" viewBox="0 0 100 100" fill="none">
            <rect width="100" height="100" rx="22" fill="#0f172a" />
            <path d="M50 15L80 32.5V67.5L50 85L20 67.5V32.5L50 15Z" stroke="#38bdf8" stroke-width="5" fill="none"/>
            <path d="M50 28L70 40V60L50 72L30 60V40L50 28Z" fill="#1e293b" stroke="#0ea5e9" stroke-width="3"/>
            <line x1="50" y1="28" x2="50" y2="72" stroke="#38bdf8" stroke-width="3" stroke-dasharray="3 3"/>
            <circle cx="50" cy="50" r="7" fill="#38bdf8"/>
          </svg>
        </div>
        <div class="wpgate-about-info">
          <div class="wpgate-about-title-row">
            <h1 class="wpgate-about-name">WPGate</h1>
            <span class="wpgate-version-pill">v0.1.4</span>
            <StatusBadge status="success">{{ t('common.productionReady') }}</StatusBadge>
          </div>
          <p class="wpgate-about-summary">{{ t('about.summary') }}</p>
        </div>
      </div>
    </div>

    <!-- Developer & Maintainer Card -->
    <SettingsPanel :title="t('about.developerCardTitle')" class="wpgate-mt-4">
      <div class="wpgate-author-card">
        <div class="wpgate-author-avatar">
          <span>HM</span>
        </div>
        <div class="wpgate-author-details">
          <h4 class="wpgate-author-name">Hossein Mohammadpour</h4>
          <p class="wpgate-author-role">{{ t('about.developerLead') }}</p>
          <div class="wpgate-author-links">
            <a href="mailto:info@hosseinmohammadpour.ir" class="wpgate-author-link" target="_blank" rel="noopener">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
              <span>info@hosseinmohammadpour.ir</span>
            </a>
            <a href="https://hosseinmohammadpour.ir" class="wpgate-author-link" target="_blank" rel="noopener">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
              <span>hosseinmohammadpour.ir</span>
            </a>
            <a href="https://github.com/satinbest/WPGate" class="wpgate-author-link" target="_blank" rel="noopener">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path></svg>
              <span>github.com/satinbest/WPGate</span>
            </a>
          </div>
        </div>
      </div>
    </SettingsPanel>

    <!-- Architecture & Legal Grid -->
    <div class="wpgate-grid-2col wpgate-mt-4">
      <SettingsPanel :title="t('about.highlightsTitle')">
        <ul class="wpgate-feature-list">
          <li><strong>{{ t('about.hlZeroPlugin') }}</strong> {{ t('about.hlZeroPluginDesc') }}</li>
          <li><strong>{{ t('about.hlZeroDisk') }}</strong> {{ t('about.hlZeroDiskDesc') }}</li>
          <li><strong>{{ t('about.hlPrivacy') }}</strong> {{ t('about.hlPrivacyDesc') }}</li>
          <li><strong>{{ t('about.hlKillSwitch') }}</strong> {{ t('about.hlKillSwitchDesc') }}</li>
        </ul>
      </SettingsPanel>

      <SettingsPanel :title="t('about.licenseCardTitle')">
        <p class="wpgate-field-help">{{ t('about.licenseDesc') }}</p>
      </SettingsPanel>
    </div>
  </div>
</template>

<script setup>
import { t } from '../i18n';
import SettingsPanel from './SettingsPanel.vue';
import StatusBadge from './StatusBadge.vue';
</script>
