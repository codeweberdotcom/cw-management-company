(function ($) {
	'use strict';

	var frame;

	$('#cw_mc_document_file_select').on('click', function (e) {
		e.preventDefault();

		if (frame) {
			frame.open();
			return;
		}

		frame = wp.media({
			title:    'Select File',
			button:   { text: 'Use this file' },
			multiple: false,
		});

		frame.on('select', function () {
			var attachment = frame.state().get('selection').first().toJSON();
			$('#cw_mc_document_file').val(attachment.id);
			$('#cw_mc_document_file_preview').text(attachment.filename || attachment.title || '');
			$('#cw_mc_document_file_remove').show();
		});

		frame.open();
	});

	$(document).on('click', '#cw_mc_document_file_remove', function (e) {
		e.preventDefault();
		$('#cw_mc_document_file').val('');
		$('#cw_mc_document_file_preview').empty();
		$(this).hide();
	});

})(jQuery);
