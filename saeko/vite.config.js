import { defineConfig } from 'vite'
import obfuscator from 'rollup-plugin-obfuscator'
import { ViteMinifyPlugin } from 'vite-plugin-minify'
import legacy from '@vitejs/plugin-legacy'
import process from 'process';
import { randomBytes } from 'crypto'
import { resolve } from 'node:path'

// https://vite.dev/config/
export default defineConfig(() => {
    const payloadDivToken = randomBytes(4).toString('hex')

    return {
        plugins: [
            {
                name: 'generate-unparsable-div-token',

                // inject define variable before "freezing" by vite
                config() {
                    return {
                        define: {
                            __PAYLOAD_DIV_TOKEN__: JSON.stringify(payloadDivToken),
                        },
                    }
                },

                // generate new asset for further loading in golang
                generateBundle() {
                    this.emitFile({
                        type: 'asset',
                        fileName: 'payload-token.txt',
                        source: payloadDivToken,
                    })
                },
            },

            obfuscator({
                global: false,
                include: '**/v2.mjs',

                options: {
                    compact: true,
                    controlFlowFlattening: true,
                    controlFlowFlatteningThreshold: 1,
                    deadCodeInjection: true,
                    deadCodeInjectionThreshold: 1,
                    // debugProtection: true,
                    // debugProtectionInterval: 1000,
                    disableConsoleOutput: true,
                    identifierNamesGenerator: 'hexadecimal',
                    log: true,
                    numbersToExpressions: true,
                    renameGlobals: false,
                    selfDefending: true,
                    simplify: true,
                    splitStrings: true,
                    splitStringsChunkLength: 5,
                    stringArray: true,
                    stringArrayCallsTransform: true,
                    stringArrayEncoding: ['base64'],
                    stringArrayIndexShift: true,
                    stringArrayRotate: true,
                    stringArrayShuffle: true,
                    stringArrayWrappersCount: 5,
                    stringArrayWrappersChainedCalls: true,
                    stringArrayWrappersParametersMaxCount: 5,
                    stringArrayWrappersType: 'function',
                    stringArrayThreshold: 1,
                    transformObjectKeys: true,
                    unicodeEscapeSequence: true
                }
            }),

            // input https://www.npmjs.com/package/html-minifier-terser options
            ViteMinifyPlugin({
                removeComments: true,
            }),

            legacy({ // copy from anilibria/frontman
                targets: [
                    '> 0.2%', // Browsers with more than 0.2% global usage
                    'not dead', // Excludes outdated, unsupported browsers (e.g. IE 10, Opera Mini)
                    'last 4 versions', // Covers the last 4 major versions of each browser
                    'Firefox ESR', // Enterprise-supported Firefox (long-term support release)

                    'Safari >= 10', // Includes Safari 10+ (for older macOS + LG Smart TVs with webOS 3.x+)
                    'ios_saf >= 11', // iOS Safari 11+ (covers all browsers on iOS due to WebKit restriction)
                    'Samsung >= 5', // Samsung Internet 5+ (Tizen Smart TVs 2017+ and Samsung Android devices)
                    'Chrome >= 53' // Chrome 53+ (used on older Android TVs, Tizen devices, Chromium-based browsers)
                ],

                polyfills: true, // (default) Automatically include polyfills based on target browsers
                // modernPolyfills: true, // (not recomended) Only inject necessary polyfills into the legacy bundle
                renderLegacyChunks: true, // (default) Generate separate legacy chunks with fallback script tags
                additionalLegacyPolyfills: [
                    'regenerator-runtime/runtime' // Required if you use async/await in legacy-compatible code
                ]
            }),
        ],
        define: {
            __COOKIE_ARGS__: JSON.stringify(
                process.env.NODE_ENV === "production" ? "; SameSite=Lax; Secure" : ""
            ),
            __DISABLE_RELOAD__: JSON.stringify(
                process.env.NODE_ENV === "production" ? false : true
            ),
            __APP_VERSION__: JSON.stringify(
                !process.env.APP_VERSION ? "devel" : process.env.APP_VERSION
            ),
            __IDB_VERSION__: JSON.stringify(
                !process.env.APP_JSVERSION ? 1 : (parseInt(process.env.APP_JSVERSION, 10) || 1)
            )
        },
        build: {
            // see plugins.legacy
            // target: "es2015",

            emptyOutDir: false,
            sourcemap: true,

            // minify: false,
            // cssMinify: false,
            minify: 'esbuild',
            cssMinify: 'esbuild',

            esbuild: {
                drop: ['console', 'debugger'],
            },

            rollupOptions: {
                input: {
                    index: resolve(__dirname, 'pages/index.html'),
                    settings: resolve(__dirname, 'pages/settings.html'),
                },
                output: {
                    entryFileNames: 'js/index-[hash].js',
                    chunkFileNames: 'js/chunk-[hash].js',

                    // assetFileNames: `[name].[ext]`
                    assetFileNames: ({ name }) => {
                        if (/\.(gif)$/.test(name ?? '')) {
                            return 'img/[name][extname]';
                        }

                        return '[name][extname]';
                    },
                },
            },
        },
        experimental: {
            renderBuiltUrl(filename) {
                let prefix = '/.within.website/x/cmd/saeko/assets/';
                if (process.env.NODE_ENV === "production") {
                    prefix = 'https://cdn.anilibria.top/.within.website/x/cmd/saeko/assets/';
                }

                return prefix + filename

                // if (type === 'public') {
                //     return 'https://www.domain.com/' + filename
                // } else if (path.extname(hostId) === '.js') {
                //     return {
                //         runtime: `window.__assetsPath(${JSON.stringify(filename)})`
                //     }
                // } else {
                //     return 'https://cdn.domain.com/.within.website/x/cmd/saeko/static' + filename
                // }
            },
        },
    }
})
