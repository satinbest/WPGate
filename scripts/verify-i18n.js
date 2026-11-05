const fs = require('fs');
const path = require('path');
const vm = require('vm');

// 1. Load translations
const i18nPath = path.join(__dirname, '..', 'admin-ui', 'src', 'i18n.js');
const i18nContent = fs.readFileSync(i18nPath, 'utf8');

const sandbox = {
  localStorage: { getItem: () => null, setItem: () => {} },
  window: {},
  ref: (v) => ({ value: v }),
  computed: (fn) => ({ get value() { return fn(); } }),
  exports: {}
};

const match = i18nContent.match(/export const translations = ({[\s\S]*?});\s*export const t/);
if (!match) {
  console.error('FAIL: Could not extract translations object from i18n.js');
  process.exit(1);
}

vm.runInContext('translations = ' + match[1], vm.createContext(sandbox));
const translations = sandbox.translations;

function flattenKeys(obj, prefix = '') {
  let keys = [];
  for (const k of Object.keys(obj)) {
    const full = prefix ? `${prefix}.${k}` : k;
    if (typeof obj[k] === 'object' && obj[k] !== null && !Array.isArray(obj[k])) {
      keys = keys.concat(flattenKeys(obj[k], full));
    } else {
      keys.push(full);
    }
  }
  return keys;
}

const enKeys = new Set(flattenKeys(translations.en));
const faKeys = new Set(flattenKeys(translations.fa));

console.log(`Checking dictionary parity... (EN: ${enKeys.size}, FA: ${faKeys.size})`);

let parityFail = false;
for (const k of enKeys) {
  if (!faKeys.has(k)) {
    console.error(`FAIL: Key "${k}" is in English but missing in Persian`);
    parityFail = true;
  }
}
for (const k of faKeys) {
  if (!enKeys.has(k)) {
    console.error(`FAIL: Key "${k}" is in Persian but missing in English`);
    parityFail = true;
  }
}

if (parityFail) {
  process.exit(1);
}
console.log('PASS: 100% Translation Dictionary Parity (0 missing keys).');

// 2. Scan all Vue & JS files in admin-ui/src for t('...') calls
console.log('Checking all t(\'...\') calls across Vue components...');
const srcDir = path.join(__dirname, '..', 'admin-ui', 'src');

function getAllFiles(dir) {
  let results = [];
  const list = fs.readdirSync(dir);
  list.forEach(file => {
    const fullPath = path.join(dir, file);
    const stat = fs.statSync(fullPath);
    if (stat && stat.isDirectory()) {
      results = results.concat(getAllFiles(fullPath));
    } else if (file.endsWith('.vue') || file.endsWith('.js')) {
      results.push(fullPath);
    }
  });
  return results;
}

const allFiles = getAllFiles(srcDir);
let missingInDict = false;

allFiles.forEach(file => {
  const content = fs.readFileSync(file, 'utf8');
  // Use word boundary \bt\( so emit('...') is not matched
  const tCalls = content.match(/\bt\(['"]([^'"]+)['"]\)/g) || [];
  tCalls.forEach(call => {
    const key = call.match(/\bt\(['"]([^'"]+)['"]\)/)[1];
    // Exclude dynamic keys like tabs.${...} or dynamic interpolation or '.' in split
    if (key.includes('${') || key.includes('+') || key === '.') return;
    if (!enKeys.has(key)) {
      console.error(`FAIL in ${path.basename(file)}: t('${key}') does not exist in translation dictionary!`);
      missingInDict = true;
    }
  });
});

if (missingInDict) {
  process.exit(1);
}
console.log('PASS: All component t(\'...\') calls resolve to valid dictionary keys.');
console.log('--- LOCALIZATION INTEGRITY VERIFICATION SUCCESSFUL ---');
