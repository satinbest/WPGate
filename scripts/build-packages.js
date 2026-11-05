const fs = require('fs');
const path = require('path');
const crypto = require('crypto');
const { execSync } = require('child_process');

const VERSION = '0.1.3';
const ROOT = path.resolve(__dirname, '..');

console.log(`Building WPGate release packages for v${VERSION}...`);

const muZip = path.join(ROOT, `WPGate-MU-Plugin-v${VERSION}.zip`);
const themeZip = path.join(ROOT, `WPGate-Theme-Module-v${VERSION}.zip`);
const completeZip = path.join(ROOT, `WPGate-Complete-Release-v${VERSION}.zip`);

// Remove old zips for this version if present
[muZip, themeZip, completeZip].forEach(z => {
  if (fs.existsSync(z)) fs.unlinkSync(z);
});

// 1. Build MU-Plugin package
console.log('Packaging MU-Plugin package...');
const muDir = path.join(ROOT, 'mu-plugin-package');
execSync(`powershell -Command "Compress-Archive -Path '${muDir}/wpgate-loader.php', '${muDir}/wpgate-core' -DestinationPath '${muZip}' -Force"`);
console.log(`Created: ${path.basename(muZip)} (${fs.statSync(muZip).size} bytes)`);

// 2. Build Theme package
console.log('Packaging Theme Module package...');
const themeDir = path.join(ROOT, 'theme-package');
execSync(`powershell -Command "Compress-Archive -Path '${themeDir}/wpgate-core' -DestinationPath '${themeZip}' -Force"`);
console.log(`Created: ${path.basename(themeZip)} (${fs.statSync(themeZip).size} bytes)`);

// 3. Build Complete Release package
console.log('Packaging Complete Release package...');
const os = require('os');
const stagingDir = path.join(os.tmpdir(), `wpgate-v${VERSION}-${Date.now()}`);
if (fs.existsSync(stagingDir)) fs.rmSync(stagingDir, { recursive: true, force: true });
fs.mkdirSync(stagingDir, { recursive: true });

// Copy essential folders and files to staging
const itemsToCopy = [
  'mu-plugin-package',
  'theme-package',
  'admin-ui',
  'docs',
  'reports',
  'tests',
  'README.md',
  'README-FA.md',
  'RELEASE-NOTES.md',
  'RELEASE-NOTES-FA.md',
  'CHANGELOG.md',
  'CHANGELOG-FA.md',
  'LICENSE',
  'SECURITY.md'
];

itemsToCopy.forEach(item => {
  const src = path.join(ROOT, item);
  const dest = path.join(stagingDir, item);
  if (fs.existsSync(src)) {
    fs.cpSync(src, dest, {
      recursive: true,
      filter: (source) => {
        return !source.includes('node_modules') && !source.includes('.git');
      }
    });
  }
});

execSync(`powershell -Command "Compress-Archive -Path '${stagingDir}/*' -DestinationPath '${completeZip}' -Force"`);
fs.rmSync(stagingDir, { recursive: true, force: true });
console.log(`Created: ${path.basename(completeZip)} (${fs.statSync(completeZip).size} bytes)`);

// 4. Generate SHA-256 Checksums
console.log('Generating SHA-256 checksums...');
const zips = [
  `WPGate-MU-Plugin-v${VERSION}.zip`,
  `WPGate-Theme-Module-v${VERSION}.zip`,
  `WPGate-Complete-Release-v${VERSION}.zip`
];

let checksumLines = [];
zips.forEach(fileName => {
  const filePath = path.join(ROOT, fileName);
  const fileBuffer = fs.readFileSync(filePath);
  const hash = crypto.createHash('sha256').update(fileBuffer).digest('hex');
  checksumLines.push(`${hash}  ${fileName}`);
  console.log(`SHA-256 (${fileName}): ${hash}`);
});

fs.writeFileSync(path.join(ROOT, 'SHA256SUMS.txt'), checksumLines.join('\n') + '\n', 'utf8');
console.log('Updated SHA256SUMS.txt successfully.');
