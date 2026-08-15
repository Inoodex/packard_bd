<?php

namespace App\Helpers;

use ZipArchive;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SimpleXlsxExporter
{
    /**
     * Download data as a fully compliant XLSX spreadsheet.
     *
     * @param string $fileName
     * @param array $headers
     * @param array $rows
     * @return BinaryFileResponse
     */
    public static function download(string $fileName, array $headers, array $rows): BinaryFileResponse
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_');

        $zip = new ZipArchive();
        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Cannot create zip file for XLSX export');
        }

        // Shared strings map
        $sharedStrings = [];
        $sharedStringsMap = [];
        $totalStringCount = 0;

        $getStringIndex = function (string $str) use (&$sharedStrings, &$sharedStringsMap, &$totalStringCount): int {
            $totalStringCount++;
            if (isset($sharedStringsMap[$str])) {
                return $sharedStringsMap[$str];
            }
            $idx = count($sharedStrings);
            $sharedStrings[] = $str;
            $sharedStringsMap[$str] = $idx;
            return $idx;
        };

        // Compute column letters (supports beyond Z)
        $getColLetter = function (int $colIdx): string {
            $letter = '';
            $colIdx += 1;
            while ($colIdx > 0) {
                $mod = ($colIdx - 1) % 26;
                $letter = chr(65 + $mod) . $letter;
                $colIdx = (int)(($colIdx - $mod) / 26);
            }
            return $letter;
        };

        $colCount = count($headers);
        $rowCount = count($rows) + 1;
        $maxColLetter = $getColLetter($colCount - 1);
        $dimensionRef = "A1:{$maxColLetter}{$rowCount}";

        // 1. [Content_Types].xml
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' .
            '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' .
            '<Default Extension="xml" ContentType="application/xml"/>' .
            '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' .
            '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' .
            '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>' .
            '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>' .
            '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>' .
            '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>' .
            '</Types>';
        $zip->addFromString('[Content_Types].xml', $contentTypes);

        // 2. _rels/.rels
        $rootRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' .
            '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>' .
            '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>' .
            '</Relationships>';
        $zip->addFromString('_rels/.rels', $rootRels);

        // 3. docProps/app.xml
        $appXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">' .
            '<Application>Microsoft Excel</Application>' .
            '<DocSecurity>0</DocSecurity>' .
            '<ScaleCrop>false</ScaleCrop>' .
            '<HeadingPairs><vt:vector size="2" baseType="variant"><vt:variant><vt:lpstr>Worksheets</vt:lpstr></vt:variant><vt:variant><vt:i4>1</vt:i4></vt:variant></vt:vector></HeadingPairs>' .
            '<TitlesOfParts><vt:vector size="1" baseType="lpstr"><vt:lpstr>Sheet1</vt:lpstr></vt:vector></TitlesOfParts>' .
            '<Company>Packard</Company>' .
            '<LinksUpToDate>false</LinksUpToDate>' .
            '<SharedDoc>false</SharedDoc>' .
            '<HyperlinksChanged>false</HyperlinksChanged>' .
            '<AppVersion>16.0300</AppVersion>' .
            '</Properties>';
        $zip->addFromString('docProps/app.xml', $appXml);

        // 4. docProps/core.xml
        $dateIso = date('Y-m-d\TH:i:s\Z');
        $coreXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' .
            '<dc:creator>Packard</dc:creator>' .
            '<cp:lastModifiedBy>Packard</cp:lastModifiedBy>' .
            "<dcterms:created xsi:type=\"dcterms:W3CDTF\">{$dateIso}</dcterms:created>" .
            "<dcterms:modified xsi:type=\"dcterms:W3CDTF\">{$dateIso}</dcterms:modified>" .
            '</cp:coreProperties>';
        $zip->addFromString('docProps/core.xml', $coreXml);

        // 5. xl/_rels/workbook.xml.rels
        $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>' .
            '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>' .
            '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>' .
            '</Relationships>';
        $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);

        // 6. xl/workbook.xml
        $workbookXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' .
            '<fileVersion appName="xl" lastEdited="7" lowestEdited="7" rupBuild="27127"/>' .
            '<workbookPr defaultThemeVersion="166925"/>' .
            '<bookViews><workbookView xWindow="0" yWindow="0" windowWidth="25600" windowHeight="14400"/></bookViews>' .
            '<sheets><sheet name="Sheet1" sheetId="1" r:id="rId1"/></sheets>' .
            '<calcPr calcId="191029"/>' .
            '</workbook>';
        $zip->addFromString('xl/workbook.xml', $workbookXml);

        // 7. xl/styles.xml
        $stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' .
            '<fonts count="2">' .
            '<font><sz val="11"/><color theme="1"/><name val="Calibri"/><family val="2"/><scheme val="minor"/></font>' .
            '<font><b/><sz val="11"/><color theme="1"/><name val="Calibri"/><family val="2"/><scheme val="minor"/></font>' .
            '</fonts>' .
            '<fills count="2">' .
            '<fill><patternFill patternType="none"/></fill>' .
            '<fill><patternFill patternType="gray125"/></fill>' .
            '</fills>' .
            '<borders count="1">' .
            '<border><left/><right/><top/><bottom/><diagonal/></border>' .
            '</borders>' .
            '<cellStyleXfs count="1">' .
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>' .
            '</cellStyleXfs>' .
            '<cellXfs count="2">' .
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>' .
            '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>' .
            '</cellXfs>' .
            '<cellStyles count="1">' .
            '<cellStyle name="Normal" xfId="0" builtinId="0"/>' .
            '</cellStyles>' .
            '<dxfs count="0"/>' .
            '<tableStyles count="0" defaultTableStyle="TableStyleMedium2" defaultPivotStyle="PivotStyleLight16"/>' .
            '</styleSheet>';
        $zip->addFromString('xl/styles.xml', $stylesXml);

        // 8. Build Sheet XML
        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' .
            "<dimension ref=\"{$dimensionRef}\"/>" .
            '<sheetViews><sheetView tabSelected="1" workbookViewId="0"/></sheetViews>' .
            '<sheetFormatPr defaultRowHeight="15"/>' .
            '<sheetData>';

        // Header Row (Style s="1" for Bold)
        $sheetXml .= "<row r=\"1\" spans=\"1:{$colCount}\">";
        foreach ($headers as $colIdx => $headerText) {
            $ref = $getColLetter($colIdx) . '1';
            $strIdx = $getStringIndex((string)$headerText);
            $sheetXml .= "<c r=\"{$ref}\" t=\"s\" s=\"1\"><v>{$strIdx}</v></c>";
        }
        $sheetXml .= '</row>';

        // Data Rows
        $rowNum = 2;
        foreach ($rows as $row) {
            $sheetXml .= "<row r=\"{$rowNum}\" spans=\"1:{$colCount}\">";
            foreach ($row as $colIdx => $val) {
                $ref = $getColLetter($colIdx) . $rowNum;
                if ($val === null || $val === '') {
                    // Empty cell
                    continue;
                }

                // Check if strictly a raw int or float
                if (is_int($val) || is_float($val)) {
                    $sheetXml .= "<c r=\"{$ref}\"><v>{$val}</v></c>";
                } elseif (is_numeric($val) && !str_starts_with((string)$val, '+') && !str_starts_with((string)$val, '0') && preg_match('/^-?[0-9]+(\.[0-9]+)?$/', (string)$val)) {
                    $sheetXml .= "<c r=\"{$ref}\"><v>{$val}</v></c>";
                } else {
                    $strIdx = $getStringIndex((string)$val);
                    $sheetXml .= "<c r=\"{$ref}\" t=\"s\"><v>{$strIdx}</v></c>";
                }
            }
            $sheetXml .= '</row>';
            $rowNum++;
        }

        $sheetXml .= '</sheetData>' .
            '<pageMargins left="0.7" right="0.7" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>' .
            '</worksheet>';
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);

        // 9. xl/sharedStrings.xml
        $uniqueCount = count($sharedStrings);
        $sstXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            "<sst xmlns=\"http://schemas.openxmlformats.org/spreadsheetml/2006/main\" count=\"{$totalStringCount}\" uniqueCount=\"{$uniqueCount}\">";
        foreach ($sharedStrings as $str) {
            $cleaned = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', (string)$str);
            $escaped = htmlspecialchars($cleaned, ENT_XML1 | ENT_COMPAT, 'UTF-8');
            $sstXml .= "<si><t xml:space=\"preserve\">{$escaped}</t></si>";
        }
        $sstXml .= '</sst>';
        $zip->addFromString('xl/sharedStrings.xml', $sstXml);

        $zip->close();

        return response()->download($tempFile, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ])->deleteFileAfterSend(true);
    }
}
