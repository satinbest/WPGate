const fs = require('fs');
const path = require('path');
const https = require('https');

const TOKEN = process.env.GITHUB_TOKEN || process.env.GH_TOKEN;
const OWNER = 'satinbest';
const REPO = 'WPGate';

function apiRequest(options, postData = null) {
  return new Promise((resolve, reject) => {
    const req = https.request(options, (res) => {
      let data = '';
      res.on('data', (chunk) => data += chunk);
      res.on('end', () => {
        let json;
        try {
          json = JSON.parse(data);
        } catch (e) {
          json = data;
        }
        if (res.statusCode >= 200 && res.statusCode < 300) {
          resolve({ status: res.statusCode, headers: res.headers, data: json });
        } else {
          reject(new Error(`API Error HTTP ${res.statusCode}: ${typeof json === 'object' ? JSON.stringify(json) : json}`));
        }
      });
    });

    req.on('error', reject);
    if (postData) {
      req.write(postData);
    }
    req.end();
  });
}

function uploadAsset(uploadUrlTemplate, filePath, contentType) {
  return new Promise((resolve, reject) => {
    const fileName = path.basename(filePath);
    const fileStats = fs.statSync(filePath);
    const fileBuffer = fs.readFileSync(filePath);

    // upload_url is like "https://uploads.github.com/repos/satinbest/WPGate/releases/12345/assets{?name,label}"
    const cleanUrl = uploadUrlTemplate.replace(/\{.*\}/, '');
    const targetUrl = new URL(`${cleanUrl}?name=${encodeURIComponent(fileName)}`);

    const options = {
      protocol: targetUrl.protocol,
      hostname: targetUrl.hostname,
      port: 443,
      path: `${targetUrl.pathname}${targetUrl.search}`,
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${TOKEN}`,
        'User-Agent': 'WPGate-Release-Agent',
        'Accept': 'application/vnd.github.v3+json',
        'Content-Type': contentType,
        'Content-Length': fileStats.size,
      }
    };

    const req = https.request(options, (res) => {
      let data = '';
      res.on('data', (chunk) => data += chunk);
      res.on('end', () => {
        let json;
        try {
          json = JSON.parse(data);
        } catch (e) {
          json = data;
        }
        if (res.statusCode >= 200 && res.statusCode < 300) {
          resolve({ status: res.statusCode, data: json });
        } else {
          reject(new Error(`Upload Error HTTP ${res.statusCode} for ${fileName}: ${typeof json === 'object' ? JSON.stringify(json) : json}`));
        }
      });
    });

    req.on('error', reject);
    req.write(fileBuffer);
    req.end();
  });
}

async function run() {
  console.log('Fetching existing releases...');
  const existingReleases = await apiRequest({
    hostname: 'api.github.com',
    path: `/repos/${OWNER}/${REPO}/releases`,
    method: 'GET',
    headers: {
      'Authorization': `Bearer ${TOKEN}`,
      'User-Agent': 'WPGate-Release-Agent',
      'Accept': 'application/vnd.github.v3+json',
    }
  });

  console.log(`Found ${existingReleases.data.length} releases.`);
  let draftRelease = existingReleases.data.find(r => r.tag_name === 'v0.1.1');

  const releaseNotesPath = path.join(__dirname, '..', 'RELEASE-NOTES.md');
  const releaseNotesContent = fs.readFileSync(releaseNotesPath, 'utf8');

  const checksumsPath = path.join(__dirname, '..', 'SHA256SUMS.txt');
  const checksumsContent = fs.readFileSync(checksumsPath, 'utf8');

  const fullReleaseBody = `${releaseNotesContent}\n\n## 🔐 Artifact Integrity (SHA-256)\n\`\`\`text\n${checksumsContent.trim()}\n\`\`\`\n`;

  if (!draftRelease) {
    console.log('Creating Draft Release v0.1.1 on GitHub...');
    const createRes = await apiRequest({
      hostname: 'api.github.com',
      path: `/repos/${OWNER}/${REPO}/releases`,
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${TOKEN}`,
        'User-Agent': 'WPGate-Release-Agent',
        'Accept': 'application/vnd.github.v3+json',
        'Content-Type': 'application/json',
      }
    }, JSON.stringify({
      tag_name: 'v0.1.1',
      target_commitish: 'feature/initial-wpgate-release',
      name: 'WPGate v0.1.1 — Standalone WordPress HTTP Control & Admin UI Fix',
      body: fullReleaseBody,
      draft: true,
      prerelease: false,
    }));

    draftRelease = createRes.data;
    console.log(`Created Draft Release ID: ${draftRelease.id} (URL: ${draftRelease.html_url})`);
  } else {
    console.log(`Draft Release v0.1.1 already exists (ID: ${draftRelease.id}). Updating body...`);
    const updateRes = await apiRequest({
      hostname: 'api.github.com',
      path: `/repos/${OWNER}/${REPO}/releases/${draftRelease.id}`,
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${TOKEN}`,
        'User-Agent': 'WPGate-Release-Agent',
        'Accept': 'application/vnd.github.v3+json',
        'Content-Type': 'application/json',
      }
    }, JSON.stringify({
      name: 'WPGate v0.1.1 — Standalone WordPress HTTP Control & Admin UI Fix',
      body: fullReleaseBody,
      draft: true,
    }));
    draftRelease = updateRes.data;
  }

  const assetsToUpload = [
    { file: path.join(__dirname, '..', 'WPGate-MU-Plugin-v0.1.1.zip'), type: 'application/zip' },
    { file: path.join(__dirname, '..', 'WPGate-Theme-Module-v0.1.1.zip'), type: 'application/zip' },
    { file: path.join(__dirname, '..', 'WPGate-Complete-Release-v0.1.1.zip'), type: 'application/zip' },
    { file: path.join(__dirname, '..', 'SHA256SUMS.txt'), type: 'text/plain' },
  ];

  for (const asset of assetsToUpload) {
    const fileName = path.basename(asset.file);
    console.log(`Processing asset: ${fileName}...`);

    // Check if asset already exists in release
    const existingAsset = (draftRelease.assets || []).find(a => a.name === fileName);
    if (existingAsset) {
      console.log(`Deleting existing asset ${fileName} (ID: ${existingAsset.id})...`);
      await apiRequest({
        hostname: 'api.github.com',
        path: `/repos/${OWNER}/${REPO}/releases/assets/${existingAsset.id}`,
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${TOKEN}`,
          'User-Agent': 'WPGate-Release-Agent',
          'Accept': 'application/vnd.github.v3+json',
        }
      });
      console.log(`Deleted old asset ${fileName}.`);
    }

    console.log(`Uploading ${fileName} (${fs.statSync(asset.file).size} bytes)...`);
    const uploadRes = await uploadAsset(draftRelease.upload_url, asset.file, asset.type);
    console.log(`Uploaded ${fileName} successfully (Asset ID: ${uploadRes.data.id}).`);
  }

  console.log('--- Draft Release Process Completed Successfully! ---');
  console.log(`Draft URL: ${draftRelease.html_url}`);
}

run().catch(err => {
  console.error('Failure:', err);
  process.exit(1);
});
