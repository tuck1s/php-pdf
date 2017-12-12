<?php
// php-pdf: Experiment in converting files to PDF in PHP, for Heroku deployment
//
// Author: Steve Tuck, November 2017
//
// External tool binary dependencies:
//  wkhtmltopdf - 0.12.3    - for local install see  https://github.com/wkhtmltopdf/wkhtmltopdf/releases/0.12.3/
//                            Heroku uses app.json buildpack definition
//
//  pdftk       -
//
//  SparkPost PHP library - for more info see https://developers.sparkpost.com
//      installation instructions on https://github.com/SparkPost/php-sparkpost

require "vendor/autoload.php";
use SparkPost\SparkPost;
use GuzzleHttp\Client;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;

function fileSystemSafeName($f) {
    // From: https://stackoverflow.com/questions/2021624/string-sanitizer-for-filename
    // Remove anything which isn't a word, whitespace, number, or any of the following caracters -_~,;[]().
    $f = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '-', $f);
    // Remove any runs of periods (thanks falstro!)
    $f = mb_ereg_replace("([\.]{2,})", '', $f);
    return $f;
}

// -----------------------------------------------------------------------------------------
// Main code
// -----------------------------------------------------------------------------------------

// TODO: These variables will be set by the calling code. These are just temporary values.
$recipient = "bob.lumreeker@gmail.com";
$password = "1234";

echo "<pre>";
echo "PHP version " . phpversion() . PHP_EOL;

$apiKey = getenv("SPARKPOST_API_KEY");
if(!$apiKey) {
    echo "Error: SPARKPOST_API_KEY config variable must be set" . PHP_EOL;
    exit(1);
}

// Prepare SparkPost API adapter
$httpClient = new GuzzleAdapter(new Client());
$sparky = new SparkPost($httpClient, ["key"=> $apiKey]);
$recip = getenv("RECIPIENT");

// Generate the PDF file. We expect to find this utility in a local subdir, as that's the default Heroku buildpack behavior
// and it's fairly easy to replicate in local testing
$wk = "bin/wkhtmltopdf";

//exec("pdftk --version 2>&1", $out);
//print_r($out); $out = NULL;

exec($wk . " --version 2>&1", $out);
print_r($out); $out = NULL;

// Need a named temporary file for the PDF output, and a similar (non-temp) name for the recipient attachment.
// PHP temp files don't have an extension, whereas it's useful to have one so we can open the temp files correctly.
$pdfDoc = tempnam(".", "gdpr-report-". fileSystemSafeName($recipient) . "-");
if(!rename($pdfDoc, $pdfDoc . ".pdf")) {
    echo "Error - could not rename temp file to have a .pdf extension";
    exit(1);
}
else {
    $pdfDoc .= ".pdf";
}
echo "Generating PDF in temp file " . $pdfDoc . PHP_EOL;
$userFileName = basename($pdfDoc);

exec($wk . " --page-size letter --dpi 300 SuppressionListEntries.html recipientlistEntries.html EventlistEntries.html $pdfDoc 2>&1", $out);
print_r($out); $out = NULL;

// Cook the PDF file into required Base64 format
$b64Doc = chunk_split(base64_encode(file_get_contents($pdfDoc)));

// Build the request structure
$jsonReq = [
    "content" => [
        "from" => [
            "name" => "SparkPost Team",
            "email" => "steve@email.thetucks.com",                  //TODO
        ],
        "subject" => "Your report",                                 //TODO - maybe use a template for this
        "html" => "<html><body><p>Hi, {{name}}<br>Attached is your personal report. You will need your password to open this file.</p></body></html>",
        "text" => "Hi, {{name}}\nAttached is your personal report. You will need your password to open this file.",
    ],
    "substitution_data" => ["name" => "billybob"],                  //TODO
    "recipients" => [
        [
            "address" => [
                "name" => "Bob",
                "email" => $recipient
            ]
        ]
    ],
    "campaign"   => "php-pdf"
];

$jsonReq["content"]["attachments"] = [
    [
        "type" => "application/pdf",
        "name" => $userFileName,
        "data" => $b64Doc
    ]
];

// Send the mail
$sparky->setOptions(["async" => false]);                            // Keep it simple for now, use synchronous call
try {
    $res = $sparky->request("POST", "transmissions", $jsonReq);

    echo $res->getStatusCode()."\n";
    print_r($res->getBody())."\n";
}
catch (\Exception $e) {
    echo $e->getCode()."\n";
    echo $e->getMessage()."\n";
}
?>