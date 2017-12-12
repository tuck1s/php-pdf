<?php
// php-pdf: Experiment in converting files to PDF in PHP, for Heroku deployment
//
// Author: Steve Tuck, November 2017
//
// External tool binary dependencies:
//  wkhtmltopdf     -
//  pdftk           -
//
//  SparkPost PHP library - for more info see https://developers.sparkpost.com
//      installation instructions on https://github.com/SparkPost/php-sparkpost

require "vendor/autoload.php";
use SparkPost\SparkPost;
use GuzzleHttp\Client;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;

echo "<pre>";
echo "PHP version " . phpversion() . PHP_EOL;

$apiKey = getenv("SPARKPOST_API_KEY");
if(!isset($apiKey)) {
    echo "Error: SPARKPOST_API_KEY must be set";
    exit(1);
}

// Prepare SparkPost API adapter
$httpClient = new GuzzleAdapter(new Client());
$sparky = new SparkPost($httpClient, ["key"=> $apiKey]);
$recip = getenv("RECIPIENT");

// Generate the PDF file
$wk = "bin/wkhtmltopdf";

//exec("pdftk --version 2>&1", $out);
//print_r($out); $out = NULL;

exec($wk . " --version 2>&1", $out);
print_r($out); $out = NULL;

$pdfDoc = "sample.pdf";                             //TODO: use temp files
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
                "email" => "bob.lumreeker@gmail.com"
            ]
        ]
    ],
    "campaign"   => "php-pdf"
];

$jsonReq["content"]["attachments"] = [
    [
        "type" => "application/pdf",
        "name" => "gdpr_report.pdf",
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