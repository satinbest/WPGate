<template>
  <div class="wpgate-setting-toggle" :class="{ 'is-disabled': disabled }">
    <div class="toggle-control-col">
      <label class="wpgate-switch" :class="{ 'switch-checked': modelValue === activeValue }">
        <input
          type="checkbox"
          :checked="modelValue === activeValue"
          :disabled="disabled"
          @change="onToggle"
          class="switch-input"
        />
        <span class="switch-slider"></span>
      </label>
    </div>
    <div class="toggle-info-col">
      <div class="toggle-title-row">
        <span class="toggle-title">{{ title }}</span>
        <StatusBadge
          v-if="badgeText"
          :type="modelValue === activeValue ? 'success' : 'neutral'"
          :text="badgeText"
        />
      </div>
      <p v-if="description" class="toggle-description">{{ description }}</p>
      <div v-if="warning && modelValue === activeValue" class="toggle-warning-alert">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="warning-icon"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
        <span>{{ warning }}</span>
      </div>
      <div v-if="$slots.extra && modelValue === activeValue" class="toggle-extra-content">
        <slot name="extra"></slot>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import StatusBadge from './StatusBadge.vue';
import { t } from '../i18n';

const props = defineProps({
  modelValue: {
    type: [String, Boolean, Number],
    default: 'no',
  },
  activeValue: {
    type: [String, Boolean, Number],
    default: 'yes',
  },
  inactiveValue: {
    type: [String, Boolean, Number],
    default: 'no',
  },
  title: {
    type: String,
    required: true,
  },
  description: {
    type: String,
    default: '',
  },
  warning: {
    type: String,
    default: '',
  },
  showBadge: {
    type: Boolean,
    default: true,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update:modelValue', 'change']);

const badgeText = computed(() => {
  if (!props.showBadge) return '';
  return props.modelValue === props.activeValue ? t('common.active') : t('common.inactive');
});

const onToggle = (e) => {
  const newVal = e.target.checked ? props.activeValue : props.inactiveValue;
  emit('update:modelValue', newVal);
  emit('change', newVal);
};
</script>

<style scoped>
.wpgate-setting-toggle {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  padding: 16px 0;
}

.wpgate-setting-toggle:not(:last-child) {
  border-bottom: 1px solid var(--wpgate-border);
}

.toggle-control-col {
  padding-top: 2px;
  flex-shrink: 0;
}

.toggle-info-col {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.toggle-title-row {
  display: flex;
  align-items: center;
  gap: 10px;
}

.toggle-title {
  font-size: 13px;
  font-weight: 600;
  color: var(--wpgate-text);
  line-height: 1.4;
}

.toggle-description {
  margin: 0;
  font-size: 12px;
  color: var(--wpgate-text-muted);
  line-height: 1.5;
}

.toggle-warning-alert {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 6px;
  padding: 8px 12px;
  border-radius: var(--wpgate-radius-sm);
  background-color: rgba(217, 119, 6, 0.1);
  border: 1px solid rgba(217, 119, 6, 0.25);
  color: #b45309;
  font-size: 12px;
  line-height: 1.4;
}

[data-theme="dark"] .toggle-warning-alert {
  background-color: rgba(245, 158, 11, 0.15);
  border-color: rgba(245, 158, 11, 0.3);
  color: #fbbf24;
}

.warning-icon {
  flex-shrink: 0;
}

.toggle-extra-content {
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px dashed var(--wpgate-border);
}

/* Custom Accessible Toggle Switch */
.wpgate-switch {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
  cursor: pointer;
}

.switch-input {
  opacity: 0;
  width: 0;
  height: 0;
  position: absolute;
}

.switch-slider {
  position: absolute;
  inset: 0;
  background-color: var(--wpgate-border);
  border-radius: 9999px;
  transition: background-color 0.2s ease, box-shadow 0.2s ease;
}

.switch-slider::before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  inset-inline-start: 3px;
  bottom: 3px;
  background-color: #ffffff;
  border-radius: 50%;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.25);
  transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

[dir="rtl"] .switch-slider::before {
  /* Handled by inset-inline-start and transform */
}

.switch-input:focus-visible + .switch-slider {
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.25);
}

.switch-input:checked + .switch-slider {
  background-color: var(--wpgate-primary);
}

.switch-input:checked + .switch-slider::before {
  transform: translateX(20px);
}

[dir="rtl"] .switch-input:checked + .switch-slider::before {
  transform: translateX(-20px);
}
</style>
