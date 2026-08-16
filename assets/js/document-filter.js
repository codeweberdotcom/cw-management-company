(function () {
	'use strict';

	function init() {
		var form = document.getElementById('cw-mc-document-filter');
		var container = document.getElementById('cw-mc-documents-list');

		if (!form || !container || typeof cwMcDocFilter === 'undefined') {
			return;
		}

		function submit() {
			var typeField = form.querySelector('[name="type"]');
			var yearField = form.querySelector('[name="year"]');

			var body = new URLSearchParams({
				action: 'cw_mc_filter_documents',
				nonce: cwMcDocFilter.nonce,
				object_id: cwMcDocFilter.objectId,
				type: typeField ? typeField.value : '',
				year: yearField ? yearField.value : '',
			});

			container.classList.add('filter-loading');

			fetch(cwMcDocFilter.ajaxUrl, { method: 'POST', body: body })
				.then(function (r) { return r.json(); })
				.then(function (res) {
					if (res && res.success) {
						container.innerHTML = res.data.html;
					}
				})
				.finally(function () {
					container.classList.remove('filter-loading');
				});
		}

		form.addEventListener('change', submit);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
