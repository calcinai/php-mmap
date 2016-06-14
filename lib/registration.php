<?php
/**
 * @package    php-mmap
 * @author     Michael Calcinai <michael@calcin.ai>
 */

//Kindof a backward way go get it as a resource.
stream_register_wrapper('mmap', \Calcinai\MMap\StreamWrapper::class);

function mmap_open($file_name, $block_size, $offset = 0){
    //TODO - finish these
    //Happy to suppress error here as an Exception will be thrown on failure.
    $stream = @fopen(sprintf('mmap://%s:%s?offset=%s', $file_name, $block_size, $offset), 'rw');
    return $stream;
}