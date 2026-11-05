<template>
  <div class="wpgate-stat-card" :class="`card-${type}`">
    <div class="stat-card-header">
      <div class="stat-icon-wrapper" :class="`icon-${type}`">
        <slot name="icon"></slot>
      </div>
      <StatusBadge v-if="badge" :type="type" :text="badge" />
    </div>

    <div class="stat-card-body">
      <span class="stat-label">{{ label }}</span>
      <div v-if="inactive" class="stat-inactive-wrap">
        <span class="stat-inactive-text">{{ inactiveText }}</span>
        <button v-if="actionText" type="button" class="stat-action-btn" @click="$emit('action')">
          {{ actionText }} &rarr;
        </button>
      </div>
      <div v-else class="stat-value-wrap">
        <span class="stat-value" :class="`val-${type}`">{{ formattedValue }}</span>
        <span v-if="suffix" class="stat-suffix">{{ suffix }}</span>
      </div>
    </div>

    <div v-if="hint || $slots.footer" class="stat-card-footer">
      <slot name="footer">
        <span class="stat-hint">{{ hint }}</span>
      </slot>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import StatusBadge from './StatusBadge.vue';

const props = defineProps({
  label: {
    type: String,
    required: true,
  },
  value: {
    type: [Number, String],
    default: 0,
  },
  type: {
    type: String,
    default: 'neutral', // 'primary', 'success', 'warning', 'danger', 'info', 'neutral'
  },
  badge: {
    type: String,
    default: '',
  },
  hint: {
    type: String,
    default: '',
  },
  suffix: {
    type: String,
    default: '',
  },
  inactive: {
    type: Boolean,
    default: false,
  },
  inactiveText: {
    type: String,
    default: 'Inactive',
  },
  actionText: {
    type: String,
    default: '',
  },
});

defineEmits(['action']);

const formattedValue = computed(() => {
  if (typeof props.value === 'number') {
    return props.value.toLocaleString();
  }
  return props.value;
});
</script>

<style scoped>
.wpgate-stat-card {
  background-color: var(--wpgate-surface);
  border: 1px solid var(--wpgate-border);
  border-radius: var(--wpgate-radius);
  padding: 20px;
  box-shadow: var(--wpgate-shadow-sm);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: 14px;
  transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
}

.wpgate-stat-card:hover {
  box-shadow: var(--wpgate-shadow-md);
  border-color: rgba(37, 99, 235, 0.3);
}

.stat-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.stat-icon-wrapper {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.icon-primary, .icon-info {
  background-color: rgba(37, 99, 235, 0.1);
  color: var(--wpgate-primary);
}

.icon-success {
  background-color: rgba(5, 150, 105, 0.1);
  color: var(--wpgate-success);
}

.icon-warning {
  background-color: rgba(217, 119, 6, 0.1);
  color: var(--wpgate-warning);
}

.icon-danger {
  background-color: rgba(220, 38, 38, 0.1);
  color: var(--wpgate-danger);
}

.icon-neutral {
  background-color: rgba(100, 116, 139, 0.1);
  color: var(--wpgate-text-muted);
}

[data-theme="dark"] .icon-primary,
[data-theme="dark"] .icon-info {
  background-color: rgba(59, 130, 246, 0.15);
}

[data-theme="dark"] .icon-success {
  background-color: rgba(16, 185, 129, 0.15);
}

[data-theme="dark"] .icon-warning {
  background-color: rgba(245, 158, 11, 0.15);
}

[data-theme="dark"] .icon-danger {
  background-color: rgba(239, 68, 68, 0.15);
}

.stat-card-body {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.stat-label {
  font-size: 0.82rem;
  font-weight: 500;
  color: var(--wpgate-text-muted);
  line-height: 1.3;
}

.stat-value-wrap {
  display: flex;
  align-items: baseline;
  gap: 6px;
}

.stat-value {
  font-size: 1.75rem;
  font-weight: 700;
  line-height: 1.1;
  color: var(--wpgate-text);
  letter-spacing: -0.02em;
}

.val-primary {
  color: var(--wpgate-primary);
}

.val-success {
  color: var(--wpgate-success);
}

.val-danger {
  color: var(--wpgate-danger);
}

.val-warning {
  color: var(--wpgate-warning);
}

.stat-suffix {
  font-size: 0.85rem;
  font-weight: 500;
  color: var(--wpgate-text-muted);
}

.stat-inactive-wrap {
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 4px 0;
}

.stat-inactive-text {
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--wpgate-text-muted);
}

.stat-action-btn {
  background: none;
  border: none;
  padding: 0;
  color: var(--wpgate-primary);
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  text-align: start;
}

.stat-action-btn:hover {
  text-decoration: underline;
}

.stat-card-footer {
  padding-top: 10px;
  border-top: 1px solid var(--wpgate-border);
  font-size: 0.78rem;
  color: var(--wpgate-text-muted);
  line-height: 1.3;
}
</style>
