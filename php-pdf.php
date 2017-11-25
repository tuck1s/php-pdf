<?php
//use mikehaertl\pdftk\Pdf;

echo "PHP version " . phpversion();

exec("pdftk --version 2>&1", $out);
echo '<pre>';
print_r($out); $out = NULL;
echo  '</pre>';

exec("wkhtmltopdf --version 2>&1", $out);
echo '<pre>';
print_r($out); $out = NULL;
echo  '</pre>';

exec("wkhtmltopdf --page-size letter --dpi 300 --zoom 4 sample.html sample.pdf 2>&1", $out);

echo '<pre>';
print_r($out); $out = NULL;
echo  '</pre>';

//phpinfo()
?>