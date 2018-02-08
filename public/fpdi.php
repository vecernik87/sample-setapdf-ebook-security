<?php

ob_start();

use setasign\FpdiProtection\FpdiProtection;

// setup the autoload function
require_once('../vendor/autoload.php');

$pdf = new FpdiProtection();
$allowed = FpdiProtection::PERM_PRINT | FpdiProtection::PERM_COPY;
$pdf->setProtection($allowed);

$pdf = new \setasign\Fpdi\Fpdi();

$pdf->AddPage();
// set the source file
//$pdf->setSourceFile("Fantastic-Speaker.pdf");
$file = '../pdf/test-links.pdf';
$pageCount = $pdf->setSourceFile($file);
for($pageNo = 1; $pageNo <= $pageCount; $pageNo++){
    // import a page
    $templateId = $pdf->importPage($pageNo);
    if($pageNo > 1){
        $pdf->AddPage();
    }
    // use the imported page and adjust the page size
    $pdf->useTemplate($templateId,['adjustPageSize' => true]);

    $pdf->SetFont('Helvetica');
    $pdf->SetXY(5,5);
    $pdf->Write(8,'test dokumentu otevreneho pomoci '.get_class($pdf));
}
echo '<pre>';
var_dump($pdf);
//die;
// Output the new PDF
while(@ob_end_clean()){
// nothing
}
$pdf->Output();
