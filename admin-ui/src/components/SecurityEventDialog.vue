<template>
  <div
    v-if="visible"
    class="wpgate-modal-overlay"
    @click.self="onClose"
    @keydown.esc="onClose"
    role="dialog"
    aria-modal="true"
    aria-labelledby="security-event-dialog-title"
  >
    <div class="security-dialog-box" ref="dialogRef">
      <!-- Dialog Header -->
      <header class="security-dialog-header">
        <div class="dialog-header-start">
          <div class="dialog-header-icon" :class="`icon-${severityType}`">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
              <line v-if="severityType === 'danger'" x1="12" y1="8" x2="12" y2="12"></line>
              <line v-if="severityType === 'danger'" x1="12" y1="16" x2="12.01" y2="16"></line>
              <polyline v-else-if="severityType === 'success'" points="9 12 11 14 15 10"></polyline>
              <line v-else x1="12" y1="9" x2="12" y2="13"></line>
            </svg>
          </div>
          <div class="dialog-header-title-wrap">
            <div class="dialog-title-row">
              <h3 id="security-event-dialog-title" class="dialog-title">
                {{ t('logs.detailsTitle') }}
              </h3>
              <span class="severity-badge" :class="`severity-${severityType}`">
                {{ severityLabel }}
              </span>
            </div>
            <span class="dialog-subtitle">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="dialog-clock-icon"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
              <span>{{ event?.time || '-' }}</span>
            </span>
          </div>
        </div>

        <button
          type="button"
          class="dialog-close-btn"
          @click="onClose"
          :title="t('common.close')"
          :aria-label="t('common.close')"
        >
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
      </header>

      <!-- Dialog Body (Scrollable) -->
      <div class="security-dialog-body">
        <!-- Section 1: Event Summary -->
        <section class="dialog-section">
          <h4 class="section-title">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
            <span>{{ t('logs.sectionSummary') }}</span>
          </h4>
          <div class="grid-key-val">
            <div class="kv-item">
              <span class="kv-key">{{ t('logs.eventType') }}:</span>
              <span class="kv-val font-semibold">{{ t('logs.eventTypeHttp') }}</span>
            </div>
            <div class="kv-item">
              <span class="kv-key">{{ t('logs.actionTaken') }}:</span>
              <span class="kv-val">
                <StatusBadge :status="severityType" :text="actionTakenText" />
              </span>
            </div>
            <div class="kv-item">
              <span class="kv-key">{{ t('logs.matchedRule') }}:</span>
              <span class="kv-val font-mono-sm">{{ matchedRuleText }}</span>
            </div>
            <div class="kv-item">
              <span class="kv-key">{{ t('logs.targetHost') }}:</span>
              <div class="host-copy-wrap">
                <code class="kv-code" dir="ltr">{{ event?.host || '-' }}</code>
                <button
                  type="button"
                  class="btn-mini-copy"
                  @click="copyText(event?.host, t('logs.hostCopied'))"
                  :title="t('common.copy') + ' ' + t('logs.host')"
                >
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                </button>
              </div>
            </div>
          </div>
        </section>

        <!-- Section 2: Request Information -->
        <section class="dialog-section">
          <div class="section-title-row">
            <h4 class="section-title">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1 4-10z"></path></svg>
              <span>{{ t('logs.sectionRequest') }}</span>
            </h4>
            <button
              type="button"
              class="wpgate-btn wpgate-btn-secondary wpgate-btn-sm"
              @click="copyText(event?.url, t('logs.urlCopied'))"
            >
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
              <span>{{ t('common.copy') }} URL</span>
            </button>
          </div>

          <div class="url-display-card">
            <div class="url-meta-bar">
              <span class="url-scheme-tag">{{ parsedScheme }}</span>
              <span class="url-domain-tag" dir="ltr">{{ event?.host }}</span>
            </div>
            <div class="url-code-area" dir="ltr">
              {{ event?.url || '-' }}
            </div>
          </div>
        </section>

        <!-- Section 3: Technical Details & Privacy Notice -->
        <section class="dialog-section">
          <h4 class="section-title">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
            <span>{{ t('logs.sectionDiagnostics') }}</span>
          </h4>

          <div class="grid-key-val">
            <div class="kv-item">
              <span class="kv-key">{{ t('logs.resourcePath') }}:</span>
              <span class="kv-val font-mono-sm" dir="ltr">{{ parsedPath }}</span>
            </div>
            <div class="kv-item">
              <span class="kv-key">{{ t('logs.eventSignature') }}:</span>
              <span class="kv-val font-mono-sm" dir="ltr">{{ eventSignature }}</span>
            </div>
          </div>

          <div class="redaction-notice-box">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shield-notice-icon"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
            <p class="redaction-notice-text">
              {{ t('logs.redactionNotice') }}
            </p>
          </div>
        </section>
      </div>

      <!-- Dialog Footer -->
      <footer class="security-dialog-footer">
        <div class="footer-start">
          <button
            type="button"
            class="wpgate-btn wpgate-btn-secondary"
            @click="copyFullJson"
          >
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
            <span>{{ t('logs.copyJson') }}</span>
          </button>
        </div>

        <div class="footer-end">
          <button
            type="button"
            class="wpgate-btn wpgate-btn-primary"
            @click="onClose"
          >
            {{ t('common.close') }}
          </button>
        </div>
      </footer>
    </div>
  </div>
