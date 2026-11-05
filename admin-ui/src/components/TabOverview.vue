<template>
  <div class="wpgate-tab-pane">
    <!-- Page Header Intro -->
    <div class="wpgate-page-intro">
      <h2 class="wpgate-page-title">{{ t('overview.title') }}</h2>
      <p class="wpgate-page-subtitle">{{ t('overview.subtitle') }}</p>
    </div>

    <!-- Killswitch alert banner if defined -->
    <div v-if="system.disabled_by_constant" class="wpgate-alert-banner alert-danger">
      <div class="alert-icon">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
      </div>
      <div class="alert-content">
        <strong>{{ t('common.warning') }}:</strong> {{ t('overview.killSwitchWarning') }}
      </div>
    </div>

    <!-- Informational banner when logging is off -->
    <div v-if="settings.enable_logging !== 'yes'" class="wpgate-alert-banner alert-info">
      <div class="alert-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
      </div>
      <div class="alert-content">
        <strong>{{ t('overview.loggingNoticeTitle') }}:</strong> {{ t('overview.loggingNoticeDesc') }}
      </div>
      <button type="button" class="wpgate-btn-link alert-action" @click="$emit('switch-tab', 'logs')">
        {{ t('overview.enableLoggingCta') }} &rarr;
      </button>
    </div>

    <!-- Metrics Cards Grid -->
    <div class="wpgate-metrics-grid">
      <!-- 1. Active Firewall Mode -->
      <StatCard
        :label="t('overview.activeMode')"
        :value="modeDisplayText"
        :type="modeBadgeType"
        :badge="modeBadgeText"
        :hint="modeHintText"
      >
        <template #icon>
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
        </template>
        <template #footer>
          <button type="button" class="wpgate-card-link-btn" @click="$emit('switch-tab', 'httpControl')">
            {{ t('http.modeLabel') }} &rarr;
          </button>
        </template>
      </StatCard>

      <!-- 2. Evaluated Requests -->
      <StatCard
        :label="t('overview.totalRequests')"
        :value="stats.total_logs || 0"
        type="info"
        :inactive="settings.enable_logging !== 'yes'"
        :inactive-text="t('common.inactive')"
        :action-text="t('overview.enableLoggingCta')"
        @action="$emit('switch-tab', 'logs')"
      >
        <template #icon>
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg>
        </template>
        <template #footer>
          <span class="wpgate-footer-meta">
            {{ settings.enable_logging === 'yes' ? `${stats.allowed_count || 0} ${t('overview.allowedCount')}` : t('logs.title') }}
          </span>
        </template>
      </StatCard>

      <!-- 3. Blocked Requests -->
      <StatCard
        :label="t('overview.blockedCount')"
        :value="stats.blocked_count || 0"
        type="danger"
        :inactive="settings.enable_logging !== 'yes'"
        :inactive-text="t('common.inactive')"
      >
        <template #icon>
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>
        </template>
        <template #footer>
          <span class="wpgate-footer-meta">
            {{ t('overview.blacklistHosts') }}: {{ stats.blacklist_count || 0 }}
          </span>
        </template>
      </StatCard>

      <!-- 4. Local Asset Rewrites -->
      <StatCard
        :label="t('overview.rewrittenCount')"
        :value="stats.rewritten_count || 0"
        type="success"
        :inactive="settings.local_asset_rewrite !== 'yes'"
        :inactive-text="t('common.disabled')"
      >
        <template #icon>
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
        </template>
        <template #footer>
          <button type="button" class="wpgate-card-link-btn" @click="$emit('switch-tab', 'localAssets')">
            {{ t('tabs.localAssets') }} &rarr;
          </button>
        </template>
      </StatCard>
    </div>

    <!-- Active Protection Matrix Section -->
    <SettingsPanel
      :title="t('overview.protectionMatrixTitle')"
      :subtitle="t('overview.systemHealthGood')"
      badge="Active Shield"
      badge-type="success"
      class="wpgate-mt-4"
    >
      <div class="wpgate-matrix-grid">
        <!-- 1. Outbound Interceptor -->
        <div class="matrix-card" :class="{ 'is-active': settings.mode !== 'disabled' }">
          <div class="matrix-card-top">
            <span class="matrix-indicator"></span>
            <span class="matrix-title">{{ t('overview.outboundFirewall') }}</span>
          </div>
          <p class="matrix-desc">{{ modeValueDetail }}</p>
          <div class="matrix-footer">
            <StatusBadge
              :type="settings.mode !== 'disabled' ? 'success' : 'neutral'"
              :text="settings.mode !== 'disabled' ? t('common.active') : t('common.disabled')"
            />
          </div>
        </div>

        <!-- 2. CDN Asset Rewriter -->
        <div class="matrix-card" :class="{ 'is-active': settings.local_asset_rewrite === 'yes' }">
          <div class="matrix-card-top">
            <span class="matrix-indicator"></span>
            <span class="matrix-title">{{ t('overview.localAssetRewrites') }}</span>
          </div>
          <p class="matrix-desc">{{ t('assets.enableRewritesDesc') }}</p>
          <div class="matrix-footer">
            <StatusBadge
              :type="settings.local_asset_rewrite === 'yes' ? 'success' : 'neutral'"
              :text="settings.local_asset_rewrite === 'yes' ? t('common.active') : t('common.disabled')"
            />
          </div>
        </div>

        <!-- 3. Timeout Guard -->
        <div class="matrix-card" :class="{ 'is-active': settings.enable_timeout_guard === 'yes' }">
          <div class="matrix-card-top">
            <span class="matrix-indicator"></span>
            <span class="matrix-title">{{ t('overview.timeoutGuard') }}</span>
          </div>
          <p class="matrix-desc">
            {{ settings.enable_timeout_guard === 'yes' ? `${t('http.maxTimeout')}: ${settings.max_request_timeout || 3}s` : t('common.disabled') }}
          </p>
          <div class="matrix-footer">
            <StatusBadge
              :type="settings.enable_timeout_guard === 'yes' ? 'success' : 'neutral'"
              :text="settings.enable_timeout_guard === 'yes' ? `${settings.max_request_timeout || 3}s` : t('common.disabled')"
            />
          </div>
        </div>

        <!-- 4. Telemetry Guard -->
        <div class="matrix-card" :class="{ 'is-active': settings.disable_telemetry === 'yes' }">
          <div class="matrix-card-top">
            <span class="matrix-indicator"></span>
            <span class="matrix-title">{{ t('overview.telemetryGuard') }}</span>
          </div>
          <p class="matrix-desc">{{ t('wpControls.disableTelemetryDesc') }}</p>
          <div class="matrix-footer">
            <StatusBadge
              :type="settings.disable_telemetry === 'yes' ? 'success' : 'neutral'"
              :text="settings.disable_telemetry === 'yes' ? t('common.active') : t('common.disabled')"
            />
          </div>
        </div>

        <!-- 5. Domestic Updates -->
        <div class="matrix-card" :class="{ 'is-active': settings.allow_whitelisted_updates === 'yes' }">
          <div class="matrix-card-top">
            <span class="matrix-indicator"></span>
            <span class="matrix-title">{{ t('overview.updateGuard') }}</span>
          </div>
          <p class="matrix-desc">{{ t('wpControls.allowWhitelistedUpdatesDesc') }}</p>
          <div class="matrix-footer">
            <StatusBadge
              :type="settings.allow_whitelisted_updates === 'yes' ? 'success' : 'neutral'"
              :text="settings.allow_whitelisted_updates === 'yes' ? t('common.active') : t('common.disabled')"
            />
          </div>
        </div>

        <!-- 6. Gravatar Local Masking -->
        <div class="matrix-card" :class="{ 'is-active': settings.disable_gravatar === 'yes' }">
          <div class="matrix-card-top">
            <span class="matrix-indicator"></span>
            <span class="matrix-title">{{ t('overview.gravatarGuard') }}</span>
          </div>
          <p class="matrix-desc">{{ t('wpControls.disableGravatarDesc') }}</p>
          <div class="matrix-footer">
            <StatusBadge
              :type="settings.disable_gravatar === 'yes' ? 'success' : 'neutral'"
              :text="settings.disable_gravatar === 'yes' ? t('common.active') : t('common.disabled')"
            />
          </div>
        </div>
      </div>
    </SettingsPanel>

    <!-- Architecture & Quick Actions 2-Col Grid -->
    <div class="wpgate-grid-2col wpgate-mt-4">
      <!-- Deployment Architecture -->
      <SettingsPanel
        :title="t('overview.deploymentType')"
        :subtitle="t('overview.deploymentDesc')"
      >
        <div class="deployment-box">
          <div class="deployment-badge-row">
            <span class="deployment-tag">
              {{ system.loading_method === 'theme' ? 'Child Theme Mode' : 'Must-Use Plugin (MU)' }}
            </span>
            <StatusBadge type="success" text="Operational" />
          </div>
          <p class="deployment-notes">
            {{ system.loading_method === 'theme'
              ? 'WPGate is bootstrapped via active child theme functions.php. For earlier lifecycle filtering, consider deploying into wp-content/mu-plugins/.'
              : 'WPGate is running from wp-content/mu-plugins/, guaranteeing early interception before standard plugins load.' }}
          </p>
          <div class="wpgate-quick-info-list">
            <div class="quick-info-row">
              <span class="quick-info-key">PHP Engine:</span>
              <span class="quick-info-val">{{ system.php_version || '7.4+' }}</span>
            </div>
            <div class="quick-info-row">
              <span class="quick-info-key">WordPress Core:</span>
              <span class="quick-info-val">{{ system.wp_version || '5.0+' }}</span>
            </div>
            <div class="quick-info-row">
              <span class="quick-info-key">Disk Writes:</span>
              <span class="quick-info-val text-success"><strong>0 Bytes (Pure RAM)</strong></span>
            </div>
          </div>
        </div>
      </SettingsPanel>

      <!-- Quick Actions -->
      <SettingsPanel :title="t('common.quickActions')">
        <div class="quick-actions-list">
          <button type="button" class="quick-action-item" @click="$emit('switch-tab', 'httpControl')">
            <div class="action-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1 4-10z"></path></svg>
            </div>
            <div class="action-text-col">
              <strong>{{ t('overview.viewRulesCta') }}</strong>
              <small>Manage allowlist, blocklist, and domestic presets.</small>
            </div>
            <span class="action-arrow">&rarr;</span>
          </button>

          <button type="button" class="quick-action-item" @click="$emit('switch-tab', 'localAssets')">
            <div class="action-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
            </div>
            <div class="action-text-col">
              <strong>{{ t('overview.manageAssetsCta') }}</strong>
              <small>Configure local CDN fallbacks and font injection.</small>
            </div>
            <span class="action-arrow">&rarr;</span>
          </button>

          <button type="button" class="quick-action-item" @click="$emit('switch-tab', 'logs')">
            <div class="action-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
            </div>
            <div class="action-text-col">
              <strong>{{ t('overview.viewLogsCta') }}</strong>
              <small>Inspect recent intercepted HTTP calls and rewrites.</small>
            </div>
            <span class="action-arrow">&rarr;</span>
          </button>
        </div>
      </SettingsPanel>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { t } from '../i18n';
