<?php

/*
 * This file is a part of the Image Metadata Parser Library.
 *
 * (c) 2013 Hauke Schade.
 *
 * For the full copyright and license information, please view the license.txt
 * file that was distributed with this source code.
 */

  include('../imageMetadataParser.php');

  echo "<html><head><title>Simple Test for ImageMetadataParser</title></head><body>\n";

  $imageparser = new ImageMetadataParser('DSC_6318.JPG');
  if (!$imageparser->parseExif())
    echo "Parsing of EXIF failed<br />\n";
  if (!$imageparser->parseIPTC())
    echo "Parsing of IPTC failed<br />\n";

  if ($imageparser->hasTitle())
    echo "Image Title: " . $imageparser->getTitle() . "<br />\n";

  if ($imageparser->hasDateTime())
    echo "Image Taken At: " . date('r', $imageparser->getDateTime()) . "<br />\n";

  if ($imageparser->hasThumbnail()) {
    echo "<img src='data:" . 
        $imageparser->getThumbnailContentType() .
        ";base64," .
        base64_encode( $imageparser->getThumbnail() ) .
        "' />";
  }

  echo "</body></html>";
