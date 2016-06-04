<?php
/**
 * @package    php-mmap
 * @author     Michael Calcinai <michael@calcin.ai>
 */

namespace Calcinai\MMap;

class StreamWrapper {

    private $process;
    private $pipes;

    const COMMAND_SEEK  = 's';
    const COMMAND_READ  = 'r';
    const COMMAND_WRITE = 'w';
    const COMMAND_EXIT  = 'e';

    public function stream_open($path, $mode){

        //Yuck.
        $subprocess_path = __DIR__ .'/../../subprocess/mmap-proxy.py';

        $components = self::parseURI($path);
        $offset = isset($components['options']['offset']) ? $components['options']['offset'] : 0;

        $subprocess_cmd = sprintf('python -u %s %s %d %d', $subprocess_path, $components['file_name'], $components['block_size'], $offset);

        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            //2 => ['pipe', 'w'] //Show it in the console / don't hijack stderr
        ];


        $this->process = proc_open($subprocess_cmd, $descriptorspec, $this->pipes);

        if($this->process === false){
            throw new \Exception('Could not spawn child process');
        }

        return true;
    }

    public function stream_close(){

        if(is_resource($this->process)){
            $this->subprocess_write(self::COMMAND_EXIT);
            proc_close($this->process);
        }
    }

    public function stream_seek($address, $whence = SEEK_SET){
        //TODO send whence
        $this->subprocess_write(self::COMMAND_SEEK, pack('V', $address));
    }

    public function stream_read($length){

        $this->subprocess_write(self::COMMAND_READ, pack('V', $length));
        return fread($this->pipes[1], $length);
    }

    public function stream_write($data){

        $length = strlen($data);
        $this->subprocess_write(self::COMMAND_WRITE, pack('V', $length).$data);
    }

    public function stream_eof(){
        return true;
    }


    /**
     * @param $command
     * @param $data
     */
    private function subprocess_write($command, $data = ''){
        var_dump($command.$data);
        fwrite($this->pipes[0], $command.$data);
    }


    /**
     * Can't parse_url as it's too malformed
     *
     * @param $uri
     * @return array
     * @throws \Exception
     */
    private static function parseURI($uri){
        //Remove protocol (for clarity).
        $uri = substr($uri, strlen('mmap://'));
        $parts = explode('?', $uri);

        $file_name_block_size = explode(':', $parts[0]);

        if(!isset($file_name_block_size[1])){
            throw new \Exception(sprintf('%s is not a valid uri', $uri));
        }

        $parsed = [];

        list($parsed['file_name'], $parsed['block_size']) = $file_name_block_size;

        if(isset($parts[1])){
            //Extra params
            parse_str($parts[1], $parsed['options']);
        } else {
            $parsed['options'] = [];
        }

        return $parsed;
    }

    public function __destruct() {
        $this->stream_close();
    }
}