import StatCard from './StatCard.vue';
import SettingsPanel from './SettingsPanel.vue';
import StatusBadge from './StatusBadge.vue';

const props = defineProps({
  settings: { type: Object, required: true },
  system: { type: Object, default: () => ({}) },
  stats: { type: Object, default: () => ({}) },
});

defineEmits(['switch-tab']);

const modeBadgeType = computed(() => {
  if (props.settings.mode === 'whitelist') return 'info';
  if (props.settings.mode === 'blacklist') return 'danger';
  return 'neutral';
});

const modeBadgeText = computed(() => {
  if (props.settings.mode === 'whitelist') return 'Strict';
  if (props.settings.mode === 'blacklist') return 'Filtering';
  return 'Disabled';
});

const modeDisplayText = computed(() => {
  if (props.settings.mode === 'whitelist') return t('overview.modeWhitelist');
  if (props.settings.mode === 'blacklist') return t('overview.modeBlacklist');
  return t('overview.modeDisabled');
});

const modeHintText = computed(() => {
  if (props.settings.mode === 'whitelist') return `${props.stats.whitelist_count || 0} ${t('overview.whitelistHosts')}`;
  if (props.settings.mode === 'blacklist') return `${props.stats.blacklist_count || 0} ${t('overview.blacklistHosts')}`;
  return 'Pass-through mode';
});

