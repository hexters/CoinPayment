<script setup lang="ts">
import { onMounted } from 'vue'
import { withBase } from 'vitepress'

onMounted(() => {
  const els = document.querySelectorAll<HTMLElement>('.cp-landing .reveal')
  if (!('IntersectionObserver' in window)) {
    els.forEach((e) => e.classList.add('in'))
    return
  }
  const io = new IntersectionObserver(
    (entries) => {
      for (const e of entries) {
        if (e.isIntersecting) {
          e.target.classList.add('in')
          io.unobserve(e.target)
        }
      }
    },
    { threshold: 0.12, rootMargin: '0px 0px -8% 0px' },
  )
  els.forEach((e) => io.observe(e))
})

const repo = 'https://github.com/hexters/CoinPayment'
const buy = 'https://buymeacoffee.com/hexters/e/545129'
const listing = 'https://www.coinpayments.net/apidoc-code'
const packagist = 'https://packagist.org/packages/hexters/coinpayment'
const actions = 'https://github.com/hexters/CoinPayment/actions/workflows/tests.yml'

const stats = [
  { n: '60K+', l: 'installs on Packagist', href: packagist + '/stats' },
  { n: '11·12·13', l: 'Laravel versions', href: packagist },
  { n: '33', l: 'passing tests', href: actions },
  { n: '$9.9', l: 'one-time license', href: withBase('/pricing') },
]

const features = [
  {
    t: 'Livewire checkout',
    d: 'A hosted-style checkout your buyer never has to leave. Coin search, QR code, and a copy-to-clipboard address.',
    p: 'M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2M3 7v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7M3 7h18M7 15h4',
  },
  {
    t: 'Real-time status',
    d: 'The payment modal polls on its own and shows a live countdown, so the buyer sees waiting, confirming, and complete without a refresh.',
    p: 'M12 7v5l3 2M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18Z',
  },
  {
    t: 'Knows partial payments',
    d: 'If a buyer sends too little, it shows exactly how much is left instead of sitting on a confusing "waiting" forever.',
    p: 'M12 8v4m0 4h.01M3 12a9 9 0 1 1 18 0 9 9 0 0 1-18 0Z',
  },
  {
    t: 'Admin panel built in',
    d: 'A standalone, gate-protected dashboard: balances with fiat values, withdrawals, and a transactions table you can search, filter and sort.',
    p: 'M4 5h16M4 12h16M4 19h10',
  },
  {
    t: 'IPN and a sync command',
    d: 'IPN callbacks are verified with HMAC-SHA512. When IPN cannot reach you, a scheduled command keeps statuses in sync.',
    p: 'M4 4v6h6M20 20v-6h-6M5.5 9a7 7 0 0 1 12-2.5L20 9M18.5 15a7 7 0 0 1-12 2.5L4 15',
  },
  {
    t: 'No Node build step',
    d: 'Livewire 3 and Alpine, with Tailwind from a CDN. The pages are standalone and never pull in your app CSS. You only set the colors.',
    p: 'M8 9l3 3-3 3m5 0h3M5 4h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Z',
  },
]
</script>

