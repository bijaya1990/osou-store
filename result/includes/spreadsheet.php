<?php
/**
 * Dependency-free spreadsheet reading.
 *
 * Supports CSV / TXT (delimiter sniffed) and XLSX (read with the bundled zip
 * and XML extensions — no Composer packages, nothing to install on shared
 * hosting). Old binary .xls is intentionally not supported; the admin screens
 * ask for the file to be re-saved as CSV or XLSX.
 */

define('NPR_MAX_IMPORT_ROWS', 20000);
define('NPR_MAX_IMPORT_COLUMNS', 80);

/**
 * Read a spreadsheet into a header row plus data rows.
 *
 * @return array{ok:bool, error:string, header:array, rows:array, truncated:bool}
 */
function npr_read_spreadsheet($path, $ext)
{
    $ext = strtolower((string) $ext);

    if ($ext === 'xlsx') {
        $raw = npr_read_xlsx($path);
    } else {
        $raw = npr_read_csv($path);
    }

    if (!$raw['ok']) {
        return $raw;
    }

    return npr_normalise_grid($raw['rows'], $raw['truncated']);
}

/**
 * Split a raw grid into header + body, trimming empty leading rows and
 * trailing empty columns.
 */
function npr_normalise_grid(array $rows, $truncated)
{
    $fail = function ($message) {
        return array('ok' => false, 'error' => $message, 'header' => array(), 'rows' => array(), 'truncated' => false);
    };

    // Drop leading rows that are completely empty.
    while ($rows && npr_row_is_empty($rows[0])) {
        array_shift($rows);
    }
    if (!$rows) {
        return $fail('The file does not contain any data.');
    }

    $header = array_shift($rows);

    // Work out how many columns actually carry a header.
    $width = 0;
    foreach ($header as $index => $value) {
        if (trim((string) $value) !== '') {
            $width = $index + 1;
        }
    }
    if ($width === 0) {
        return $fail('The first row of the file must contain column headings.');
    }
    $width = min($width, NPR_MAX_IMPORT_COLUMNS);

    $header = array_slice(array_pad($header, $width, ''), 0, $width);
    foreach ($header as $i => $value) {
        $value = trim(preg_replace('/\s+/u', ' ', (string) $value));
        $header[$i] = $value === '' ? 'Column ' . ($i + 1) : $value;
    }

    $body = array();
    foreach ($rows as $row) {
        if (npr_row_is_empty($row)) {
            continue;
        }
        $body[] = array_slice(array_pad($row, $width, ''), 0, $width);
    }

    if (!$body) {
        return $fail('The file contains column headings but no student rows.');
    }

    return array('ok' => true, 'error' => '', 'header' => $header, 'rows' => $body, 'truncated' => (bool) $truncated);
}

function npr_row_is_empty($row)
{
    if (!is_array($row)) {
        return true;
    }
    foreach ($row as $cell) {
        if (trim((string) $cell) !== '') {
            return false;
        }
    }
    return true;
}

/* ---------------------------------------------------------------------------
 * CSV
 * ------------------------------------------------------------------------ */

function npr_read_csv($path)
{
    $handle = @fopen($path, 'rb');
    if (!$handle) {
        return array('ok' => false, 'error' => 'The uploaded file could not be opened.', 'rows' => array(), 'truncated' => false);
    }

    $sample = (string) fread($handle, 8192);
    rewind($handle);

    $delimiter = npr_sniff_delimiter($sample);
    $needsUtf8 = !npr_looks_like_utf8($sample);

    $rows = array();
    $truncated = false;
    $first = true;

    while (($row = fgetcsv($handle, 0, $delimiter, '"', '\\')) !== false) {
        if ($row === array(null)) { // blank line
            continue;
        }
        if (count($rows) >= NPR_MAX_IMPORT_ROWS + 1) {
            $truncated = true;
            break;
        }
        foreach ($row as $i => $cell) {
            $cell = (string) $cell;
            if ($needsUtf8) {
                $cell = npr_to_utf8($cell);
            }
            if ($first && $i === 0) {
                $cell = preg_replace('/^\xEF\xBB\xBF/', '', $cell); // strip BOM
            }
            $row[$i] = trim($cell);
        }
        $first = false;
        $rows[] = $row;
    }
    fclose($handle);

    if (!$rows) {
        return array('ok' => false, 'error' => 'The CSV file appears to be empty.', 'rows' => array(), 'truncated' => false);
    }

    return array('ok' => true, 'error' => '', 'rows' => $rows, 'truncated' => $truncated);
}

