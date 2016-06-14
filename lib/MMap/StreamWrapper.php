<?php
/**
 * @package    php-mmap
 * @author     Michael Calcinai <michael@calcin.ai>
 */

namespace Calcinai\MMap;

class StreamWrapper {

    private $process;
    private $pipes;

    private $size;
    private $position;

    const COMMAND_SEEK  = 's';
    const COMMAND_TELL  = 't';
    const COMMAND_READ  = 'r';
    const COMMAND_WRITE = 'w';
    const COMMAND_EXIT  = 'e';

    /**
     * @param $path
     * @param $mode
     * @return bool
     * @throws \Exception
     */
    public function stream_open($path, $mode){

        $test = @fopen($path, $mode);
        if($test === false){
            throw new \Exception(sprintf('Could not open [%s]', $path));
        }
        fclose($test);

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
        $this->size = $components['block_size'];
        $this->position = 0;

        if($this->process === false){
            throw new \Exception('Could not spawn child process');
        }

        return true;
    }

    /**
     *
     */
    public function stream_close(){

        if(is_resource($this->process)){
            $this->subprocess_write(self::COMMAND_EXIT);
            proc_close($this->process);
        }
    }

    /**
     * TODO send whence
     *
     * @param $address
     * @param int $whence
     * @return bool
     */
    public function stream_seek($address, $whence = SEEK_SET){
        $this->subprocess_write(self::COMMAND_SEEK, pack('v', $address));

        //This is an assumption that the stream will always seek where its sold - can get some info back if this fails.
        $this->position = $address;
        return true;
    }

    /**
     * @return mixed
     */
    public function stream_tell(){
        return $this->position;
    }

    /**
     * @param $length
     * @return string
     */
    public function stream_read($length){
        $this->subprocess_write(self::COMMAND_READ, pack('v', $length));

        //Assume success
        $this->position += $length;
        return $this->subprocess_read($length);
    }

    /**
     * @param $data
     * @return int
     */
    public function stream_write($data){

        $length = strlen($data);
        $this->subprocess_write(self::COMMAND_WRITE, pack('v', $length).$data);

        //Assume success for now
        $this->position += $length;
        return $length;
    }

    /**
     * @return mixed
     */
    public function stream_eof(){
        return $this->position >= $this->size;
    }


    /**
     * @param $command
     * @param $data
     */
    private function subprocess_write($command, $data = ''){
        fwrite($this->pipes[0], $command.$data);
    }

    /**
     * @param $length
     * @return string
     */
    private function subprocess_read($length){
        $data = fread($this->pipes[1], $length);
        return $data;
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
