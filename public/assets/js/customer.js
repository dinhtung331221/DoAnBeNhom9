(function () {
  'use strict';
  var input = document.getElementById('qrUploadInput');
  if (input) input.addEventListener('change', function () {
    var file = input.files && input.files[0];
    if (!file) return;
    if (!window.jsQR) { window.TableTap.toast('Chưa tải được bộ đọc QR. Hãy thử lại.', 'error'); return; }
    var reader = new FileReader();
    reader.onload = function (event) {
      var image = new Image();
      image.onload = function () {
        var max = 1600;
        var scale = Math.min(1, max / Math.max(image.naturalWidth, image.naturalHeight));
        var canvas = document.createElement('canvas');
        canvas.width = Math.max(1, Math.round(image.naturalWidth * scale));
        canvas.height = Math.max(1, Math.round(image.naturalHeight * scale));
        var context = canvas.getContext('2d', { willReadFrequently: true });
        context.drawImage(image, 0, 0, canvas.width, canvas.height);
        var pixels = context.getImageData(0, 0, canvas.width, canvas.height);
        var result = window.jsQR(pixels.data, canvas.width, canvas.height, { inversionAttempts: 'attemptBoth' });
        if (!result || !result.data) { window.TableTap.toast('Không tìm thấy mã QR rõ nét trong ảnh.', 'error'); return; }
        try {
          var parsed = new URL(result.data, window.location.origin);
          var appBase = new URL(window.APP_URL, window.location.origin);
          if (parsed.protocol !== 'http:' && parsed.protocol !== 'https:') throw new Error('invalid');
          if (parsed.origin !== appBase.origin || parsed.pathname !== appBase.pathname.replace(/\/$/, '') + '/menu' || !parsed.searchParams.get('token')) throw new Error('invalid');
          window.location.href = parsed.href;
        } catch (error) { window.TableTap.toast('Đây không phải mã QR bàn hợp lệ của TableTap.', 'error'); }
      };
      image.onerror = function () { window.TableTap.toast('Không thể đọc ảnh QR.', 'error'); };
      image.src = event.target.result;
    };
    reader.readAsDataURL(file);
    input.value = '';
  });
  var helpButton = document.querySelector('[data-show-qr-help]');
  var helpModal = document.getElementById('qrHelpModal');
  if (helpButton && helpModal) helpButton.addEventListener('click', function () { helpModal.showModal(); });
})();
