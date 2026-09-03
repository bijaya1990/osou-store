<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* A minimal, dependency-free .xlsx reader - just enough to read a plain
   data-only worksheet (the kind any "Export to Excel" admission register
   produces): shared strings + the first sheet's cell values, no formulas,
   no styles. Avoids pulling in a full library (PhpSpreadsheet etc.) that
   free/shared hosting may not have room or Composer support for; .xlsx is
   just a zip of XML files, so PHP's built-in ZipArchive is all this needs. */
class KSR_Xlsx_Reader {

	/** @return true|WP_Error */
	public static function available() {
		if ( ! class_exists( 'ZipArchive' ) ) {
			return new WP_Error( 'ksr_no_zip', 'This server\'s PHP does not have the Zip extension enabled, which is required to read .xlsx files. Please ask your host to enable "php-zip", or save the file as .csv and upload that instead.' );
		}
		return true;
	}

	/** @return array[]|WP_Error  Array of rows, each row an array of cell strings (row 1 = header). */
	public static function read( $file_path ) {
		$ok = self::available();
		if ( is_wp_error( $ok ) ) return $ok;

		$zip = new ZipArchive();
		if ( $zip->open( $file_path ) !== true ) {
			return new WP_Error( 'ksr_bad_zip', 'Could not open the uploaded file - is it a valid .xlsx file?' );
		}

		$shared = self::read_shared_strings( $zip );

		// Find the first sheet's XML path from the workbook's sheet list, honouring rels.
		$sheet_path = self::first_sheet_path( $zip );
		if ( ! $sheet_path ) {
			$zip->close();
			return new WP_Error( 'ksr_no_sheet', 'Could not find a worksheet inside the uploaded file.' );
		}

		$sheet_xml = $zip->getFromName( $sheet_path );
		$zip->close();
		if ( $sheet_xml === false ) {
			return new WP_Error( 'ksr_no_sheet_xml', 'Could not read the worksheet data inside the uploaded file.' );
		}

		return self::parse_sheet( $sheet_xml, $shared );
	}

	private static function read_shared_strings( ZipArchive $zip ) {
		$xml = $zip->getFromName( 'xl/sharedStrings.xml' );
		if ( $xml === false ) return array();
		$strings = array();
		$prev = libxml_use_internal_errors( true );
		$doc = simplexml_load_string( $xml );
		libxml_use_internal_errors( $prev );
		if ( ! $doc ) return array();
		foreach ( $doc->si as $si ) {
			// A shared string can be a single <t>, or several <r><t> runs (rich text) - concatenate all text nodes.
			$text = '';
			if ( isset( $si->t ) ) {
				$text = (string) $si->t;
			} else {
				foreach ( $si->r as $run ) {
					$text .= (string) $run->t;
				}
			}
			$strings[] = $text;
		}
		return $strings;
	}

	private static function first_sheet_path( ZipArchive $zip ) {
		// Simplest reliable case: worksheets are named sheet1.xml, sheet2.xml... in document order.
		$wb = $zip->getFromName( 'xl/workbook.xml' );
		if ( $wb === false ) return 'xl/worksheets/sheet1.xml';

		$rels_xml = $zip->getFromName( 'xl/_rels/workbook.xml.rels' );
		$rels = array();
		if ( $rels_xml !== false ) {
			$prev = libxml_use_internal_errors( true );
			$rdoc = simplexml_load_string( $rels_xml );
			libxml_use_internal_errors( $prev );
			if ( $rdoc ) {
				foreach ( $rdoc->Relationship as $rel ) {
					$rels[ (string) $rel['Id'] ] = (string) $rel['Target'];
				}
			}
		}

		$prev = libxml_use_internal_errors( true );
		$doc = simplexml_load_string( $wb );
		libxml_use_internal_errors( $prev );
		if ( $doc && isset( $doc->sheets->sheet[0] ) ) {
			$first = $doc->sheets->sheet[0];
			$rid = (string) $first->attributes( 'http://schemas.openxmlformats.org/officeDocument/2006/relationships' )->id;
			if ( $rid && isset( $rels[ $rid ] ) ) {
				$target = ltrim( $rels[ $rid ], '/' );
				return ( strpos( $target, 'xl/' ) === 0 ) ? $target : 'xl/' . $target;
			}
		}
		return 'xl/worksheets/sheet1.xml';
	}

	private static function parse_sheet( $xml, $shared ) {
		$prev = libxml_use_internal_errors( true );
		$doc = simplexml_load_string( $xml );
		libxml_use_internal_errors( $prev );
		if ( ! $doc ) return new WP_Error( 'ksr_bad_sheet_xml', 'The worksheet data could not be parsed.' );

		$rows = array();
		foreach ( $doc->sheetData->row as $row ) {
			$cells = array();
			foreach ( $row->c as $c ) {
				$ref = (string) $c['r']; // e.g. "C7"
				$col_index = self::col_letters_to_index( preg_replace( '/[0-9]/', '', $ref ) );
				$type = (string) $c['t'];
				$value = '';
				if ( $type === 's' ) { // shared string
					$idx = (int) $c->v;
					$value = isset( $shared[ $idx ] ) ? $shared[ $idx ] : '';
				} elseif ( $type === 'inlineStr' ) {
					$value = isset( $c->is->t ) ? (string) $c->is->t : '';
				} else {
					$value = isset( $c->v ) ? (string) $c->v : '';
				}
				$cells[ $col_index ] = $value;
			}
			if ( ! $cells ) { $rows[] = array(); continue; }
			$max = max( array_keys( $cells ) );
			$line = array();
			for ( $i = 0; $i <= $max; $i++ ) {
				$line[] = isset( $cells[ $i ] ) ? $cells[ $i ] : '';
			}
			$rows[] = $line;
		}
		return $rows;
	}

	private static function col_letters_to_index( $letters ) {
		$letters = strtoupper( $letters );
		$index = 0;
		for ( $i = 0; $i < strlen( $letters ); $i++ ) {
			$index = $index * 26 + ( ord( $letters[ $i ] ) - 64 );
		}
		return $index - 1; // zero-based
	}
}
