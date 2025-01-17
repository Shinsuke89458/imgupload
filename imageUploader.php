<?php

namespace MyApp;

class ImageUploader {

  private $_imageFileName;
  private $_imageType;

  public function upload() {
    try {
      $this->_validateUpload();
      $ext = $this->_validateImageType();
      $savePath = $this->_save($ext);
      $this->_createTumbnail($savePath);
      $_SESSION['success'] = 'Upload Done!';
    } catch (\Exception $e) {
      $_SESSION['error'] = $e->getMessage();
      // exit;
    }
    header('Location: http://'.$_SERVER['HTTP_HOST']);
    exit;
  }

  public function getResults() {
    $success = NULL;
    $error = NULL;
    if (isset($_SESSION['success'])) {
      $success = $_SESSION['success'];
      unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
      $error = $_SESSION['error'];
      unset($_SESSION['error']);
    }
    return [$success, $error];
  }

  public function getImages() {
    $images = [];
    $files = [];
    $imageDir = opendir(IMAGES_DIR);
    while (false !== ($file = readdir($imageDir))) {
      if ($file === '.' || $file === '..') {
        continue;
      }
      $files[] = $file;
      if (file_exists(THUMBNAILS_DIR.'/'.$file)) {
        $images[] = basename(THUMBNAILS_DIR).'/'.$file;
      } else {
        $images[] = basename(IMAGES_DIR).'/'.$file;
      }
    }
    array_multisort($files, SORT_DESC, $images);
    return $images;
  }

  private function _createTumbnail($savePath) {
    $imageSize = getimagesize($savePath);
    $width = $imageSize[0];
    $height = $imageSize[1];
    if ($width > THUMBNAIL_WIDTH) {
      $this->_createTumbnailMain($savePath, $width, $height);
    }
  }
  private function _createTumbnailMain($savePath, $width, $height) {
    switch ($this->_imageType) {
      case IMAGETYPE_GIF:
        $srcImage = imagecreatefromgif($savePath);
        break;
      case IMAGETYPE_JPEG:
        $srcImage = imagecreatefromjpeg($savePath);
        break;
      case IMAGETYPE_PNG:
        $srcImage = imagecreatefrompng($savePath);
        break;
    }
    $thumbHeight = round($height * THUMBNAIL_WIDTH / $width);
    $thumbImage = imagecreatetruecolor(THUMBNAIL_WIDTH, $thumbHeight);
    imagecopyresampled($thumbImage, $srcImage, 0, 0, 0, 0, THUMBNAIL_WIDTH, $thumbHeight, $width, $height);
    switch ($this->_imageType) {
      case IMAGETYPE_GIF:
        imagegif($thumbImage, THUMBNAILS_DIR.'/'.$this->_imageFileName);
        break;
      case IMAGETYPE_JPEG:
        imagejpeg($thumbImage, THUMBNAILS_DIR.'/'.$this->_imageFileName);
        break;
      case IMAGETYPE_PNG:
        imagepng($thumbImage, THUMBNAILS_DIR.'/'.$this->_imageFileName);
        break;
    }
  }

  private function _save($ext) {
    $this->_imageFileName = sprintf('%s_%s.%s', time(), sha1(uniqid(mt_rand(), true)), $ext);
    $savePath = IMAGES_DIR.'/'.$this->_imageFileName;
    $res = move_uploaded_file($_FILES['image']['tmp_name'], $savePath);
    if ($res === false) {
      throw new \Exception('Could not upload!');
    }
    return $savePath;
  }

  private function _validateImageType() {
    $this->_imageType = exif_imagetype($_FILES['image']['tmp_name']);
    switch ($this->_imageType) {
      case IMAGETYPE_GIF:
        return 'gif';
        break;
      case IMAGETYPE_JPEG:
        return 'jpg';
        break;
      case IMAGETYPE_PNG:
        return 'png';
        break;
      default:
        throw new \Exception('PNG/JPEG/GIF only!');
    }
  }

  private function _validateUpload() {
    if (!isset($_FILES['image']) || !isset($_FILES['image']['error'])) {
      throw new \Exception('Upload Error!');
    }
    switch ($_FILES['image']['error']) {
      case UPLOAD_ERR_OK:
        return true;
        break;
      case UPLOAD_ERR_INI_SIZE:
      case UPLOAD_ERR_FORM_SIZE:
        throw new \Exception('File too large!');
        break;
      default:
        throw new \Exception('Err: '.$_FILES['image']['error']);
    }
  }


}
