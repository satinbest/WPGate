<template>
  <Transition name="toast-fade">
    <div v-if="visible" :class="['wpgate-toast', `wpgate-toast-${type}`]">
      <span class="wpgate-toast-icon">
        <svg v-if="type === 'success'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
        <svg v-else-if="type === 'error'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
        <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
      </span>
      <span class="wpgate-toast-text">{{ message }}</span>
      <button type="button" class="wpgate-toast-close" @click="close">&times;</button>
    </div>
  </Transition>
</template>

<script setup>
import { ref } from 'vue';

const visible = ref(false);
const message = ref('');
const type = ref('success');
let timer = null;

const show = (msg, msgType = 'success', duration = 4000) => {
  message.value = msg;
  type.value = msgType;
  visible.value = true;
  if (timer) clearTimeout(timer);
  if (duration > 0) {
    timer = setTimeout(() => {
      visible.value = false;
    }, duration);
  }
};

const close = () => {
  visible.value = false;
  if (timer) clearTimeout(timer);
};

defineExpose({ show, close });
</script>