function npr_sniff_delimiter($sample)
{
    $candidates = array(',' => 0, ';' => 0, "\t" => 0, '|' => 0);
    $lines = preg_split('/\r\n|\r|\n/', $sample);
    $checked = 0;

    foreach ($lines as $line) {
        if (trim($line) === '') {
            continue;
        }
        foreach ($candidates as $char => $count) {
            $candidates[$char] += substr_count($line, $char);
        }
        if (++$checked >= 5) {
            break;
        }
    }

    arsort($candidates);
    $best = key($candidates);
    return $candidates[$best] > 0 ? $best : ',';
}

function npr_looks_like_utf8($string)
{
    return (bool) preg_match('//u', $string);
}

function npr_to_utf8($string)
{
    if (npr_looks_like_utf8($string)) {
        return $string;
    }
    if (function_exists('mb_convert_encoding')) {
        return mb_convert_encoding($string, 'UTF-8', 'Windows-1252, ISO-8859-1');
    }
    if (function_exists('iconv')) {
        $out = @iconv('Windows-1252', 'UTF-8//IGNORE', $string);
        if ($out !== false) {
            return $out;
        }
    }
    return preg_replace('/[^\x20-\x7E]/', '', $string);
}

/* ---------------------------------------------------------------------------
 * XLSX
 * ------------------------------------------------------------------------ */

function npr_read_xlsx($path)
{
    $fail = function ($message) {
        return array('ok' => false, 'error' => $message, 'rows' => array(), 'truncated' => false);
    };

    if (!class_exists('ZipArchive')) {
        return $fail('This server cannot read .xlsx files (the PHP zip extension is missing). Please save the file as CSV and upload it again.');
    }
    if (!class_exists('XMLReader')) {
        return $fail('This server cannot read .xlsx files (the PHP XML extension is missing). Please save the file as CSV and upload it again.');
    }

    $zip = new ZipArchive();
    if ($zip->open($path) !== true) {
        return $fail('The .xlsx workbook could not be opened. Please re-save it and try again.');
    }

    $sheetPath = npr_xlsx_first_sheet_path($zip);
    if ($sheetPath === '') {
        $zip->close();
        return $fail('No worksheet was found inside the workbook.');
    }

    $shared = npr_xlsx_shared_strings($zip);
    $dateStyles = npr_xlsx_date_styles($zip);
    $sheetXml = $zip->getFromName($sheetPath);
    $zip->close();

    if ($sheetXml === false) {
        return $fail('The first worksheet could not be read.');
    }

    $reader = new XMLReader();
    $opened = @$reader->XML($sheetXml, 'UTF-8', LIBXML_NONET);
    if (!$opened) {
        return $fail('The worksheet XML could not be parsed.');
    }

    $rows = array();
    $truncated = false;

    while ($reader->read()) {
        if ($reader->nodeType !== XMLReader::ELEMENT || $reader->name !== 'row') {
            continue;
        }
        if (count($rows) >= NPR_MAX_IMPORT_ROWS + 1) {
            $truncated = true;
            break;
        }

        $rowXml = $reader->readOuterXml();
        $rows[] = npr_xlsx_parse_row($rowXml, $shared, $dateStyles);
    }
    $reader->close();

    if (!$rows) {
        return $fail('The worksheet does not contain any rows.');
    }

    return array('ok' => true, 'error' => '', 'rows' => $rows, 'truncated' => $truncated);
}

/**
 * Resolve the path of the first worksheet in workbook order.
 */
