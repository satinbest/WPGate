const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const ROOT = path.resolve(__dirname, '..');
const BRANDING_DIR = path.join(ROOT, 'assets', 'branding');
if (!fs.existsSync(BRANDING_DIR)) {
  fs.mkdirSync(BRANDING_DIR, { recursive: true });
}

// 1. Authoritative Version Detection
function getAuthoritativeVersion() {
  const bootstrapPath = path.join(ROOT, 'mu-plugin-package', 'wpgate-core', 'bootstrap.php');
  if (fs.existsSync(bootstrapPath)) {
    const content = fs.readFileSync(bootstrapPath, 'utf8');
    const match = content.match(/define\(\s*['"]WPGATE_CORE_VERSION['"]\s*,\s*['"]([^'"]+)['"]\s*\)/);
    if (match) return match[1];
  }
  const pkgPath = path.join(ROOT, 'admin-ui', 'package.json');
  if (fs.existsSync(pkgPath)) {
    const pkg = JSON.parse(fs.readFileSync(pkgPath, 'utf8'));
    if (pkg.version) return pkg.version;
  }
  return '0.1.5';
}

const VERSION = getAuthoritativeVersion();
console.log(`[Branding Workflow] Authoritative WPGate Version: v${VERSION}`);

// 2. Locate Headless Browser Renderer
function getBrowserExecutable() {
  const candidates = [
    'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
    'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
    'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
    'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe'
  ];
  for (const c of candidates) {
    if (fs.existsSync(c)) return c;
  }
  throw new Error('No compatible headless browser (Edge/Chrome) found for graphic generation.');
}

const BROWSER_PATH = getBrowserExecutable();
console.log(`[Branding Workflow] Rendering engine located: ${BROWSER_PATH}`);

// Helper to render HTML template to PNG
function renderHtmlToPng(htmlContent, outputPath, width, height) {
  const tempHtml = path.join(BRANDING_DIR, `temp-${Date.now()}-${Math.random().toString(36).substr(2, 5)}.html`);
  fs.writeFileSync(tempHtml, htmlContent, 'utf8');

  const fileUrl = 'file:///' + tempHtml.replace(/\\/g, '/');
  const cmd = `"${BROWSER_PATH}" --headless=new --screenshot="${outputPath}" --window-size=${width},${height} --hide-scrollbars --force-device-scale-factor=1 --default-background-color=00000000 "${fileUrl}"`;

  try {
    execSync(cmd, { stdio: 'pipe' });
    console.log(`✓ Rendered: ${path.basename(outputPath)} (${width}x${height}, ${fs.statSync(outputPath).size} bytes)`);
  } finally {
    if (fs.existsSync(tempHtml)) {
      fs.unlinkSync(tempHtml);
    }
  }
}

// -----------------------------------------------------------------------------
// 3. Template: Official Logo (512x512)
// -----------------------------------------------------------------------------
function generateLogoHtml() {
  return `<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    width: 512px;
    height: 512px;
    background: transparent;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  }
  .logo-card {
    width: 480px;
    height: 480px;
    background: radial-gradient(circle at 35% 30%, #1e293b 0%, #0f172a 60%, #070a12 100%);
    border-radius: 112px;
    border: 2px solid rgba(56, 189, 248, 0.25);
    box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.8), 0 0 50px rgba(2, 132, 199, 0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
  }
  .glow-ring {
    position: absolute;
    width: 320px;
    height: 320px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(14, 165, 233, 0.25) 0%, rgba(2, 132, 199, 0.05) 50%, transparent 70%);
    filter: blur(20px);
  }
  .svg-emblem {
    width: 310px;
    height: 310px;
    z-index: 2;
    filter: drop-shadow(0 12px 24px rgba(2, 132, 199, 0.45));
  }
</style>
</head>
<body>
  <div class="logo-card">
    <div class="glow-ring"></div>
    <svg class="svg-emblem" viewBox="0 0 100 100" fill="none">
      <defs>
        <linearGradient id="shieldGrad" x1="18" y1="14" x2="82" y2="86" gradientUnits="userSpaceOnUse">
          <stop offset="0%" stop-color="#38bdf8" />
          <stop offset="50%" stop-color="#0ea5e9" />
          <stop offset="100%" stop-color="#0284c7" />
        </linearGradient>
        <linearGradient id="innerGrad" x1="28" y1="26" x2="72" y2="74" gradientUnits="userSpaceOnUse">
          <stop offset="0%" stop-color="#1e293b" />
          <stop offset="100%" stop-color="#0f172a" />
        </linearGradient>
      </defs>
      <!-- Outer Gateway Shield -->
      <path d="M50 12L84 30.5V69.5L50 88L16 69.5V30.5L50 12Z" stroke="url(#shieldGrad)" stroke-width="5" stroke-linejoin="round" fill="none" />
      <!-- Inner Gateway Core -->
      <path d="M50 25L73 37.5V62.5L50 75L27 62.5V37.5L50 25Z" fill="url(#innerGrad)" stroke="#38bdf8" stroke-width="3" stroke-linejoin="round" />
      <!-- Core Network Matrix Axis -->
      <line x1="50" y1="25" x2="50" y2="75" stroke="#38bdf8" stroke-width="2.5" stroke-dasharray="3.5 3.5" />
      <line x1="27" y1="50" x2="73" y2="50" stroke="#0ea5e9" stroke-width="2" stroke-opacity="0.6" />
      <!-- Central Gateway Node -->
      <circle cx="50" cy="50" r="8" fill="#38bdf8" />
      <circle cx="50" cy="50" r="13" stroke="#38bdf8" stroke-width="2" stroke-opacity="0.5" />
    </svg>
  </div>
</body>
</html>`;
}

// -----------------------------------------------------------------------------
// 4. Template: Official README Banner (1600x600)
// -----------------------------------------------------------------------------
function generateBannerHtml(version) {
  return `<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    width: 1600px;
    height: 600px;
    background: #070a12;
    overflow: hidden;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;
    color: #f8fafc;
    position: relative;
    display: flex;
    align-items: center;
  }

  /* Cyber Grid & Ambient Lighting */
  .grid-backdrop {
    position: absolute;
    inset: 0;
    background-image: 
      linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
      linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
    background-size: 40px 40px;
    mask-image: radial-gradient(ellipse at center, rgba(0,0,0,1) 40%, rgba(0,0,0,0) 80%);
  }

  .glow-left {
    position: absolute;
    top: -120px;
    left: -100px;
    width: 650px;
    height: 650px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(2, 132, 199, 0.22) 0%, transparent 65%);
    filter: blur(50px);
  }

  .glow-right {
    position: absolute;
    bottom: -150px;
    right: 50px;
    width: 750px;
    height: 750px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(14, 165, 233, 0.16) 0%, transparent 65%);
    filter: blur(60px);
  }

  .banner-content {
    position: relative;
    z-index: 10;
    width: 100%;
    padding: 0 85px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 60px;
  }

  /* Left Column: Brand Identity */
  .brand-col {
    max-width: 780px;
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .version-pill-row {
    display: inline-flex;
    align-items: center;
    gap: 10px;
  }

  .version-tag {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(2, 132, 199, 0.12);
    border: 1px solid rgba(56, 189, 248, 0.35);
    padding: 6px 14px;
    border-radius: 9999px;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.05em;
    color: #38bdf8;
    text-transform: uppercase;
  }

  .pulse-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #38bdf8;
    box-shadow: 0 0 10px #38bdf8;
  }

  .sub-tag {
    font-size: 13px;
    font-weight: 600;
    color: #94a3b8;
  }

  .title-row {
    display: flex;
    align-items: center;
    gap: 20px;
  }

  .brand-title {
    font-size: 72px;
    font-weight: 900;
    letter-spacing: -0.035em;
    line-height: 1;
    background: linear-gradient(135deg, #ffffff 30%, #e2e8f0 70%, #94a3b8 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    filter: drop-shadow(0 4px 16px rgba(0,0,0,0.5));
  }

  .brand-subtitle {
    font-size: 26px;
    font-weight: 600;
    color: #38bdf8;
    line-height: 1.3;
    letter-spacing: -0.015em;
  }

  .brand-description {
    font-size: 16px;
    line-height: 1.6;
    color: #94a3b8;
    max-width: 660px;
  }

  .badges-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 10px;
    flex-wrap: wrap;
  }

  .feature-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 7px 14px;
    background: rgba(30, 41, 59, 0.65);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    color: #e2e8f0;
    backdrop-filter: blur(8px);
  }

  .feature-chip svg {
    color: #38bdf8;
    flex-shrink: 0;
  }

  /* Right Column: Sleek UI Mockup Card */
  .preview-col {
    flex-shrink: 0;
    width: 580px;
  }

  .ui-window {
    background: #0f172a;
    border: 1px solid rgba(56, 189, 248, 0.25);
    border-radius: 16px;
    box-shadow: 0 25px 60px -10px rgba(0, 0, 0, 0.8), 0 0 40px rgba(2, 132, 199, 0.2);
    overflow: hidden;
  }

  .window-header {
    background: #1e293b;
    padding: 12px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  }

  .window-dots {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .window-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
  }
  .dot-red { background-color: #ef4444; }
  .dot-yellow { background-color: #f59e0b; }
  .dot-green { background-color: #10b981; }

  .window-title {
    font-size: 12px;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    color: #94a3b8;
  }

  .status-pill-header {
    background: rgba(16, 185, 129, 0.15);
    color: #34d399;
    border: 1px solid rgba(16, 185, 129, 0.3);
    padding: 2px 8px;
    border-radius: 9999px;
    font-size: 11px;
    font-weight: 700;
  }

  .window-body {
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 14px;
  }

  .mock-metric-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
  }

  .mock-card {
    background: rgba(30, 41, 59, 0.5);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 10px;
    padding: 12px 14px;
  }

  .mock-card-label {
    font-size: 11px;
    color: #64748b;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin-bottom: 4px;
  }

  .mock-card-val {
    font-size: 18px;
    font-weight: 700;
    color: #ffffff;
  }

  .mock-card-val.active-mode {
    color: #38bdf8;
    font-size: 14px;
  }

  .mock-log-box {
    background: #070a12;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 8px;
    padding: 12px 14px;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 11px;
    line-height: 1.6;
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .log-line { display: flex; align-items: center; justify-content: space-between; }
  .log-pass { color: #34d399; }
  .log-block { color: #f87171; }
  .log-host { color: #e2e8f0; }
  .log-time { color: #64748b; font-size: 10px; }

  /* Footer bar inside banner */
  .footer-row {
    margin-top: 14px;
    padding-top: 12px;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 12px;
    color: #64748b;
  }

  .footer-author {
    color: #94a3b8;
    font-weight: 500;
  }
</style>
</head>
<body>
  <div class="grid-backdrop"></div>
  <div class="glow-left"></div>
  <div class="glow-right"></div>

  <div class="banner-content">
    <!-- Left Column: Product Identity -->
    <div class="brand-col">
      <div class="version-pill-row">
        <span class="version-tag">
          <span class="pulse-dot"></span>
          Version ${version}
        </span>
        <span class="sub-tag">Powered by Vue 3 &middot; MU Plugin Ready</span>
      </div>

      <div class="title-row">
        <h1 class="brand-title">WPGate</h1>
      </div>

      <h2 class="brand-subtitle">WordPress HTTP Control &amp; Local Asset Manager</h2>

      <p class="brand-description">
        Enterprise-grade standalone security and performance module for WordPress. Intercepts external outbound HTTP queries, enforces strict allowlist firewall rules, caches remote CDN fonts &amp; scripts locally, and ensures uninterrupted operation during international network degradation.
      </p>

      <div class="badges-row">
        <span class="feature-chip">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
          Outbound HTTP Firewall
        </span>
        <span class="feature-chip">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polyline></svg>
          Local CDN Asset Fallback
        </span>
        <span class="feature-chip">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
          Credential Redaction
        </span>
        <span class="feature-chip">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg>
          Timeout Guard (2.5s)
        </span>
      </div>
    </div>

    <!-- Right Column: Interactive UI Window Preview -->
    <div class="preview-col">
      <div class="ui-window">
        <div class="window-header">
          <div class="window-dots">
            <span class="window-dot dot-red"></span>
            <span class="window-dot dot-yellow"></span>
            <span class="window-dot dot-green"></span>
          </div>
          <span class="window-title">wpgate-core &bull; live status</span>
          <span class="status-pill-header">Protected</span>
        </div>

        <div class="window-body">
          <div class="mock-metric-grid">
            <div class="mock-card">
              <div class="mock-card-label">Active Mode</div>
              <div class="mock-card-val active-mode">Strict Allowlist</div>
            </div>
            <div class="mock-card">
              <div class="mock-card-label">Approved Hosts</div>
              <div class="mock-card-val">48 Hosts</div>
            </div>
          </div>

          <div class="mock-log-box">
            <div class="log-line">
              <span class="log-pass">[ALLOWED] cdn.jsdelivr.net &rarr; local mirror</span>
              <span class="log-time">0.4ms</span>
            </div>
            <div class="log-line">
              <span class="log-pass">[ALLOWED] shaparak.ir &rarr; direct gateway</span>
              <span class="log-time">12ms</span>
            </div>
            <div class="log-line">
              <span class="log-block">[BLOCKED] telemetry.elementor.com (Track Guard)</span>
              <span class="log-time">0.1ms</span>
            </div>
            <div class="log-line">
              <span class="log-pass">[REWRITE] fonts.googleapis.com &rarr; Vazirmatn</span>
              <span class="log-time">0.2ms</span>
            </div>
          </div>

          <div class="footer-row">
            <span>Developer: <strong class="footer-author">Hossein Mohammadpour</strong></span>
            <span>github.com/satinbest/WPGate</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>`;
}

// -----------------------------------------------------------------------------
// 5. Template: GitHub Social Preview (1280x640)
// -----------------------------------------------------------------------------
function generateSocialPreviewHtml(version) {
  return `<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    width: 1280px;
    height: 640px;
    background: #070a12;
    overflow: hidden;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;
    color: #f8fafc;
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
  }

  .grid-backdrop {
    position: absolute;
    inset: 0;
    background-image: 
      linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
      linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
    background-size: 32px 32px;
    mask-image: radial-gradient(ellipse at center, rgba(0,0,0,1) 30%, rgba(0,0,0,0) 75%);
  }

  .glow-center {
    position: absolute;
    width: 800px;
    height: 500px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(2, 132, 199, 0.22) 0%, rgba(14, 165, 233, 0.08) 45%, transparent 70%);
    filter: blur(50px);
  }

  .content-wrap {
    position: relative;
    z-index: 10;
    max-width: 980px;
    padding: 0 40px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 18px;
  }

  .logo-box {
    width: 90px;
    height: 90px;
    background: radial-gradient(circle at 35% 30%, #1e293b 0%, #0f172a 70%);
    border-radius: 24px;
    border: 1.5px solid rgba(56, 189, 248, 0.35);
    box-shadow: 0 10px 30px rgba(0,0,0,0.6), 0 0 35px rgba(2, 132, 199, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .logo-box svg {
    width: 58px;
    height: 58px;
  }

  .version-tag {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(2, 132, 199, 0.12);
    border: 1px solid rgba(56, 189, 248, 0.35);
    padding: 5px 14px;
    border-radius: 9999px;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.05em;
    color: #38bdf8;
    text-transform: uppercase;
  }

  .pulse-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background-color: #38bdf8;
    box-shadow: 0 0 8px #38bdf8;
  }

  .main-title {
    font-size: 64px;
    font-weight: 900;
    letter-spacing: -0.035em;
    line-height: 1;
    background: linear-gradient(135deg, #ffffff 40%, #e2e8f0 75%, #94a3b8 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    filter: drop-shadow(0 4px 20px rgba(0,0,0,0.5));
  }

  .subtitle {
    font-size: 24px;
    font-weight: 600;
    color: #38bdf8;
    line-height: 1.3;
    letter-spacing: -0.015em;
  }

  .summary-text {
    font-size: 16px;
    line-height: 1.6;
    color: #94a3b8;
    max-width: 820px;
  }

  .badges-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin-top: 6px;
    flex-wrap: wrap;
  }

  .chip {
    padding: 6px 14px;
    background: rgba(30, 41, 59, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #e2e8f0;
  }

  .footer-meta {
    margin-top: 10px;
    display: flex;
    align-items: center;
    gap: 16px;
    font-size: 13px;
    color: #64748b;
  }

  .footer-meta strong {
    color: #94a3b8;
  }
</style>
</head>
<body>
  <div class="grid-backdrop"></div>
  <div class="glow-center"></div>

  <div class="content-wrap">
    <div class="logo-box">
      <svg viewBox="0 0 100 100" fill="none">
        <path d="M50 12L84 30.5V69.5L50 88L16 69.5V30.5L50 12Z" stroke="#38bdf8" stroke-width="6" fill="none" />
        <path d="M50 25L73 37.5V62.5L50 75L27 62.5V37.5L50 25Z" fill="#1e293b" stroke="#0ea5e9" stroke-width="3" />
        <line x1="50" y1="25" x2="50" y2="75" stroke="#38bdf8" stroke-width="2.5" stroke-dasharray="3.5 3.5" />
        <circle cx="50" cy="50" r="8" fill="#38bdf8" />
      </svg>
    </div>

    <span class="version-tag">
      <span class="pulse-dot"></span>
      Version ${version} &bull; Official Release
    </span>

    <h1 class="main-title">WPGate</h1>
    <h2 class="subtitle">WordPress HTTP Control &amp; Local Asset Manager</h2>

    <p class="summary-text">
      Independent Outbound Firewall &amp; Local Asset Gateway for WordPress &middot; Vue 3 Admin Interface &middot; Autonomous MU Plugin Architecture
    </p>

    <div class="badges-row">
      <span class="chip">Outbound HTTP Firewall</span>
      <span class="chip">Local CDN Asset Caching</span>
      <span class="chip">Vue 3 Dashboard</span>
      <span class="chip">Zero Disk File Logging</span>
      <span class="chip">100% Bilingual (EN/FA)</span>
    </div>

    <div class="footer-meta">
      <span>Architect: <strong>Hossein Mohammadpour</strong></span>
      <span>&bull;</span>
      <span>github.com/satinbest/WPGate</span>
      <span>&bull;</span>
      <span>MIT / GPLv2+ License</span>
    </div>
  </div>
</body>
</html>`;
}

// -----------------------------------------------------------------------------
// 6. Main Generator Function
// -----------------------------------------------------------------------------
function generateAllBranding() {
  console.log('--- Generating High-Resolution WPGate Release Branding ---');

  // 1. Logo (512x512)
  const logoTarget = path.join(BRANDING_DIR, 'wpgate-logo.png');
  renderHtmlToPng(generateLogoHtml(), logoTarget, 512, 512);

  // 2. Banner (1600x600)
  const bannerTarget = path.join(BRANDING_DIR, 'wpgate-banner.png');
  renderHtmlToPng(generateBannerHtml(VERSION), bannerTarget, 1600, 600);

  // 3. Social Preview (1280x640)
  const socialTarget = path.join(BRANDING_DIR, 'wpgate-social-preview.png');
  renderHtmlToPng(generateSocialPreviewHtml(VERSION), socialTarget, 1280, 640);

  // 4. Backward Compatibility Sync in root assets/
  console.log('--- Synchronizing Root Assets for Compatibility ---');
  const rootAssetsDir = path.join(ROOT, 'assets');
  fs.copyFileSync(bannerTarget, path.join(rootAssetsDir, 'banner-github.png'));
  fs.copyFileSync(socialTarget, path.join(rootAssetsDir, 'social-preview.png'));
  fs.copyFileSync(logoTarget, path.join(rootAssetsDir, 'logo.png'));
  console.log('✓ Synchronized legacy asset paths (banner-github.png, social-preview.png, logo.png)');

  console.log('=== All WPGate Release Branding Generated Successfully! ===');
}

generateAllBranding();
