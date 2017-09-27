<?php

namespace Hydra;

interface ILogger
{
    public function info($str,$tag);
    public function debug($str,$tag);
    public function warn($str,$tag);
    public function error($str,$tag);
}
