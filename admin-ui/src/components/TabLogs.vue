<template>
  <div class="wpgate-tab-pane">
    <div class="wpgate-section-header">
      <h2 class="wpgate-section-title">{{ t('logs.title') }}</h2>
      <p class="wpgate-section-desc">{{ t('logs.desc') }}</p>
    </div>

    <!-- Logging Config Card -->
    <div class="wpgate-panel">
      <div class="wpgate-panel-body">
        <div class="wpgate-toggle-row">
          <label class="wpgate-switch">
            <input type="checkbox" v-model="settings.enable_logging" true-value="yes" false-value="no" />
            <span class="wpgate-slider"></span>
          </label>
          <div class="wpgate-toggle-info">
            <div class="wpgate-toggle-title">{{ t('logs.enableLogging') }}</div>
            <div class="wpgate-toggle-desc">Records outbound network requests to internal database option with automatic redaction.</div>
          </div>
        </div>

        <div v-show="settings.enable_logging === 'yes'" class="wpgate-grid-options wpgate-mt-3">
          <div class="wpgate-form-group">
            <label class="wpgate-label">{{ t('logs.retentionDays') }}</label>
            <select v-model="settings.log_retention_days" class="wpgate-select">
              <option value="1">1 Day</option>
              <option value="3">3 Days</option>
              <option value="7">7 Days (Recommended)</option>
              <option value="15">15 Days</option>
              <option value="30">30 Days</option>
            </select>
          </div>

          <div class="wpgate-form-group">
            <label class="wpgate-label">{{ t('logs.maxEntries') }}</label>
            <select v-model="settings.log_max_entries" class="wpgate-select">
              <option value="100">100 Entries</option>
              <option value="300">300 Entries (Recommended)</option>
              <option value="500">500 Entries</option>
              <option value="1000">1000 Entries</option>
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
      </div>
    </div>

    <!-- Log Viewer Panel -->
    <div class="wpgate-panel wpgate-mt-4">
      <div class="wpgate-panel-header wpgate-flex-between">
        <div class="wpgate-flex-center wpgate-gap-2">
          <input
            type="text"
            v-model="searchTerm"
            :placeholder="t('common.search')"
            class="wpgate-input wpgate-search-input"
            @input="debouncedFetch"
          />
          <select v-model="statusFilter" class="wpgate-select wpgate-filter-select" @change="fetchLogs">
            <option value="all">{{ t('common.all') }}</option>
            <option value="blocked">{{ t('common.blocked') }}</option>
            <option value="allowed">{{ t('common.allowed') }}</option>
            <option value="rewritten">{{ t('common.rewritten') }}</option>
          </select>
        </div>

        <div class="wpgate-flex-center wpgate-gap-2">
          <button type="button" class="wpgate-btn wpgate-btn-secondary" @click="fetchLogs" :disabled="loading">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="wpgate-icon"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
            {{ t('common.refresh') }}
          </button>
          <button type="button" class="wpgate-btn wpgate-btn-danger" @click="onClearLogs" :disabled="loading || !total">
            {{ t('logs.clearLogs') }}
          </button>
        </div>
      </div>

      <div class="wpgate-table-wrapper">
        <div v-if="loading" class="wpgate-table-loading">
          <div class="wpgate-spinner"></div>
          <span>{{ t('common.loading') }}</span>
        </div>

        <table v-else-if="items && items.length" class="wpgate-table">
          <thead>
            <tr>
              <th style="width: 160px;">{{ t('logs.time') }}</th>
              <th style="width: 140px;">{{ t('logs.mode') }}</th>
              <th style="width: 200px;">{{ t('logs.host') }}</th>
              <th>{{ t('logs.url') }}</th>
              <th style="width: 90px; text-align: center;">{{ t('common.details') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(log, idx) in items" :key="idx">
              <td class="wpgate-text-muted wpgate-nowrap">{{ log.time }}</td>
              <td>
                <span class="wpgate-badge" :class="getModeBadgeClass(log.mode)">
                  {{ formatMode(log.mode) }}
                </span>
              </td>
              <td><strong>{{ log.host }}</strong></td>
              <td class="wpgate-code-cell" :title="log.url">{{ log.url }}</td>
              <td style="text-align: center;">
                <button type="button" class="wpgate-btn-icon" @click="selectedLog = log" title="View details">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-else class="wpgate-empty-state">
          <p>{{ t('logs.noLogs') }}</p>
        </div>
      </div>

      <!-- Pagination Footer -->
      <div v-if="totalPages > 1" class="wpgate-panel-footer wpgate-flex-between">
        <span class="wpgate-text-muted">
          Showing page {{ page }} of {{ totalPages }} ({{ total }} total events)
        </span>
        <div class="wpgate-pagination">
          <button type="button" class="wpgate-btn wpgate-btn-secondary" :disabled="page <= 1" @click="changePage(page - 1)">&larr; Prev</button>
          <span class="wpgate-page-num">{{ page }}</span>
          <button type="button" class="wpgate-btn wpgate-btn-secondary" :disabled="page >= totalPages" @click="changePage(page + 1)">Next &rarr;</button>
        </div>
      </div>
    </div>

    <!-- Log Details Modal -->
    <div v-if="selectedLog" class="wpgate-modal-overlay" @click.self="selectedLog = null">
      <div class="wpgate-modal-box">
        <div class="wpgate-modal-header">
          <h3 class="wpgate-modal-title">Log Event Details</h3>
          <button type="button" class="wpgate-modal-close" @click="selectedLog = null">&times;</button>
        </div>
        <div class="wpgate-modal-body">
          <div class="wpgate-detail-row">
            <strong>Timestamp:</strong> <span>{{ selectedLog.time }}</span>
          </div>
          <div class="wpgate-detail-row">
            <strong>Target Host:</strong> <code>{{ selectedLog.host }}</code>
          </div>
          <div class="wpgate-detail-row">
            <strong>Interception Mode:</strong>
            <span class="wpgate-badge" :class="getModeBadgeClass(selectedLog.mode)">{{ selectedLog.mode }}</span>
          </div>
          <div class="wpgate-detail-row">
            <strong>Sanitized URL:</strong>
            <pre class="wpgate-code-block">{{ selectedLog.url }}</pre>
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

const getModeBadgeClass = (mode) => {
  if (!mode) return 'wpgate-badge-neutral';
  if (mode.includes('block')) return 'wpgate-badge-danger';
  if (mode.includes('rewritten') || mode.includes('local')) return 'wpgate-badge-info';
  return 'wpgate-badge-success';
};

const formatMode = (mode) => {
  if (!mode) return '';
  return mode.replace(/_/g, ' ');
};

onMounted(() => {
  fetchLogs();
});
</script>
