import { createApp } from 'vue';
import App from './App.vue';
import './assets/styles.css';

const mountEl = document.getElementById('wpgate-admin-app');
if (mountEl) {
  createApp(App).mount(mountEl);
}
