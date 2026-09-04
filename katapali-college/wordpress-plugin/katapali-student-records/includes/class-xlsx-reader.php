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

	/**
	 * Extracts embedded pictures from the first worksheet, if any, mapped
	 * to the (0-based) worksheet row each is anchored to. Only common web
	 * image formats are returned - Excel sometimes embeds decorative
	 * icons (e.g. dropdown/checkbox glyphs) as .emf/.wmf vector images,
	 * which browsers can't display and are never real student photos, so
	 * those are silently skipped rather than imported as a "photo".
	 *
	 * @return array  [ row_0based => [ 'data' => bytes, 'ext' => 'png' ] ]
	 */
	public static function extract_images( $file_path ) {
		if ( is_wp_error( self::available() ) ) return array();

		$zip = new ZipArchive();
		if ( $zip->open( $file_path ) !== true ) return array();

		$sheet_path = self::first_sheet_path( $zip );
		$sheet_rels_path = self::rels_path_for( $sheet_path );
		$sheet_rels_xml = $zip->getFromName( $sheet_rels_path );
		if ( $sheet_rels_xml === false ) { $zip->close(); return array(); }

		$drawing_target = null;
		$prev = libxml_use_internal_errors( true );
		$rdoc = simplexml_load_string( $sheet_rels_xml );
		libxml_use_internal_errors( $prev );
		if ( $rdoc ) {
			foreach ( $rdoc->Relationship as $rel ) {
				if ( strpos( (string) $rel['Type'], '/drawing' ) !== false ) {
					$drawing_target = (string) $rel['Target'];
					break;
				}
			}
		}
		if ( ! $drawing_target ) { $zip->close(); return array(); }

		$drawing_path = self::resolve_path( dirname( $sheet_path ), $drawing_target );
		$drawing_xml = $zip->getFromName( $drawing_path );
		if ( $drawing_xml === false ) { $zip->close(); return array(); }

		$drawing_rels_xml = $zip->getFromName( self::rels_path_for( $drawing_path ) );
		$drawing_rels = array();
		if ( $drawing_rels_xml !== false ) {
			$prev = libxml_use_internal_errors( true );
			$drdoc = simplexml_load_string( $drawing_rels_xml );
			libxml_use_internal_errors( $prev );
			if ( $drdoc ) {
				foreach ( $drdoc->Relationship as $rel ) {
					$drawing_rels[ (string) $rel['Id'] ] = (string) $rel['Target'];
				}
			}
		}

		$prev = libxml_use_internal_errors( true );
		$ddoc = simplexml_load_string( $drawing_xml );
		libxml_use_internal_errors( $prev );
		$images = array();
		if ( $ddoc ) {
			$ddoc->registerXPathNamespace( 'xdr', 'http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing' );
			$ddoc->registerXPathNamespace( 'a', 'http://schemas.openxmlformats.org/drawingml/2006/main' );
			$ddoc->registerXPathNamespace( 'r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships' );

			$anchors = $ddoc->xpath( '//xdr:twoCellAnchor | //xdr:oneCellAnchor' );
			foreach ( $anchors as $anchor ) {
				$anchor->registerXPathNamespace( 'xdr', 'http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing' );
				$anchor->registerXPathNamespace( 'a', 'http://schemas.openxmlformats.org/drawingml/2006/main' );
				$anchor->registerXPathNamespace( 'r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships' );

				$row_nodes = $anchor->xpath( './xdr:from/xdr:row' );
				if ( ! $row_nodes ) continue;
				$row = (int) (string) $row_nodes[0];

				$blip_nodes = $anchor->xpath( './/a:blip' );
				if ( ! $blip_nodes ) continue;
				$rid_attrs = $blip_nodes[0]->attributes( 'http://schemas.openxmlformats.org/officeDocument/2006/relationships' );
				$rid = (string) $rid_attrs['embed'];
				if ( ! $rid || ! isset( $drawing_rels[ $rid ] ) ) continue;

				$img_path = self::resolve_path( dirname( $drawing_path ), $drawing_rels[ $rid ] );
				$ext = strtolower( pathinfo( $img_path, PATHINFO_EXTENSION ) );
				if ( ! in_array( $ext, array( 'png', 'jpg', 'jpeg', 'gif' ), true ) ) continue; // skip .emf/.wmf decorative icons

				$data = $zip->getFromName( $img_path );
				if ( $data === false ) continue;

				$images[ $row ] = array( 'data' => $data, 'ext' => $ext );
			}
		}

		$zip->close();
		return $images;
	}

	private static function rels_path_for( $part_path ) {
		return dirname( $part_path ) . '/_rels/' . basename( $part_path ) . '.rels';
	}

	/** Resolves an OOXML relative Target (e.g. "../media/image1.png") against a base directory. */
	private static function resolve_path( $base_dir, $target ) {
		if ( strpos( $target, '/' ) === 0 ) return ltrim( $target, '/' );
		$parts = explode( '/', trim( $base_dir, '/' ) . '/' . $target );
		$out = array();
		foreach ( $parts as $p ) {
			if ( $p === '' || $p === '.' ) continue;
			if ( $p === '..' ) { array_pop( $out ); continue; }
			$out[] = $p;
		}
		return implode( '/', $out );
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
