<template>
  <div class="wpgate-tab-pane">
    <div class="wpgate-section-header">
      <div>
        <h2 class="wpgate-section-title">{{ t('logs.title') }}</h2>
        <p class="wpgate-section-desc">{{ t('logs.desc') }}</p>
      </div>
      <StatusBadge :status="settings.enable_logging === 'yes' ? 'success' : 'neutral'">
        {{ settings.enable_logging === 'yes' ? t('common.enabled') : t('common.disabled') }}
      </StatusBadge>
    </div>

    <!-- Logging Engine Configuration -->
    <SettingsPanel title="Audit &amp; Diagnostic Engine">
      <SettingToggle
        v-model="settings.enable_logging"
        :title="t('logs.enableLogging')"
        description="Records intercepted outbound HTTP requests to the database with automatic cryptographic query parameter redaction."
      >
        <template #extra v-if="settings.enable_logging === 'yes'">
          <div class="wpgate-grid-options wpgate-mt-4">
            <div class="wpgate-form-group">
              <label class="wpgate-label">{{ t('logs.retentionDays') }}</label>
              <select v-model="settings.log_retention_days" class="wpgate-select">
                <option value="1">1 Day</option>
                <option value="3">3 Days</option>
                <option value="7">7 Days (Standard)</option>
                <option value="15">15 Days</option>
                <option value="30">30 Days</option>
              </select>
            </div>

            <div class="wpgate-form-group">
              <label class="wpgate-label">{{ t('logs.maxEntries') }}</label>
              <select v-model="settings.log_max_entries" class="wpgate-select">
                <option value="100">100 Events</option>
                <option value="300">300 Events (Recommended)</option>
                <option value="500">500 Events</option>
                <option value="1000">1000 Events</option>
              </select>
            </div>

            <div class="wpgate-form-group">
              <label class="wpgate-label">&nbsp;</label>
              <label class="wpgate-checkbox-label">
                <input type="checkbox" v-model="settings.log_asset_events" true-value="yes" false-value="no" />
                <span>{{ t('logs.enableAssetLogging') }}</span>
              </label>
            </div>
          </div>
        </template>
      </SettingToggle>
    </SettingsPanel>

    <!-- Logs Viewer Panel -->
    <SettingsPanel class="wpgate-mt-4" title="Event Log Viewer">
      <template #badge>
        <StatusBadge status="neutral">{{ total }} {{ t('common.entries') }}</StatusBadge>
      </template>

      <!-- Action & Search Toolbar -->
      <div class="wpgate-logs-toolbar">
        <div class="wpgate-logs-filters">
          <div class="wpgate-search-box">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="wpgate-search-icon"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input
              type="text"
              v-model="searchTerm"
              :placeholder="t('common.search') + ' (host, URL)...'"
              class="wpgate-input wpgate-search-input"
              @input="debouncedFetch"
            />
          </div>

          <select v-model="statusFilter" class="wpgate-select wpgate-filter-select" @change="fetchLogs">
            <option value="all">{{ t('common.all') }} Actions</option>
            <option value="blocked">{{ t('common.blocked') }}</option>
            <option value="allowed">{{ t('common.allowed') }}</option>
            <option value="rewritten">{{ t('common.rewritten') }}</option>
          </select>
        </div>

        <div class="wpgate-logs-actions">
          <button type="button" class="wpgate-btn wpgate-btn-secondary" @click="fetchLogs" :disabled="loading" :title="t('common.refresh')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="wpgate-icon" :class="{ 'wpgate-spin': loading }"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
            <span>{{ t('common.refresh') }}</span>
          </button>
          <button type="button" class="wpgate-btn wpgate-btn-danger" @click="onClearLogs" :disabled="loading || !total">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="wpgate-icon"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
            <span>{{ t('logs.clearLogs') }}</span>
          </button>
        </div>
      </div>

      <!-- Table Area -->
      <div class="wpgate-table-wrapper wpgate-mt-3">
        <div v-if="loading" class="wpgate-table-loading">
          <div class="wpgate-spinner"></div>
          <span>{{ t('common.loading') }}</span>
        </div>

        <table v-else-if="items && items.length" class="wpgate-table">
          <thead>
            <tr>
              <th style="width: 170px;">{{ t('logs.time') }}</th>
              <th style="width: 130px;">{{ t('logs.mode') }}</th>
              <th style="width: 220px;">{{ t('logs.host') }}</th>
              <th>{{ t('logs.url') }}</th>
              <th style="width: 80px; text-align: center;">{{ t('common.details') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(log, idx) in items" :key="idx">
              <td class="wpgate-text-muted wpgate-nowrap">{{ log.time }}</td>
              <td>
                <StatusBadge :status="getModeStatus(log.mode)">
                  {{ formatMode(log.mode) }}
                </StatusBadge>
              </td>
              <td><strong class="wpgate-code-text" dir="ltr">{{ log.host }}</strong></td>
              <td class="wpgate-code-cell" dir="ltr" :title="log.url">{{ log.url }}</td>
              <td style="text-align: center;">
                <button type="button" class="wpgate-btn-icon" @click="selectedLog = log" title="View details">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Empty State -->
        <EmptyState
          v-else
          :title="settings.enable_logging === 'yes' ? t('logs.noLogs') : 'Logging Engine Disabled'"
          :description="settings.enable_logging === 'yes' ? 'No outbound network interception events recorded matching your filter criteria.' : 'Enable diagnostic logging above to record outbound requests with secret masking.'"
        />
      </div>

      <!-- Pagination Footer -->
      <div v-if="totalPages > 1" class="wpgate-pagination-bar wpgate-mt-4">
        <span class="wpgate-text-muted wpgate-text-sm">
          Page <strong>{{ page }}</strong> of <strong>{{ totalPages }}</strong> ({{ total }} events)
        </span>
        <div class="wpgate-pagination">
          <button type="button" class="wpgate-btn wpgate-btn-secondary wpgate-btn-sm" :disabled="page <= 1" @click="changePage(page - 1)">&larr; Prev</button>
          <span class="wpgate-page-num">{{ page }}</span>
          <button type="button" class="wpgate-btn wpgate-btn-secondary wpgate-btn-sm" :disabled="page >= totalPages" @click="changePage(page + 1)">Next &rarr;</button>
        </div>
      </div>
    </SettingsPanel>

    <!-- Log Details Modal -->
    <div v-if="selectedLog" class="wpgate-modal-overlay" @click.self="selectedLog = null">
      <div class="wpgate-modal-box">
        <div class="wpgate-modal-header">
          <h3 class="wpgate-modal-title">Interception Event Details</h3>
          <button type="button" class="wpgate-modal-close" @click="selectedLog = null">&times;</button>
        </div>
        <div class="wpgate-modal-body">
          <div class="wpgate-detail-row">
            <strong>Timestamp:</strong> <span>{{ selectedLog.time }}</span>
          </div>
          <div class="wpgate-detail-row">
            <strong>Target Host:</strong> <code dir="ltr">{{ selectedLog.host }}</code>
          </div>
          <div class="wpgate-detail-row">
            <strong>Action / Mode:</strong>
            <StatusBadge :status="getModeStatus(selectedLog.mode)">{{ formatMode(selectedLog.mode) }}</StatusBadge>
          </div>
          <div class="wpgate-detail-row">
            <strong>Sanitized URL (Parameters Redacted):</strong>
            <pre class="wpgate-code-block" dir="ltr">{{ selectedLog.url }}</pre>
          </div>
        </div>
        <div class="wpgate-modal-footer">
          <button type="button" class="wpgate-btn wpgate-btn-secondary" @click="copyUrl(selectedLog.url)">
            {{ t('common.copy') }} URL
          </button>
          <button type="button" class="wpgate-btn wpgate-btn-primary" @click="selectedLog = null">
            {{ t('common.close') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { api } from '../api';
import { t } from '../i18n';
import SettingsPanel from './SettingsPanel.vue';
import SettingToggle from './SettingToggle.vue';
import StatusBadge from './StatusBadge.vue';
import EmptyState from './EmptyState.vue';

const props = defineProps({
  settings: { type: Object, required: true },
});

const emit = defineEmits(['notify', 'confirm']);

const items = ref([]);
const total = ref(0);
const page = ref(1);
const perPage = ref(20);
const totalPages = ref(1);
const loading = ref(false);
const searchTerm = ref('');
const statusFilter = ref('all');
const selectedLog = ref(null);
let debounceTimer = null;

const fetchLogs = async () => {
  loading.value = true;
  try {
    const res = await api.getLogs({
      page: page.value,
      per_page: perPage.value,
      search: searchTerm.value,
      status: statusFilter.value,
    });
    if (res.success && res.data) {
      items.value = res.data.items || [];
      total.value = res.data.total || 0;
      page.value = res.data.page || 1;
      totalPages.value = res.data.total_pages || 1;
    }
  } catch (err) {
    emit('notify', { type: 'error', message: 'Failed to load diagnostic logs.' });
  } finally {
    loading.value = false;
  }
};

const debouncedFetch = () => {
  if (debounceTimer) clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    page.value = 1;
    fetchLogs();
  }, 350);
};

const changePage = (p) => {
  page.value = p;
  fetchLogs();
};

const onClearLogs = () => {
  emit('confirm', {
    title: t('logs.clearLogs'),
    message: t('logs.clearLogsConfirm'),
    confirmText: t('common.confirm'),
    cancelText: t('common.cancel'),
    isDanger: true,
    action: async () => {
      try {
        await api.clearLogs();
        emit('notify', { type: 'success', message: 'Diagnostic logs cleared successfully.' });
        page.value = 1;
        fetchLogs();
      } catch (err) {
        emit('notify', { type: 'error', message: 'Failed to clear logs.' });
      }
    },
  });
};

const copyUrl = (text) => {
  if (navigator.clipboard) {
    navigator.clipboard.writeText(text);
    emit('notify', { type: 'success', message: t('common.copied') });
  }
};

const getModeStatus = (mode) => {
  if (!mode) return 'neutral';
  if (mode.includes('block')) return 'danger';
  if (mode.includes('rewritten') || mode.includes('local')) return 'info';
  return 'success';
};

const formatMode = (mode) => {
  if (!mode) return '';
  return mode.replace(/_/g, ' ');
};

onMounted(() => {
  fetchLogs();
});
</script>
