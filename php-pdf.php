<?php
//use mikehaertl\pdftk\Pdf;

echo "PHP version " . phpversion();

exec("pdftk --version", $out);
echo '<pre>';
print_r($out);
echo  '</pre>';

exec("wkhtmltopdf --version 2>&1", $out2);
echo '<pre>';
print_r($out2);
echo  '</pre>';

exec("wkhtmltopdf --page-size letter --dpi 300 --zoom 4 sample.html sample.pdf 2>&1");
exec("open sample.pdf")

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