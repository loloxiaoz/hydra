<?php
require_once("init.php") ;

for($i =0  ; $i < 10000 ; $i++)
{
    Hydra::trigger("event","Hello") ;
}
