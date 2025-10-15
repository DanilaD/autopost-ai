import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import fs from 'fs'

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    server: {
        host: 'autopost-ai.test',
        port: 3000,
        https: {
            key: fs.readFileSync(
                '/Users/daniladolmatov/.config/valet/Certificates/autopost-ai.test.key'
            ),
            cert: fs.readFileSync(
                '/Users/daniladolmatov/.config/valet/Certificates/autopost-ai.test.crt'
            ),
        },
        strictPort: true,
        hmr: {
            host: 'autopost-ai.test',
            port: 3000,
            protocol: 'wss',
        },
    },
})