<template>
  <div class="cp-landing">
    <!-- ======================= HERO ======================= -->
    <section class="hero">
      <video class="bg-video" autoplay muted loop playsinline preload="auto" tabindex="-1" aria-hidden="true">
        <source :src="withBase('/hero-bg.mp4')" type="video/mp4" />
      </video>
      <div class="bg-scrim" aria-hidden="true"></div>
      <div class="hero-bg" aria-hidden="true"></div>
      <div class="hero-grid" aria-hidden="true"></div>
      <div class="hero-grain" aria-hidden="true"></div>

      <div class="wrap hero-inner">
        <div class="hero-copy">
          <div class="badges">
            <a class="eyebrow" :href="listing" target="_blank" rel="noopener">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
              Recommended by CoinPayments
            </a>
            <span class="eyebrow plain">Legacy v1 API</span>
          </div>

          <h1>Crypto payments in Laravel,<br><span class="grad">done properly.</span></h1>

          <p class="lede">
            The CoinPayments-recommended Laravel module for the legacy v1 Merchant API. A drop-in
            integration with a Livewire checkout, real-time status, IPN handling, and an admin
            panel. Generate a link, the buyer pays in crypto, your order gets fulfilled.
          </p>

          <div class="cta">
            <a class="btn primary" :href="withBase('/guide/installation')">
              Get started
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
            </a>
            <a class="btn ghost" :href="repo" target="_blank" rel="noopener">
              <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.58 2 12.25c0 4.53 2.87 8.37 6.84 9.73.5.1.68-.22.68-.49v-1.7c-2.78.62-3.37-1.21-3.37-1.21-.46-1.18-1.11-1.5-1.11-1.5-.91-.64.07-.62.07-.62 1 .07 1.53 1.06 1.53 1.06.9 1.57 2.34 1.12 2.91.85.09-.66.35-1.12.63-1.37-2.22-.26-4.55-1.14-4.55-5.05 0-1.12.39-2.03 1.03-2.75-.1-.26-.45-1.3.1-2.7 0 0 .84-.28 2.75 1.05a9.3 9.3 0 0 1 5 0c1.91-1.33 2.75-1.05 2.75-1.05.55 1.4.2 2.44.1 2.7.64.72 1.03 1.63 1.03 2.75 0 3.92-2.34 4.78-4.57 5.04.36.32.68.94.68 1.9v2.82c0 .27.18.59.69.49A10.27 10.27 0 0 0 22 12.25C22 6.58 17.52 2 12 2Z"/></svg>
              Star on GitHub
            </a>
          </div>

          <dl class="stats">
            <a v-for="s in stats" :key="s.l" :href="s.href" class="stat">
              <dt>{{ s.n }}</dt>
              <dd>{{ s.l }}</dd>
            </a>
          </dl>
        </div>

        <div class="hero-shot">
          <div class="frame">
            <span class="dots"><i></i><i></i><i></i></span>
            <img :src="withBase('/payment-page.png')" alt="CoinPayments crypto checkout page" loading="eager" />
          </div>
        </div>
      </div>
    </section>

    <!-- ======================= FEATURES ======================= -->
    <section class="section">
      <div class="wrap">
        <p class="kicker reveal">Everything for accepting crypto</p>
        <h2 class="h2 reveal">A complete checkout, not a thin API wrapper.</h2>
        <div class="cards">
          <article v-for="(f, i) in features" :key="f.t" class="card reveal" :style="{ '--d': i * 0.05 + 's' }">
            <span class="ic">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" :d="f.p"/></svg>
            </span>
            <h3>{{ f.t }}</h3>
            <p>{{ f.d }}</p>
          </article>
        </div>
      </div>
    </section>

    <!-- ======================= SHOWCASE ======================= -->
    <section class="section alt">
      <div class="wrap showcase">
        <div class="showcase-copy reveal">
          <p class="kicker">Three lines to a paid invoice</p>
          <h2 class="h2">Hand back a link. The package does the rest.</h2>
          <p class="muted">
            Build the order on your side, call <code>generatelink()</code>, and redirect.
            The buyer picks a coin and pays; you fulfil the order from a queued job when the
            status hits complete.
          </p>
          <a class="textlink" :href="withBase('/guide/checkout')">Read the checkout guide
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
          </a>
        </div>
        <div class="code reveal" style="--d:.1s">
          <span class="dots"><i></i><i></i><i></i></span>
          <pre><code><span class="c">use</span> Hexters\CoinPayment\CoinPayment;

<span class="v">$transaction</span> = [
  <span class="s">'order_id'</span>     => <span class="fn">uniqid</span>(),
  <span class="s">'amountTotal'</span>  => <span class="n">37.5</span>,
  <span class="s">'buyer_email'</span>  => <span class="s">'buyer@mail.com'</span>,
  <span class="s">'redirect_url'</span> => <span class="fn">url</span>(<span class="s">'/thank-you'</span>),
];

