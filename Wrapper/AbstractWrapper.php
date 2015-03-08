<?php
namespace Poirot\Stream\Wrapper;

use Poirot\Stream\Interfaces\Wrapper\iSWrapper;

/**
 * @link http://php.net/manual/en/function.stream-wrapper-register.php
 * @link http://php.net/manual/en/class.streamwrapper.php
 */
abstract class AbstractWrapper
    implements iSWrapper
{
    /**
     * @var resource
     */
    public $context;

    /**
     * @return bool
     */
    function dir_closedir() { }

    /**
     * @param string   $path
     * @param int      $options
     *
     * @return bool
     */
    function dir_opendir($path, $options) { }

    /**
     * @return string
     */
    function dir_readdir() { }

    /**
     * @return bool
     */
    function dir_rewinddir() { }

    /**
     * @param string $path
     * @param int    $mode
     * @param int    $options
     *
     * @return bool
     */
    function mkdir($path, $mode, $options) { }

    /**
     * @param string $path_from
     * @param string $path_to
     *
     * @return bool
     */
    function rename($path_from, $path_to) { }

    /**
     * @param string $path
     * @param int    $options
     *
     * @return bool
     */
    function rmdir($path, $options) { }

    /**
     * @param int $cast_as
     *
     * @return resource
     */
    function stream_cast($cast_as) { }

    /**
     * @return void
     */
    function stream_close() { }

    /**
     * @return bool
     */
    function stream_eof() { }

    /**
     * @return bool
     */
    function stream_flush() { }

    /**
     * @param int $operation
     *
     * @return bool
     */
    function stream_lock($operation) { }

    /**
     * @param string  $path
     * @param int     $option
     * @param mixed   $value
     *
     * @return bool
     */
    function stream_metadata($path, $option, $value ) { }

    /**
     * @param string       $path
     * @param string       $mode
     * @param int          $options
     * @param string       $opened_path
     *
     * @return bool
     */
    function stream_open($path, $mode, $options, &$opened_path) { }

    /**
     * @param int $count
     *
     * @return string
     */
    function stream_read($count) { }

    /**
     * @param int $offset
     * @param int $whence
     *
     * @return bool
     */
    function stream_seek($offset, $whence = SEEK_SET ) { }

    /**
     * @param int $option
     * @param int $arg1
     * @param int $arg2
     *
     * @return bool
     */
    function stream_set_option($option, $arg1, $arg2) { }

    /**
     * @return array
     */
    function stream_stat() { }

    /**
     * @return int
     */
    function stream_tell() { }

    /**
     * @param int $new_size
     *
     * @return bool
     */
    function stream_truncate($new_size) { }

    /**
     * @param string $data
     *
     * @return int
     */
    function stream_write($data) { }

    /**
     * @param string $path
     *
     * @return bool
     */
    function unlink($path) { }

    /**
     * @param string $path
     * @param int    $flags
     *
     * @return array
     */
    function url_stat($path, $flags) { }
}
