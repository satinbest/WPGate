const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

console.log('Testing WPGate-MU-Plugin-v0.1.1.zip integrity and extraction...');
const tempDir = path.join(__dirname, '..', 'tests', 'scratch', 'mu_test_unzip');
if (fs.existsSync(tempDir)) fs.rmSync(tempDir, { recursive: true, force: true });
fs.mkdirSync(tempDir, { recursive: true });

execSync(`powershell -Command "Expand-Archive -Path WPGate-MU-Plugin-v0.1.1.zip -DestinationPath tests/scratch/mu_test_unzip -Force"`);

const requiredFiles = [
  'wpgate-loader.php',
  'wpgate-core/bootstrap.php',
  'wpgate-core/includes/Context.php',
  'wpgate-core/includes/HttpGuard.php',
  'wpgate-core/includes/Settings.php',
  'wpgate-core/includes/RestController.php',
  'wpgate-core/admin/AdminController.php',
  'wpgate-core/admin/dist/wpgate-admin.js',
  'wpgate-core/admin/dist/wpgate-admin.css',
  'wpgate-core/assets/admin/dist/wpgate-admin.js',
  'wpgate-core/assets/admin/dist/wpgate-admin.css',
  'wpgate-core/assets/vendor/google-fonts/vazirmatn.css',
];

let allOk = true;
requiredFiles.forEach(f => {
  const fp = path.join(tempDir, f);
  if (!fs.existsSync(fp)) {
    console.error('FAIL: Missing ' + f);
    allOk = false;
  } else {
    console.log('OK: ' + f + ' (' + fs.statSync(fp).size + ' bytes)');
  }
});

// PHP Syntax check on unzipped files
console.log('Testing PHP syntax on unzipped loader...');
execSync('php -l tests/scratch/mu_test_unzip/wpgate-loader.php');
execSync('php -l tests/scratch/mu_test_unzip/wpgate-core/bootstrap.php');
console.log('Syntax check passed!');

// Clean up test scratch
fs.rmSync(tempDir, { recursive: true, force: true });

if (!allOk) {
  process.exit(1);
} else {
  console.log('--- PACKAGE INTEGRITY VERIFICATION SUCCESSFUL ---');
}