const modeValueDetail = computed(() => {
  if (props.settings.mode === 'whitelist') return t('http.modeWhitelistDesc');
  if (props.settings.mode === 'blacklist') return t('http.modeBlacklistDesc');
  return t('http.modeDisabledDesc');
});
</script>

<style scoped>
.wpgate-page-intro {
  margin-bottom: 24px;
}

.wpgate-page-title {
  margin: 0 0 6px 0;
  font-size: 1.45rem;
  font-weight: 700;
  color: var(--wpgate-text);
  letter-spacing: -0.01em;
}

.wpgate-page-subtitle {
  margin: 0;
  font-size: 0.92rem;
  color: var(--wpgate-text-muted);
  line-height: 1.5;
}

/* Alert Banners */
.wpgate-alert-banner {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 18px;
  border-radius: var(--wpgate-radius);
  margin-bottom: 20px;
  font-size: 0.88rem;
  line-height: 1.45;
  border: 1px solid transparent;
}

.alert-danger {
  background-color: rgba(220, 38, 38, 0.08);
  border-color: rgba(220, 38, 38, 0.25);
  color: #991b1b;
}

.alert-info {
  background-color: rgba(37, 99, 235, 0.08);
  border-color: rgba(37, 99, 235, 0.22);
  color: #1e40af;
}

