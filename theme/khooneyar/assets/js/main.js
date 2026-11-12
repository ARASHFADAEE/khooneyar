/* Khooneyar front-end: nav toggle + order form enhancement. No dependencies. */
(function () {
	'use strict';

	var toggle = document.querySelector('.nav-toggle');
	var nav = document.getElementById('primary-nav');
	if (toggle && nav) {
		toggle.addEventListener('click', function () {
			var open = nav.classList.toggle('is-open');
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
	}

	var faDigits = '۰۱۲۳۴۵۶۷۸۹';
	var arDigits = '٠١٢٣٤٥٦٧٨٩';
	function toAscii(s) {
		return String(s).replace(/[۰-۹٠-٩]/g, function (d) {
			var i = faDigits.indexOf(d);
			return i > -1 ? i : arDigits.indexOf(d);
		});
	}
	function toFa(s) {
		return String(s).replace(/\d/g, function (d) { return faDigits[d]; });
	}
	function mobile(raw) {
		var n = toAscii(raw).replace(/[\s\-().]/g, '').replace(/^(\+98|0098|98)/, '0');
		if (/^9\d{9}$/.test(n)) { n = '0' + n; }
		return /^09\d{9}$/.test(n) ? n : '';
	}

	var apiBase = (window.KHY && window.KHY.rest) || '/wp-json/khooneyar/v1/';
	var freshNonce = null;
	function getNonce() {
		if (freshNonce) { return freshNonce; }
		freshNonce = fetch(apiBase + 'nonce', { credentials: 'omit', cache: 'no-store' })
			.then(function (r) { return r.json(); })
			.then(function (j) { return j.nonce; })
			.catch(function () { freshNonce = null; return null; });
		return freshNonce;
	}

	function setError(form, name, msg) {
		var input = form.elements[name];
		if (!input) { return; }
		var wrap = input.closest('.field');
		var out = wrap && wrap.querySelector('.field-error');
		if (wrap) { wrap.classList.toggle('has-error', !!msg); }
		if (out) { out.textContent = msg || ''; }
		input.setAttribute('aria-invalid', msg ? 'true' : 'false');
	}

	function initForm(form) {
		var box = form.closest('.order-box');
		var status = form.querySelector('[data-khy-status]');
		var success = box.querySelector('[data-khy-success]');

		// Warm a fresh nonce (keeps working behind full-page caches).
		form.addEventListener('focusin', getNonce, { once: true });

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			var f = form.elements;
			var errors = {};
			var name = f.khy_name.value.trim();
			if (name.length < 2) { errors.khy_name = 'لطفاً نام خود را وارد کنید.'; }
			var phone = mobile(f.khy_phone.value);
			if (!phone) { errors.khy_phone = 'شمارهٔ موبایل معتبر نیست. مثال: ۰۹۱۲۱۲۳۴۵۶۷'; }
			if (!f.khy_service.value) { errors.khy_service = 'لطفاً نوع خدمت را انتخاب کنید.'; }
			['khy_name', 'khy_phone', 'khy_service'].forEach(function (k) { setError(form, k, errors[k]); });
			if (Object.keys(errors).length) {
				status.textContent = 'لطفاً خطاهای فرم را اصلاح کنید.';
				var first = form.elements[Object.keys(errors)[0]];
				if (first) { first.focus(); }
				return;
			}
			status.textContent = '';
			box.classList.add('is-loading');

			getNonce().then(function (nonce) {
				var data = {
					khy_nonce: nonce || f.khy_nonce.value,
					khy_website: f.khy_website.value,
					khy_source: f.khy_source.value,
					khy_name: name,
					khy_phone: phone,
					khy_service: f.khy_service.value,
					khy_area: f.khy_area.value,
					khy_time: f.khy_time.value,
					khy_note: f.khy_note.value
				};
				return fetch(apiBase + 'order', {
					method: 'POST',
					credentials: 'omit',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify(data)
				}).then(function (r) { return r.json().then(function (j) { j._status = r.status; return j; }); });
			}).then(function (res) {
				box.classList.remove('is-loading');
				freshNonce = null;
				if (res && res.ok) {
					success.querySelector('[data-khy-name]').textContent = res.name || name;
					success.querySelector('[data-khy-code]').textContent = toFa(res.code || '');
					var wa = success.querySelector('[data-khy-wa]');
					if (res.wa_url) { wa.href = res.wa_url; } else { wa.hidden = true; }
					form.hidden = true;
					success.hidden = false;
					success.focus();
					return;
				}
				var errs = (res && res.errors) || {};
				Object.keys(errs).forEach(function (k) { setError(form, k, errs[k]); });
				status.textContent = (res && res.message) || 'ارسال انجام نشد. لطفاً دوباره تلاش کنید یا تماس بگیرید.';
			}).catch(function () {
				box.classList.remove('is-loading');
				freshNonce = null;
				status.textContent = 'ارتباط با سرور برقرار نشد. اینترنت خود را بررسی کنید یا تماس بگیرید.';
			});
		});
	}

	document.querySelectorAll('form[data-khy-form]').forEach(initForm);

	// Price-table buttons: preselect the service in the first order form on the page.
	document.addEventListener('click', function (e) {
		var a = e.target.closest('.js-order-pick');
		if (!a) { return; }
		var sel = document.querySelector('#order select[name="khy_service"]');
		if (sel && a.dataset.service) { sel.value = a.dataset.service; }
	});
})();
