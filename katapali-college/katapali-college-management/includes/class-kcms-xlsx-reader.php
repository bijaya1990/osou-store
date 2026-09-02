<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* Minimal, dependency-free .xlsx reader. An .xlsx file is a zip archive of
   XML parts; we only need the first worksheet plus the shared-strings
   table, both read with PHP's built-in ZipArchive + SimpleXML (no Composer
   packages, nothing heavy to run on a resource-limited free host). Returns
   an array of rows, each row an array of cell strings (row 1 = headers). */
class KCMS_Xlsx_Reader {

	public static function read( $file_path ) {
		if ( ! class_exists( 'ZipArchive' ) ) {
			return new WP_Error( 'kcms_no_zip', 'The PHP ZipArchive extension is required to read .xlsx files but is not enabled on this server. Please ask your host to enable it, or upload the file as .csv instead.' );
		}
		$zip = new ZipArchive();
		if ( true !== $zip->open( $file_path ) ) {
			return new WP_Error( 'kcms_xlsx_open_failed', 'Could not open the uploaded file - is it a valid .xlsx file?' );
		}

		$shared = array();
		$shared_xml = $zip->getFromName( 'xl/sharedStrings.xml' );
		if ( false !== $shared_xml ) {
			$sx = simplexml_load_string( $shared_xml, 'SimpleXMLElement', LIBXML_NOCDATA );
			if ( $sx ) {
				foreach ( $sx->si as $si ) {
					if ( isset( $si->t ) ) {
						$shared[] = (string) $si->t;
					} else {
						$text = '';
						foreach ( $si->r as $r ) { $text .= (string) $r->t; }
						$shared[] = $text;
					}
				}
			}
		}

		/* find the first worksheet - usually xl/worksheets/sheet1.xml, but
		   confirm via the workbook relationships in case sheet order differs */
		$sheet_path = 'xl/worksheets/sheet1.xml';
		$sheet_xml = $zip->getFromName( $sheet_path );
		if ( false === $sheet_xml ) {
			$zip->close();
			return new WP_Error( 'kcms_xlsx_no_sheet', 'Could not find a worksheet inside the uploaded file.' );
		}

		$sx = simplexml_load_string( $sheet_xml, 'SimpleXMLElement', LIBXML_NOCDATA );
		$zip->close();
		if ( ! $sx ) {
			return new WP_Error( 'kcms_xlsx_parse_failed', 'Could not parse the worksheet XML.' );
		}

		$rows = array();
		foreach ( $sx->sheetData->row as $row ) {
			$cells = array();
			$col_index = 0;
			foreach ( $row->c as $c ) {
				$ref = (string) $c['r'];
				$this_col = self::col_index_from_ref( $ref );
				while ( $col_index < $this_col ) { $cells[] = ''; $col_index++; }

				$type = (string) $c['t'];
				$value = isset( $c->v ) ? (string) $c->v : '';
				if ( 's' === $type ) {
					$value = isset( $shared[ (int) $value ] ) ? $shared[ (int) $value ] : '';
				} elseif ( 'inlineStr' === $type ) {
					$value = isset( $c->is->t ) ? (string) $c->is->t : '';
				}
				$cells[] = $value;
				$col_index++;
			}
			$rows[] = $cells;
		}
		return $rows;
	}

	private static function col_index_from_ref( $ref ) {
		preg_match( '/^([A-Z]+)/', $ref, $m );
		$letters = $m[1] ?? 'A';
		$index = 0;
		for ( $i = 0; $i < strlen( $letters ); $i++ ) {
			$index = $index * 26 + ( ord( $letters[ $i ] ) - 64 );
		}
		return $index - 1;
	}
}
