import { defineConfig } from 'vitepress'

const site = 'https://hexters.github.io/CoinPayment'
const desc =
  'Accept crypto payments in Laravel with a Livewire checkout, real-time payment status, IPN, and a full admin panel. The CoinPayments-recommended Laravel module for the legacy v1 Merchant API.'

export default defineConfig({
  lang: 'en-US',
  title: 'CoinPayments Legacy for Laravel',
  description: desc,
  base: '/CoinPayment/',
  cleanUrls: true,
  lastUpdated: true,
  sitemap: { hostname: site },

  // Add a canonical URL to every page (good for SEO / duplicate-content).
  transformPageData(pageData) {
    const path = pageData.relativePath.replace(/(^|\/)index\.md$/, '$1').replace(/\.md$/, '')
    pageData.frontmatter.head ??= []
    pageData.frontmatter.head.push(['link', { rel: 'canonical', href: `${site}/${path}` }])
  },

  head: [
    ['link', { rel: 'icon', href: '/CoinPayment/favicon.svg', type: 'image/svg+xml' }],
    ['link', { rel: 'preconnect', href: 'https://fonts.googleapis.com' }],
    ['link', { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' }],
    ['link', {
      rel: 'stylesheet',
      href: 'https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700&family=Schibsted+Grotesk:wght@500;700;800;900&family=JetBrains+Mono:wght@400;500;600&display=swap',
    }],
    ['meta', { name: 'theme-color', content: '#2f6fed' }],
    ['meta', { name: 'author', content: 'Asep SS (hexters)' }],
    ['meta', { name: 'keywords', content: 'laravel, coinpayments, crypto payment gateway, bitcoin, livewire, php, cryptocurrency, payment package' }],
    ['meta', { property: 'og:type', content: 'website' }],
    ['meta', { property: 'og:site_name', content: 'CoinPayments for Laravel' }],
    ['meta', { property: 'og:title', content: 'CoinPayments Legacy for Laravel — crypto checkout, done right' }],
    ['meta', { property: 'og:description', content: desc }],
    ['meta', { property: 'og:image', content: site + '/og.png' }],
    ['meta', { property: 'og:url', content: site + '/' }],
    ['meta', { name: 'twitter:card', content: 'summary_large_image' }],
    ['meta', { name: 'twitter:title', content: 'CoinPayments for Laravel — crypto checkout, done right' }],
    ['meta', { name: 'twitter:description', content: desc }],
    ['meta', { name: 'twitter:image', content: site + '/og.png' }],
  ],

  themeConfig: {
    siteTitle: 'CoinPayments · Laravel',

    nav: [
      { text: 'Guide', link: '/guide/introduction', activeMatch: '/guide/' },
      { text: 'Admin', link: '/guide/admin' },
      { text: 'Configuration', link: '/guide/configuration' },
      { text: 'Pricing', link: '/pricing' },
      {
        text: 'v4',
        items: [
          { text: 'Changelog', link: 'https://github.com/hexters/CoinPayment/blob/master/CHANGELOG.md' },
          { text: 'Report an issue', link: 'https://github.com/hexters/CoinPayment/issues' },
          { text: 'Listed by CoinPayments', link: 'https://www.coinpayments.net/apidoc-code' },
        ],
      },
    ],

    sidebar: {
      '/guide/': [
        {
          text: 'Getting started',
          items: [
            { text: 'Introduction', link: '/guide/introduction' },
            { text: 'Installation', link: '/guide/installation' },
            { text: 'Create a payment', link: '/guide/checkout' },
          ],
        },
        {
          text: 'Handling payments',
          items: [
            { text: 'The listener job', link: '/guide/listener' },
            { text: 'IPN callbacks', link: '/guide/ipn' },
            { text: 'Sync without IPN', link: '/guide/sync' },
          ],
        },
        {
          text: 'Admin panel',
          items: [{ text: 'Overview', link: '/guide/admin' }],
        },
        {
          text: 'Reference',
          items: [
            { text: 'Configuration', link: '/guide/configuration' },
            { text: 'Testnet testing', link: '/guide/testing' },
            { text: 'Pricing & license', link: '/pricing' },
          ],
        },
      ],
    },

    socialLinks: [{ icon: 'github', link: 'https://github.com/hexters/CoinPayment' }],
    search: { provider: 'local' },
    editLink: {
      pattern: 'https://github.com/hexters/CoinPayment/edit/master/docs/:path',
      text: 'Edit this page on GitHub',
    },
    footer: {
      message: 'Recommended by CoinPayments. Source-available, with a paid license for production.',
      copyright: '© 2018–2026 Asep SS (hexters)',
    },
  },
})
