<?php

declare(strict_types = 1);

$includes = [];

if(PHP_VERSION_ID < 80200){
	$includes[] = __DIR__.'/baseline-lt-8.2.neon';
}
elseif(PHP_VERSION_ID < 80300){
	$includes[] = __DIR__.'/baseline-lt-8.3.neon';
}

$config                             = [];
$config['includes']                 = $includes;
$config['parameters']['phpVersion'] = PHP_VERSION_ID;

return $config;
