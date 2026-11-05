<template>
  <Transition name="modal-fade">
    <div v-if="isOpen" class="wpgate-modal-overlay" @click.self="cancel">
      <div class="wpgate-modal-box">
        <div class="wpgate-modal-header">
          <h3 class="wpgate-modal-title">{{ title }}</h3>
          <button type="button" class="wpgate-modal-close" @click="cancel">&times;</button>
        </div>
        <div class="wpgate-modal-body">
          <p>{{ message }}</p>
        </div>
        <div class="wpgate-modal-footer">
          <button type="button" class="wpgate-btn wpgate-btn-secondary" @click="cancel">{{ cancelText }}</button>
          <button type="button" :class="['wpgate-btn', isDanger ? 'wpgate-btn-danger' : 'wpgate-btn-primary']" @click="confirm">{{ confirmText }}</button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref } from 'vue';

const isOpen = ref(false);
const title = ref('');
const message = ref('');
const confirmText = ref('Confirm');
const cancelText = ref('Cancel');
const isDanger = ref(false);
let resolvePromise = null;

const open = (options = {}) => {
  title.value = options.title || 'Confirm Action';
  message.value = options.message || 'Are you sure?';
  confirmText.value = options.confirmText || 'Confirm';
  cancelText.value = options.cancelText || 'Cancel';
  isDanger.value = Boolean(options.isDanger);
  isOpen.value = true;

  return new Promise((resolve) => {
    resolvePromise = resolve;
  });
};

const confirm = () => {
  isOpen.value = false;
  if (resolvePromise) resolvePromise(true);
};

const cancel = () => {
  isOpen.value = false;
  if (resolvePromise) resolvePromise(false);
};

defineExpose({ open });
</script>
