<?php

namespace Hydra;

interface ICollector
{
    public function trigger($topic, $data, $delay=0, $ttl=60);
}


