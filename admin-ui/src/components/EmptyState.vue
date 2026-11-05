<template>
  <div class="wpgate-empty-state">
    <div class="empty-icon-wrap">
      <slot name="icon">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="8" x2="12" y2="12"></line>
          <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
      </slot>
    </div>
    <h4 class="empty-title">{{ title }}</h4>
    <p v-if="description" class="empty-description">{{ description }}</p>
    <div v-if="$slots.action" class="empty-action">
      <slot name="action"></slot>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true,
  },
  description: {
    type: String,
    default: '',
  },
});
</script>

<style scoped>
.wpgate-empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 48px 24px;
}

.empty-icon-wrap {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background-color: var(--wpgate-surface-alt);
  color: var(--wpgate-text-muted);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 16px;
  border: 1px solid var(--wpgate-border);
}

.empty-title {
  margin: 0 0 6px 0;
  font-size: 1.05rem;
  font-weight: 600;
  color: var(--wpgate-text);
}

.empty-description {
  margin: 0 0 16px 0;
  font-size: 0.88rem;
  color: var(--wpgate-text-muted);
  max-width: 420px;
  line-height: 1.5;
}

.empty-action {
  margin-top: 8px;
}
</style>
