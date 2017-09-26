<?php

namespace Hydra;

interface IDispatcher
{
    public function serving($src, $subscriber, $commander);
}
