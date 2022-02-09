<?php
namespace STORMSQ\DeveloperUtils\Services;
use Image;
use Storage;
use Log;
use Exception;
use Illuminate\Http\UploadedFile;

class ImageService {
   
    public $imgFormat = ['jpg','jpeg','png','gif','svg','ico','bmp'];
    private $newFileName=null;
    private $fileSize;
    public function initImage(UploadedFile $image)
    {
        try{
            $this->newFileName=null;
            if(!in_array($image->extension(),$this->imgFormat)){
                return null;
            }
            $image->store(trim(config('developer-utils.uploadFile.path.temp'),'//'),'public');
            $this->newFileName = $image->hashName();
    
            $image = Image::make(public_path().'/storage/'.trim(config('developer-utils.uploadFile.path.temp'),'//').'/'.$this->newFileName);
            return $image;
        }catch(Exception $e){
            Log::error($e);
            Storage::disk('public')->delete(trim(config('developer-utils.uploadFile.path.temp'),'//').'/'.$this->newFileName);
            return null;
        }
       
        
        

    }
    
    public function moveImage($image)
    {
        Storage::disk('public')->move(trim(config('developer-utils.uploadFile.path.temp'),'//').'/'.$image->basename, trim(config('developer-utils.uploadFile.path.images'),'//').'/'.$image->basename);
    }

    public function moveImagetoTempFromFTP($imageName)
    {    
        try{
            $this->newFileName=null;
            $explode = explode(".",$imageName);
            $extension = $explode[count($explode)-1];
            if(!in_array($extension,$this->imgFormat)){
                throw new Exception("不是圖片格式");
            }
            $this->newFileName =  hash("sha256",generateUUID()).".".$extension;
            Storage::disk('public')->copy(trim(config('developer-utils.uploadFile.path.FTPLocation'),'//').'/'.$imageName,trim(config('developer-utils.uploadFile.path.temp'),'//').'/'.$this->newFileName);

            /**
             * 使用同步排程時換成以下
             */
            //$image = Image::make('storage/temp/'.$this->newFileName);

            /**
             * 使用非同步排程時換成以下
             */
            
            $image = Image::make(trim(config('developer-utils.uploadFile.path.temp'),'//').'/'.$this->newFileName);

            return $image;
        }catch(Exception $e){
            Log::error($e);
            Storage::disk('public')->delete(trim(config('developer-utils.uploadFile.path.temp'),'//').'/'.$this->newFileName);
            return null;
        }
               
    }
    public function checkImageType($fileName)
    {
        
        if(false !== $pos = strripos($fileName, '.')){
            if(in_array(substr($fileName, $pos+1, strlen($fileName)),$this->imgFormat) ){
                return true;
            }
        }else{
            return false;
        }
       
    }
    public function checkImagePath($fileName)
    {
        if(Storage::disk('public')->exists(trim(config('developer-utils.uploadFile.path.FTPLocation'),'//').'/'.$fileName)){
            return true;
        }else{
            return false;
        }
    }
    
}
