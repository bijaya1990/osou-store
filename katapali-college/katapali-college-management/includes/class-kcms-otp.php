<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* OTP delivery is pluggable: if an SMS gateway (MSG91 or Twilio) is
   configured under Katapali College Management -> Settings, OTPs are sent
   as a real SMS to the mobile number. Until a gateway is configured, OTPs
   fall back to email (to the applicant's registered email address) so the
   whole approval flow keeps working end-to-end while the SMS account is
   being set up. */
class KCMS_OTP {

	const TTL_MINUTES = 10;
	const MAX_ATTEMPTS = 5;

	public static function generate_and_send( $context, $ref_id, $mobile, $email = '' ) {
		global $wpdb;

		if ( self::rate_limited( $context, $ref_id ) ) {
			return new WP_Error( 'kcms_otp_rate_limited', 'Please wait a minute before requesting another OTP.' );
		}

		$otp = str_pad( (string) wp_rand( 0, 999999 ), 6, '0', STR_PAD_LEFT );
		$wpdb->insert( KCMS_DB::t( 'otp_tokens' ), array(
			'context'    => $context,
			'ref_id'     => $ref_id,
			'otp_hash'   => wp_hash_password( $otp ),
			'attempts'   => 0,
			'verified'   => 0,
			'expires_at' => gmdate( 'Y-m-d H:i:s', time() + self::TTL_MINUTES * 60 ),
			'created_at' => current_time( 'mysql', true ),
		) );

		$sent = false;
		$settings = get_option( 'kcms_sms_settings', array() );
		if ( ! empty( $settings['gateway'] ) && ! empty( $mobile ) ) {
			$sent = self::send_sms( $mobile, $otp, $settings );
		}
		if ( ! $sent && $email ) {
			$sent = wp_mail( $email, 'Your OTP Code - Katapali +3 College', "Your verification OTP is: {$otp}\n\nThis code expires in " . self::TTL_MINUTES . " minutes. Do not share it with anyone." );
		}
		return $sent ? true : new WP_Error( 'kcms_otp_send_failed', 'Could not send OTP - no SMS gateway configured and email delivery failed. Add SMS gateway keys under Katapali College Management -> Settings, or check the site\'s email (wp_mail) configuration.' );
	}

	public static function verify( $context, $ref_id, $otp ) {
		global $wpdb;
		$table = KCMS_DB::t( 'otp_tokens' );
		$row = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$table} WHERE context=%s AND ref_id=%d AND verified=0 ORDER BY otp_id DESC LIMIT 1",
			$context, $ref_id
		) );
		if ( ! $row ) return new WP_Error( 'kcms_otp_not_found', 'No pending OTP found - please request a new one.' );
		if ( strtotime( $row->expires_at ) < time() ) return new WP_Error( 'kcms_otp_expired', 'OTP has expired - please request a new one.' );
		if ( (int) $row->attempts >= self::MAX_ATTEMPTS ) return new WP_Error( 'kcms_otp_locked', 'Too many incorrect attempts - please request a new OTP.' );

		if ( ! wp_check_password( $otp, $row->otp_hash ) ) {
			$wpdb->update( KCMS_DB::t( 'otp_tokens' ), array( 'attempts' => (int) $row->attempts + 1 ), array( 'otp_id' => $row->otp_id ) );
			return new WP_Error( 'kcms_otp_incorrect', 'Incorrect OTP.' );
		}
		$wpdb->update( KCMS_DB::t( 'otp_tokens' ), array( 'verified' => 1 ), array( 'otp_id' => $row->otp_id ) );
		return true;
	}

	private static function rate_limited( $context, $ref_id ) {
		global $wpdb;
		$table = KCMS_DB::t( 'otp_tokens' );
		$last = $wpdb->get_var( $wpdb->prepare(
			"SELECT created_at FROM {$table} WHERE context=%s AND ref_id=%d ORDER BY otp_id DESC LIMIT 1",
			$context, $ref_id
		) );
		return $last && ( time() - strtotime( $last ) ) < 60;
	}

	private static function send_sms( $mobile, $otp, $settings ) {
		$message = "Your OTP for Katapali +3 College is {$otp}. Valid for " . self::TTL_MINUTES . " minutes. Do not share.";
		if ( 'msg91' === $settings['gateway'] && ! empty( $settings['msg91_authkey'] ) ) {
			$resp = wp_remote_post( 'https://control.msg91.com/api/v5/otp', array(
				'timeout' => 15,
				'headers' => array( 'authkey' => $settings['msg91_authkey'], 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode( array( 'mobile' => $mobile, 'otp' => $otp, 'template_id' => $settings['msg91_template_id'] ?? '' ) ),
			) );
			return ! is_wp_error( $resp ) && wp_remote_retrieve_response_code( $resp ) < 300;
		}
		if ( 'twilio' === $settings['gateway'] && ! empty( $settings['twilio_sid'] ) && ! empty( $settings['twilio_token'] ) ) {
			$resp = wp_remote_post( "https://api.twilio.com/2010-04-01/Accounts/{$settings['twilio_sid']}/Messages.json", array(
				'timeout' => 15,
				'headers' => array( 'Authorization' => 'Basic ' . base64_encode( $settings['twilio_sid'] . ':' . $settings['twilio_token'] ) ),
				'body'    => array( 'From' => $settings['twilio_from'] ?? '', 'To' => $mobile, 'Body' => $message ),
			) );
			return ! is_wp_error( $resp ) && wp_remote_retrieve_response_code( $resp ) < 300;
		}
		return false;
	}
}
