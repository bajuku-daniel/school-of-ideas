import { defineConfig } from 'vite';

export default defineConfig({
  root: 'src',
  publicDir: false,
  base: '/assets/',
  css: {
    preprocessorOptions: {
      scss: {
        quietDeps: true,
        silenceDeprecations: ['import', 'global-builtin', 'color-functions', 'if-function']
      }
    }
  },
  build: {
    outDir: '../public/assets',
    emptyOutDir: true,
    rollupOptions: {
      input: 'src/js/main.js',
      output: {
        entryFileNames: 'main.js',
        assetFileNames: (assetInfo) => {
          if (assetInfo.name?.match(/\.(woff2?|ttf)$/)) {
            return 'fonts/[name][extname]';
          }

          return 'main[extname]';
        }
      }
    }
  }
});
