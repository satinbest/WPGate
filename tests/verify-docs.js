/**
 * WPGate Documentation Parity & Link Integrity Verifier
 */
const fs = require('fs');
const path = require('path');

const rootDir = path.resolve(__dirname, '..');

const pairs = [
  ['README.md', 'README-FA.md'],
  ['CHANGELOG.md', 'CHANGELOG-FA.md'],
  ['CONTRIBUTING.md', 'CONTRIBUTING-FA.md'],
  ['SECURITY.md', 'SECURITY-FA.md'],
  ['ROADMAP.md', 'ROADMAP-FA.md'],
  ['RELEASE-NOTES.md', 'RELEASE-NOTES-FA.md'],
  ['LICENSE-NOTICE.md', 'LICENSE-NOTICE-FA.md'],
  ['PERFORMANCE-REPORT.md', 'PERFORMANCE-REPORT-FA.md'],
  ['TEST-REPORT.md', 'TEST-REPORT-FA.md'],
  ['docs/en/README.md', 'docs/fa/README.md'],
  ['docs/en/installation-mu-plugin.md', 'docs/fa/installation-mu-plugin.md'],
  ['docs/en/installation-theme-loader.md', 'docs/fa/installation-theme-loader.md'],
  ['docs/en/configuration.md', 'docs/fa/configuration.md'],
  ['docs/en/features.md', 'docs/fa/features.md'],
  ['docs/en/architecture.md', 'docs/fa/architecture.md'],
  ['docs/en/admin-interface.md', 'docs/fa/admin-interface.md'],
  ['docs/en/vue-development.md', 'docs/fa/vue-development.md'],
  ['docs/en/troubleshooting.md', 'docs/fa/troubleshooting.md'],
  ['docs/en/faq.md', 'docs/fa/faq.md'],
  ['docs/en/release-process.md', 'docs/fa/release-process.md'],
  ['docs/en/migration.md', 'docs/fa/migration.md'],
];

let failed = false;

console.log('Verifying documentation pairs...');
pairs.forEach(([en, fa]) => {
  const enPath = path.join(rootDir, en);
  const faPath = path.join(rootDir, fa);

  if (!fs.existsSync(enPath)) {
    console.error(`Missing English document: ${en}`);
    failed = true;
  }
  if (!fs.existsSync(faPath)) {
    console.error(`Missing Persian document: ${fa}`);
    failed = true;
  }
});

console.log('Verifying internal markdown links...');
const docFiles = [
  'README.md', 'README-FA.md',
  'CHANGELOG.md', 'CHANGELOG-FA.md',
  'CONTRIBUTING.md', 'CONTRIBUTING-FA.md',
  'SECURITY.md', 'SECURITY-FA.md',
  'ROADMAP.md', 'ROADMAP-FA.md',
  'RELEASE-NOTES.md', 'RELEASE-NOTES-FA.md',
  'LICENSE-NOTICE.md', 'LICENSE-NOTICE-FA.md',
  'PERFORMANCE-REPORT.md', 'PERFORMANCE-REPORT-FA.md',
  'TEST-REPORT.md', 'TEST-REPORT-FA.md',
  'docs/README.md',
  'docs/en/README.md', 'docs/fa/README.md',
  'docs/en/installation-mu-plugin.md', 'docs/fa/installation-mu-plugin.md',
  'docs/en/installation-theme-loader.md', 'docs/fa/installation-theme-loader.md',
  'docs/en/configuration.md', 'docs/fa/configuration.md',
  'docs/en/features.md', 'docs/fa/features.md',
  'docs/en/architecture.md', 'docs/fa/architecture.md',
  'docs/en/admin-interface.md', 'docs/fa/admin-interface.md',
  'docs/en/vue-development.md', 'docs/fa/vue-development.md',
  'docs/en/troubleshooting.md', 'docs/fa/troubleshooting.md',
  'docs/en/faq.md', 'docs/fa/faq.md',
  'docs/en/release-process.md', 'docs/fa/release-process.md',
  'docs/en/migration.md', 'docs/fa/migration.md',
];

docFiles.forEach(file => {
  const fullPath = path.join(rootDir, file);
  if (!fs.existsSync(fullPath)) return;
  const content = fs.readFileSync(fullPath, 'utf8');
  const dir = path.dirname(fullPath);
  const regex = /\[([^\]]+)\]\(([^)]+)\)/g;
  let match;
  while ((match = regex.exec(content)) !== null) {
    let target = match[2].trim();
    if (target.startsWith('http://') || target.startsWith('https://') || target.startsWith('mailto:') || target.startsWith('#')) {
      continue;
    }
    const targetPathOnly = target.split('#')[0];
    if (!targetPathOnly) continue;
    const resolvedPath = path.resolve(dir, targetPathOnly);
    if (!fs.existsSync(resolvedPath)) {
      console.error(`Broken link in ${file}: [${match[1]}](${target}) -> ${resolvedPath}`);
      failed = true;
    }
  }
});

if (failed) {
  console.error('Documentation verification FAILED!');
  process.exit(1);
} else {
  console.log('Documentation parity and link verification PASSED successfully!');
}