function npr_xlsx_first_sheet_path(ZipArchive $zip)
{
    $workbook = $zip->getFromName('xl/workbook.xml');
    $rels = $zip->getFromName('xl/_rels/workbook.xml.rels');

    if ($workbook !== false && $rels !== false) {
        $prev = libxml_use_internal_errors(true);
        $wb = simplexml_load_string($workbook, 'SimpleXMLElement', LIBXML_NONET);
        $rl = simplexml_load_string($rels, 'SimpleXMLElement', LIBXML_NONET);
        libxml_use_internal_errors($prev);

        if ($wb !== false && $rl !== false) {
            $map = array();
            foreach ($rl->children() as $rel) {
                $map[(string) $rel['Id']] = (string) $rel['Target'];
            }
            $sheets = $wb->sheets ? $wb->sheets->children() : array();
            foreach ($sheets as $sheet) {
                $rid = '';
                foreach ($sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships') as $name => $value) {
                    if ($name === 'id') {
                        $rid = (string) $value;
                    }
                }
                if ($rid !== '' && isset($map[$rid])) {
                    $target = ltrim($map[$rid], '/');
                    if (strpos($target, 'xl/') !== 0) {
                        $target = 'xl/' . $target;
                    }
                    if ($zip->locateName($target) !== false) {
                        return $target;
                    }
                }
            }
        }
    }

    // Fall back to the conventional location, then to any sheet in the archive.
    if ($zip->locateName('xl/worksheets/sheet1.xml') !== false) {
        return 'xl/worksheets/sheet1.xml';
    }
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = $zip->getNameIndex($i);
        if (strpos($name, 'xl/worksheets/') === 0 && substr($name, -4) === '.xml') {
            return $name;
        }
    }
    return '';
}

/**
 * Shared string table as a plain array.
 */
function npr_xlsx_shared_strings(ZipArchive $zip)
{
    $xml = $zip->getFromName('xl/sharedStrings.xml');
    if ($xml === false) {
        return array();
    }

    $reader = new XMLReader();
    if (!@$reader->XML($xml, 'UTF-8', LIBXML_NONET)) {
        return array();
    }

    $strings = array();
    while ($reader->read()) {
        if ($reader->nodeType === XMLReader::ELEMENT && $reader->name === 'si') {
            $node = $reader->readOuterXml();
            $text = '';
            if (preg_match_all('/<t[^>]*>(.*?)<\/t>/su', $node, $matches)) {
                $text = implode('', $matches[1]);
            }
            $strings[] = npr_xml_decode($text);
        }
    }
    $reader->close();

    return $strings;
}

/**
 * Style indexes that represent dates, so serial numbers can be rendered
 * as readable dates instead of five-digit numbers.
 */
function npr_xlsx_date_styles(ZipArchive $zip)
{
    $xml = $zip->getFromName('xl/styles.xml');
    if ($xml === false) {
        return array();
    }

    $prev = libxml_use_internal_errors(true);
    $styles = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NONET);
    libxml_use_internal_errors($prev);
    if ($styles === false) {
        return array();
    }

    $builtinDateFormats = array(14, 15, 16, 17, 18, 19, 20, 21, 22, 45, 46, 47);
    $customDateFormats = array();

    if (isset($styles->numFmts)) {
        foreach ($styles->numFmts->numFmt as $fmt) {
            $code = (string) $fmt['formatCode'];
            $stripped = preg_replace('/\[[^\]]*\]|"[^"]*"/', '', $code);
            if (preg_match('/[dmyhs]/i', $stripped) && !preg_match('/^[#0.,%\s]*$/', $stripped)) {
                $customDateFormats[] = (int) $fmt['numFmtId'];
            }
        }
    }

    $dateStyles = array();
    if (isset($styles->cellXfs)) {
        $index = 0;
        foreach ($styles->cellXfs->xf as $xf) {
            $numFmtId = (int) $xf['numFmtId'];
            if (in_array($numFmtId, $builtinDateFormats, true) || in_array($numFmtId, $customDateFormats, true)) {
                $dateStyles[$index] = true;
            }
            $index++;
        }
    }

    return $dateStyles;
}

