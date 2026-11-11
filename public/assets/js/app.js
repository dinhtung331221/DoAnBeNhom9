(function () {
  'use strict';

  var csrf = document.querySelector('meta[name="csrf-token"]');
  var csrfToken = csrf ? csrf.content : '';

  function icons() {
    if (window.lucide) {
      window.lucide.createIcons({ attrs: { 'stroke-width': 1.8 } });
    }
  }

  function toast(message, type) {
    var region = document.getElementById('toastRegion');
    if (!region) { window.alert(message); return; }
    var node = document.createElement('div');
    node.className = 'toast ' + (type || '');
    node.innerHTML = '<i data-lucide="' + (type === 'error' ? 'circle-alert' : 'circle-check') + '"></i><span></span>';
    node.querySelector('span').textContent = message;
    region.appendChild(node);
    icons();
    window.setTimeout(function () { node.remove(); }, 4000);
  }

  function api(path, options) {
    options = options || {};
    options.method = options.method || 'POST';
    options.headers = options.headers || {};
    options.headers['X-CSRF-Token'] = csrfToken;
    if (options.body && !(options.body instanceof FormData)) {
      options.headers['Content-Type'] = 'application/json';
      options.body = JSON.stringify(options.body);
    }
    return fetch(window.APP_URL + '/api/' + path, options).then(function (response) {
      return response.json().catch(function () { return { success: false, message: 'Phản hồi máy chủ không hợp lệ.' }; })
        .then(function (data) {
          if (!response.ok || !data.success) {
            var firstError = data.errors ? Object.keys(data.errors).map(function (key) { return data.errors[key]; })[0] : null;
            throw new Error(firstError || data.message || 'Không thể thực hiện thao tác.');
          }
          return data;
        });
    });
  }

  document.querySelectorAll('[data-open-modal]').forEach(function (button) {
    button.addEventListener('click', function () {
      var dialog = document.getElementById(button.dataset.openModal);
      if (!dialog) { return; }
      var form = dialog.querySelector('form');
      if (form) { form.reset(); form.querySelectorAll('input[name="id"]').forEach(function (input) { input.value = ''; }); }
      var title = dialog.querySelector('[data-modal-title]');
      if (title) { title.textContent = title.textContent.replace('Sửa', 'Thêm'); }
      dialog.showModal();
    });
  });
  document.querySelectorAll('[data-close-modal]').forEach(function (button) {
    button.addEventListener('click', function () { button.closest('dialog').close(); });
  });

  document.querySelectorAll('[data-api-form]').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      event.preventDefault();
      if (!form.reportValidity()) { return; }
      var button = form.querySelector('[type="submit"]');
      var body = new FormData(form);
      form.querySelectorAll('input[type="checkbox"][name]').forEach(function (checkbox) {
        if (!checkbox.checked) { body.set(checkbox.name, '0'); }
      });
      button.classList.add('is-loading'); button.disabled = true;
      api(form.dataset.apiForm, { body: body }).then(function (data) {
        toast(data.message);
        window.setTimeout(function () { window.location.reload(); }, 450);
      }).catch(function (error) { toast(error.message, 'error'); })
        .finally(function () { button.classList.remove('is-loading'); button.disabled = false; });
    });
  });

  document.querySelectorAll('[data-api-action]').forEach(function (button) {
    button.addEventListener('click', function () {
      if (button.dataset.confirm && !window.confirm(button.dataset.confirm)) { return; }
      button.disabled = true; button.classList.add('is-loading');
      api(button.dataset.apiAction, { body: { id: Number(button.dataset.id) } }).then(function (data) {
        toast(data.message); window.setTimeout(function () { window.location.reload(); }, 400);
      }).catch(function (error) { toast(error.message, 'error'); button.disabled = false; button.classList.remove('is-loading'); });
    });
  });

  document.querySelectorAll('[data-edit-category]').forEach(function (button) {
    button.addEventListener('click', function () {
      var data = JSON.parse(button.dataset.editCategory); var dialog = document.getElementById('categoryModal'); var form = dialog.querySelector('form');
      form.elements.id.value = data.id; form.elements.ten_danh_muc.value = data.ten_danh_muc; form.elements.mo_ta.value = data.mo_ta || ''; form.elements.trang_thai.checked = Number(data.trang_thai) === 1;
      dialog.querySelector('[data-modal-title]').textContent = 'Sửa danh mục'; dialog.showModal();
    });
  });

  document.querySelectorAll('[data-edit-product]').forEach(function (button) {
    button.addEventListener('click', function () {
      var data = JSON.parse(button.dataset.editProduct); var dialog = document.getElementById('productModal'); var form = dialog.querySelector('form');
      form.elements.id.value = data.id; form.elements.ten_san_pham.value = data.ten_san_pham; form.elements.danh_muc_id.value = data.danh_muc_id; form.elements.mo_ta.value = data.mo_ta || ''; form.elements.gia_ban_chinh.value = data.gia_ban_chinh; form.elements.trang_thai.checked = Number(data.trang_thai) === 1;
      (data.sizes || '').split('|').forEach(function (item) { var parts = item.split(':'); var field = form.elements['sizes[' + parts[0] + ']']; if (field) { field.value = parts[2]; } });
      dialog.querySelector('[data-modal-title]').textContent = 'Sửa sản phẩm'; dialog.showModal();
    });
  });

  document.querySelectorAll('[data-edit-table]').forEach(function (button) {
    button.addEventListener('click', function () {
      var data = JSON.parse(button.dataset.editTable); var dialog = document.getElementById('tableModal'); var form = dialog.querySelector('form');
      ['id','ma_ban','ten_ban','khu_vuc','suc_chua','trang_thai','ghi_chu'].forEach(function (key) { if (form.elements[key]) { form.elements[key].value = data[key] || ''; } });
      dialog.querySelector('[data-modal-title]').textContent = 'Sửa thông tin bàn'; dialog.showModal();
    });
  });

  document.querySelectorAll('[data-table-search]').forEach(function (input) {
    input.addEventListener('input', function () {
      var query = input.value.trim().toLocaleLowerCase('vi');
      document.querySelectorAll('#' + input.dataset.tableSearch + ' tbody tr').forEach(function (row) { row.hidden = row.textContent.toLocaleLowerCase('vi').indexOf(query) === -1; });
    });
  });

  var kitchenBoard = document.querySelector('[data-kitchen-board]');
  if (kitchenBoard) {
    var busy = false;
    function refreshKitchen() {
      if (busy || document.hidden) { return; }
      busy = true;
      fetch(window.location.href, { cache: 'no-store' }).then(function (response) {
        return response.ok ? response.text() : '';
      }).then(function (html) {
        var page = new DOMParser().parseFromString(html, 'text/html');
        var board = page.querySelector('[data-kitchen-board]');
        if (board) {
          kitchenBoard.innerHTML = board.innerHTML;
          icons();
        }
      }).catch(function () {}).finally(function () { busy = false; });
    }

    kitchenBoard.addEventListener('click', function (event) {
      var button = event.target.closest('[data-kitchen-status]');
      if (!button || button.disabled) { return; }
      button.disabled = true;
      api('kitchen/status', { body: { item_id: Number(button.dataset.itemId), status: button.dataset.kitchenStatus } })
        .then(function () { window.location.reload(); })
        .catch(function (error) { toast(error.message, 'error'); button.disabled = false; });
    });
    window.setInterval(function () {
      if (!kitchenBoard.querySelector('button:disabled') && !kitchenBoard.contains(document.activeElement)) {
        refreshKitchen();
      }
    }, 5000);
  }

  document.querySelectorAll('.qr-canvas[data-qr-value]').forEach(function (container) {
    if (window.QRCode && container.dataset.qrValue) { new window.QRCode(container, { text: container.dataset.qrValue, width: 168, height: 168, correctLevel: window.QRCode.CorrectLevel.H }); }
  });
  document.querySelectorAll('[data-download-qr]').forEach(function (button) {
    button.addEventListener('click', function () {
      var container = document.querySelector('[data-qr-id="' + button.dataset.downloadQr + '"]'); var canvas = container ? container.querySelector('canvas') : null;
      if (!canvas) { toast('QR chưa thể tải. Vui lòng kiểm tra kết nối thư viện.', 'error'); return; }
      var link = document.createElement('a'); link.download = 'tabletap-qr-' + button.dataset.downloadQr + '.png'; link.href = canvas.toDataURL('image/png'); link.click();
    });
  });

  var sidebarButton = document.querySelector('[data-sidebar-toggle]');
  if (sidebarButton) { sidebarButton.addEventListener('click', function () { document.getElementById('sidebar').classList.toggle('is-open'); }); }
  var current = window.location.pathname.replace(/\/$/, '');
  document.querySelectorAll('.nav-link').forEach(function (link) { if (link.pathname.replace(/\/$/, '') === current) { link.classList.add('is-active'); } });

  window.TableTap = { api: api, toast: toast, icons: icons };
  icons();
})();
