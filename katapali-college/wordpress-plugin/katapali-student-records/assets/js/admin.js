(function ($) {
	'use strict';
	$(document).ready(function () {
		var btn = document.getElementById('ksr-upload-photo-btn');
		if (!btn) return;
		var frame;
		btn.addEventListener('click', function (e) {
			e.preventDefault();
			if (frame) { frame.open(); return; }
			frame = wp.media({
				title: 'Select Student Photo',
				button: { text: 'Use this photo' },
				multiple: false,
				library: { type: 'image' },
			});
			frame.on('select', function () {
				var att = frame.state().get('selection').first().toJSON();
				document.getElementById('ksr-photo-id').value = att.id;
				document.getElementById('ksr-photo-preview').src = att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url;
			});
			frame.open();
		});
	});
})(jQuery);
