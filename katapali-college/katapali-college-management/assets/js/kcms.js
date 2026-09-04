(function ($) {
	'use strict';

	function showMsg( $el, text, ok ) {
		$el.text( text ).removeClass( 'kcms-msg-error kcms-msg-ok' ).addClass( ok ? 'kcms-msg-ok' : 'kcms-msg-error' ).prop( 'hidden', false );
	}

	$( document ).on( 'click', '.kcms-toggle', function () {
		var target = document.getElementById( $( this ).data( 'target' ) );
		if ( target ) target.hidden = ! target.hidden;
	} );

	/* ---- Leave form ---- */
	var $leaveForm = $( '#kcms-leave-form' );
	if ( $leaveForm.length ) {
		var $msg = $( '#kcms-leave-msg' );
		$( '#kcms-leave-send-otp' ).on( 'click', function () {
			var $btn = $( this ).prop( 'disabled', true ).text( 'Sending...' );
			$.post( KCMS.ajaxUrl, { action: 'kcms_leave_send_otp', nonce: KCMS.nonce } )
				.done( function ( res ) {
					if ( res.success ) { showMsg( $msg, 'OTP sent - please check your email/SMS.', true ); }
					else { showMsg( $msg, res.data || 'Could not send OTP.', false ); }
				} )
				.fail( function () { showMsg( $msg, 'Network error - please try again.', false ); } )
				.always( function () { $btn.prop( 'disabled', false ).text( 'Send OTP to Verify' ); } );
		} );

		$leaveForm.on( 'submit', function ( e ) {
			e.preventDefault();
			var fd = new FormData( this );
			fd.append( 'action', 'kcms_leave_submit' );
			fd.append( 'nonce', KCMS.nonce );
			var $btn = $leaveForm.find( 'button[type=submit]' ).prop( 'disabled', true ).text( 'Submitting...' );
			$.ajax( { url: KCMS.ajaxUrl, type: 'POST', data: fd, processData: false, contentType: false } )
				.done( function ( res ) {
					if ( res.success ) {
						$leaveForm.hide();
						$( '#kcms-leave-success' ).prop( 'hidden', false ).html(
							'<p><strong>Application submitted successfully.</strong> Reference No: ' + res.data.number + '</p>' +
							'<p><a href="' + res.data.print_url + '" target="_blank" class="kcms-btn kcms-btn-outline">View / Print Application</a></p>'
						);
					} else {
						showMsg( $msg, res.data || 'Could not submit application.', false );
						$btn.prop( 'disabled', false ).text( 'Submit Leave Application' );
					}
				} )
				.fail( function () { showMsg( $msg, 'Network error - please try again.', false ); $btn.prop( 'disabled', false ).text( 'Submit Leave Application' ); } );
		} );
	}

	/* ---- Certificate form ---- */
	var $certForm = $( '#kcms-cert-form' );
	if ( $certForm.length ) {
		var $cmsg = $( '#kcms-cert-msg' );
		$( '#kcms-cert-send-otp' ).on( 'click', function () {
			var $btn = $( this ).prop( 'disabled', true ).text( 'Sending...' );
			$.post( KCMS.ajaxUrl, { action: 'kcms_cert_send_otp', nonce: KCMS.nonce } )
				.done( function ( res ) {
					if ( res.success ) { showMsg( $cmsg, 'OTP sent - please check your email/SMS.', true ); }
					else { showMsg( $cmsg, res.data || 'Could not send OTP.', false ); }
				} )
				.fail( function () { showMsg( $cmsg, 'Network error - please try again.', false ); } )
				.always( function () { $btn.prop( 'disabled', false ).text( 'Send OTP to Verify' ); } );
		} );

		$certForm.on( 'submit', function ( e ) {
			e.preventDefault();
			var fd = new FormData( this );
			fd.append( 'action', 'kcms_cert_submit' );
			fd.append( 'nonce', KCMS.nonce );
			var $btn = $certForm.find( 'button[type=submit]' ).prop( 'disabled', true ).text( 'Submitting...' );
			$.ajax( { url: KCMS.ajaxUrl, type: 'POST', data: fd, processData: false, contentType: false } )
				.done( function ( res ) {
					if ( res.success ) {
						$certForm.hide();
						$( '#kcms-cert-success' ).prop( 'hidden', false ).html(
							'<p><strong>Request submitted successfully.</strong> Reference No: ' + res.data.number + '</p>' +
							'<p><a href="' + res.data.print_url + '" target="_blank" class="kcms-btn kcms-btn-outline">View / Print Request</a></p>'
						);
					} else {
						showMsg( $cmsg, res.data || 'Could not submit request.', false );
						$btn.prop( 'disabled', false ).text( 'Submit Request' );
					}
				} )
				.fail( function () { showMsg( $cmsg, 'Network error - please try again.', false ); $btn.prop( 'disabled', false ).text( 'Submit Request' ); } );
		} );
	}
})( jQuery );
