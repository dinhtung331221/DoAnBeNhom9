const port = process.argv[2] || '9334';
const output = process.argv[3] || 'D:/xampp/tmp/tabletap-upload-test.png';
const base = 'http://localhost/DoAnBeNhom9/DoAnBeNhom9/public';
const targets = await fetch(`http://127.0.0.1:${port}/json`).then((response) => response.json());
const target = targets.find((item) => item.type === 'page');
if (!target) throw new Error('Chrome DevTools target not found.');
const socket = new WebSocket(target.webSocketDebuggerUrl);
await new Promise((resolve, reject) => { socket.addEventListener('open', resolve, { once: true }); socket.addEventListener('error', reject, { once: true }); });
let id = 0;
const waiting = new Map();
socket.addEventListener('message', (event) => { const message = JSON.parse(event.data); if (!message.id || !waiting.has(message.id)) return; const job = waiting.get(message.id); waiting.delete(message.id); message.error ? job.reject(new Error(message.error.message)) : job.resolve(message.result); });
function command(method, params = {}) { const requestId = ++id; socket.send(JSON.stringify({ id: requestId, method, params })); return new Promise((resolve, reject) => waiting.set(requestId, { resolve, reject })); }
const delay = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
async function navigate(url) { await command('Page.navigate', { url }); await delay(1200); }
async function login(username, password) { await navigate(`${base}/login`); await command('Runtime.evaluate', { expression: `document.querySelector('[name="username"]').value=${JSON.stringify(username)};document.querySelector('[name="password"]').value=${JSON.stringify(password)};document.querySelector('form').submit();` }); await delay(1400); }
await command('Page.enable'); await command('Runtime.enable'); await command('Network.enable'); await command('DOM.enable');
await login('admin', 'Admin@123');
await navigate(`${base}/admin/qr`);
const qrResult = await command('Runtime.evaluate', { expression: `document.querySelector('.qr-card:not(.is-revoked) .qr-canvas canvas')?.toDataURL('image/png') || ''`, returnByValue: true });
if (!qrResult.result.value) throw new Error('No active QR canvas found.');
await import('node:fs/promises').then((fs) => fs.writeFile(output, Buffer.from(qrResult.result.value.split(',')[1], 'base64')));
await command('Network.clearBrowserCookies');
await login('customer', 'Customer@123');
const documentNode = await command('DOM.getDocument', { depth: -1 });
const input = await command('DOM.querySelector', { nodeId: documentNode.root.nodeId, selector: '#qrUploadInput' });
await command('DOM.setFileInputFiles', { nodeId: input.nodeId, files: [output] });
await command('Runtime.evaluate', { expression: `document.querySelector('#qrUploadInput').dispatchEvent(new Event('change',{bubbles:true}))` });
await delay(2200);
const result = await command('Runtime.evaluate', { expression: `JSON.stringify({url:location.href,menu:!!document.querySelector('#menuGrid'),table:document.querySelector('.table-chip strong')?.textContent||''})`, returnByValue: true });
console.log(result.result.value);
socket.close();
