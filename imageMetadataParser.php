<?php

/*
 * This file is a part of the Image Metadata Parser Library.
 *
 * (c) 2013 Hauke Schade.
 *
 * For the full copyright and license information, please view the license.txt
 * file that was distributed with this source code.
 */

class ImageMetadataParser {

  protected $sFilename;

  protected $aAttributes = array();

  public function __construct($sFilename) {
    $this->sFilename = $sFilename;
  }
  
  public static function exifAvailable() {
    $load_ext = get_loaded_extensions();
    return in_array('exif', $load_ext);
  }

  public function parseExif() {
    $aArr = @exif_read_data($this->sFilename, 'IFD0,THUMBNAIL', true);
    if ($aArr === false)
      return false;

    // the date and time the image was taken
    if (isset($aArr['IFD0']['DateTime'])) {
      $iTimestamp = self::timestampFromEXIF($aArr['IFD0']['DateTime']);
      if ($iTimestamp !== false)
        $this->aAttributes['datetime'] = $iTimestamp;
    }
    else if (isset($aArr['EXIF']['DateTimeOriginal'])) {
      $iTimestamp = self::timestampFromEXIF($aArr['EXIF']['DateTimeOriginal']);
      if ($iTimestamp !== false)
        $this->aAttributes['datetime'] = $iTimestamp;
    }
    else if (isset($aArr['EXIF']['DateTimeDigitized'])) {
      $iTimestamp = self::timestampFromEXIF($aArr['EXIF']['DateTimeDigitized']);
      if ($iTimestamp !== false)
        $this->aAttributes['datetime'] = $iTimestamp;
    }

    // the images title
    if (isset($aArr['COMPUTED']['UserComment']))
      $this->aAttributes['title'] = trim($aArr['COMPUTED']['UserComment']);

    // the thumbnails mimetype
    if (isset($aArr['COMPUTED']['Thumbnail.MimeType']))
      $this->aAttributes['thumbnailtype'] = $aArr['COMPUTED']['Thumbnail.MimeType'];

    // gps
    if (isset($aArr['GPS']))
      $this->aAttributes['gps'] = $aArr['GPS'];

    // the exif orientation
    if (isset($aArr['IFD0']['Orientation']))
      $this->aAttributes['orientation'] = $aArr['IFD0']['Orientation'];

    return true;
  }

  public function parseIPTC() {
    $aArr = @exif_read_data($this->sFilename, 'IDF0', true);
    $size = getimagesize($this->sFilename, $info);
    if(!isset($info['APP13']))
      return false;

    $iptc = iptcparse($info['APP13']);

    if (isset($iptc["2#120"][0])) # caption
      $this->aAttributes['title'] = trim($iptc["2#120"][0]);
    else if (isset($iptc["2#105"][0])) # headline
      $this->aAttributes['title'] = trim($iptc["2#105"][0]);
    else if (isset($iptc["2#005"][0])) # graphic name
      $this->aAttributes['title'] = trim($iptc["2#005"][0]);

    if (isset($iptc["2#055"][0]) && isset($iptc["2#060"][0])) {# creation date
      $iTimestamp = self::timestampFromIPTC($iptc["2#055"][0], $iptc["2#060"][0]);
      if ($iTimestamp !== false)
        $this->aAttributes['datetime'] = $iTimestamp;
    }

    return true;
  }

  private function timestampFromIPTC( $date, $time ) {
    if ( ! ( preg_match('/\d\d\d\d\d\d[-+]\d\d\d\d/', $time)
        && preg_match('/\d\d\d\d\d\d\d\d/', $date)
        && substr($date, 0, 8) !== '00000000' ) ) {
      // wrong dates
      return false;
    }

    $iTimestamp = mktime(
            substr( $time, 0, 2 ), 
            substr( $time, 2, 2 ), 
            substr( $time, 4, 2 ), 
            substr( $date, 4, 2 ), 
            substr( $date, 6, 2 ), 
            substr( $date, 0, 4 ));

    $iDiff = ( intval( substr( $time, 7, 2 ) ) *60*60 )
            + ( intval( substr( $time, 9, 2 ) ) * 60 );
    if ( substr( $time, 6, 1 ) === '-' )
      $iDiff = - $iDiff;

    return $iTimestamp + $iDiff;
  }

