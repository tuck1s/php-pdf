<?php

echo '<pre>';
echo "PHP version " . phpversion();

$os = php_uname();

// Check if running on Linux, e.g. Heroku
if (strpos($os, 'Ubuntu') !== false) {
    $wk = "/app/bin/wkhtmltopdf";
}
    else {
        $wk = "wkhtmltopdf";                            // Mac appropriate
}

exec("pdftk --version 2>&1", $out);
print_r($out); $out = NULL;

exec($wk." --version 2>&1", $out);
print_r($out); $out = NULL;

exec($wk." --page-size letter --dpi 300 --zoom 4 sample.html sample.pdf 2>&1", $out);

print_r($out); $out = NULL;

//phpinfo()
?>