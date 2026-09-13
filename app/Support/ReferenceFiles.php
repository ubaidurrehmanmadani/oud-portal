<?php

namespace App\Support;

use App\Models\WorkspaceItem;
use Illuminate\Support\Facades\Storage;

class ReferenceFiles
{
    public function attach(WorkspaceItem $record, string $fileName): void
    {
        if ($record->file_path && ! str_starts_with($record->file_path, 'workspace/demo/') && ! str_starts_with($record->file_path, 'workspace/reference/')) {
            return;
        }
        $path = 'workspace/reference/'.$record->id.'/'.$fileName;
        $lines = [$record->title, 'OUD supplied reference content', $record->property?->name ?? '', $record->period ?? '', $record->body ?? '', 'Reference preview: the original supporting file was not supplied with the HTML screens.'];
        if (str_ends_with($fileName, '.xlsx')) {
            $temporary = tempnam(sys_get_temp_dir(), 'oud-reference-');
            $zip = new \ZipArchive;
            $zip->open($temporary, \ZipArchive::OVERWRITE);
            $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>');
            $zip->addFromString('_rels/.rels', '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
            $zip->addFromString('xl/workbook.xml', '<?xml version="1.0"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Reference content" sheetId="1" r:id="rId1"/></sheets></workbook>');
            $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>');
            $rows = '';
            foreach ($lines as $index => $line) {
                $rows .= '<row r="'.($index + 1).'"><c r="A'.($index + 1).'" t="inlineStr"><is><t>'.htmlspecialchars($line, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</t></is></c></row>';
            }
            $zip->addFromString('xl/worksheets/sheet1.xml', '<?xml version="1.0"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><cols><col min="1" max="1" width="110" customWidth="1"/></cols><sheetData>'.$rows.'</sheetData></worksheet>');
            $zip->close();
            Storage::disk('local')->put($path, file_get_contents($temporary));
            unlink($temporary);
        } else {
            $stream = "BT /F1 12 Tf 50 780 Td 18 TL\n";
            foreach ($lines as $line) {
                foreach (explode("\n", wordwrap($line, 85)) as $wrapped) {
                    $stream .= '('.str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $wrapped).") Tj T*\n";
                }
                $stream .= "T*\n";
            }
            $stream .= 'ET';
            $objects = ['<< /Type /Catalog /Pages 2 0 R >>', '<< /Type /Pages /Kids [3 0 R] /Count 1 >>', '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>', '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>', '<< /Length '.strlen($stream).">>\nstream\n".$stream."\nendstream"];
            $pdf = "%PDF-1.4\n";
            $offsets = [0];
            foreach ($objects as $index => $object) {
                $offsets[] = strlen($pdf);
                $pdf .= ($index + 1)." 0 obj\n".$object."\nendobj\n";
            }
            $xref = strlen($pdf);
            $pdf .= "xref\n0 6\n0000000000 65535 f \n";
            foreach (array_slice($offsets, 1) as $offset) {
                $pdf .= sprintf("%010d 00000 n \n", $offset);
            }
            $pdf .= "trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n".$xref."\n%%EOF\n";
            Storage::disk('local')->put($path, $pdf);
        }
        $record->update(['file_path' => $path, 'file_name' => $fileName]);
    }
}
