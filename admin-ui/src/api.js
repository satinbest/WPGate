/**
 * WPGate REST API Client
 * Enterprise-grade client for communicating with /wp-json/wpgate/v1
 * Developer: Hossein Mohammadpour <info@hosseinmohammadpour.ir>
 */

const getApiConfig = () => {
  const wpSettings = window.wpgateApiSettings || {};
  const root = wpSettings.root ? wpSettings.root.replace(/\/+$/, '') : '/wp-json';
  const nonce = wpSettings.nonce || '';
  return {
    baseUrl: `${root}/wpgate/v1`,
    nonce,
    version: wpSettings.version || '0.1.3',
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

  /**
   * Retrieve all settings and system diagnostics metadata.
   */
  async getSettings() {
    return this.request('/settings', { method: 'GET' });
  },

  /**
   * Retrieve system diagnostics info directly.
   */
  async getSystemInfo() {
    try {
      const res = await this.getSettings();
      return {
        success: true,
        data: res?.data?.system || {},
      };
    } catch (err) {
      console.warn('WPGate: getSystemInfo fallback triggered:', err);
      return {
        success: false,
        data: {},
      };
    }
  },

  /**
   * Persist updated settings to WordPress database options.
   */
  async saveSettings(settings) {
    return this.request('/settings', {
      method: 'POST',
      body: JSON.stringify(settings),
    });
  },

  /**
   * Reset all settings to factory default configurations.
   */
  async resetSettings() {
    return this.request('/settings/reset', { method: 'POST' });
  },

  /**
   * Fetch paginated diagnostic logs with search and filter queries.
   */
  async getLogs(params = {}) {
    const query = new URLSearchParams(params).toString();
    return this.request(`/logs?${query}`, { method: 'GET' });
  },

  /**
   * Clear all recorded diagnostic logs from the database.
   */
  async clearLogs() {
    return this.request('/logs/clear', { method: 'POST' });
  },

  /**
   * Retrieve domestic payment gateway, SMS, and infrastructure presets.
   */
  async getPresets() {
    return this.request('/presets', { method: 'GET' });
  },

  /**
   * Retrieve real-time operational telemetry and count statistics.
   */
  async getStats() {
    return this.request('/stats', { method: 'GET' });
  },
};