<span class="c">return</span> <span class="fn">redirect</span>(CoinPayment::<span class="fn">generatelink</span>(<span class="v">$transaction</span>));</code></pre>
        </div>
      </div>
    </section>

    <!-- ======================= ADMIN ======================= -->
    <section class="section">
      <div class="wrap admin">
        <div class="admin-shot reveal">
          <div class="frame">
            <span class="dots"><i></i><i></i><i></i></span>
            <img :src="withBase('/des-balance.png')" alt="CoinPayments admin wallet dashboard" loading="lazy" />
          </div>
        </div>
        <div class="admin-copy reveal" style="--d:.08s">
          <p class="kicker">Manage it without leaving your app</p>
          <h2 class="h2">An admin panel that ships in the box.</h2>
          <ul class="checks">
            <li>Wallet dashboard with balances converted to your currency.</li>
            <li>Withdrawals: history, detail, single refresh, and cancel.</li>
            <li>Transactions table with search, filters, sorting, and pagination.</li>
            <li>Gate-protected and fail-closed, with a configurable login redirect.</li>
            <li>Responsive, with a bottom navigation bar on phones.</li>
          </ul>
          <a class="textlink" :href="withBase('/guide/admin')">Explore the admin panel
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
          </a>
        </div>
      </div>
    </section>

    <!-- ======================= TRUST ======================= -->
    <section class="section alt">
      <div class="wrap trust">
        <h2 class="h2 center reveal">Would a careful developer install this?</h2>
        <p class="muted center narrow">
          That is the question I kept asking while building it. The honest answer comes down
          to whether you can trust the money path, and whether the docs respect your time.
        </p>
        <div class="trust-grid reveal">
          <div><h4>CoinPayments recommends it</h4><p>CoinPayments links to this module for Laravel from their own developer docs.</p></div>
          <div><h4>The callback is verified</h4><p>Every IPN is checked with HMAC-SHA512 before it touches your database.</p></div>
          <div><h4>The status path is documented</h4><p>Status codes, the listener payload, and the Eloquent model are all written down.</p></div>
          <div><h4>It is honest about the legacy API</h4><p>This targets the v1 Merchant API, and the docs say so plainly.</p></div>
        </div>

        <p class="verify">Don't take my word for it</p>
        <div class="badges-strip">
          <a :href="packagist" target="_blank" rel="noopener"><img src="https://img.shields.io/packagist/v/hexters/coinpayment?style=flat-square&color=2f6fed&label=packagist" alt="Packagist version" loading="lazy"></a>
          <a :href="packagist + '/stats'" target="_blank" rel="noopener"><img src="https://img.shields.io/packagist/dt/hexters/coinpayment?style=flat-square&color=2f6fed&label=installs" alt="Total installs" loading="lazy"></a>
          <a :href="actions" target="_blank" rel="noopener"><img src="https://img.shields.io/github/actions/workflow/status/hexters/CoinPayment/tests.yml?style=flat-square&color=2f6fed&label=tests&branch=master" alt="Tests status" loading="lazy"></a>
          <a :href="repo" target="_blank" rel="noopener"><img src="https://img.shields.io/github/stars/hexters/CoinPayment?style=flat-square&color=2f6fed" alt="GitHub stars" loading="lazy"></a>
        </div>

        <p class="muted center narrow maint">
          Actively maintained, with v4 being a full rewrite. It targets the CoinPayments legacy v1
          API, which they still document and point Laravel developers to. If that ever changes,
          it will be in the changelog, not a surprise.
        </p>
      </div>
    </section>

    <!-- ======================= PRICING CTA ======================= -->
    <section class="cta-band">
      <video class="bg-video" autoplay muted loop playsinline preload="auto" tabindex="-1" aria-hidden="true">
        <source :src="withBase('/cta-bg.mp4')" type="video/mp4" />
      </video>
      <div class="bg-scrim" aria-hidden="true"></div>
      <div class="cta-bg" aria-hidden="true"></div>
      <div class="wrap cta-inner reveal">
        <p class="kicker light">Free while you build</p>
        <h2 class="h2 light">Go live for a one-time <span class="grad">$9.9</span>.</h2>
        <p class="cta-sub">
          Develop locally with no limits. On production and staging, a single license unlocks
          every coin, withdrawals, and the detail views. No subscription, no account.
        </p>
        <div class="cta">
          <a class="btn primary" :href="buy" target="_blank" rel="noopener">Buy a license</a>
          <a class="btn ghost light" :href="withBase('/pricing')">See what's included</a>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
