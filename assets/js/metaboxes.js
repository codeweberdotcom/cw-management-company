/* global wp */
(function () {
	'use strict';

	// ── Repeater: serialize rows to hidden JSON input ──────────────────────────

	function serializeTariff() {
		var rows = [];
		document.querySelectorAll('#cw-mc-tariff-rows .cw-mc-tariff-row').forEach(function (tr) {
			rows.push({
				name: tr.querySelector('.tr-name').value,
				val:  tr.querySelector('.tr-val').value,
				pct:  tr.querySelector('.tr-pct').value,
			});
		});
		document.getElementById('cw-mc-tariff-json').value = JSON.stringify(rows);
	}

	function serializeWorks() {
		var rows = [];
		document.querySelectorAll('#cw-mc-works-rows .cw-mc-work-row').forEach(function (tr) {
			rows.push({
				type:   tr.querySelector('.wk-type').value,
				date:   tr.querySelector('.wk-date').value,
				title:  tr.querySelector('.wk-title').value,
				detail: tr.querySelector('.wk-detail').value,
				cost:   tr.querySelector('.wk-cost').value,
				status: tr.querySelector('.wk-status').value,
			});
		});
		document.getElementById('cw-mc-works-json').value = JSON.stringify(rows);
	}

	// ── Tariff table ────────────────────────────────────────────────────────────

	function initTariff() {
		var tbody = document.getElementById('cw-mc-tariff-rows');
		var addBtn = document.getElementById('cw-mc-tariff-add');
		if (!tbody || !addBtn) return;

		function newTariffRow() {
			var tr = document.createElement('tr');
			tr.className = 'cw-mc-tariff-row';
			tr.innerHTML =
				'<td><input type="text" class="widefat tr-name"></td>' +
				'<td><input type="text" class="widefat tr-val"></td>' +
				'<td><input type="text" class="widefat tr-pct"></td>' +
				'<td><button type="button" class="button cw-mc-row-remove" title="Remove">✕</button></td>';
			tbody.appendChild(tr);
		}

		addBtn.addEventListener('click', function () {
			newTariffRow();
		});

		tbody.addEventListener('click', function (e) {
			if (e.target.classList.contains('cw-mc-row-remove')) {
				e.target.closest('tr').remove();
				serializeTariff();
			}
		});

		tbody.addEventListener('input', serializeTariff);
		tbody.addEventListener('change', serializeTariff);
	}

	// ── Works table ─────────────────────────────────────────────────────────────

	function initWorks() {
		var tbody = document.getElementById('cw-mc-works-rows');
		var addBtn = document.getElementById('cw-mc-works-add');
		if (!tbody || !addBtn) return;

		function newWorkRow() {
			var tr = document.createElement('tr');
			tr.className = 'cw-mc-work-row';
			tr.innerHTML =
				'<td><select class="wk-type">' +
					'<option value="done">Done</option>' +
					'<option value="plan">Plan</option>' +
				'</select></td>' +
				'<td><input type="text" class="widefat wk-date" placeholder="июль 2026"></td>' +
				'<td><input type="text" class="widefat wk-title"></td>' +
				'<td><input type="text" class="widefat wk-detail"></td>' +
				'<td><input type="text" class="widefat wk-cost" placeholder="100 000 ₽"></td>' +
				'<td><input type="text" class="widefat wk-status" placeholder="Выполнено"></td>' +
				'<td><button type="button" class="button cw-mc-row-remove" title="Remove">✕</button></td>';
			tbody.appendChild(tr);
		}

		addBtn.addEventListener('click', function () {
			newWorkRow();
		});

		tbody.addEventListener('click', function (e) {
			if (e.target.classList.contains('cw-mc-row-remove')) {
				e.target.closest('tr').remove();
				serializeWorks();
			}
		});

		tbody.addEventListener('input', serializeWorks);
		tbody.addEventListener('change', serializeWorks);
	}

	// ── Team members table ───────────────────────────────────────────────────────

	function serializeTeam() {
		var rows = [];
		document.querySelectorAll('#cw-mc-team-rows .cw-mc-team-row').forEach(function (tr) {
			rows.push({
				initials: tr.querySelector('.tm-ini').value,
				name:     tr.querySelector('.tm-name').value,
				role:     tr.querySelector('.tm-role').value,
			});
		});
		document.getElementById('cw-mc-team-json').value = JSON.stringify(rows);
	}

	function initTeam() {
		var tbody  = document.getElementById('cw-mc-team-rows');
		var addBtn = document.getElementById('cw-mc-team-add');
		if (!tbody || !addBtn) return;

		function newTeamRow() {
			var tr = document.createElement('tr');
			tr.className = 'cw-mc-team-row';
			tr.innerHTML =
				'<td><input type="text" class="widefat tm-ini" maxlength="3"></td>' +
				'<td><input type="text" class="widefat tm-name"></td>' +
				'<td><input type="text" class="widefat tm-role"></td>' +
				'<td><button type="button" class="button cw-mc-row-remove" title="Remove">&#x2715;</button></td>';
			tbody.appendChild(tr);
		}

		addBtn.addEventListener('click', function () {
			newTeamRow();
		});

		tbody.addEventListener('click', function (e) {
			if (e.target.classList.contains('cw-mc-row-remove')) {
				e.target.closest('tr').remove();
				serializeTeam();
			}
		});

		tbody.addEventListener('input',  serializeTeam);
		tbody.addEventListener('change', serializeTeam);
	}

	// ── Gallery media picker ─────────────────────────────────────────────────────

	function initGallery() {
		document.querySelectorAll('.cw-mc-media-slot').forEach(function (slot) {
			var selectBtn = slot.querySelector('.cw-mc-media-select');
			var removeBtn = slot.querySelector('.cw-mc-media-remove');
			var idInput   = slot.querySelector('.cw-mc-media-id');
			var preview   = slot.querySelector('.cw-mc-media-preview');

			if (!selectBtn || !idInput || !preview) return;

			selectBtn.addEventListener('click', function () {
				if (typeof wp === 'undefined' || !wp.media) return;
				var frame = wp.media({
					title:    'Select Image',
					button:   { text: 'Use this image' },
					multiple: false,
				});
				frame.on('select', function () {
					var att = frame.state().get('selection').first().toJSON();
					idInput.value = att.id;
					var src = att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url;
					preview.innerHTML = '<img src="' + src + '" style="max-width:100%;max-height:160px;display:block;">';
					selectBtn.textContent = 'Replace';
					if (!slot.querySelector('.cw-mc-media-remove')) {
						var btn = document.createElement('button');
						btn.type = 'button';
						btn.className = 'button cw-mc-media-remove';
						btn.textContent = 'Remove';
						selectBtn.parentNode.appendChild(btn);
						initRemove(btn, idInput, preview, selectBtn);
					}
				});
				frame.open();
			});

			if (removeBtn) {
				initRemove(removeBtn, idInput, preview, selectBtn);
			}
		});
	}

	function initRemove(btn, idInput, preview, selectBtn) {
		btn.addEventListener('click', function () {
			idInput.value = '';
			preview.innerHTML = '<span style="color:#aaa;font-size:13px;">No image</span>';
			selectBtn.textContent = 'Select Image';
			btn.remove();
		});
	}

	// ── Serialize on submit ────────────────────────────────────────────────────

	var form = document.getElementById('post');
	if (form) {
		form.addEventListener('submit', function () {
			serializeTariff();
			serializeWorks();
			serializeTeam();
		});
	}

	// ── Init ──────────────────────────────────────────────────────────────────

	document.addEventListener('DOMContentLoaded', function () {
		initTariff();
		initWorks();
		initTeam();
		initGallery();
	});
})();
