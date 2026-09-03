<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* Imports an admission-register Excel/CSV export into wp_ksr_students.
   Header names are matched case-insensitively and loosely (spaces/case
   only) so a slightly different export still maps correctly. Only the
   handful of columns we actually search/print by get their own DB
   column; everything else rides along in fields_json untouched. */
class KSR_Importer {

	// DB column => list of acceptable header names (first match wins).
	const CORE_MAP = array(
		'roll_no' => array( 'roll no', 'roll number' ),
		'name'    => array( 'applicant name', 'name', 'student name' ),
		'dob'     => array( 'dob', 'date of birth' ),
		'mobile'  => array( 'mobile', 'mobile no', 'mobile number', 'contact no' ),
		'stream'  => array( 'stream' ),
	);

	// Everything else worth keeping, stored in fields_json under these keys.
	const EXTRA_MAP = array(
		'sl_no'           => array( 'sl#', 'sl no' ),
		'barcode_no'      => array( 'barcode number' ),
		'father_name'     => array( "father's name", 'father name' ),
		'mother_name'     => array( "mother's name", 'mother name' ),
		'aadhaar_no'      => array( 'aadhaar no', 'aadhar no' ),
		'email'           => array( 'email id', 'email' ),
		'board'           => array( 'board' ),
		'gender'          => array( 'gender' ),
		'blood_group'     => array( 'blood group' ),
		'address'         => array( 'address' ),
		'category'        => array( 'category' ),
		'religion'        => array( 'religion' ),
		'slc_no'          => array( 'slc no' ),
		'slc_date'        => array( 'slc date' ),
		'marks_percent'   => array( 'mark (%) secured (excluding weightage)', 'marks', 'mark' ),
		'admission_date'  => array( 'admission date' ),
		'admission_type'  => array( 'type of admission' ),
		'hostel_allot'    => array( 'hostel allot' ),
		'subject_name'    => array( 'subject name' ),
		'tc_date'         => array( 'tc date' ),
		'amount'          => array( 'amount' ),
	);

	/**
	 * @param string $file_path  Local path to the uploaded .xlsx or .csv file.
	 * @param string $batch_year Free-text batch label, e.g. "2025-26".
	 * @return array|WP_Error  array( 'inserted'=>n, 'updated'=>n, 'skipped'=>n, 'errors'=>array )
	 */
	public static function import( $file_path, $batch_year, $is_csv ) {
		$rows = $is_csv ? self::read_csv( $file_path ) : KSR_Xlsx_Reader::read( $file_path );
		if ( is_wp_error( $rows ) ) return $rows;
		if ( count( $rows ) < 2 ) return new WP_Error( 'ksr_empty', 'The file has no data rows.' );

		$header = array_shift( $rows );
		$col_index = self::map_columns( $header );

		if ( ! isset( $col_index['roll_no'] ) || ! isset( $col_index['name'] ) ) {
			return new WP_Error( 'ksr_missing_cols', 'Could not find a "Roll No" and "Applicant Name" column in the file - please check the file matches the usual admission register export format.' );
		}

		global $wpdb;
		$table = KSR_Install::table_name();
		$stats = array( 'inserted' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => array() );

		foreach ( $rows as $i => $row ) {
			$roll_no = isset( $row[ $col_index['roll_no'] ] ) ? trim( $row[ $col_index['roll_no'] ] ) : '';
			$name    = isset( $row[ $col_index['name'] ] ) ? trim( $row[ $col_index['name'] ] ) : '';
			if ( $roll_no === '' || $name === '' ) { $stats['skipped']++; continue; }

			$dob    = isset( $col_index['dob'] ) ? trim( $row[ $col_index['dob'] ] ?? '' ) : '';
			$mobile = isset( $col_index['mobile'] ) ? trim( $row[ $col_index['mobile'] ] ?? '' ) : '';
			$stream = isset( $col_index['stream'] ) ? trim( $row[ $col_index['stream'] ] ?? '' ) : '';

			$fields = array();
			foreach ( self::EXTRA_MAP as $key => $aliases ) {
				if ( isset( $col_index[ $key ] ) ) {
					$fields[ $key ] = trim( $row[ $col_index[ $key ] ] ?? '' );
				}
			}

			$existing_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE roll_no = %s", $roll_no ) );

			$data = array(
				'name'        => $name,
				'batch_year'  => $batch_year,
				'stream'      => $stream,
				'dob'         => $dob,
				'mobile'      => $mobile,
				'fields_json' => wp_json_encode( $fields ),
			);

			if ( $existing_id ) {
				$wpdb->update( $table, $data, array( 'id' => $existing_id ) );
				$stats['updated']++;
			} else {
				$data['roll_no'] = $roll_no;
				$wpdb->insert( $table, $data );
				$new_id = $wpdb->insert_id;
				if ( $new_id ) {
					$id_card_no = 'KC' . str_pad( $new_id, 6, '0', STR_PAD_LEFT );
					$wpdb->update( $table, array( 'id_card_no' => $id_card_no ), array( 'id' => $new_id ) );
				}
				$stats['inserted']++;
			}
		}

		return $stats;
	}

	private static function map_columns( $header ) {
		$norm = array_map( function ( $h ) { return strtolower( trim( preg_replace( '/\s+/', ' ', (string) $h ) ) ); }, $header );
		$map = array();
		$all = self::CORE_MAP + self::EXTRA_MAP;
		foreach ( $all as $key => $aliases ) {
			foreach ( $aliases as $alias ) {
				$idx = array_search( $alias, $norm, true );
				if ( $idx !== false ) { $map[ $key ] = $idx; break; }
			}
		}
		return $map;
	}

	private static function read_csv( $file_path ) {
		$fh = fopen( $file_path, 'r' );
		if ( ! $fh ) return new WP_Error( 'ksr_csv_open', 'Could not open the uploaded CSV file.' );
		$rows = array();
		while ( ( $line = fgetcsv( $fh, 0, ',', '"', '\\' ) ) !== false ) {
			$rows[] = $line;
		}
		fclose( $fh );
		return $rows;
	}
}