[data-theme="dark"] .alert-danger {
  background-color: rgba(239, 68, 68, 0.15);
  border-color: rgba(239, 68, 68, 0.35);
  color: #fca5a5;
}

[data-theme="dark"] .alert-info {
  background-color: rgba(59, 130, 246, 0.15);
  border-color: rgba(59, 130, 246, 0.35);
  color: #93c5fd;
}

.alert-icon {
  flex-shrink: 0;
}

.alert-content {
  flex-grow: 1;
}

.alert-action {
  white-space: nowrap;
  font-weight: 600;
  color: inherit;
  background: none;
  border: none;
  cursor: pointer;
  text-decoration: underline;
}

/* Metrics Grid */
.wpgate-metrics-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-bottom: 20px;
}

@media (max-width: 1200px) {
  .wpgate-metrics-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 640px) {
  .wpgate-metrics-grid {
    grid-template-columns: 1fr;
  }
}

.wpgate-card-link-btn {
  background: none;
  border: none;
  padding: 0;
  color: var(--wpgate-primary);
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
}

.wpgate-card-link-btn:hover {
  text-decoration: underline;
}

.wpgate-footer-meta {
  font-size: 0.78rem;
  color: var(--wpgate-text-muted);
}

/* Matrix Grid */
.wpgate-matrix-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
}

@media (max-width: 900px) {
  .wpgate-matrix-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 600px) {
  .wpgate-matrix-grid {
    grid-template-columns: 1fr;
  }
}

.matrix-card {
  background-color: var(--wpgate-surface-alt);
  border: 1px solid var(--wpgate-border);
  border-radius: var(--wpgate-radius-sm);
  padding: 16px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: 10px;
  transition: all 0.15s ease;
}

.matrix-card.is-active {
  border-color: rgba(37, 99, 235, 0.25);
  background-color: rgba(37, 99, 235, 0.03);
}

[data-theme="dark"] .matrix-card.is-active {
  background-color: rgba(59, 130, 246, 0.06);
}

.matrix-card-top {
  display: flex;
  align-items: center;
  gap: 8px;
}

.matrix-indicator {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background-color: #94a3b8;
}

.matrix-card.is-active .matrix-indicator {
  background-color: #10b981;
  box-shadow: 0 0 6px rgba(16, 185, 129, 0.5);
}

.matrix-title {
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--wpgate-text);
}

.matrix-desc {
  margin: 0;
  font-size: 0.8rem;
  color: var(--wpgate-text-muted);
  line-height: 1.4;
  flex-grow: 1;
}

.matrix-footer {
  padding-top: 6px;
}

/* Deployment & Quick Actions */
.deployment-box {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.deployment-badge-row {
  display: flex;
  align-items: center;
  gap: 10px;
}

.deployment-tag {
  font-size: 0.82rem;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: var(--wpgate-radius-sm);
  background-color: var(--wpgate-surface-alt);
  color: var(--wpgate-primary);
  border: 1px solid var(--wpgate-border);
}

.deployment-notes {
  margin: 0;
  font-size: 0.85rem;
  color: var(--wpgate-text-muted);
  line-height: 1.5;
}

.wpgate-quick-info-list {
  display: flex;
  flex-direction: column;
  gap: 6px;
  margin-top: 6px;
  padding-top: 10px;
  border-top: 1px dashed var(--wpgate-border);
}

.quick-info-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 0.82rem;
}

.quick-info-key {
  color: var(--wpgate-text-muted);
}

.quick-info-val {
  color: var(--wpgate-text);
  font-family: var(--wpgate-font-mono);
}

.quick-actions-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.quick-action-item {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 12px 14px;
  background-color: var(--wpgate-surface-alt);
  border: 1px solid var(--wpgate-border);
  border-radius: var(--wpgate-radius-sm);
  color: var(--wpgate-text);
  cursor: pointer;
  text-align: start;
  transition: all 0.15s ease;
}

.quick-action-item:hover {
  border-color: var(--wpgate-primary);
  background-color: rgba(37, 99, 235, 0.04);
}

.action-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background-color: var(--wpgate-surface);
  color: var(--wpgate-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  border: 1px solid var(--wpgate-border);
}

.action-text-col {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.action-text-col strong {
  font-size: 0.88rem;
  font-weight: 600;
}

.action-text-col small {
  font-size: 0.78rem;
  color: var(--wpgate-text-muted);
}

.action-arrow {
  color: var(--wpgate-text-muted);
  font-size: 1rem;
}

[dir="rtl"] .action-arrow {
  transform: rotate(180deg);
}
</style>
