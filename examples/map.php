<?php

require __DIR__.'/../vendor/autoload.php';


$mmap = fopen('mmap:///dev/mem:1024?offset=0', 'rw');

var_dump(fread($mmap, 8));