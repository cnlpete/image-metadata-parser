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


    if ($imageparser->hasTitle())
    	echo "Image Title: " . $imageparser->getTitle();

Hope you find it useful! :)