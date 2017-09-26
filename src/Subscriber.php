<?php

namespace Hydra;

class Subscriber
{
    private function load()
    {
        if(file_exists($this->dataFile)){
            $data = file_get_contents($this->dataFile);
            $this->topics = unserialize($data);
        }
    }

    private function save()
    {
        $data = serialize($this->topics);
        file_put_contents($this->dataFile, $data);
    }

    public function __construct($datafile)
    {
        $this->topics   = array();
        $this->dataFile = $datafile;
        if(empty($datafile)){
            $this->dataFile = $GLOBALS["DATA_PATH"] . "/subscribe.dat";
        }
        $this->load();
    }

    public function regist($topic, $client)
    {
        $clients = array();
        if(isset($this->topics[$topic])){
            $clients = $this->topics[$topic];
        }
        array_push($clients,$client);
        $clients = array_unique($clients);
        $this->topics[$topic] = $clients;
        $this->save();
    }

    public function unRegist($topic, $client)
    {
        $clients = array();
        if(isset($this->topics[$topic])){
            $clients = $this->topics[$topic];
        }
        $fun = function ($c) use($client){
            return $c != $client;
        };
        $this->topics[$topic] = array_filter($clients, $fun);
        $this->save();
    }

    public function clear()
    {
        $this->topics = array();
        $this->save();
    }

    public function subs($topic)
    {
        $clients = array();
        if(isset($this->topics[$topic])){
            $clients = $this->topics[$topic];
        }
        return $clients;
    }
}
