(function () {
  'use strict';

  var tableId = document.body.dataset.tableId || '0';
  if (tableId === '0') { return; }
  var storageKey = 'tabletap_cart_' + tableId;
  var cart = loadCart();
  var currentProduct = null;
  var currentQuantity = 1;

  function loadCart() {
    try { var value = JSON.parse(localStorage.getItem(storageKey)); return Array.isArray(value) ? value : []; } catch (error) { return []; }
  }
  function saveCart() { localStorage.setItem(storageKey, JSON.stringify(cart)); updateCounts(); }
  function money(value) { return Number(value).toLocaleString('vi-VN') + 'đ'; }
  function updateCounts() {
    var count = cart.reduce(function (total, item) { return total + Number(item.quantity); }, 0);
    document.querySelectorAll('[data-cart-count]').forEach(function (element) { element.textContent = count; element.hidden = count === 0; });
  }

  document.querySelectorAll('.category-tab').forEach(function (button) {
    button.addEventListener('click', function () {
      document.querySelectorAll('.category-tab').forEach(function (tab) { tab.classList.remove('is-active'); }); button.classList.add('is-active');
      document.querySelectorAll('.menu-item').forEach(function (item) { item.hidden = button.dataset.category !== 'all' && item.dataset.categoryId !== button.dataset.category; });
    });
  });

  var picker = document.getElementById('productPicker');
  document.querySelectorAll('[data-add-product]').forEach(function (button) {
    button.addEventListener('click', function () {
      currentProduct = JSON.parse(button.dataset.addProduct); currentQuantity = 1;
      picker.querySelector('[data-picker-name]').textContent = currentProduct.name; picker.querySelector('[data-qty]').textContent = '1'; picker.querySelector('[name="note"]').value = '';
      var sizes = picker.querySelector('[data-picker-sizes]'); sizes.innerHTML = '';
      currentProduct.sizes.forEach(function (size, index) {
        var label = document.createElement('label'); label.className = 'size-option';
        var input = document.createElement('input'); input.type = 'radio'; input.name = 'size'; input.value = size.id; input.checked = index === 0;
        var display = document.createElement('span'); var strong = document.createElement('strong'); strong.textContent = size.name; display.appendChild(strong); display.appendChild(document.createTextNode(money(size.price)));
        label.appendChild(input); label.appendChild(display); sizes.appendChild(label);
      });
      picker.showModal();
    });
  });
  if (picker) {
    picker.querySelector('[data-qty-minus]').addEventListener('click', function () { currentQuantity = Math.max(1, currentQuantity - 1); picker.querySelector('[data-qty]').textContent = currentQuantity; });
    picker.querySelector('[data-qty-plus]').addEventListener('click', function () { currentQuantity = Math.min(20, currentQuantity + 1); picker.querySelector('[data-qty]').textContent = currentQuantity; });
    picker.querySelector('form').addEventListener('submit', function (event) {
      event.preventDefault();
      var sizeId = Number(new FormData(event.currentTarget).get('size')); var size = currentProduct.sizes.find(function (option) { return option.id === sizeId; }); var note = event.currentTarget.elements.note.value.trim();
      var existing = cart.find(function (item) { return item.product_id === currentProduct.id && item.size_id === size.id && item.note === note; });
      if (existing) { existing.quantity = Math.min(20, existing.quantity + currentQuantity); } else { cart.push({ product_id: currentProduct.id, name: currentProduct.name, image: currentProduct.image, size_id: size.id, size_name: size.name, unit_price: size.price, quantity: currentQuantity, note: note }); }
      saveCart(); picker.close(); window.TableTap.toast('Đã thêm món vào giỏ.');
    });
  }

  function renderCart() {
    var list = document.getElementById('cartList'); if (!list) { return; } list.innerHTML = '';
    if (!cart.length) {
      var empty = document.createElement('div'); empty.className = 'empty-state content-panel'; empty.innerHTML = '<i data-lucide="shopping-bag"></i><h2>Giỏ hàng đang trống</h2><p>Chọn một món ngon từ menu để bắt đầu.</p>';
      var link = document.createElement('a'); link.className = 'button button-primary'; link.href = window.APP_URL + '/menu'; link.textContent = 'Xem menu'; empty.appendChild(link); list.appendChild(empty);
    }
    cart.forEach(function (item, index) {
      var row = document.createElement('article'); row.className = 'cart-row';
      var image = document.createElement('img'); image.src = item.image; image.alt = '';
      var info = document.createElement('div'); var name = document.createElement('h2'); name.textContent = item.name; var description = document.createElement('p'); description.textContent = 'Size ' + item.size_name + (item.note ? ' · ' + item.note : '');
      var bottom = document.createElement('div'); bottom.className = 'cart-row-bottom mini-qty';
      var minus = iconButton('minus', 'Giảm số lượng'); var quantity = document.createElement('span'); quantity.textContent = item.quantity; var plus = iconButton('plus', 'Tăng số lượng'); var remove = iconButton('trash-2', 'Xóa món'); remove.classList.add('danger');
      minus.addEventListener('click', function () { item.quantity--; if (item.quantity < 1) { cart.splice(index, 1); } saveCart(); renderCart(); });
      plus.addEventListener('click', function () { item.quantity = Math.min(20, item.quantity + 1); saveCart(); renderCart(); });
      remove.addEventListener('click', function () { cart.splice(index, 1); saveCart(); renderCart(); });
      bottom.appendChild(minus); bottom.appendChild(quantity); bottom.appendChild(plus); bottom.appendChild(remove); info.appendChild(name); info.appendChild(description); info.appendChild(bottom);
      var price = document.createElement('strong'); price.textContent = money(item.unit_price * item.quantity); row.appendChild(image); row.appendChild(info); row.appendChild(price); list.appendChild(row);
    });
    var subtotal = cart.reduce(function (sum, item) { return sum + item.unit_price * item.quantity; }, 0);
    document.querySelectorAll('[data-cart-subtotal],[data-cart-total]').forEach(function (element) { element.textContent = money(subtotal); });
    var submit = document.getElementById('placeOrder'); if (submit) { submit.disabled = !cart.length; }
    window.TableTap.icons();
  }

  function iconButton(name, label) { var button = document.createElement('button'); button.type = 'button'; button.className = 'icon-button'; button.setAttribute('aria-label', label); button.innerHTML = '<i data-lucide="' + name + '"></i>'; return button; }

  var placeOrder = document.getElementById('placeOrder');
  if (placeOrder) {
    placeOrder.addEventListener('click', function () {
      if (!cart.length) { return; } placeOrder.disabled = true; placeOrder.classList.add('is-loading');
      var items = cart.map(function (item) { return { product_id: item.product_id, size_id: item.size_id, quantity: item.quantity, note: item.note }; });
      window.TableTap.api('orders', { body: { items: items, note: document.getElementById('orderNote').value.trim() } }).then(function (response) {
        cart = []; saveCart(); document.querySelector('[data-order-code]').textContent = response.data.code; document.getElementById('orderSuccess').showModal();
      }).catch(function (error) { window.TableTap.toast(error.message, 'error'); placeOrder.disabled = false; }).finally(function () { placeOrder.classList.remove('is-loading'); });
    });
  }

  document.querySelectorAll('[data-table-request]').forEach(function (button) {
    button.addEventListener('click', function () {
      var type = button.dataset.tableRequest; var message = type === 'yeu_cau_thanh_toan' ? 'Gửi yêu cầu thanh toán đến thu ngân?' : 'Gọi nhân viên đến bàn?';
      if (!window.confirm(message)) { return; }
      button.disabled = true;
      window.TableTap.api('table-requests', { body: { type: type, content: '' } }).then(function (response) { window.TableTap.toast(response.message); }).catch(function (error) { window.TableTap.toast(error.message, 'error'); }).finally(function () { button.disabled = false; });
    });
  });

  updateCounts(); renderCart();
})();
