import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';
import { viteStaticCopy } from 'vite-plugin-static-copy';

export default defineConfig({
  build: {
    outDir: 'resources/dist',
    rollupOptions: {
      output: {
        entryFileNames: 'js/[name].js',
        chunkFileNames: 'js/[name].js',
        assetFileNames: 'css/[name].[ext]',
      }
    }
  },
  plugins: [
    laravel({
      input: [
        'resources/css/theme.css',
        'resources/js/codemirror.component.js',
        'resources/js/highlight.js',
      ],
    }),
    viteStaticCopy({
      targets: [
        {
          src: 'resources/images',
          dest: ''
        },
      ],
    }),
  ],
});
