<template>
  <div
    class="wpgate-search-input-wrap"
    :class="{
      'is-disabled': disabled,
      'is-focused': isFocused,
      'has-value': !!modelValue,
      'is-loading': loading
    }"
  >
    <!-- Search Icon (Inline-Start) -->
    <span class="search-icon-box" aria-hidden="true">
      <svg
        v-if="!loading"
        width="15"
        height="15"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        class="search-svg"
      >
        <circle cx="11" cy="11" r="8"></circle>
        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
      </svg>
      <svg
        v-else
        width="15"
        height="15"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2.5"
        class="search-loading-spinner"
      >
        <path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"></path>
      </svg>
    </span>

    <!-- Search Input -->
    <input
      ref="inputRef"
      type="text"
      class="wpgate-search-field"
      :value="modelValue"
      :placeholder="placeholder"
      :disabled="disabled"
      :aria-label="ariaLabel || placeholder"
      @input="onInput"
      @focus="isFocused = true"
      @blur="isFocused = false"
      @keydown.esc="onEsc"
      @keydown.enter="$emit('search', modelValue)"
    />

    <!-- Clear Search Button (Inline-End) -->
    <button
      v-if="modelValue && !disabled && !loading"
      type="button"
      class="search-clear-btn"
      :title="t('common.clear')"
      :aria-label="t('common.clear')"
      @click="onClear"
    >
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <line x1="18" y1="6" x2="6" y2="18"></line>
        <line x1="6" y1="6" x2="18" y2="18"></line>
      </svg>
    </button>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { t } from '../i18n';

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  placeholder: {
    type: String,
    default: '',
  },
  ariaLabel: {
    type: String,
    default: '',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  loading: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update:modelValue', 'search', 'clear']);

const inputRef = ref(null);
const isFocused = ref(false);

const onInput = (e) => {
  emit('update:modelValue', e.target.value);
};

const onClear = () => {
  emit('update:modelValue', '');
  emit('clear');
  if (inputRef.value) {
    inputRef.value.focus();
  }
};

const onEsc = () => {
  if (props.modelValue) {
    onClear();
  }
};

defineExpose({
  focus: () => inputRef.value?.focus(),
  blur: () => inputRef.value?.blur(),
});
</script>

<style scoped>
.wpgate-search-input-wrap {
  position: relative;
  display: inline-flex;
  align-items: center;
  width: 100%;
  max-width: 320px;
}

.search-icon-box {
  position: absolute;
  inset-inline-start: 11px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: var(--wpgate-text-muted);
  pointer-events: none;
  z-index: 1;
}

.search-svg {
  transition: color 0.15s ease;
}

.wpgate-search-input-wrap.is-focused .search-icon-box {
  color: var(--wpgate-primary);
}

.search-loading-spinner {
  animation: wpgate-spin 0.8s linear infinite;
  color: var(--wpgate-primary);
}

.wpgate-search-field {
  width: 100%;
  height: 38px;
  padding-inline-start: 36px;
  padding-inline-end: 34px;
  font-size: 13px;
  line-height: 1.4;
  font-family: inherit;
  color: var(--wpgate-text);
  background-color: var(--wpgate-surface);
  border: 1px solid var(--wpgate-border);
  border-radius: var(--wpgate-radius-sm);
  transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
  outline: none;
}

.wpgate-search-field::placeholder {
  color: var(--wpgate-text-subtle);
  font-size: 12.5px;
}

.wpgate-search-field:hover:not(:disabled) {
  border-color: var(--wpgate-text-subtle);
}

.wpgate-search-field:focus {
  border-color: var(--wpgate-border-focus);
  box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12);
  background-color: var(--wpgate-surface);
}

[data-theme="dark"] .wpgate-search-field:focus {
  box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.15);
}

.wpgate-search-field:disabled {
  background-color: var(--wpgate-surface-alt);
  color: var(--wpgate-text-muted);
  cursor: not-allowed;
  opacity: 0.7;
}

.search-clear-btn {
  position: absolute;
  inset-inline-end: 8px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 22px;
  height: 22px;
  padding: 0;
  border: none;
  background-color: var(--wpgate-surface-alt);
  color: var(--wpgate-text-muted);
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.15s ease;
  z-index: 1;
}

.search-clear-btn:hover {
  background-color: var(--wpgate-border);
  color: var(--wpgate-text);
}

.search-clear-btn:focus-visible {
  outline: 2px solid var(--wpgate-primary);
  outline-offset: 1px;
}
</style>