</template>

<script setup>
import { computed, watch, ref, onMounted, onUnmounted } from 'vue';
import StatusBadge from './StatusBadge.vue';
import { t } from '../i18n';

const props = defineProps({
  visible: {
    type: Boolean,
    default: false,
  },
  event: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(['close', 'copied']);

const dialogRef = ref(null);
let previousActiveElement = null;

// Lock body scrolling when modal is open
watch(
  () => props.visible,
  (val) => {
    if (val) {
      previousActiveElement = document.activeElement;
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = '';
      if (previousActiveElement && previousActiveElement.focus) {
        previousActiveElement.focus();
      }
    }
  }
);

onUnmounted(() => {
  document.body.style.overflow = '';
});

const onClose = () => {
  emit('close');
};

const severityType = computed(() => {
  const mode = props.event?.mode || '';
  if (mode.includes('block')) return 'danger';
  if (mode.includes('rewritten') || mode.includes('local')) return 'info';
  return 'success';
});

const severityLabel = computed(() => {
  const type = severityType.value;
  if (type === 'danger') return t('logs.severityHigh');
  if (type === 'info') return t('logs.severityMedium');
  return t('logs.severityLow');
});

const actionTakenText = computed(() => {
  const mode = props.event?.mode || '';
  if (mode.includes('block')) return t('logs.actionBlocked');
  if (mode.includes('rewritten')) return t('logs.actionRewritten');
  return t('logs.actionAllowed');
});

const matchedRuleText = computed(() => {
  const mode = props.event?.mode || '';
  if (mode.includes('whitelist_blocked')) return t('logs.ruleWhitelist');
  if (mode.includes('blacklist_blocked')) return t('logs.ruleBlacklist');
  if (mode.includes('telemetry_blocked')) return t('logs.ruleTelemetry');
  if (mode.includes('rewritten')) return t('logs.ruleAssetRewrite');
  if (mode.includes('whitelist_allowed')) return t('logs.ruleWhitelistAllowed');
  if (mode.includes('bypass_allowed')) return t('logs.ruleBypassAllowed');
  return mode || t('common.none');
});

const parsedScheme = computed(() => {
  const url = props.event?.url || '';
  if (url.startsWith('https://')) return 'HTTPS';
  if (url.startsWith('http://')) return 'HTTP';
  return 'URI';
});

const parsedPath = computed(() => {
  try {
    const url = props.event?.url || '';
    if (url.startsWith('http://') || url.startsWith('https://')) {
      const u = new URL(url);
      return u.pathname || '/';
    }
  } catch (e) {
    // ignore
  }
  return '/';
});

const eventSignature = computed(() => {
  const time = props.event?.time || '';
  const host = props.event?.host || '';
  const mode = props.event?.mode || '';
  const raw = `${time}:${host}:${mode}`;
  let hash = 0;
  for (let i = 0; i < raw.length; i++) {
    hash = (hash << 5) - hash + raw.charCodeAt(i);
    hash |= 0;
  }
  return `EVT-${Math.abs(hash).toString(16).toUpperCase().padStart(8, '0')}`;
});

const copyText = (text, message) => {
  if (!text) return;
  if (navigator?.clipboard?.writeText) {
    navigator.clipboard.writeText(text);
  }
  emit('copied', message || t('common.copied'));
};

const copyFullJson = () => {
  const json = JSON.stringify(
    {
      signature: eventSignature.value,
      timestamp: props.event?.time,
      host: props.event?.host,
      url: props.event?.url,
      mode: props.event?.mode,
      severity: severityLabel.value,
      action: actionTakenText.value,
      rule: matchedRuleText.value,
    },
    null,
    2
  );
  copyText(json, t('logs.jsonCopied'));
};
</script>

<style scoped>
.security-dialog-box {
  background-color: var(--wpgate-surface);
  border: 1px solid var(--wpgate-border);
  border-radius: var(--wpgate-radius-lg);
  width: 100%;
  max-width: 620px;
  max-height: 85vh;
  box-shadow: var(--wpgate-shadow-lg);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  animation: wpgate-modal-pop 0.18s cubic-bezier(0.16, 1, 0.3, 1);
}

.security-dialog-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid var(--wpgate-border);
  background-color: var(--wpgate-surface);
  gap: 12px;
}

.dialog-header-start {
  display: flex;
  align-items: center;
  gap: 12px;
}

.dialog-header-icon {
  width: 40px;
  height: 40px;
  border-radius: var(--wpgate-radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.dialog-header-icon.icon-danger {
  background-color: var(--wpgate-danger-tint);
  color: var(--wpgate-danger);
}

.dialog-header-icon.icon-info {
  background-color: var(--wpgate-info-tint);
  color: var(--wpgate-info);
}

.dialog-header-icon.icon-success {
  background-color: var(--wpgate-success-tint);
  color: var(--wpgate-success);
}

.dialog-header-title-wrap {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.dialog-title-row {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.dialog-title {
  margin: 0;
  font-size: 15px;
  font-weight: 700;
  color: var(--wpgate-text);
  line-height: 1.3;
}

.severity-badge {
  font-size: 11px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 9999px;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.severity-danger {
  background-color: var(--wpgate-danger-tint);
  color: var(--wpgate-danger);
  border: 1px solid rgba(239, 68, 68, 0.25);
}

.severity-info {
  background-color: var(--wpgate-info-tint);
  color: var(--wpgate-info);
  border: 1px solid rgba(14, 165, 233, 0.25);
}

.severity-success {
  background-color: var(--wpgate-success-tint);
  color: var(--wpgate-success);
  border: 1px solid rgba(16, 185, 129, 0.25);
}

.dialog-subtitle {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: var(--wpgate-text-muted);
}

.dialog-clock-icon {
  flex-shrink: 0;
}

.dialog-close-btn {
  background: none;
  border: none;
  padding: 6px;
  cursor: pointer;
  color: var(--wpgate-text-muted);
  border-radius: var(--wpgate-radius-sm);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s ease;
}

.dialog-close-btn:hover {
  background-color: var(--wpgate-surface-alt);
  color: var(--wpgate-text);
}

.security-dialog-body {
  padding: 20px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.dialog-section {
  background-color: var(--wpgate-surface-alt);
  border: 1px solid var(--wpgate-border);
  border-radius: var(--wpgate-radius);
  padding: 14px 16px;
}

.section-title-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 12px;
}

.section-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  font-weight: 600;
  color: var(--wpgate-text);
  margin: 0 0 12px 0;
}

.grid-key-val {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 10px 16px;
}

.kv-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  font-size: 12.5px;
}

.kv-key {
  color: var(--wpgate-text-muted);
}

.kv-val {
  color: var(--wpgate-text);
  text-align: end;
}

.font-semibold {
  font-weight: 600;
}

.font-mono-sm {
  font-family: var(--wpgate-font-mono);
  font-size: 12px;
}

.host-copy-wrap {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.kv-code {
  font-family: var(--wpgate-font-mono);
  font-size: 12px;
  background-color: var(--wpgate-surface);
  border: 1px solid var(--wpgate-border);
  padding: 2px 6px;
  border-radius: 4px;
}

.btn-mini-copy {
  background: none;
  border: none;
  padding: 3px;
  color: var(--wpgate-text-muted);
  cursor: pointer;
  border-radius: 4px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-mini-copy:hover {
  color: var(--wpgate-primary);
  background-color: var(--wpgate-surface);
}

.url-display-card {
  background-color: var(--wpgate-surface);
  border: 1px solid var(--wpgate-border);
  border-radius: var(--wpgate-radius-sm);
  overflow: hidden;
}

.url-meta-bar {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 10px;
  background-color: rgba(2, 132, 199, 0.05);
  border-bottom: 1px solid var(--wpgate-border);
}

.url-scheme-tag {
  font-size: 10.5px;
  font-weight: 700;
  color: var(--wpgate-primary);
  background-color: var(--wpgate-surface);
  border: 1px solid var(--wpgate-border);
  padding: 1px 6px;
  border-radius: 4px;
}

.url-domain-tag {
  font-family: var(--wpgate-font-mono);
  font-size: 11.5px;
  font-weight: 600;
  color: var(--wpgate-text);
}

.url-code-area {
  padding: 10px 12px;
  font-family: var(--wpgate-font-mono);
  font-size: 12px;
  line-height: 1.5;
  color: var(--wpgate-text);
  word-break: break-all;
  white-space: pre-wrap;
  max-height: 120px;
  overflow-y: auto;
}

.redaction-notice-box {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  margin-top: 12px;
  padding: 10px 12px;
  border-radius: var(--wpgate-radius-sm);
  background-color: var(--wpgate-surface);
  border: 1px dashed var(--wpgate-border);
}

.shield-notice-icon {
  flex-shrink: 0;
  color: var(--wpgate-primary);
  margin-top: 2px;
}

.redaction-notice-text {
  margin: 0;
  font-size: 11.5px;
  line-height: 1.5;
  color: var(--wpgate-text-muted);
}

.security-dialog-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 20px;
  border-top: 1px solid var(--wpgate-border);
  background-color: var(--wpgate-surface);
  gap: 12px;
}

@media (max-width: 600px) {
  .security-dialog-box {
    max-height: 92vh;
  }
  .grid-key-val {
    grid-template-columns: 1fr;
  }
  .security-dialog-footer {
    flex-direction: column-reverse;
  }
  .security-dialog-footer .footer-start,
  .security-dialog-footer .footer-end,
  .security-dialog-footer button {
    width: 100%;
  }
}
</style>
