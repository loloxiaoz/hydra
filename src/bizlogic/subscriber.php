<?php
class Subscriber
{
    public function  __construct($datafile=null) 
    {
        $this->topics = array();     
        $this->dataFile = $datafile ;
        if($datafile == null)
        {
            $this->dataFile    = XEnv::get("DATA_PATH") . "/subscribe.dat" ;
        }
        $this->load();

    }
    public function unRegist($topic,$client) 
    {
        $clients = array();
        if(isset($this->topics[$topic]))
        {
            $clients = $this->topics[$topic] ;
        }
        $fun = function ($c) use($client) {  return $c != $client ; } ;
        $this->topics[$topic] =  array_filter($clients,$fun) ;
        $this->save();
    }
    public function clear()
    {
        $this->topics = array();
        $this->save();
    }

    private function save()
    {
            $data = serialize($this->topics) ;
            file_put_contents($this->dataFile,$data) ;

    }
    private function load() 
    {
        if(file_exists($this->dataFile))
        {
            $data = file_get_contents($this->dataFile) ;
            $this->topics = unserialize($data) ;
        }
        
    }

    public function regist($topic,$client) 
    {
        $clients = array();
        if(isset($this->topics[$topic]))
        {
            $clients = $this->topics[$topic] ;
        }
        array_push($clients,$client);
        $clients = array_unique($clients) ;
        $this->topics[$topic] = $clients ;
        $this->save();


    }
    public function subs($topic) 
    {
        $clients = array();
        if(isset($this->topics[$topic]))
        {
            $clients = $this->topics[$topic] ;
        }
        return $clients;
    }
}