  private function timestampFromEXIF( $string ) {
    if ( ! ( preg_match('/\d\d\d\d:\d\d:\d\d \d\d:\d\d:\d\d/', $string))) {
      // wrong date
      return false;
    }

    $iTimestamp = mktime(
            substr( $string, 11, 2 ), 
            substr( $string, 14, 2 ), 
            substr( $string, 17, 2 ), 
            substr( $string, 5, 2 ), 
            substr( $string, 8, 2 ), 
            substr( $string, 0, 4 ));

    return $iTimestamp;
  }

  public function hasTitle() {
    return isset($this->aAttributes['title']);
  }
  public function getTitle() {
    return (string)$this->aAttributes['title'];
  }

  public function hasThumbnail() {
    return (isset($this->aAttributes['thumbnailtype']));
  }
  public function getThumbnail() {
    return exif_thumbnail($this->sFilename);
  }
  public function getThumbnailContentType() {
    return $this->aAttributes['thumbnailtype'];
  }

  public function hasDateTime() {
    return isset($this->aAttributes['datetime']);
  }
  public function getDateTime() {
    return (int)$this->aAttributes['datetime'];
  }

  public function hasGPS() {
    return isset($this->aAttributes['gps']) &&
            isset($this->aAttributes['gps']['GPSLongitude'][0]) &&
            isset($this->aAttributes['gps']['GPSLatitude'][0]);
  }
  public function getGPS(&$dLat, &$dLong) {
    $latFirst  = explode("/", $this->aAttributes['gps']['GPSLatitude'][0]);
    $latSecond = explode("/", $this->aAttributes['gps']['GPSLatitude'][1]);
    $latThird  = explode("/", $this->aAttributes['gps']['GPSLatitude'][2]);
    $latRef    = isset($this->aAttributes['gps']['GPSLatitudeRef']) ? $this->aAttributes['gps']['GPSLatitudeRef'] : 'N';

    $latFirst  = intval($latFirst[0]) / intval($latFirst[1]);
    $latSecond = intval($latSecond[0])/ intval($latSecond[1]);
    $latThird  = intval($latThird[0]) / intval($latThird[1]);

    $dLat = $latFirst + ($latSecond*60 + $latThird) / 3600;
    if ($latRef == 'S')
      $dLat *= -1;

    $longFirst  = explode("/", $this->aAttributes['gps']['GPSLongitude'][0]);
    $longSecond = explode("/", $this->aAttributes['gps']['GPSLongitude'][1]);
    $longThird  = explode("/", $this->aAttributes['gps']['GPSLongitude'][2]);
    $longRef    = isset($this->aAttributes['gps']['GPSLongitudeRef']) ? $this->aAttributes['gps']['GPSLongitudeRef'] : 'E';

    $longFirst  = intval($longFirst[0]) / intval($longFirst[1]);
    $longSecond = intval($longSecond[0])/ intval($longSecond[1]);
    $longThird  = intval($longThird[0]) / intval($longThird[1]);

    $dLong = $longFirst + ($longSecond*60 + $longThird) / 3600;
    if ($longRef == 'W')
      $dLat *= -1;
  }
  public function getGPSArray() {
    $dLat = 0.0; $dLong = 0.0;
    $this->getGPS($dLat, $dLong);
    return array('lat' => $dLat, 'long' => $dLong);
  }

  public function hasOrientation() {
    return isset($this->aAttributes['orientation']);
  }
  public function getOrientation() {
    switch($this->aAttributes['orientation']) {
      case 3:
        return 180;
        break;
      case 6:
        return -90;
        break;
      case 8:
        return 90;
        break;
      default:
        return 0;
    }
  }
  public function getRawOrientation() {
    return $this->aAttributes['orientation'];
  }

}
