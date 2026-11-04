<template>
  <div class="wpgate-tab-pane">
    <div class="wpgate-section-header">
      <h2 class="wpgate-section-title">{{ t('overview.title') }}</h2>
      <p class="wpgate-section-desc">{{ t('tagline') }}</p>
    </div>

    <!-- Killswitch alert banner if defined -->
    <div v-if="system.disabled_by_constant" class="wpgate-alert wpgate-alert-warning">
      <div class="wpgate-alert-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
      </div>
      <div class="wpgate-alert-content">
        <strong>{{ t('common.warning') }}:</strong> {{ t('overview.killSwitchWarning') }}
      </div>
    </div>

    <!-- Metrics Cards Grid -->
    <div class="wpgate-cards-grid">
      <!-- Active Mode Card -->
      <div class="wpgate-card">
        <div class="wpgate-card-header">
          <span class="wpgate-card-badge" :class="modeBadgeClass">
            {{ modeBadgeText }}
          </span>
          <span class="wpgate-card-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
          </span>
        </div>
        <div class="wpgate-card-body">
          <div class="wpgate-card-label">{{ t('overview.activeMode') }}</div>
          <div class="wpgate-card-value">{{ modeValueText }}</div>
        </div>
        <div class="wpgate-card-footer">
          <button type="button" class="wpgate-btn-link" @click="$emit('switch-tab', 'httpControl')">
            {{ t('http.modeLabel') }} &rarr;
          </button>
        </div>
      </div>

      <!-- Outbound Requests Filtered -->
      <div class="wpgate-card">
        <div class="wpgate-card-header">
          <span class="wpgate-card-badge wpgate-badge-info">{{ stats.total_logs || 0 }}</span>
          <span class="wpgate-card-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg>
          </span>
        </div>
        <div class="wpgate-card-body">
          <div class="wpgate-card-label">{{ t('overview.totalRequests') }}</div>
          <div class="wpgate-card-value">{{ stats.total_logs || 0 }}</div>
        </div>
        <div class="wpgate-card-footer">
          <button type="button" class="wpgate-btn-link" @click="$emit('switch-tab', 'logs')">
            {{ t('tabs.logs') }} &rarr;
          </button>
        </div>
      </div>

      <!-- Blocked Requests -->
      <div class="wpgate-card">
        <div class="wpgate-card-header">
          <span class="wpgate-card-badge wpgate-badge-danger">{{ stats.blocked_count || 0 }}</span>
          <span class="wpgate-card-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>
          </span>
        </div>
        <div class="wpgate-card-body">
          <div class="wpgate-card-label">{{ t('overview.blockedCount') }}</div>
          <div class="wpgate-card-value text-danger">{{ stats.blocked_count || 0 }}</div>
        </div>
        <div class="wpgate-card-footer">
          <span class="wpgate-card-meta">{{ t('overview.blacklistHosts') }}: {{ stats.blacklist_count || 0 }}</span>
        </div>
      </div>

      <!-- Local Asset Rewrites -->
      <div class="wpgate-card">
        <div class="wpgate-card-header">
          <span class="wpgate-card-badge wpgate-badge-success">{{ stats.rewritten_count || 0 }}</span>
          <span class="wpgate-card-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
          </span>
        </div>
        <div class="wpgate-card-body">
          <div class="wpgate-card-label">{{ t('overview.rewrittenCount') }}</div>
          <div class="wpgate-card-value text-success">{{ stats.rewritten_count || 0 }}</div>
        </div>
        <div class="wpgate-card-footer">
          <button type="button" class="wpgate-btn-link" @click="$emit('switch-tab', 'localAssets')">
            {{ t('tabs.localAssets') }} &rarr;
          </button>
        </div>
      </div>
    </div>

    <!-- Quick Info Banner -->
    <div class="wpgate-panel wpgate-mt-4">
      <div class="wpgate-panel-header">
        <h3 class="wpgate-panel-title">{{ t('overview.deploymentType') }}</h3>
      </div>
      <div class="wpgate-panel-body">
        <div class="wpgate-deployment-row">
          <div class="wpgate-deployment-tag" :class="system.loading_method === 'mu-plugin' ? 'tag-primary' : 'tag-secondary'">
            {{ system.loading_method === 'mu-plugin' ? 'Must-Use Plugin (wp-content/mu-plugins)' : 'Theme Loader (wp-content/themes)' }}
          </div>
          <div class="wpgate-deployment-desc">
            {{ system.loading_method === 'mu-plugin'
                ? 'Running as a WordPress Must-Use plugin. Starts early during muplugins_loaded, protecting network requests before regular plugins execute.'
                : 'Running as a theme-level module loaded via functions.php.' }}
          </div>
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
  system: { type: Object, required: true },
  stats: { type: Object, required: true },
});

defineEmits(['switch-tab']);

const modeBadgeClass = computed(() => {
  if (props.system.disabled_by_constant) return 'wpgate-badge-warning';
  const mode = props.settings.mode || 'disabled';
  if (mode === 'whitelist') return 'wpgate-badge-success';
  if (mode === 'blacklist') return 'wpgate-badge-info';
  return 'wpgate-badge-neutral';
});

const modeBadgeText = computed(() => {
  if (props.system.disabled_by_constant) return 'Bypassed (Constant)';
  const mode = props.settings.mode || 'disabled';
  if (mode === 'whitelist') return 'Whitelist Active';
  if (mode === 'blacklist') return 'Blacklist Active';
  return 'Disabled';
});

const modeValueText = computed(() => {
  if (props.system.disabled_by_constant) return 'Kill-Switch Active';
  const mode = props.settings.mode || 'disabled';
  if (mode === 'whitelist') return t('overview.modeWhitelist');
  if (mode === 'blacklist') return t('overview.modeBlacklist');
  return t('overview.modeDisabled');
});
</script>
