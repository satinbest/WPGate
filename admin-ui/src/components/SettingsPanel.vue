<template>
  <section class="wpgate-panel" :class="{ 'panel-danger': danger }">
    <header v-if="title || $slots.header" class="wpgate-panel-header">
      <div class="panel-header-title-wrap">
        <h3 v-if="title" class="wpgate-panel-title">
          {{ title }}
        </h3>
        <p v-if="panelSubtitle" class="wpgate-panel-subtitle">{{ panelSubtitle }}</p>
      </div>
      <div v-if="$slots.actions || $slots.badge || badge" class="panel-header-actions">
        <slot name="actions"></slot>
        <slot name="badge"></slot>
        <StatusBadge v-if="badge" :type="resolvedBadgeType" :text="badge" />
      </div>
    </header>
    <div class="wpgate-panel-body">
      <slot></slot>
    </div>
    <footer v-if="$slots.footer" class="wpgate-panel-footer">
      <slot name="footer"></slot>
    </footer>
  </section>
</template>

<script setup>
import { computed } from 'vue';
import StatusBadge from './StatusBadge.vue';

const props = defineProps({
  title: {
    type: String,
    default: '',
  },
  subtitle: {
    type: String,
    default: '',
  },
  description: {
    type: String,
    default: '',
  },
  badge: {
    type: String,
    default: '',
  },
  badgeType: {
    type: String,
    default: '',
  },
  badgeStatus: {
    type: String,
    default: '',
  },
  danger: {
    type: Boolean,
    default: false,
  },
});

const panelSubtitle = computed(() => props.subtitle || props.description || '');
const resolvedBadgeType = computed(() => props.badgeStatus || props.badgeType || 'neutral');
</script>

<style scoped>
.wpgate-panel {
  background-color: var(--wpgate-surface);
  border: 1px solid var(--wpgate-border);
  border-radius: var(--wpgate-radius);
  box-shadow: var(--wpgate-shadow-sm);
  margin-bottom: 20px;
  overflow: hidden;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.panel-danger {
  border-color: rgba(220, 38, 38, 0.3);
}

[data-theme="dark"] .panel-danger {
  border-color: rgba(239, 68, 68, 0.4);
}

.wpgate-panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 14px 20px;
  border-bottom: 1px solid var(--wpgate-border);
  background-color: var(--wpgate-surface);
}

.panel-header-title-wrap {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.wpgate-panel-title {
  margin: 0;
  font-size: 14px;
  font-weight: 600;
  color: var(--wpgate-text);
  line-height: 1.4;
}

.wpgate-panel-subtitle {
  margin: 0;
  font-size: 12px;
  color: var(--wpgate-text-muted);
  line-height: 1.4;
}

.panel-header-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

.wpgate-panel-body {
  padding: 18px 20px;
}

.wpgate-panel-footer {
  padding: 12px 20px;
  background-color: var(--wpgate-bg);
  border-top: 1px solid var(--wpgate-border);
  display: flex;
  align-items: center;
  justify-content: flex-end;
}
</style>
