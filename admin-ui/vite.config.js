import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

export default defineConfig({
  plugins: [vue()],
  build: {
    outDir: resolve(__dirname, '../mu-plugin-package/mu-plugins/wpgate-core/admin/dist'),
    emptyOutDir: true,
    rollupOptions: {
      input: resolve(__dirname, 'src/main.js'),
      output: {
        entryFileNames: 'wpgate-admin.js',
        chunkFileNames: 'chunks/[name].js',
        assetFileNames: (assetInfo) => {
          if (assetInfo.name && assetInfo.name.endsWith('.css')) {
            return 'wpgate-admin.css';
          }
          return 'assets/[name][extname]';
        },
      },
    },
    minify: 'esbuild',
    cssMinify: true,
  },
  define: {
    'process.env.NODE_ENV': JSON.stringify('production'),
  },
});
