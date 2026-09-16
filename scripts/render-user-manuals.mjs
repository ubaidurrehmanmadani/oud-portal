// Start local headless Chrome with --remote-debugging-port=9238, then run this file.
// Requires Node 22+; no browser package dependencies. Only connects to loopback.
import fs from 'node:fs';
import {execFileSync} from 'node:child_process';
import path from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';
const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const screens = JSON.parse(execFileSync('php', [path.join(root, 'scripts/render-manual-screens.php')], {maxBuffer: 20 * 1024 * 1024}).toString());
const targets = await (await fetch('http://127.0.0.1:9238/json')).json();
const target = targets.find(t => t.type === 'page');
if (!target) throw new Error('Open a page in the local headless browser first.');
const ws = new WebSocket(target.webSocketDebuggerUrl);
await new Promise(r => ws.addEventListener('open', r, {once: true}));
let sequence = 0;
const pending = new Map();
ws.addEventListener('message', event => {
    const message = JSON.parse(event.data);
    if (pending.has(message.id)) {
        pending.get(message.id)(message);
        pending.delete(message.id);
    }
});
const call = (method, params = {}) => new Promise((resolve, reject) => {
    const id = ++sequence;
    const timer = setTimeout(() => { pending.delete(id); reject(new Error(method + ' timed out')); }, 20000);
    pending.set(id, message => { clearTimeout(timer); message.error ? reject(message.error) : resolve(message.result); });
    ws.send(JSON.stringify({id, method, params}));
});
try {
    await call('Page.enable');
    await call('Runtime.enable');
    for (const locale of ['en', 'ar']) {
        const base = path.join(root, 'docs/manuals', `user-manual-${locale}`);
        await call('Page.navigate', {url: pathToFileURL(base + '.html').href});
        await new Promise(r => setTimeout(r, 700));
        await call('Runtime.evaluate', {expression: `(() => { const pages = ${JSON.stringify(screens)}; document.querySelectorAll('iframe[data-screen]').forEach(f => { f.srcdoc = pages[f.dataset.screen]; }); })()`});
        await new Promise(r => setTimeout(r, 2000));
        await call('Runtime.evaluate', {expression: `document.querySelectorAll('iframe[data-screen$="-edit"], iframe[data-screen$="-permissions"]').forEach(f => { f.contentDocument.querySelectorAll('.hero, .card:has(input[name="_method"])').forEach(el => el.remove()); f.contentWindow.scrollTo(0, 0); })`});
        await call('Runtime.evaluate', {expression: `document.querySelectorAll('iframe[data-screen$="-report-reviews"]').forEach(f => { f.contentDocument.querySelectorAll('.hero, form.workspace-filters').forEach(el => el.remove()); f.contentWindow.scrollTo(0, 0); })`});
        await call('Runtime.evaluate', {expression: `document.querySelectorAll('iframe[data-screen$="-targeting"]').forEach(f => { f.contentDocument.querySelector('.hero')?.remove(); f.contentDocument.querySelectorAll('form[method="POST"] .field').forEach(el => { if (!el.querySelector('[id^="target_"]')) el.remove(); }); })`});
        const readiness = await call('Runtime.evaluate', {expression: 'document.readyState === "complete" && Array.from(document.querySelectorAll("iframe[data-screen]")).every(f => f.contentDocument?.readyState === "complete" && f.contentDocument.body.innerText.length > 100)'});
        if (!readiness.result.value) throw new Error(`${locale}: screen HTML not loaded ${JSON.stringify(readiness)}`);
        const overflow = await call('Runtime.evaluate', {expression: 'Array.from(document.querySelectorAll(".page")).some(p => p.scrollHeight > p.clientHeight)'});
        if (overflow.result.value) throw new Error(`${locale}: manual page content overflows`);
        const pdf = await call('Page.printToPDF', {printBackground: true, preferCSSPageSize: true, displayHeaderFooter: false});
        fs.writeFileSync(base + '.pdf', Buffer.from(pdf.data, 'base64'));
        console.log(`${locale}: PDF generated`);
    }
} finally {
    ws.close();
}
