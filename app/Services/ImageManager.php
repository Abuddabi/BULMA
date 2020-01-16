<?php
namespace App\Services;

use Intervention\Image\ImageManagerStatic as Image;

class ImageManager
{
  private $galleryFolder;
  private $profileFolder;
  public $folder;

  public function __construct()
  {
    $this->galleryFolder = config('galleryFolder');
    $this->profileFolder = config('profileFolder');
    $this->folder;
  }

  public function whichFolder($whichFolder)
  {
    if($whichFolder == null) $this->folder = $this->galleryFolder;
    elseif($whichFolder == 'avatar') $this->folder = $this->profileFolder;

    return $this->folder;
  }

  public function uploadImage($image, $currentImage = null, $whichFolder = null)
  { 
    $folder = $this->whichFolder($whichFolder);
    if(!is_file($image['tmp_name']) && !is_uploaded_file($image['tmp_name'])) { return $currentImage; }

    $this->deleteImage($currentImage, $folder);

    $filename = strtolower(str_random(10)) . '.' . pathinfo($image['name'], PATHINFO_EXTENSION);
    $image = Image::make($image['tmp_name']);
    $image->save($folder . $filename);
    // move_uploaded_file($image['tmp_name'], $this->folder . $filename);
    return $filename;
  }

  public function checkImageExists($path, $whichFolder)
  {
    $folder = $this->whichFolder($whichFolder);
    if($path != null && is_file($folder . $path) && file_exists($folder . $path)) {
      return true;
    }
  }

  public function deleteImage($image, $whichFolder = null)
  {
    $folder = $this->whichFolder($whichFolder);
    if($this->checkImageExists($image, $whichFolder)) {
      unlink($folder . $image);
    }
  }

  public function getDimensions($file, $whichFolder = null)
  {
    $folder = $this->whichFolder($whichFolder);
    if($this->checkImageExists($file, $whichFolder)) {
      list($width, $height) = getimagesize($folder . $file);
      return $width . "x" . $height;
    }
  }

  public function getImage($image, $whichFolder = null) 
  {
    // echo $whichFolder;
    $folder = $this->whichFolder($whichFolder);
    // echo $folder;
    if($this->checkImageExists($image, $whichFolder)) {
        return '/public/'.$folder . $image;
    }

    return '/public/img/no-user.png';
  }
}