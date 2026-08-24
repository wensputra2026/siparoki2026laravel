const { chromium } = require('playwright');

const baseUrl = process.env.BASE_URL || 'http://127.0.0.1:8000';

const routes = [
  '/',
  '/profil',
  '/sejarah',
  '/visi-misi',
  '/riwayat-pastor',
  '/kronik',
  '/struktur',
  '/kapela',
  '/peta-kapela',
  '/direktori-dpp',
  '/direktori-katekis',
  '/direktori-misdinar',
  '/pelayan-pastoral',
  '/sambutan',
  '/jadwal-misa',
  '/agenda',
  '/kegiatan',
  '/berita',
  '/artikel',
  '/pengumuman',
  '/renungan',
  '/galeri',
  '/video',
  '/statistik',
  '/kontak',
  '/downloads',
  '/pelayanan',
  '/sakramen',
  '/login',
  '/register',
  '/lupa-password',
];

const viewports = [
  { name: 'desktop', width: 1440, height: 900 },
  { name: 'laptop', width: 1366, height: 768 },
  { name: 'tablet', width: 768, height: 1024 },
  { name: 'mobile', width: 390, height: 844 },
];

function uniq(items) {
  return [...new Set(items)];
}

(async () => {
  const browser = await chromium.launch({ headless: true });
  const results = [];

  for (const viewport of viewports) {
    const page = await browser.newPage({ viewport });
    page.setDefaultTimeout(15000);
    await page.route('**/*', (route) => {
      const request = route.request();
      const url = request.url();
      const type = request.resourceType();
      if (type === 'media' || url.includes('tile.openstreetmap.org') || url.includes('google.com/maps')) {
        return route.abort();
      }
      return route.continue();
    });

    for (const route of routes) {
      const url = new URL(route, baseUrl).toString();
      const consoleIssues = [];

      page.removeAllListeners('console');
      page.on('console', (msg) => {
        if (['error', 'warning'].includes(msg.type())) {
          consoleIssues.push(`${msg.type()}: ${msg.text()}`.slice(0, 180));
        }
      });

      let status = null;
      let error = null;

      try {
        const response = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 12000 });
        status = response ? response.status() : null;
        await page.waitForTimeout(450);
      } catch (err) {
        error = err.message;
      }

      const metrics = error
        ? {}
        : await page.evaluate(() => {
            const doc = document.documentElement;
            const body = document.body;
            const viewportWidth = window.innerWidth;
            const header = document.querySelector('.header');
            const firstMainChild = document.querySelector('main > *');
            const headerRect = header ? header.getBoundingClientRect() : null;
            const firstRect = firstMainChild ? firstMainChild.getBoundingClientRect() : null;
            const overflowing = [];

            document.querySelectorAll('body *').forEach((el) => {
              if (el.closest('.hero-quote-ticker, .page-banner, .leaflet-container')) return;
              const rect = el.getBoundingClientRect();
              if (rect.width < 1 || rect.height < 1) return;
              if (rect.right > viewportWidth + 2 || rect.left < -2) {
                const cls = typeof el.className === 'string' ? el.className.trim().replace(/\s+/g, '.') : '';
                overflowing.push({
                  tag: el.tagName.toLowerCase(),
                  id: el.id || '',
                  cls: cls ? `.${cls}` : '',
                  left: Math.round(rect.left),
                  right: Math.round(rect.right),
                  width: Math.round(rect.width),
                });
              }
            });

            return {
              title: document.title,
              scrollWidth: doc.scrollWidth,
              bodyScrollWidth: body ? body.scrollWidth : null,
              viewportWidth,
              overflowX: Math.max(doc.scrollWidth, body ? body.scrollWidth : 0) - viewportWidth,
              headerTop: headerRect ? Math.round(headerRect.top) : null,
              headerPosition: header ? getComputedStyle(header).position : null,
              firstMainTop: firstRect ? Math.round(firstRect.top) : null,
              overflowing: overflowing.slice(0, 8),
            };
          });

      results.push({
        route,
        viewport: viewport.name,
        width: viewport.width,
        status,
        ok: !error && status && status < 400 && (metrics.overflowX || 0) <= 2,
        error,
        ...metrics,
        consoleIssues: uniq(consoleIssues).slice(0, 5),
      });
    }

    await page.close();
  }

  await browser.close();

  const failures = results.filter((r) => !r.ok || r.consoleIssues.length);
  console.log(JSON.stringify({ generatedAt: new Date().toISOString(), total: results.length, failures }, null, 2));
})();
