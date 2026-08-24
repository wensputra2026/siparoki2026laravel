const { chromium } = require('playwright');

const baseUrl = process.env.BASE_URL || 'http://127.0.0.1:8000';
const login = process.env.BACKEND_LOGIN || 'admin@siparoki.com';
const password = process.env.BACKEND_PASSWORD || 'admin123';

const essentialRoutes = [
  '/superadmin',
  '/superadmin/dashboard',
  '/superadmin/panduan-hak-akses',
  '/superadmin/profil-paroki',
  '/superadmin/profil-saya',
  '/superadmin/kk-katolik',
  '/superadmin/kk-katolik/create',
  '/superadmin/umat',
  '/superadmin/konten',
  '/superadmin/konten/create',
  '/superadmin/galeri',
  '/superadmin/jadwal-misa',
  '/superadmin/provinsi',
  '/superadmin/kabupaten',
  '/superadmin/kecamatan',
  '/superadmin/desa-kelurahan',
  '/superadmin/backup-database',
];

const allMenuRoutes = [
  ...essentialRoutes,
  '/superadmin/keuskupan',
  '/superadmin/dekenat',
  '/superadmin/paroki',
  '/superadmin/kuasi-paroki',
  '/superadmin/kapela',
  '/superadmin/wilayah',
  '/superadmin/kub',
  '/superadmin/provinsi',
  '/superadmin/kabupaten',
  '/superadmin/kecamatan',
  '/superadmin/desa-kelurahan',
  '/superadmin/direktori-dpp',
  '/superadmin/direktori-katekis',
  '/superadmin/direktori-misdinar',
  '/superadmin/riwayat-pastor',
  '/superadmin/kronik',
  '/superadmin/peran-kategorial',
  '/superadmin/anggota-kategorial',
  '/superadmin/jadwal-misa',
  '/superadmin/statistik',
  '/superadmin/sakramen',
  '/superadmin/pengajuan-sakramen',
  '/superadmin/lapak-produk',
  '/superadmin/surat-masuk',
  '/superadmin/surat-keluar',
  '/superadmin/arsip-digital',
  '/superadmin/rapat-notulen',
  '/superadmin/keuangan',
  '/superadmin/intensi-misa',
  '/superadmin/aset',
  '/superadmin/kategori-konten',
  '/superadmin/konten',
  '/superadmin/konten/create',
  '/superadmin/konten?tipe=Halaman',
  '/superadmin/kegiatan',
  '/superadmin/galeri',
  '/superadmin/download',
  '/superadmin/pengumuman',
  '/superadmin/renungan',
  '/superadmin/pengaturan-aplikasi',
  '/superadmin/security-settings',
  '/superadmin/backup-database',
  '/superadmin/user',
  '/superadmin/role',
  '/admin/master-referensi',
];

const routes = process.env.FULL_BACKEND_AUDIT === '1'
  ? [...new Set(allMenuRoutes)]
  : [...new Set(essentialRoutes)];

const allViewports = [
  { name: 'desktop', width: 1440, height: 900 },
  { name: 'tablet', width: 768, height: 1024 },
  { name: 'mobile', width: 390, height: 844 },
];

const requestedViewports = (process.env.BACKEND_AUDIT_VIEWPORTS || '')
  .split(',')
  .map((item) => item.trim())
  .filter(Boolean);

const viewports = requestedViewports.length
  ? allViewports.filter((viewport) => requestedViewports.includes(viewport.name))
  : allViewports;

function uniq(items) {
  return [...new Set(items)];
}

async function loginPanel(page) {
  await page.goto(new URL('/login', baseUrl).toString(), { waitUntil: 'domcontentloaded' });
  await page.fill('input[name="login"]', login);
  await page.fill('input[name="password"]', password);
  await Promise.all([
    page.waitForURL(/\/(superadmin|paroki|v2|dashboard)/, { timeout: 15000 }),
    page.click('button[type="submit"]'),
  ]);
}

async function collectMetrics(page) {
  return page.evaluate(() => {
    const viewportWidth = window.innerWidth;
    const doc = document.documentElement;
    const body = document.body;
    const shell = document.querySelector('body > div');
    const main = document.querySelector('main');
    const header = document.querySelector('header');
    const footer = document.querySelector('footer');
    const visibleOverflow = [];

    document.querySelectorAll('body *').forEach((el) => {
      if (el.closest('[data-ignore-responsive-audit]')) return;
      const style = getComputedStyle(el);
      if (style.position === 'fixed' && (style.transform !== 'none' || style.visibility === 'hidden')) return;
      const rect = el.getBoundingClientRect();
      if (rect.width < 1 || rect.height < 1) return;
      if (rect.right > viewportWidth + 2 || rect.left < -2) {
        const cls = typeof el.className === 'string' ? el.className.trim().replace(/\s+/g, '.') : '';
        visibleOverflow.push({
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
      viewportWidth,
      documentOverflowX: Math.max(doc.scrollWidth, body ? body.scrollWidth : 0) - viewportWidth,
      shellOverflowX: shell ? Math.round(shell.scrollWidth - shell.clientWidth) : 0,
      mainOverflowX: main ? Math.round(main.scrollWidth - main.clientWidth) : 0,
      headerHeight: header ? Math.round(header.getBoundingClientRect().height) : null,
      footerHeight: footer ? Math.round(footer.getBoundingClientRect().height) : null,
      visibleOverflow: visibleOverflow.slice(0, 10),
    };
  });
}

(async () => {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({ viewport: viewports[0] });
  const page = await context.newPage();
  page.setDefaultTimeout(15000);
  await loginPanel(page);
  await context.storageState({ path: 'tests/.backend-auth-state.json' });
  await context.close();

  const results = [];

  for (const viewport of viewports) {
    const ctx = await browser.newContext({
      viewport,
      storageState: 'tests/.backend-auth-state.json',
    });
    const p = await ctx.newPage();
    p.setDefaultTimeout(15000);

    for (const route of routes) {
      const url = new URL(route, baseUrl).toString();
      const consoleIssues = [];
      p.removeAllListeners('console');
      p.on('console', (msg) => {
        if (msg.type() === 'error') {
          const text = msg.text();
          if (!text.includes('A listener indicated an asynchronous response')) {
            consoleIssues.push(text.slice(0, 220));
          }
        }
      });

      let status = null;
      let error = null;
      let metrics = {};

      try {
        const response = await p.goto(url, { waitUntil: 'domcontentloaded', timeout: 20000 });
        status = response ? response.status() : null;
        await p.waitForTimeout(700);
        metrics = await collectMetrics(p);
      } catch (err) {
        error = err.message;
      }

      const overflow = Math.max(metrics.documentOverflowX || 0, metrics.shellOverflowX || 0);
      results.push({
        route,
        viewport: viewport.name,
        width: viewport.width,
        status,
        ok: !error && status && status < 400 && overflow <= 2 && consoleIssues.length === 0,
        error,
        ...metrics,
        consoleIssues: uniq(consoleIssues).slice(0, 5),
      });
    }

    await ctx.close();
  }

  await browser.close();

  const failures = results.filter((r) => !r.ok);
  console.log(JSON.stringify({
    generatedAt: new Date().toISOString(),
    total: results.length,
    failures,
  }, null, 2));

  if (failures.length) process.exitCode = 1;
})();
