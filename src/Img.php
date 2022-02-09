<?php
namespace STORMSQ\DeveloperUtils;
use Intervention\Image\Facades\Image;
use Illuminate\Http\UploadedFile;
class Img{
    private $image;
    private $imageName;
    private $imageCode;
    private $imagePath;
    private $basename;
    public function __construct($image=null,$imageName=null,$imageCode='image',$from='upload')
    {
        $this->image = $image;
        $this->imageName = $imageName;
        $this->imageCode = $imageCode;
    }
 
    public function setOriginalName($filename)
    {
        $this->originalName = $filename;
    }
    public function getOriginalName()
    {
        return $this->originalName;
    }
    public function getImage()
    {
        return $this->image;
    }
    public function setImage($image)
    {
        $this->image = $image;
    }
    public function setImageName($imageName)
    {
        $this->imageName = $imageName;
    }
    public function getImageName()
    {
        return $this->imageName;
    }
    public function setImageCode($code)
    {
        $this->imageCode = $code;
    }
    public function getImageCode()
    {
        return $this->imageCode;
    }
    public function setAttribute($attributeKey,$attributeValue)
    {
        $this->$attributeKey = $attributeValue;
    }
    public function getAttribute($key)
    {
        if(\property_exists($this,$key)){
            return $this->$key;
        }else{
            return null;
        }
        
    }
    public function setImagePath($path)
    {
        $this->imagePath = $path;
    }
    public function getImagePath()
    {
        return $this->imagePath;
    }


}