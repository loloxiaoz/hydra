<?php

namespace Hydra;

class MsgDTO
{
    public $name;
    public $data;
    public $cls;
    public $tag;
    public $delay;
    public $happen;

    static public function create($name, $data, $tag, $delay)
    {
        $cls         = __class__;
        $dto         = new $cls;
        $dto->happen = time();
        $dto->name   = $name;
        $dto->tag    = $tag;
        if(is_object($data)){
            $dto->cls  = get_class($data);
        }
        $dto->delay = $delay;
        $dto->data  = $data;
        return $dto;
    }

    static public function fromJson($json)
    {
        $cls          = __class__;
        $dto          = new $cls;
        $obj          = json_decode($json);
        if($obj == null){
            return null;
        }
        $dto->name    = $obj->name;
        $dto->happen  = $obj->happen;
        $dto->tag     = $obj->tag;
        $dto->cls     = $obj->cls;
        $dto->delay   = $obj->delay;
        $dto->data    = $obj->data;
        return $dto;
    }

    public function encode()
    {
        $data = $this->data;
        if(is_object($data)){
            $data = json_encode($data);
        }
        $this->data = base64_encode($data);
    }

    public function decode()
    {
        $odata = base64_decode($this->data);
        if($this->cls != null){
            $data = json_decode($odata);
        }
        $this->data = $data==null? $odata : $data;
    }
}


