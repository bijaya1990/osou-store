<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* Two-way encryption for phone numbers (AES-256-CBC) so they are never
   stored in plain text, and a masking helper so only the admin panel
   (kcms_manage_* capability) ever sees the full number. */
class KCMS_Crypto {

	private static function key() {
		if ( defined( 'AUTH_KEY' ) && AUTH_KEY && AUTH_KEY !== 'put your unique phrase here' ) {
			return hash( 'sha256', AUTH_KEY, true );
		}
		$key = get_option( 'kcms_crypto_key' );
		if ( ! $key ) {
			$key = wp_generate_password( 64, true, true );
			update_option( 'kcms_crypto_key', $key, false );
		}
		return hash( 'sha256', $key, true );
	}

	public static function encrypt( $plain ) {
		if ( '' === (string) $plain ) return '';
		$iv = openssl_random_pseudo_bytes( 16 );
		$cipher = openssl_encrypt( $plain, 'aes-256-cbc', self::key(), OPENSSL_RAW_DATA, $iv );
		if ( false === $cipher ) return '';
		return base64_encode( $iv . $cipher );
	}

	public static function decrypt( $encoded ) {
		if ( '' === (string) $encoded ) return '';
		$raw = base64_decode( $encoded, true );
		if ( false === $raw || strlen( $raw ) < 17 ) return '';
		$iv = substr( $raw, 0, 16 );
		$cipher = substr( $raw, 16 );
		$plain = openssl_decrypt( $cipher, 'aes-256-cbc', self::key(), OPENSSL_RAW_DATA, $iv );
		return false === $plain ? '' : $plain;
	}

	/* Shows only the last 4 digits, e.g. "XXXXXX3210" - used anywhere
	   outside the admin panel (student/teacher self-view, print templates). */
	public static function mask( $plain_or_encrypted, $already_encrypted = true ) {
		$plain = $already_encrypted ? self::decrypt( $plain_or_encrypted ) : $plain_or_encrypted;
		$len = strlen( $plain );
		if ( $len <= 4 ) return str_repeat( 'X', $len );
		return str_repeat( 'X', $len - 4 ) . substr( $plain, -4 );
	}
}
