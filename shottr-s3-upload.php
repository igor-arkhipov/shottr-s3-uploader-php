<?php

require 'vendor/autoload.php';
require '../../creds.php'; // place outside webserver folder

use Aws\S3\S3Client;
use Aws\Exception\AwsException;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $tmpPath = $file['tmp_name'];

    // [TODO] maybe need logging to file

    $s3 = new S3Client([
      'version' => 'latest',
      'region' => $region,
      'credentials' => [
        'key' => $key,
        'secret' => $secret
      ]
    ]);

    try {
      $result = $s3->putObject([
        'Bucket' => $bucket,
        'Key' => $path . '/' . $file['name'],
        'Body' => fopen($tmpPath, 'rb'),
        'ACL' => 'public-read',
        'ContentType' => mime_content_type($tmpPath),
        'CacheControl' => 'max-age=31536000'
      ]);

      $filename = basename($result['ObjectURL']);
      $longOwnUrl = "https://{$domain}/{$path}/{$filename}";

      // [TODO] request to url shortner

      $resultUrl = $longOwnUrl;

      $response = "SUCCESS: " . $resultUrl;
    } catch (AwsException $e) {
      $response = "ERROR: " . $e->getMessage();
    }
  } else {
    $response = "ERROR: File not found";
  }
} else {
  $response = "ERROR: Invalid request method";
}

echo $response;
