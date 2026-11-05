<template>
  <div
    class="wpgate-select-wrapper"
    :class="{
      'is-disabled': disabled,
      'is-focused': isFocused,
      'has-error': error,
      'is-compact': compact,
      'w-full': fullWidth
    }"
  >
    <select
      ref="selectRef"
      class="wpgate-select-control"
      :value="modelValue"
      :disabled="disabled"
      :aria-label="ariaLabel"
      @change="onChange"
      @focus="isFocused = true"
      @blur="isFocused = false"
    >
      <slot>
        <option
          v-for="opt in normalizedOptions"
          :key="opt.value"
          :value="opt.value"
          :disabled="opt.disabled"
        >
          {{ opt.label }}
        </option>
      </slot>
    </select>

    <!-- Custom Dropdown Chevron Indicator (Inline-End) -->
    <span class="select-chevron-indicator" aria-hidden="true">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="6 9 12 15 18 9"></polyline>
      </svg>
    </span>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: '',
  },
  options: {
    type: Array,
    default: () => [],
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  error: {
    type: Boolean,
    default: false,
  },
  compact: {
    type: Boolean,
    default: false,
  },
  fullWidth: {
    type: Boolean,
    default: false,
  },
  ariaLabel: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['update:modelValue', 'change']);

const selectRef = ref(null);
const isFocused = ref(false);

const normalizedOptions = computed(() => {
  return props.options.map((opt) => {
    if (typeof opt === 'object' && opt !== null) {
      return {
        value: opt.value,
        label: opt.label ?? opt.value,
        disabled: !!opt.disabled,
      };
    }
    return {
      value: opt,
      label: opt,
      disabled: false,
    };
  });
});

const onChange = (e) => {
  const val = e.target.value;
  emit('update:modelValue', val);
  emit('change', val);
};

defineExpose({
  focus: () => selectRef.value?.focus(),
  blur: () => selectRef.value?.blur(),
});
</script>

<style scoped>
.wpgate-select-wrapper {
  position: relative;
  display: inline-flex;
  align-items: center;
  vertical-align: middle;
  min-width: 140px;
}

.wpgate-select-wrapper.w-full {
  width: 100%;
}

.wpgate-select-control {
  width: 100%;
  height: 38px;
  padding-inline-start: 12px;
  padding-inline-end: 32px;
  font-size: 13px;
  line-height: 1.4;
  font-family: inherit;
  color: var(--wpgate-text);
  background-color: var(--wpgate-surface);
  border: 1px solid var(--wpgate-border);
  border-radius: var(--wpgate-radius-sm);
  outline: none;
  cursor: pointer;
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.wpgate-select-wrapper.is-compact .wpgate-select-control {
  height: 32px;
  font-size: 12px;
  padding-inline-start: 10px;
  padding-inline-end: 28px;
}

.wpgate-select-control:hover:not(:disabled) {
  border-color: var(--wpgate-text-subtle);
  background-color: var(--wpgate-surface-alt);
}

.wpgate-select-control:focus {
  border-color: var(--wpgate-border-focus);
  box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12);
  background-color: var(--wpgate-surface);
}

[data-theme="dark"] .wpgate-select-control:focus {
  box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.15);
}

.wpgate-select-control:disabled {
  background-color: var(--wpgate-surface-alt);
  color: var(--wpgate-text-muted);
  cursor: not-allowed;
  opacity: 0.65;
}

.wpgate-select-wrapper.has-error .wpgate-select-control {
  border-color: var(--wpgate-danger);
}

.select-chevron-indicator {
  position: absolute;
  inset-inline-end: 10px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: var(--wpgate-text-muted);
  pointer-events: none;
  transition: color 0.15s ease, transform 0.15s ease;
}

.wpgate-select-wrapper.is-focused .select-chevron-indicator {
  color: var(--wpgate-primary);
}

.wpgate-select-control option {
  background-color: var(--wpgate-surface);
  color: var(--wpgate-text);
  padding: 8px 12px;
  font-family: inherit;
}
</style>
