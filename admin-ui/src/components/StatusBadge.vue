<template>
  <span class="wpgate-status-badge" :class="`badge-${badgeType}`">
    <span class="badge-dot" v-if="dot"></span>
    <slot>{{ text }}</slot>
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  type: {
    type: String,
    default: '',
  },
  status: {
    type: String,
    default: '',
  },
  text: {
    type: String,
    default: '',
  },
  dot: {
    type: Boolean,
    default: true,
  },
});

const badgeType = computed(() => {
  return props.status || props.type || 'neutral';
});
</script>

<style scoped>
.wpgate-status-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 3px 10px;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  line-height: 1.2;
  border: 1px solid transparent;
  white-space: nowrap;
}

.badge-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background-color: currentColor;
}

.badge-success {
  background-color: rgba(5, 150, 105, 0.1);
  color: #059669;
  border-color: rgba(5, 150, 105, 0.2);
}

.badge-warning {
  background-color: rgba(217, 119, 6, 0.1);
  color: #d97706;
  border-color: rgba(217, 119, 6, 0.2);
}

.badge-danger {
  background-color: rgba(220, 38, 38, 0.1);
  color: #dc2626;
  border-color: rgba(220, 38, 38, 0.2);
}

.badge-info {
  background-color: rgba(14, 165, 233, 0.1);
  color: #0ea5e9;
  border-color: rgba(14, 165, 233, 0.2);
}

.badge-primary {
  background-color: rgba(37, 99, 235, 0.1);
  color: #2563eb;
  border-color: rgba(37, 99, 235, 0.2);
}

.badge-neutral {
  background-color: rgba(100, 116, 139, 0.1);
  color: #64748b;
  border-color: rgba(100, 116, 139, 0.2);
}

:global([data-theme="dark"]) .badge-success {
  background-color: rgba(16, 185, 129, 0.15);
  color: #34d399;
  border-color: rgba(16, 185, 129, 0.3);
}

:global([data-theme="dark"]) .badge-warning {
  background-color: rgba(245, 158, 11, 0.15);
  color: #fbbf24;
  border-color: rgba(245, 158, 11, 0.3);
}

:global([data-theme="dark"]) .badge-danger {
  background-color: rgba(239, 68, 68, 0.15);
  color: #f87171;
  border-color: rgba(239, 68, 68, 0.3);
}

:global([data-theme="dark"]) .badge-info {
  background-color: rgba(56, 189, 248, 0.15);
  color: #38bdf8;
  border-color: rgba(56, 189, 248, 0.3);
}

:global([data-theme="dark"]) .badge-primary {
  background-color: rgba(59, 130, 246, 0.15);
  color: #60a5fa;
  border-color: rgba(59, 130, 246, 0.3);
}

:global([data-theme="dark"]) .badge-neutral {
  background-color: rgba(148, 163, 184, 0.15);
  color: #94a3b8;
  border-color: rgba(148, 163, 184, 0.3);
}
</style>
