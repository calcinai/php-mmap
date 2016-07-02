<?php
/**
 * @package    php-mmap
 * @author     Michael Calcinai <michael@calcin.ai>
 */


if(!function_exists('mmap_open')){
    //stream_register_wrapper('mmap', \Calcinai\MMap\StreamWrapper::class); //Only thing here that requires php5.5
    stream_register_wrapper('mmap', '\\Calcinai\\MMap\\StreamWrapper');

    //Kindof a backward way go get it as a resource.
    function mmap_open($file_name, $block_size, $offset = 0){
        //TODO - finish these
        return fopen(sprintf('mmap://%s:%s?offset=%s', $file_name, $block_size, $offset), 'rw');
    }
}