.wrap { max-width: 1180px; margin: 0 auto; padding: 0 24px; }

/* ---------- animation ---------- */
@keyframes heroIn { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: none; } }
@keyframes floaty { 0%, 100% { transform: rotate(1.2deg) translateY(0); } 50% { transform: rotate(1.2deg) translateY(-9px); } }

.reveal { opacity: 0; transform: translateY(22px); transition: opacity .65s cubic-bezier(.22,1,.36,1), transform .65s cubic-bezier(.22,1,.36,1); transition-delay: var(--d, 0s); }
.reveal.in { opacity: 1; transform: none; }

.hero-copy > * { animation: heroIn .7s cubic-bezier(.22,1,.36,1) both; }
.hero-copy > *:nth-child(1) { animation-delay: .04s; }
.hero-copy > *:nth-child(2) { animation-delay: .12s; }
.hero-copy > *:nth-child(3) { animation-delay: .2s; }
.hero-copy > *:nth-child(4) { animation-delay: .28s; }
.hero-copy > *:nth-child(5) { animation-delay: .36s; }
.hero-shot { animation: heroIn .9s .22s cubic-bezier(.22,1,.36,1) both; }

@media (prefers-reduced-motion: reduce) {
  .reveal, .reveal.in { opacity: 1 !important; transform: none !important; transition: none; }
  .hero-copy > *, .hero-shot, .hero-shot .frame { animation: none !important; }
  .bg-video { display: none; }
}

/* ---------- video backgrounds ---------- */
.bg-video { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; opacity: .4; pointer-events: none; }
.bg-scrim { position: absolute; inset: 0; background: linear-gradient(180deg, rgba(11,18,32,.8), rgba(11,18,32,.9)); }
@media (max-width: 700px) { .bg-video { display: none; } }

