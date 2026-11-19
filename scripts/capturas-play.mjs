// scripts/capturas-play.mjs
// Genera capturas de pantalla del sitio real en tamaño de teléfono
// (1080x1920, proporción 9:16 que exige Google Play).
// Uso: SITE_URL=https://... node scripts/capturas-play.mjs
import { chromium } from 'playwright';
import { mkdir } from 'node:fs/promises';

const SITE = process.env.SITE_URL || 'https://simuladords.onrender.com/';
const OUT = process.env.OUT_DIR || 'capturas';
await mkdir(OUT, { recursive: true });

const browser = await chromium.launch(process.env.CHROME_PATH ? { executablePath: process.env.CHROME_PATH, args: ['--no-proxy-server'] } : {});
const context = await browser.newContext({
    viewport: { width: 360, height: 640 },   // x3 = 1080x1920
    deviceScaleFactor: 3,
    isMobile: true,
    hasTouch: true,
    locale: 'es-DO',
    userAgent: 'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Mobile Safari/537.36',
});
const page = await context.newPage();
const shot = (name) => page.screenshot({ path: `${OUT}/${name}.png` });

// El plan gratis de Render puede estar dormido: hasta 2 minutos para despertar
await page.goto(SITE, { waitUntil: 'networkidle', timeout: 180000 });
await page.waitForTimeout(1500);

// 1) Portada
await shot('1-inicio');

// 2) Perfil de ejemplo + ajustes
await page.click('#btn-example');
await page.locator('#ajuste_dificultad').scrollIntoViewIfNeeded();
await page.selectOption('#ajuste_dificultad', 'realista');
await page.evaluate(() => {
    const el = document.querySelector('#ajuste_dificultad').closest('.row');
    const top = el.getBoundingClientRect().top + window.scrollY - 70;
    window.scrollTo(0, top);
});
await page.waitForTimeout(500);
await shot('2-ajustes');

// 3) Entrevista
const voice = page.locator('#voice-toggle');
if (await voice.count()) await voice.uncheck().catch(() => {});
await page.click('#btn-start');

const waitReply = async () => {
    await page.waitForSelector('#chat-container .msg-assistant:not(.typing), #chat-container .decision-card, #chat-container .msg-error', { timeout: 90000 });
    await page.waitForFunction(() => !document.querySelector('#chat-container .typing'), null, { timeout: 90000 });
    await page.waitForTimeout(400);
};
await waitReply();

const answers = [
    'Voy de vacaciones con mi esposo y mis dos hijos a los parques de Orlando, ocho días.',
    'Soy contadora sénior en Distribuidora del Caribe desde marzo de 2017.',
    'Gano 95 mil pesos al mes y tenemos ahorrados unos siete mil dólares para el viaje.',
    'Regresamos el día 8; mis hijos están en la escuela y tengo mi trabajo y mi apartamento aquí.',
];
let i = 0;
for (const answer of answers) {
    if (await page.locator('.decision-card').count()) break;
    await page.fill('#user-input', answer);
    await page.press('#user-input', 'Enter');
    await waitReply();
    i += 1;
    if (i === 2) {
        await page.evaluate(() => document.querySelector('#step-chat').scrollIntoView());
        await page.waitForTimeout(600);
        await shot('3-entrevista');
    }
}

// Pedir la decisión si no llegó sola
for (let tries = 0; tries < 3 && !(await page.locator('.decision-card').count()); tries++) {
    await page.fill('#user-input', 'Eso es todo, oficial. ¿Cuál es su decisión?');
    await page.press('#user-input', 'Enter');
    await waitReply();
}

// 4) Decisión: se agranda el chat para que se vea la tarjeta completa
await page.evaluate(() => {
    const chat = document.querySelector('#chat-container');
    chat.style.height = 'auto';
    chat.style.maxHeight = 'none';
    const card = document.querySelector('.decision-card');
    if (card) window.scrollTo(0, card.getBoundingClientRect().top + window.scrollY - 12);
});
await page.waitForTimeout(800);
await shot('4-decision');

await browser.close();
console.log('Capturas listas en', OUT);
