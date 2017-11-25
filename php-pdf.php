<?php
use mikehaertl\pdftk\Pdf;

echo "PHP version " . phpversion();
exec("pdftk --version", $out);
print_r($out);

/*
// Fill form with data array
$pdf = new Pdf('/Users/stuck/PhpstormProjects/php-pdf/myform.pdf');
$pdf->fillForm([
    'name'=>'ÄÜÖ äüö мирано čárka',
    'nested.name' => 'valX',
])
    ->needAppearances()
    ->saveAs('filled.pdf');

// Fill form from FDF
$pdf = new Pdf('form.pdf');
$pdf->fillForm('data.xfdf')
    ->saveAs('filled.pdf');

// Check for errors
if (!$pdf->saveAs('my.pdf')) {
    $error = $pdf->getError();
}

*/
?>