/* ---------- hero ---------- */
.hero { position: relative; overflow: hidden; background: var(--cp-ink); color: #fff; }
.hero-bg {
  position: absolute; inset: 0;
  background:
    radial-gradient(60% 60% at 78% -10%, rgba(47,111,237,.55), transparent 60%),
    radial-gradient(50% 50% at 10% 110%, rgba(31,87,196,.4), transparent 60%);
}
.hero-grid {
  position: absolute; inset: 0; opacity: .5;
  background-image:
    linear-gradient(rgba(255,255,255,.05) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,.05) 1px, transparent 1px);
  background-size: 54px 54px;
  mask-image: radial-gradient(70% 70% at 50% 20%, #000 30%, transparent 75%);
}
.hero-grain {
  position: absolute; inset: 0; opacity: .04; mix-blend-mode: overlay;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.8' numOctaves='2'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
}
.hero-inner {
  position: relative; display: grid; grid-template-columns: 1.05fr .95fr; gap: 48px;
  align-items: center; padding-top: 92px; padding-bottom: 96px;
}
.eyebrow {
  display: inline-flex; align-items: center; gap: 8px;
  font-size: 13px; font-weight: 600; color: #cfe0ff; text-decoration: none;
  background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.14);
  padding: 7px 13px; border-radius: 999px; backdrop-filter: blur(6px);
  transition: background .15s;
}
.eyebrow:hover { background: rgba(255,255,255,.13); }
.eyebrow svg { width: 14px; height: 14px; color: #5cf2a6; }
.badges { display: flex; flex-wrap: wrap; gap: 8px; }
.eyebrow.plain { color: #9fb0cf; }

.hero-copy h1 { font-size: clamp(38px, 5.4vw, 66px); font-weight: 900; margin-top: 22px; }
.grad { background: linear-gradient(92deg, #7fb0ff, #2f6fed 60%); -webkit-background-clip: text; background-clip: text; color: transparent; }
.lede { margin-top: 20px; max-width: 30em; font-size: 17px; line-height: 1.6; color: #aebbd4; }

.cta { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 28px; }
.btn {
  display: inline-flex; align-items: center; gap: 8px; height: 48px; padding: 0 22px;
  border-radius: 12px; font-weight: 700; font-size: 15px; text-decoration: none;
  transition: transform .12s ease, background .15s, border-color .15s;
}
.btn svg { width: 18px; height: 18px; }
.btn:active { transform: translateY(1px); }
.btn.primary { background: var(--cp-blue); color: #fff; box-shadow: 0 12px 30px -10px rgba(47,111,237,.7); }
.btn.primary:hover { background: #437dff; }
.btn.ghost { background: rgba(255,255,255,.06); color: #fff; border: 1px solid rgba(255,255,255,.18); }
.btn.ghost:hover { background: rgba(255,255,255,.12); }

.stats { display: flex; flex-wrap: wrap; gap: 30px; margin: 40px 0 0; }
.stat { display: flex; flex-direction: column; text-decoration: none; transition: opacity .15s; }
.stat:hover { opacity: .78; }
.stats dt { font-family: 'Schibsted Grotesk'; font-weight: 800; font-size: 26px; color: #fff; }
.stats dd { margin: 2px 0 0; font-size: 13px; color: #8a98b6; }

.hero-shot { position: relative; }
.frame {
  position: relative; border-radius: 16px; overflow: hidden;
  background: #0f1830; border: 1px solid rgba(255,255,255,.12);
  box-shadow: 0 50px 90px -40px rgba(0,0,0,.8), 0 0 0 1px rgba(255,255,255,.04);
}
.hero-shot .frame { transform: rotate(1.2deg); animation: floaty 7s ease-in-out 1.1s infinite; }
.frame .dots { display: flex; gap: 6px; padding: 11px 14px; background: rgba(255,255,255,.04); border-bottom: 1px solid rgba(255,255,255,.07); }
.frame .dots i { width: 10px; height: 10px; border-radius: 99px; background: rgba(255,255,255,.18); }
.frame img { display: block; width: 100%; }

/* ---------- sections ---------- */
.section { padding: 88px 0; background: #fff; }
.section.alt { background: #f5f7fc; }
.kicker { font-size: 13px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--cp-blue); margin: 0 0 12px; }
.kicker.light { color: #9cc0ff; }
.h2 { font-size: clamp(28px, 3.4vw, 40px); font-weight: 800; color: var(--cp-ink); max-width: 18ch; }
.h2.center { margin: 0 auto; text-align: center; max-width: 22ch; }
.h2.light { color: #fff; }
.muted { color: #51607a; line-height: 1.65; font-size: 16.5px; max-width: 34em; margin-top: 16px; }
.muted.center { margin: 16px auto 0; text-align: center; }
.narrow { max-width: 30em; }

.cards { margin-top: 40px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; }
.card {
  background: #fff; border: 1px solid var(--cp-line); border-radius: 18px; padding: 26px;
  transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
}
.section.alt .card { background: #fff; }
.card:hover { transform: translateY(-4px); box-shadow: 0 24px 50px -28px rgba(15,23,41,.4); border-color: rgba(47,111,237,.3); }
.card .ic { display: grid; place-items: center; width: 46px; height: 46px; border-radius: 13px; background: var(--vp-c-brand-soft); color: var(--cp-blue); }
.card .ic svg { width: 23px; height: 23px; }
.card h3 { font-family: 'Schibsted Grotesk'; font-size: 18px; font-weight: 700; color: var(--cp-ink); margin: 16px 0 8px; }
.card p { color: #51607a; font-size: 14.5px; line-height: 1.6; margin: 0; }

/* showcase */
.showcase { display: grid; grid-template-columns: 1fr 1.1fr; gap: 48px; align-items: center; }
.showcase-copy code { background: var(--vp-c-brand-soft); color: var(--cp-blue-d); padding: 2px 7px; border-radius: 6px; font-size: .92em; }
.code { border-radius: 16px; overflow: hidden; background: var(--cp-ink); box-shadow: 0 34px 60px -34px rgba(15,23,41,.6); }
.code .dots { display: flex; gap: 6px; padding: 13px 16px; background: rgba(255,255,255,.04); border-bottom: 1px solid rgba(255,255,255,.07); }
.code .dots i { width: 11px; height: 11px; border-radius: 99px; background: rgba(255,255,255,.16); }
.code pre { margin: 0; padding: 22px 24px; overflow-x: auto; }
.code code { font-family: var(--vp-font-family-mono); font-size: 13.5px; line-height: 1.85; color: #c7d2e8; }
.code .c { color: #ff8fab; } .code .s { color: #8ee29a; } .code .v { color: #7fb0ff; } .code .fn { color: #ffcf74; } .code .n { color: #d7a4ff; }

.textlink { display: inline-flex; align-items: center; gap: 6px; margin-top: 22px; font-weight: 700; color: var(--cp-blue); text-decoration: none; }
.textlink svg { width: 17px; height: 17px; transition: transform .15s; }
.textlink:hover svg { transform: translateX(3px); }

/* admin */
.admin { display: grid; grid-template-columns: 1.1fr .9fr; gap: 48px; align-items: center; }
.admin-shot .frame { transform: rotate(-1deg); border-color: var(--cp-line); box-shadow: 0 40px 70px -38px rgba(15,23,41,.5); }
.admin-shot .frame .dots { background: #f1f4fa; border-bottom-color: var(--cp-line); }
.admin-shot .frame .dots i { background: rgba(15,23,41,.14); }
.checks { list-style: none; padding: 0; margin: 24px 0 0; display: grid; gap: 12px; }
.checks li { position: relative; padding-left: 30px; color: #364152; line-height: 1.5; font-size: 15.5px; }
.checks li::before {
  content: ''; position: absolute; left: 0; top: 2px; width: 20px; height: 20px; border-radius: 7px;
  background: var(--vp-c-brand-soft) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%232f6fed' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m5 13 4 4L19 7'/%3E%3C/svg%3E") center/13px no-repeat;
}

/* trust */
.trust-grid { margin-top: 44px; display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; max-width: 880px; margin-left: auto; margin-right: auto; }
.trust-grid > div { background: #fff; border: 1px solid var(--cp-line); border-radius: 16px; padding: 24px; }
.trust-grid h4 { font-family: 'Schibsted Grotesk'; font-size: 16px; font-weight: 700; color: var(--cp-ink); margin: 0 0 8px; }
.trust-grid p { margin: 0; color: #51607a; font-size: 14.5px; line-height: 1.55; }
.verify { text-align: center; margin: 44px 0 14px; font-size: 12px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: #8a98b6; }
.badges-strip { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; align-items: center; }
.badges-strip img { height: 22px; display: block; }
.maint { margin-top: 26px !important; }

/* cta band */
.cta-band { position: relative; overflow: hidden; background: var(--cp-ink); color: #fff; padding: 96px 0; }
.cta-bg { position: absolute; inset: 0; background: radial-gradient(50% 80% at 50% 0%, rgba(47,111,237,.5), transparent 60%); }
.cta-inner { position: relative; text-align: center; }
.cta-inner .h2 { margin: 0 auto; }
.cta-inner .cta { justify-content: center; }
.cta-sub { max-width: 38em; margin: 16px auto 30px; color: #aebbd4; line-height: 1.65; font-size: 16.5px; }
.btn.ghost.light { border-color: rgba(255,255,255,.22); }

/* ---------- responsive ---------- */
@media (max-width: 900px) {
  .hero-inner { grid-template-columns: 1fr; gap: 40px; padding-top: 64px; padding-bottom: 64px; }
  .showcase, .admin { grid-template-columns: 1fr; gap: 32px; }
  .admin-shot { order: 2; }
  .cards { grid-template-columns: 1fr; }
  .trust-grid { grid-template-columns: 1fr; }
  .section { padding: 64px 0; }
}
@media (min-width: 640px) and (max-width: 900px) {
  .cards { grid-template-columns: repeat(2, 1fr); }
}
</style>
