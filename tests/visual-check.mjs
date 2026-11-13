const port = process.argv[2] || '9333';
const output = process.argv[3] || 'D:/xampp/tmp/tabletap-customer-v2.png';
const width = Number(process.argv[4] || 1440);
const height = Number(process.argv[5] || 900);

const targets = await fetch(`http://127.0.0.1:${port}/json`).then((response) => response.json());
const target = targets.find((item) => item.type === 'page');
if (!target) throw new Error('Chrome DevTools target not found.');

const socket = new WebSocket(target.webSocketDebuggerUrl);
await new Promise((resolve, reject) => {
  socket.addEventListener('open', resolve, { once: true });
  socket.addEventListener('error', reject, { once: true });
});

let id = 0;
const waiting = new Map();
socket.addEventListener('message', (event) => {
  const message = JSON.parse(event.data);
  if (!message.id || !waiting.has(message.id)) return;
  const promise = waiting.get(message.id);
  waiting.delete(message.id);
  if (message.error) promise.reject(new Error(message.error.message));
  else promise.resolve(message.result);
});

function command(method, params = {}) {
  const requestId = ++id;
  socket.send(JSON.stringify({ id: requestId, method, params }));
  return new Promise((resolve, reject) => waiting.set(requestId, { resolve, reject }));
}

function delay(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

await command('Page.enable');
await command('Runtime.enable');
await command('Emulation.setDeviceMetricsOverride', { width, height, deviceScaleFactor: 1, mobile: width <= 500 });
await command('Page.navigate', { url: 'http://localhost/DoAnBeNhom9/DoAnBeNhom9/public/login' });
await delay(1200);
await command('Runtime.evaluate', {
  expression: `document.querySelector('[name="username"]').value='customer';document.querySelector('[name="password"]').value='Customer@123';document.querySelector('form').submit();`,
});
await delay(1800);
const layout = await command('Runtime.evaluate', {
  expression: `JSON.stringify({url:location.href,width:innerWidth,scrollWidth:document.documentElement.scrollWidth,upload:!!document.querySelector('#qrUploadInput')})`,
  returnByValue: true,
});
const capture = await command('Page.captureScreenshot', { format: 'png', captureBeyondViewport: false });
await import('node:fs/promises').then((fs) => fs.writeFile(output, Buffer.from(capture.data, 'base64')));
console.log(layout.result.value);
socket.close();
