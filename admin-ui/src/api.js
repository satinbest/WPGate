/**
 * WPGate REST API Client
 */

const getApiConfig = () => {
  const wpSettings = window.wpgateApiSettings || {};
  const root = wpSettings.root ? wpSettings.root.replace(/\/+$/, '') : '/wp-json';
  const nonce = wpSettings.nonce || '';
  return {
    baseUrl: `${root}/wpgate/v1`,
    nonce,
    version: wpSettings.version || '0.1.0',
    isRtl: Boolean(wpSettings.isRtl),
    locale: wpSettings.locale || 'en_US',
    author: wpSettings.author || 'Hossein Mohammadpour',
    authorUri: wpSettings.authorUri || 'https://hosseinmohammadpour.ir',
    supportEmail: wpSettings.supportEmail || 'info@hosseinmohammadpour.ir',
  };
};

export const api = {
  config: getApiConfig(),

  async request(endpoint, options = {}) {
    const { baseUrl, nonce } = this.config;
    const url = `${baseUrl}${endpoint}`;

    const headers = {
      'Content-Type': 'application/json',
      'X-WP-Nonce': nonce,
      ...(options.headers || {}),
    };

    try {
      const res = await fetch(url, { ...options, headers });
      const data = await res.json();
      if (!res.ok) {
        throw new Error(data.message || `HTTP ${res.status}: ${res.statusText}`);
      }
      return data;
    } catch (err) {
      console.error(`WPGate API Error (${endpoint}):`, err);
      throw err;
    }
  },

  async getSettings() {
    return this.request('/settings', { method: 'GET' });
  },

  async saveSettings(settings) {
    return this.request('/settings', {
      method: 'POST',
      body: JSON.stringify(settings),
    });
  },

  async resetSettings() {
    return this.request('/settings/reset', { method: 'POST' });
  },

  async getLogs(params = {}) {
    const query = new URLSearchParams(params).toString();
    return this.request(`/logs?${query}`, { method: 'GET' });
  },

  async clearLogs() {
    return this.request('/logs/clear', { method: 'POST' });
  },

  async getPresets() {
    return this.request('/presets', { method: 'GET' });
  },

  async getStats() {
    return this.request('/stats', { method: 'GET' });
  },
};
