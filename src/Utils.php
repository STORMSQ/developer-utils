<?php
namespace STORMSQ\DeveloperUtils;

use Log;
use Exception;
use STORMSQ\DeveloperUtils\Services\ImageService;
use STORMSQ\DeveloperUtils\Services\WaterMarkService;
use STORMSQ\DeveloperUtils\Img;
use STORMSQ\DeveloperService\ServiceBuilder;
trait Utils{
    private $waterMarkStatus=false; //浮水印狀態
    private $WaterMarkService=null; //浮水印WaterMarkService instance
    private $useWaterMark = [];
    private $ImageService=null; //圖片處理ImageService instance
    private $must = [];//必填欄位
    private $mustFieldWord = []; //必填欄位描述
    private $isCheckMustField = false;
    private $standbyForDeleteFile = [];
    private $service;
    private $singleRowData = [];
    private $totalData = [];
    private $uploadedFile = [];
    private $singleRowErrorMessage = []; //單筆資料的錯誤紀錄集
    private $totalErrorMessage = [];

    /*public function __construct()
    {
        $this->setWaterMarkService(new WaterMarkService);
    }*/
    public function initUtils()
    {
        $this->setWaterMarkService(new WaterMarkService);
        $this->setImageService(new ImageService);
        $this->setService(new ServiceBuilder);
    }
    public function setUseWaterMark(array $field):void
    {
        $this->useWaterMark = $field;
    }

    public function getUseWaterMark():array
    {
        return $this->useWaterMark;
    }

    public function setStandbyForDeleteFile($filename):void
    {
        $this->standbyForDeleteFile[] = $filename;
    }
    public function getStandbyForDeleteFile():array
    {
        return $this->standbyForDeleteFile;
    }
    public function setIsCheckMustField(bool $status):void
    {
        $this->isCheckMustField = $status;
    }
    public function getIsCheckMustField():bool
    {
        return $this->isCheckMustField;
    }
    public function strRemoveHash($value)
    {
        return preg_replace("/(.+)(-[0-9a-z]{32})/","$1",$value);
    }

    public function setService($service):void
    {
        $this->service = $service;
    }
    public function getService()
    {
        return $this->service;
    }
    public function setImageService(ImageService $imageService):void
    {
        $this->ImageService = $imageService;
    }
    public function getImageService():ImageService
    {
        return $this->ImageService;
    }
    public function setWaterMarkService(WaterMarkService $waterMarkService):void
    {
        $this->WaterMarkService = $waterMarkService;
    }
    public function getWaterMarkService():WaterMarkService
    {
        return $this->WaterMarkService;
    }
    public function setWaterMarkStatus(bool $status):void
    {
        $this->waterMarkStatus = $status;
    }
    public function getWaterMarkStatus():bool
    {
        return $this->waterMarkStatus;
    }
    public function setMustFieldWord(array $data):void
    {
        foreach($data as $key=>$row){
            $this->mustFieldWord[$key] = $row;
        }
    }
    public function getMustFieldWord():array
    {
        return $this->mustFieldWord;
    }
    public function setMust(array $must):void
    {
        foreach($must as $row){
            $this->must[] = $row;
        }
    }
    public function getMust():array
    {
        return $this->must;
    }
    public function imageAction($instance, Img $image,&$imageErrorMessage=[])
    {
        try{
            if(!$image->getImage()){
                $imageErrorMessage[] =$image->getAttribute('clientOriginalName')."必須是圖片格式";
                throw new Exception($image->getAttribute('clientOriginalName')."必須是圖片格式");
            }
         
            $this->setStandbyForDeleteFile(trim(config('developer-utils.uploadFile.path.images'),"//").'/'.$instance->{$image->getImageCode().'_hashname'});
            if($this->getWaterMarkStatus() && in_array($image->getImageCode(),$this->getUseWaterMark())){ 
                $this->getWaterMarkService()->makeWaterMark($image->getImage());
            }
            $instance->{$image->getImageCode().'_hashname'}=$image->getImage()->basename;
            $instance->{$image->getImageCode().'_realname'}=$image->getAttribute('clientOriginalName');

            $this->getImageService()->moveImage($image->getImage());
            $this->setUploadedFile(trim(config('developer-utils.uploadFile.path.images'),"//").'/'.$image->getImage()->basename);
            $instance->save();
            return true;
        }catch(Exception $e){
            Log::info($e);
            return false;
        } 
    }
    public function checkMustField($keys)
    {
        $compare = $keys->map(function($value,$key){
            if(isset($this->list[$this->strRemoveHash($value)])){
                return $this->list[$this->strRemoveHash($value)];
            }
        })->filter(function($value,$key){
            return $value;
        })->unique();
       
        $must = collect($this->getMust());
        $diff = $must->diff($must->intersect($compare));
        if($diff->isNotEmpty()){
            $word="";
            foreach($diff as $row){
                if(isset($this->getMustFieldWord()[$row])){
                    $word.=' '.$this->getMustFieldWord()[$row];
                }
                
            }
            $word.='為必填';
            $this->pushTotalErrorMessage($word);
            
            throw new Exception('必填欄位未填');
        }

    }

    public function setTotalErrorMessage($key,$message,$repeat=false):void
    {
        if($repeat){
            if(!isset($this->totalErrorMessage[$key])){
                $this->totalErrorMessage[$key] = [];
            }
            $data = $this->totalErrorMessage[$key];
            if(is_array($message)){
                if(!is_array($data)){
                    $data = array_merge([$data],$message);
                }else{
                    $data = array_merge($data,$message);
                }
            }else{
                if(is_array($data)){
                    $data[] = $message;
                }else{
                    $data.=$message;
                   
                }
                
            }
            $this->totalErrorMessage[$key] = $data;
            
        }else{
            $this->totalErrorMessage[$key] = $message;
        }
        
    }
    public function pushTotalErrorMessage($message):void
    {
        $this->totalErrorMessage[] = $message;
    }
    public function getTotalErrorMessage():array
    {
        return $this->totalErrorMessage;
    }
    public function flushSingleRowErrorMessage():void
    {
        $this->singleRowErrorMessage = [];
    }
    public function setSingleRowErrorMessage(string $message):void
    {
        $this->singleRowErrorMessage[] = $message;
    }
    public function getSingleRowErrorMessage():array
    {
        return $this->singleRowErrorMessage;
    }

   
    public function setSingleRowData($key,$value,$repeat=false):void
    {
        if($repeat){
            if(!isset($this->singleRowData[$key])){
                $this->singleRowData[$key] = [];
            }
            $data = $this->singleRowData[$key];
            $data[]=$value;
            $this->singleRowData[$key] = $data;
        }else{
            $this->singleRowData[$key]= $value;
        }
        
    }
    public function getSingleRowData():array
    {
        return $this->singleRowData;
    }
    public function flushSingleRowData():void
    {
        $this->singleRowData = [];
    }

    public function setTotalData($value):void
    {
        $this->totalData[]= $value;
    }
    public function getTotalData():array
    {
        return $this->totalData;
    }
    public function flushTotalData():void
    {
        $this->totalData = [];
    }

    public function setUploadedFile(string $filename):void
    {
        $this->uploadedFile[] = $filename;
    }
    public function getUploadedFile():array
    {
        return $this->uploadedFile;
    }

    public function validateImgFormat($img):bool
    {
        $array = explode(".",$img);
        $extension = $array[count($array)-1];
        if(in_array($extension,config('developer-utils.uploadFile.image.validFormat'))){
            return true;
        }else{
            return false;
        }
    }

}