/**
 * Parse one <row> element into a zero-indexed array of cell strings.
 */
function npr_xlsx_parse_row($rowXml, array $shared, array $dateStyles)
{
    $row = array();

    if (!preg_match_all('/<c\s([^>]*)\/>|<c\s([^>]*)>(.*?)<\/c>/su', $rowXml, $cells, PREG_SET_ORDER)) {
        return $row;
    }

    $cursor = 0;
    foreach ($cells as $cell) {
        $attributes = $cell[1] !== '' ? $cell[1] : (isset($cell[2]) ? $cell[2] : '');
        $inner = isset($cell[3]) ? $cell[3] : '';

        $index = $cursor;
        if (preg_match('/r="([A-Z]+)\d+"/', $attributes, $ref)) {
            $index = npr_column_index($ref[1]);
        }
        if ($index > NPR_MAX_IMPORT_COLUMNS) {
            continue;
        }

        $type = preg_match('/t="([^"]+)"/', $attributes, $t) ? $t[1] : 'n';
        $styleId = preg_match('/s="(\d+)"/', $attributes, $s) ? (int) $s[1] : -1;

        $value = '';
        if ($type === 'inlineStr') {
            if (preg_match_all('/<t[^>]*>(.*?)<\/t>/su', $inner, $texts)) {
                $value = npr_xml_decode(implode('', $texts[1]));
            }
        } elseif (preg_match('/<v[^>]*>(.*?)<\/v>/su', $inner, $v)) {
            $value = npr_xml_decode($v[1]);
            if ($type === 's') {
                $sharedIndex = (int) $value;
                $value = isset($shared[$sharedIndex]) ? $shared[$sharedIndex] : '';
            } elseif ($type === 'b') {
                $value = $value === '1' ? 'TRUE' : 'FALSE';
            } elseif ($type === 'e') {
                $value = '';
            } elseif (isset($dateStyles[$styleId]) && is_numeric($value)) {
                $value = npr_excel_serial_to_date((float) $value);
            } elseif (is_numeric($value)) {
                // Trim float noise such as 78.00000000000001.
                $value = npr_trim_float($value);
            }
        }

        // Pad any skipped columns.
        for ($i = count($row); $i < $index; $i++) {
            $row[$i] = '';
        }
        $row[$index] = trim((string) $value);
        $cursor = $index + 1;
    }

    ksort($row);
    return array_values($row);
}

function npr_column_index($letters)
{
    $letters = strtoupper($letters);
    $index = 0;
    $length = strlen($letters);
    for ($i = 0; $i < $length; $i++) {
        $index = $index * 26 + (ord($letters[$i]) - 64);
    }
    return max(0, $index - 1);
}

function npr_xml_decode($text)
{
    return html_entity_decode((string) $text, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

function npr_trim_float($value)
{
    $float = (float) $value;
    $rounded = round($float, 6);
    if (abs($rounded - round($rounded)) < 0.0000001) {
        return (string) (int) round($rounded);
    }
    return rtrim(rtrim(sprintf('%.6F', $rounded), '0'), '.');
}

/**
 * Convert an Excel date serial to Y-m-d (or Y-m-d H:i when it carries a time).
 */
function npr_excel_serial_to_date($serial)
{
    if ($serial <= 0) {
        return (string) $serial;
    }
    // Excel's leap-year bug: serial 60 is the non-existent 29 Feb 1900.
    $days = (int) floor($serial);
    $fraction = $serial - $days;
    if ($days > 59) {
        $days--;
    }
    $timestamp = ($days - 25568) * 86400 + (int) round($fraction * 86400);
    if ($timestamp < -62135596800) {
        return (string) $serial;
    }
    return $fraction > 0.00001 ? gmdate('Y-m-d H:i', $timestamp) : gmdate('Y-m-d', $timestamp);
}
