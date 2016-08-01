<?php
/**
 * @package    php-mmap
 * @author     Michael Calcinai <michael@calcin.ai>
 */


use Calcinai\MMap\StreamWrapper;

if(!function_exists('mmap_open')){

    StreamWrapper::register();

    //Kindof a backward way go get it as a resource.
    function mmap_open($file_name, $block_size, $offset = 0){
        //TODO - finish these
        return fopen(sprintf('mmap://%s:%s?offset=%s', $file_name, $block_size, $offset), 'rw');
    }
}