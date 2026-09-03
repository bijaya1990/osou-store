(function () {
	'use strict';
	document.addEventListener('DOMContentLoaded', function () {
		var wrap = document.getElementById('kc-apply-page');
		if (!wrap) return;

		var collegeName = wrap.dataset.college || '';
		var collegeAddr = wrap.dataset.address || '';

		var btns = wrap.querySelectorAll('.kc-apply-btn');
		var boxes = { clc: document.getElementById('kc-form-clc'), cl: document.getElementById('kc-form-cl'), certmark: document.getElementById('kc-form-certmark') };
		var previewWrap = document.getElementById('kc-apply-preview-wrap');
		var previewEl = document.getElementById('kc-apply-preview');
		var successModal = document.getElementById('kc-apply-success-modal');

		function finishGenerate(html, boxKey) {
			previewEl.innerHTML = html;
			boxes[boxKey].hidden = true;
			previewWrap.hidden = false;
			successModal.hidden = false;
		}
		document.getElementById('kc-apply-modal-close').addEventListener('click', function () {
			successModal.hidden = true;
			previewWrap.scrollIntoView({ behavior: 'smooth', block: 'start' });
		});

		function hideAllForms() {
			Object.keys(boxes).forEach(function (k) { if (boxes[k]) boxes[k].hidden = true; });
			previewWrap.hidden = true;
			successModal.hidden = true;
			btns.forEach(function (b) { b.classList.remove('active'); });
		}

		btns.forEach(function (btn) {
			btn.addEventListener('click', function () {
				hideAllForms();
				var formKey = btn.dataset.form;
				var box = boxes[formKey];
				if (!box) return;
				box.hidden = false;
				btn.classList.add('active');
				box.scrollIntoView({ behavior: 'smooth', block: 'start' });

				if (formKey === 'certmark') {
					var pre = btn.dataset.preselect;
					var certChk = document.getElementById('cm_type_cert');
					var markChk = document.getElementById('cm_type_mark');
					if (pre === 'certificate') { certChk.checked = true; markChk.checked = false; }
					else if (pre === 'marksheet') { certChk.checked = false; markChk.checked = true; }
				}
			});
		});

		function val(id) { var el = document.getElementById(id); return el ? el.value.trim() : ''; }
		function checked(id) { var el = document.getElementById(id); return el ? el.checked : false; }
		function fullName(prefix) {
			var parts = [val(prefix + '_first'), val(prefix + '_middle'), val(prefix + '_last')].filter(Boolean);
			return parts.join(' ');
		}
		function today() {
			var d = new Date();
			return String(d.getDate()).padStart(2, '0') + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + d.getFullYear();
		}
		function fmtDate(iso) {
			if (!iso) return '__________';
			var p = iso.split('-');
			return p.length === 3 ? p[2] + '-' + p[1] + '-' + p[0] : iso;
		}
		function blankIfEmpty(v) { return v ? v : '__________'; }

		function pageHead() {
			return '<div class="kc-p-head"><h3>' + collegeName + '</h3><p>' + collegeAddr + '</p></div>';
		}
		function signRow(signLabel) {
			return '<div class="kc-p-sign-row">' +
				'<div class="kc-p-sign-box"><div class="line">' + signLabel + '<br>Date: ' + today() + '</div></div>' +
				'<div class="kc-p-sign-box"><div class="line">Principal Approval<br>&#9744; Approved &nbsp; &#9744; Not Approved<br>Principal\'s Signature: __________________</div></div>' +
				'</div>';
		}

		/* ---------------- CLC ---------------- */
		var clcFrom = document.getElementById('kc-form-clc');
		if (clcFrom) {
			document.getElementById('kc-generate-clc').addEventListener('click', function () {
				var form = boxes.clc;
				if (!form.reportValidity()) return;
				var name = val('clc_title') + ' ' + fullName('clc');
				var relation = val('clc_relation');
				var parent = blankIfEmpty(val('clc_parent'));
				var result = checked('clc_result_pass') ? 'Pass' : ( checked('clc_result_fail') ? 'Fail' : '__________' );
				var addr = [val('clc_addr_at'), val('clc_addr_po'), val('clc_addr_block')].filter(Boolean).join(', ');
				var addr2 = [val('clc_addr_dist'), val('clc_addr_state')].filter(Boolean).join(', ') + (val('clc_addr_pin') ? ' - ' + val('clc_addr_pin') : '');

				var html = pageHead() +
					'<div class="kc-p-title">APPLICATION FOR COLLEGE LEAVING CERTIFICATE (CLC)</div>' +
					'<div class="kc-p-body">' +
					'<p>To<br>The Principal,<br>' + collegeName + '<br><strong>Subject:</strong> Application for issuance of College Leaving Certificate (CLC)</p>' +
					'<p>Respected Sir/Madam,</p>' +
					'<p>I, ' + name + ', ' + relation + ' ' + parent + ', bearing College Roll No. ' + blankIfEmpty(val('clc_college_roll')) + ' and University Roll No. ' + blankIfEmpty(val('clc_univ_roll')) + ', respectfully apply for the issuance of my College Leaving Certificate (CLC). I have completed / appeared in my ' + blankIfEmpty(val('clc_semester')) + ' semester and my result is ' + result + '.</p>' +
					'</div>' +
					'<table class="kc-p-fields">' +
					'<tr><td class="kc-p-label">Full Name</td><td>' + name + '</td></tr>' +
					'<tr><td class="kc-p-label">' + relation + '</td><td>' + parent + '</td></tr>' +
					'<tr><td class="kc-p-label">College Roll No.</td><td>' + blankIfEmpty(val('clc_college_roll')) + '</td></tr>' +
					'<tr><td class="kc-p-label">University Roll No.</td><td>' + blankIfEmpty(val('clc_univ_roll')) + '</td></tr>' +
					'<tr><td class="kc-p-label">Semester</td><td>' + blankIfEmpty(val('clc_semester')) + '</td></tr>' +
					'<tr><td class="kc-p-label">Result</td><td>' + result + '</td></tr>' +
					'<tr><td class="kc-p-label">Date of Birth</td><td>' + fmtDate(val('clc_dob')) + '</td></tr>' +
					'<tr><td class="kc-p-label">Address</td><td>' + blankIfEmpty(addr) + '<br>' + blankIfEmpty(addr2) + '</td></tr>' +
					'</table>' +
					'<div class="kc-p-body"><p>I therefore request you to kindly issue my College Leaving Certificate at the earliest. I shall be grateful for your kind consideration.</p></div>' +
					'<div class="kc-p-declaration"><strong>DECLARATION</strong><br>I hereby declare that the information given by me in this application is true and correct to the best of my knowledge and belief. I understand that I shall be responsible for any incorrect information furnished by me.</div>' +
					signRow('Student\'s Signature') +
					'<div class="kc-p-office">For Office Use: CLC No. __________________ &nbsp; Date of Issue: ____/____/______</div>';

				finishGenerate(html, 'clc');
			});
		}

		/* ---------------- Certificate / Marksheet ---------------- */
		var cmForm = document.getElementById('kc-form-certmark');
		if (cmForm) {
			document.getElementById('kc-generate-certmark').addEventListener('click', function () {
				var form = boxes.certmark;
				if (!form.reportValidity()) return;
				var certOn = checked('cm_type_cert'), markOn = checked('cm_type_mark');
				if (!certOn && !markOn) { alert('Please select Certificate and/or Mark Sheet.'); return; }
				var typeLabel = certOn && markOn ? 'Certificate and Mark Sheet' : (certOn ? 'Certificate' : 'Mark Sheet');

				var name = val('cm_title') + ' ' + fullName('cm');
				var relation = val('cm_relation');
				var parent = blankIfEmpty(val('cm_parent'));
				var classChecks = [
					checked('cm_class_1') ? 'First Class Honours' : '',
					checked('cm_class_2') ? 'Second Class Honours' : '',
					checked('cm_class_3') ? 'Pass Without Honours' : '',
					checked('cm_class_4') ? 'First Class Honours with Distinction' : ''
				].filter(Boolean).join(', ') || '__________';
				var addr = [val('cm_addr_at'), val('cm_addr_po'), val('cm_addr_block')].filter(Boolean).join(', ');
				var addr2 = [val('cm_addr_dist'), val('cm_addr_state')].filter(Boolean).join(', ') + (val('cm_addr_pin') ? ' - ' + val('cm_addr_pin') : '');

				var html = pageHead() +
					'<div class="kc-p-title">APPLICATION FOR CERTIFICATE / MARK SHEET</div>' +
					'<div class="kc-p-body">' +
					'<p>To<br>The Principal,<br>' + collegeName + '<br><strong>Subject:</strong> Application for issuance of ' + typeLabel + '</p>' +
					'<p>Respected Sir/Madam,</p>' +
					'<p>I, ' + name + ', ' + relation + ' ' + parent + ', bearing College Roll No. ' + blankIfEmpty(val('cm_college_roll')) + ' and University Roll No. ' + blankIfEmpty(val('cm_univ_roll')) + ', respectfully apply for the issue of my ' + typeLabel + '. The required particulars are furnished below for necessary verification and record.</p>' +
					'</div>' +
					'<table class="kc-p-fields">' +
					'<tr><td class="kc-p-label">Full Name</td><td>' + name + '</td></tr>' +
					'<tr><td class="kc-p-label">Father\'s / Mother\'s Name</td><td>' + parent + '</td></tr>' +
					'<tr><td class="kc-p-label">College Roll No.</td><td>' + blankIfEmpty(val('cm_college_roll')) + '</td></tr>' +
					'<tr><td class="kc-p-label">University Roll No.</td><td>' + blankIfEmpty(val('cm_univ_roll')) + '</td></tr>' +
					'<tr><td class="kc-p-label">Course / Stream</td><td>' + blankIfEmpty(val('cm_course')) + '</td></tr>' +
					'<tr><td class="kc-p-label">Semester / Examination</td><td>' + blankIfEmpty(val('cm_semester')) + '</td></tr>' +
					'<tr><td class="kc-p-label">Examination Year</td><td>' + blankIfEmpty(val('cm_exam_year')) + '</td></tr>' +
					'<tr><td class="kc-p-label">Grade Point Secured</td><td>' + blankIfEmpty(val('cm_gradepoint')) + ' (' + classChecks + ')</td></tr>' +
					'<tr><td class="kc-p-label">Date of Birth</td><td>' + fmtDate(val('cm_dob')) + '</td></tr>' +
					'<tr><td class="kc-p-label">Address</td><td>' + blankIfEmpty(addr) + '<br>' + blankIfEmpty(addr2) + '</td></tr>' +
					'<tr><td class="kc-p-label">Document(s) Requested</td><td>' + typeLabel + '</td></tr>' +
					'</table>' +
					'<div class="kc-p-body"><p>I therefore request you to kindly issue the above-mentioned ' + typeLabel + ' at the earliest. I shall be grateful for your kind consideration.</p></div>' +
					'<div class="kc-p-declaration"><strong>DECLARATION</strong><br>I hereby declare that the information given by me in this application is true and correct to the best of my knowledge and belief. I shall be responsible for any discrepancy found in the information furnished above.</div>' +
					signRow('Student\'s Signature') +
					'<div class="kc-p-office">For Office Use: Application No. __________________ &nbsp; Date: ____/____/______ &nbsp; Certificate/Mark Sheet No. __________________</div>';

				finishGenerate(html, 'certmark');
			});
		}

		/* ---------------- C.L. (employee) ---------------- */
		var clForm = document.getElementById('kc-form-cl');
		if (clForm) {
			var fromEl = document.getElementById('cl_from'), toEl = document.getElementById('cl_to'), daysEl = document.getElementById('cl_days');
			function recalcDays() {
				if (!fromEl.value || !toEl.value) { daysEl.value = ''; return; }
				var f = new Date(fromEl.value), t = new Date(toEl.value);
				var diff = Math.round((t - f) / 86400000) + 1;
				daysEl.value = diff > 0 ? diff : '';
			}
			fromEl.addEventListener('change', recalcDays);
			toEl.addEventListener('change', recalcDays);

			document.getElementById('kc-generate-cl').addEventListener('click', function () {
				var form = boxes.cl;
				if (!form.reportValidity()) return;
				var name = val('cl_title') + ' ' + fullName('cl');
				var designation = val('cl_designation');
				var joining = fmtDate(val('cl_joining'));

				var html = pageHead() +
					'<div class="kc-p-title">APPLICATION FOR C.L.</div>' +
					'<div class="kc-p-body">' +
					'<p>To<br>The Principal,<br>' + collegeName + '<br><strong>Subject:</strong> Application for C.L.</p>' +
					'<p>Sir/Madam,</p>' +
					'<p>I, ' + name + ', respectfully submit that I am working in this institution as ' + designation + ' (Designation). I request you to kindly grant me Casual Leave (C.L.) for the following period and reason:</p>' +
					'</div>' +
					'<table class="kc-p-fields">' +
					'<tr><td class="kc-p-label">Name</td><td>' + name + '</td></tr>' +
					'<tr><td class="kc-p-label">Designation</td><td>' + designation + '</td></tr>' +
					'<tr><td class="kc-p-label">Reason for Leave</td><td>' + blankIfEmpty(val('cl_reason')) + '</td></tr>' +
					'<tr><td class="kc-p-label">C.L. From Date</td><td>' + fmtDate(val('cl_from')) + '</td></tr>' +
					'<tr><td class="kc-p-label">To Date</td><td>' + fmtDate(val('cl_to')) + '</td></tr>' +
					'<tr><td class="kc-p-label">Number of Days</td><td>' + blankIfEmpty(val('cl_days')) + ' days</td></tr>' +
					'<tr><td class="kc-p-label">Date of Joining</td><td>' + joining + '</td></tr>' +
					'</table>' +
					'<div class="kc-p-body"><p>I will join my duties on ' + joining + '. I therefore request you to kindly allow me the above-mentioned C.L. period.</p><p>Thanking you.</p><p>Yours faithfully,</p></div>' +
					signRow('Employee\'s Signature') +
					'';

				finishGenerate(html, 'cl');
			});
		}

		/* ---------------- Preview actions ---------------- */
		document.getElementById('kc-apply-print').addEventListener('click', function () { window.print(); });

		document.getElementById('kc-apply-download').addEventListener('click', function () {
			var btn = this;
			if (typeof html2pdf === 'undefined') { alert('PDF library failed to load - please check your internet connection and try again, or use Print instead.'); return; }
			btn.disabled = true; btn.textContent = 'Preparing PDF...';
			html2pdf().set({
				margin: 10,
				filename: 'Application-' + Date.now() + '.pdf',
				image: { type: 'jpeg', quality: 0.98 },
				html2canvas: { scale: 2, useCORS: true },
				jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
			}).from(previewEl).save().then(function () {
				btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-download"></i> Download PDF';
			});
		});

		document.getElementById('kc-apply-edit').addEventListener('click', function () {
			previewWrap.hidden = true;
			btns.forEach(function (b) {
				if (b.classList.contains('active')) { boxes[b.dataset.form].hidden = false; boxes[b.dataset.form].scrollIntoView({ behavior: 'smooth' }); }
			});
		});
	});
})();
