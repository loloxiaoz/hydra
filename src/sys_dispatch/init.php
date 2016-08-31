<?php
include "pylon/pylon.php" ;
// XSetting::$logMode   = XSetting::LOG_DEBUG_MODE ;
XSetting::$logMode   = XSetting::LOG_ONLINE_MODE ;
XSetting::$prjName   = "hydra" ;
XSetting::$logTag    = XSetting::ensureEnv("USER") ;
XSetting::$runPath   = XSetting::ensureEnv("RUN_PATH") ;
XSetting::$bootstrap = XSetting::ensureEnv("PRJ_ROOT")  . "/src/sys_dispatch/bootstrap.php" ;
XPylon::useEnv() ;
