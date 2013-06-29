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

  $imageparser = new ImageMetadataParser('test.jpg');
  if (!$imageparser->parseExif())
    echo "Parsing of EXIF failed<br />\n";
  if (!$imageparser->parseIPTC())
    echo "Parsing of IPTC failed<br />\n";

  if ($imageparser->hasTitle())
    echo "Image Title: " . $imageparser->getTitle() . "<br />\n";

  if ($imageparser->hasDateTime())
    echo "Image Taken At: " . date('r', $imageparser->getDateTime()) . "<br />\n";

  if ($imageparser->hasOrientation())
    if ($imageparser->getOrientation() === 0)
      echo "Image is oriented properly.<br />\n";
    else
      echo "Image needs to be rotated with imagerotate(image, " . $imageparser->getOrientation() . ", 0);<br />\n";

  if ($imageparser->hasThumbnail()) {
    echo "<img src='data:" . 
        $imageparser->getThumbnailContentType() .
        ";base64," .
        base64_encode( $imageparser->getThumbnail() ) .
        "' />";
  }

  if ($imageparser->hasGPS()) {
    $dLat = 0; $dLong = 0;
    $imageparser->getGPS($dLat, $dLong);
    echo "approximate GPS position: " .
        "<a href='https://maps.google.com/maps?q=" . $dLat . "," . $dLong . "'>Lat: " . $dLat . " Long: " . $dLong . "</a>";
  }

  echo "</body></html>";
