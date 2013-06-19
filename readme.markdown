# Image-Metadata-Parser

Hi! Image-Metadata-Parser is a set of php function which try to simplify the access to some imagefiles metadata (exif, itpc or xmp).

## Usage

To use Image-Metadata-Parser, you simply need to place the class somewhere accessible within your application.

Then you can create a new ParserObject with some imagefile and read its metadata.

    <?php
    include('inc/imageMetadataParser.php');

    $imageparser = new ImageMetadataParser('path/to/imagefile.jpg');
    if (!$imageparser->parseExif())
      echo "Parsing of Exif Data failed";
    if (!$imageparser->parseIPTC())
      echo "Parsing of IPTC failed";

Get the title

    if ($imageparser->hasTitle())
      echo "Image Title: " . $imageparser->getTitle();

get the date and time

    if ($imageparser->hasDateTime())
      echo "Image Taken At: " . date('r', $imageparser->getDateTime());

get a thumbnail

    if ($imageparser->hasThumbnail()) {
      echo "<img src='data:" . 
          $imageparser->getThumbnailContentType() .
          ";base64," .
          base64_encode( $imageparser->getThumbnail() ) .
          "' />";
    }

get the gps coordinates

    if ($imageparser->hasGPS()) {
      $dLat = 0; $dLong = 0;
      $imageparser->getGPS($dLat, $dLong);
      echo "approximate GPS position: " .
          "<a href='https://maps.google.com/maps?q=" . $dLat . "," . $dLong . "'>Lat: " . $dLat . " Long: " . $dLong . "</a>";
    }

Hope you find it useful! :)
