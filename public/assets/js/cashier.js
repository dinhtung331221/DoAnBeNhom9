(function () {
  'use strict';
  var sessions = Array.isArray(window.CASHIER_SESSIONS) ? window.CASHIER_SESSIONS : [];
  var selected = null;
  var quote = null;
  function money(value) { return Number(value || 0).toLocaleString('vi-VN') + 'đ'; }
  function text(selector, value) { var node = document.querySelector(selector); if (node) node.textContent = value; }
  function selectSession(id) {
    selected = sessions.find(function (session) { return Number(session.session_id) === Number(id); });
    if (!selected) return;
    quote = { subtotal: Number(selected.invoice_subtotal), discount: Number(selected.discount), total: Number(selected.invoice_total), paid: Number(selected.paid_amount), remaining: Number(selected.remaining), promotion: null, promotion_code: selected.promotion_code, invoice_status: selected.invoice_status };
    document.querySelectorAll('[data-session-id]').forEach(function (button) { button.classList.toggle('is-active', Number(button.dataset.sessionId) === Number(id)); });
    document.querySelector('[data-cashier-empty]').classList.add('is-hidden');
    document.querySelector('[data-cashier-content]').classList.remove('is-hidden');
    text('[data-bill-session]', selected.ma_phien);
    text('[data-bill-table]', selected.ten_ban);
    text('[data-bill-meta]', (selected.khu_vuc || 'Chưa phân khu') + ' · ' + selected.order_count + ' đơn · mở lúc ' + new Date(selected.thoi_gian_bat_dau.replace(' ', 'T')).toLocaleTimeString('vi-VN', {hour:'2-digit', minute:'2-digit'}));
    var body = document.querySelector('[data-bill-items]'); body.innerHTML = '';
    selected.items.forEach(function (item) {
      var row = document.createElement('tr');
      row.innerHTML = '<td><strong></strong><small></small></td><td></td><td></td><td><strong></strong></td>';
      row.children[0].querySelector('strong').textContent = item.ten_san_pham_snapshot;
      row.children[0].querySelector('small').textContent = (item.ten_size_snapshot ? 'Size ' + item.ten_size_snapshot : '') + (item.ghi_chu ? ' · ' + item.ghi_chu : '');
      row.children[1].textContent = item.so_luong; row.children[2].textContent = money(item.don_gia); row.children[3].querySelector('strong').textContent = money(item.thanh_tien); body.appendChild(row);
    });
    document.querySelector('[data-promotion-code]').value = selected.promotion_code || '';
    document.querySelector('[data-promotion-code]').disabled = quote.paid > 0;
    document.querySelector('[data-apply-promotion]').disabled = quote.paid > 0;
    text('[data-promotion-result]', quote.paid > 0 ? 'Hóa đơn đã thanh toán một phần; khuyến mãi được giữ nguyên.' : 'Nhập mã nếu khách có khuyến mãi.');
    text('[data-bill-status]', quote.invoice_status === 'partially_paid' ? 'Thanh toán một phần' : 'Chưa thanh toán');
    updateTotals();
  }
  function updateTotals() {
    text('[data-bill-subtotal]', money(quote.subtotal)); text('[data-bill-discount]', '-' + money(quote.discount)); text('[data-bill-paid]', money(quote.paid || 0)); text('[data-bill-total]', money(quote.remaining === undefined ? quote.total : quote.remaining));
    document.querySelector('[data-payment-amount]').value = Math.round(quote.remaining === undefined ? quote.total : quote.remaining);
  }
  document.querySelectorAll('[data-session-id]').forEach(function (button) { button.addEventListener('click', function () { selectSession(button.dataset.sessionId); }); });
  var search = document.querySelector('[data-cashier-search]');
  if (search) search.addEventListener('input', function () { var query = search.value.trim().toLocaleLowerCase('vi'); document.querySelectorAll('[data-session-id]').forEach(function (button) { button.hidden = button.textContent.toLocaleLowerCase('vi').indexOf(query) === -1; }); });
  var apply = document.querySelector('[data-apply-promotion]');
  if (apply) apply.addEventListener('click', function () {
    if (!selected) return; apply.disabled = true;
    var code = document.querySelector('[data-promotion-code]').value.trim();
    window.TableTap.api('cashier/quote', { body: { session_id: selected.session_id, promotion_code: code } }).then(function (response) {
      quote = response.data; updateTotals(); text('[data-promotion-result]', quote.promotion ? quote.promotion.ten_khuyen_mai + ' · giảm ' + money(quote.discount) : 'Không áp dụng mã khuyến mãi.'); window.TableTap.toast(response.message);
    }).catch(function (error) { window.TableTap.toast(error.message, 'error'); text('[data-promotion-result]', error.message); }).finally(function () { apply.disabled = false; });
  });
  var pay = document.querySelector('[data-confirm-payment]');
  if (pay) pay.addEventListener('click', function () {
    if (!selected) return;
    var method = document.querySelector('[name="payment_method"]:checked').value;
    var amount = Number(document.querySelector('[data-payment-amount]').value);
    if (!amount || amount <= 0) { window.TableTap.toast('Nhập số tiền thanh toán hợp lệ.', 'error'); return; }
    if (!window.confirm('Xác nhận thu ' + money(amount) + ' cho ' + selected.ten_ban + '?')) return;
    pay.disabled = true; pay.classList.add('is-loading');
    window.TableTap.api('cashier/pay', { body: { session_id: selected.session_id, promotion_code: document.querySelector('[data-promotion-code]').value.trim(), method: method, amount: amount, note: document.querySelector('[data-payment-note]').value.trim() } }).then(function (response) {
      window.TableTap.toast(response.message); window.setTimeout(function () { window.location.reload(); }, 700);
    }).catch(function (error) { window.TableTap.toast(error.message, 'error'); pay.disabled = false; }).finally(function () { pay.classList.remove('is-loading'); });
  });
  if (sessions.length) selectSession(sessions[0].session_id);
})();
