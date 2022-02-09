<?php
namespace STORMSQ\DeveloperUtils\Services;
use Illuminate\Http\Request;
use Image;
use Storage;
use Log;
use Exception;
class WaterMarkService{

    public $water=null;
    protected $positions = ['top-left','bottom-left','top-right','bottom-right'];
    protected $currentPosition;
    protected $initError=false;
    protected $waterMarkName=null;
    public function setWaterMark($water,$opacity,$position)
    {
        try{
           
            $water->store(trim(config('developer-utils.uploadFile.path.temp'),'//'),'public');
            $this->waterMarkName = $water->hashName();
            $water = Image::make('storage/'.trim(config('developer-utils.uploadFile.path.temp'),'//').'/'.$water->hashName());
            $this->initWaterMark($water,$opacity,$position);
            return true;
        }catch(Exception $e){
            $this->initError=true;
            Storage::disk('public')->delete(trim(config('developer-utils.uploadFile.path.temp'),'//').'/'.$water->hashname());
            return false;
        }
        
    }
    public function getWaterMarkName()
    {
        return $this->waterMarkName;
    }    
    /**
     * 讀取浮水印，用於資料匯入使用
     *
     * @param [type] $path
     * @param [type] $opacity
     * @param [type] $position
     * @return void
     */           
    public function loadExistWaterMark($path,$opacity,$position)
    {
        try{
            $water = Image::make(public_path().'/storage/'.trim(config('developer-utils.uploadFile.path.temp'),'//').'/'.$path); 
            $explode = explode(".",$path);
            $extension = $explode[count($explode)-1];
            $newPath = public_path().'/storage/'.trim(config('developer-utils.uploadFile.path.temp'),'//').'/tempWaterMark.'.$extension; 
            $this->waterMarkName = 'tempWaterMark.'.$extension;
            
            $this->initWaterMark($water,$opacity,$position,public_path().'/storage/'.trim(config('developer-utils.uploadFile.path.temp'),'//').'/'.$this->getWaterMarkName());
            Storage::disk('public')->delete(trim(config('developer-utils.uploadFile.path.temp'),'//').'/'.$path);
            
            return true;
        }catch(Exception $e){
            Log::error($e);
            Storage::disk('public')->delete(trim(config('developer-utils.uploadFile.path.temp'),'//').'/'.$path);
            return false;
        }
    }
    public function initWaterMark($water,$opacity,$position,$newPath=null)
    {
        $this->currentPosition = $position;    
        $water->opacity($opacity); 
        if($newPath!=null){    
            $water->save($newPath);

        }else{
            $water->save();
        }
        
        $this->water = $water; 
    }
    public function isInitSuccess()
    {
        return $this->initError;
    }
    public  function makeWaterMark($image)
    {
        try{
            $image->insert($this->water,$this->positions[$this->currentPosition],5,5);
            $image->save();
            return true;
        }catch(\Exception $e){
            Log::info($e);
            return false;
        }

    }
    public function removeWaterMark()
    {
        if($this->water){
            Storage::disk('public')->delete(trim(config('developer-utils.uploadFile.path.temp'),'//').'/'.$this->getWaterMarkName());
            return true;
        }else{
            return false;
        }
        
    }

    

}