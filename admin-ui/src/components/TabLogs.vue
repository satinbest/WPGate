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
    <SettingsPanel :title="t('logs.auditEngineTitle')">
      <SettingToggle
        v-model="settings.enable_logging"
        :title="t('logs.enableLogging')"
        :description="t('logs.enableLoggingDesc')"
      >
        <template #extra v-if="settings.enable_logging === 'yes'">
          <div class="wpgate-grid-options wpgate-mt-4">
            <div class="wpgate-form-group">
              <label class="wpgate-label">{{ t('logs.retentionDays') }}</label>
              <AppSelect
                v-model="settings.log_retention_days"
                :options="retentionOptions"
                full-width
              />
            </div>

            <div class="wpgate-form-group">
              <label class="wpgate-label">{{ t('logs.maxEntries') }}</label>
              <AppSelect
                v-model="settings.log_max_entries"
                :options="maxEntriesOptions"
                full-width
              />
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
    <SettingsPanel class="wpgate-mt-4" :title="t('logs.viewerTitle')">
      <template #badge>
        <StatusBadge status="neutral">{{ total }} {{ t('common.entries') }}</StatusBadge>
      </template>

      <!-- Action & Search Toolbar -->
      <div class="wpgate-logs-toolbar">
        <div class="wpgate-logs-filters">
          <!-- Reusable Search Input Component -->
          <SearchInput
            v-model="searchTerm"
            :placeholder="t('common.search')"
            :loading="loading"
            @input="debouncedFetch"
            @clear="onSearchClear"
            @search="fetchLogs"
          />

          <!-- Reusable Status Filter Select -->
          <AppSelect
            v-model="statusFilter"
            :options="statusFilterOptions"
            @change="fetchLogs"
          />
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

        <table v-else-if="items.length" class="wpgate-table">
          <thead>
            <tr>
              <th>{{ t('logs.time') }}</th>
              <th>{{ t('logs.action') }}</th>
              <th>{{ t('logs.host') }}</th>
              <th>{{ t('logs.url') }}</th>
              <th style="width: 70px; text-align: center;">{{ t('common.details') }}</th>
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
                <button type="button" class="wpgate-btn-icon" @click="selectedLog = log" :title="t('common.details')">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Empty State -->
        <EmptyState
          v-else
          :title="settings.enable_logging === 'yes' ? t('logs.noLogs') : t('logs.engineDisabled')"
          :description="settings.enable_logging === 'yes' ? t('logs.noFilterLogs') : t('logs.enableLoggingPrompt')"
        />
      </div>

      <!-- Pagination Footer -->
      <div v-if="totalPages > 1" class="wpgate-pagination-bar wpgate-mt-4">
        <span class="wpgate-text-muted wpgate-text-sm">
          {{ t('common.pageOf').replace('{page}', page).replace('{totalPages}', totalPages).replace('{total}', total) }}
        </span>
        <div class="wpgate-pagination">
          <button type="button" class="wpgate-btn wpgate-btn-secondary wpgate-btn-sm" :disabled="page <= 1" @click="changePage(page - 1)">&larr; {{ t('common.prev') }}</button>
          <span class="wpgate-page-num">{{ page }}</span>
          <button type="button" class="wpgate-btn wpgate-btn-secondary wpgate-btn-sm" :disabled="page >= totalPages" @click="changePage(page + 1)">{{ t('common.next') }} &rarr;</button>
        </div>
      </div>
    </SettingsPanel>

    <!-- Professional Security Event Details Dialog -->
    <SecurityEventDialog
      :visible="!!selectedLog"
      :event="selectedLog"
      @close="selectedLog = null"
      @copied="onCopied"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { api } from '../api';
import { t } from '../i18n';
import SettingsPanel from './SettingsPanel.vue';
import SettingToggle from './SettingToggle.vue';
import StatusBadge from './StatusBadge.vue';
import EmptyState from './EmptyState.vue';
import SearchInput from './SearchInput.vue';
import AppSelect from './AppSelect.vue';
import SecurityEventDialog from './SecurityEventDialog.vue';

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

const retentionOptions = computed(() => [
  { value: '1', label: t('logs.daySingle') },
  { value: '3', label: t('logs.day3') },
  { value: '7', label: t('logs.dayStandard') },
  { value: '15', label: t('logs.day15') },
  { value: '30', label: t('logs.day30') },
]);

const maxEntriesOptions = computed(() => [
  { value: '100', label: t('logs.event100') },
  { value: '300', label: t('logs.eventRecommended') },
  { value: '500', label: t('logs.event500') },
  { value: '1000', label: t('logs.event1000') },
]);

const statusFilterOptions = computed(() => [
  { value: 'all', label: t('common.all') },
  { value: 'blocked', label: t('common.blocked') },
  { value: 'allowed', label: t('common.allowed') },
  { value: 'rewritten', label: t('common.rewritten') },
]);

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
    emit('notify', { type: 'error', message: t('logs.loadFailed') });
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

const onSearchClear = () => {
  page.value = 1;
  fetchLogs();
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
        emit('notify', { type: 'success', message: t('logs.clearSuccess') });
        page.value = 1;
        fetchLogs();
      } catch (err) {
        emit('notify', { type: 'error', message: t('logs.clearFailed') });
      }
    },
  });
};

const onCopied = (msg) => {
  emit('notify', { type: 'success', message: msg || t('common.copied') });
};

const getModeStatus = (mode) => {
  if (!mode) return 'neutral';
  if (mode.includes('block')) return 'danger';
  if (mode.includes('rewritten') || mode.includes('local')) return 'info';
  return 'success';
};

const formatMode = (mode) => {
  if (!mode) return '';
  if (mode === 'blocked') return t('common.blocked');
  if (mode === 'allowed') return t('common.allowed');
  if (mode === 'rewritten') return t('common.rewritten');
  return mode.replace(/_/g, ' ');
};

onMounted(() => {
  fetchLogs();
});
</script>
