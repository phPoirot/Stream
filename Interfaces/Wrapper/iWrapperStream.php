<?php
namespace Poirot\Stream\Interfaces\Wrapper;

use Poirot\Std\Interfaces\Pact\ipOptionsProvider;

/**
 * Just a Prototype Class to Describe Methods
 */
interface iWrapperStream 
    extends ipOptionsProvider
{
    /**
     * Context Wrapper Options
     *
     * The current context, or NULL if no context was passed to the caller function.
     * Use the stream_context_get_options() to parse the context
     *
     * @var resource
     */
    #public $context;

    /**
     * Get Wrapper Protocol Label
     *
     * - used on register/unregister wrappers, ...
     *
     *   label://
     *   -----
     *
     * @return string
     */
    function getLabel();

    // Prototype Implementation Of PHP Stream Methods:

    /**
     * Open file or URL.
     * This method is called immediately after the wrapper is initialized (f.e.
     * by fopen() and file_get_contents()).
     *
     * @param   string  $path           Specifies the URL that was passed to the
     *                                  original function.
     * @param   string  $mode           The mode used to open the file, as
     *                                  detailed for fopen().
     * @param   int     $options        Holds additional flags set by the
     *                                  streams API. It can hold one or more of
     *                                  the following values OR'd together:
     *                                    * STREAM_USE_PATH, if path is relative,
     *                                      search for the resource using the
     *                                      include_path;
     *                                    * STREAM_REPORT_ERRORS, if this is
     *                                    set, you are responsible for raising
     *                                    errors using trigger_error during
     *                                    opening the stream. If this is not
     *                                    set, you should not raise any errors.
     * @param   string  &$openedPath    If the $path is opened successfully, and
     *                                  STREAM_USE_PATH is set in $options,
     *                                  $openedPath should be set to the full
     *                                  path of the file/resource that was
     *                                  actually opened.
     * @return  bool
     */
    #function stream_open($path, $mode, $options, &$opened_path);

    /**
     * Close a resource.
     * This method is called in response to fclose().
     * All resources that were locked, or allocated, by the wrapper should be
     * released.
     *
     * @return  void
     */
    #function stream_close();

    /**
     * Write to stream.
     * This method is called in response to fwrite().
     *
     * @param string $data
     *
     * @return int
     */
    #function stream_write($data);

    /**
     * Read from stream.
     * This method is called in response to fread() and fgets().
     *
     * - If we have reached at the end of stream empty string must returned
     *
     * @param   int     $count    How many bytes of data from the current
     *                            position should be returned.
     * @return  string
     */
    #function stream_read($count);

    /**
     * Tests for end-of-file on a file pointer.
     * This method is called in response to feof().
     *
     * @return  bool
     */
    #function stream_eof();

    /**
     * Flush the output.
     * This method is called in response to fflush().
     * If we have cached data in our stream but not yet stored it into the
     * underlying storage, we should do so now.
     *
     * - At copy end this method was called after write
     *
     * @return  bool
     */
    #function stream_flush();

    /**
     * Seek to specific location in a stream.
     * This method is called in response to fseek().
     * The read/write position of the stream should be updated according to the
     * $offset and $whence.
     *
     * @param   int     $offset    The stream offset to seek to.
     * @param   int     $whence    Possible values:
     *                               * SEEK_SET to set position equal to $offset
     *                                 bytes ;
     *                               * SEEK_CUR to set position to current
     *                                 location plus $offsete ;
     *                               * SEEK_END to set position to end-of-file
     *                                 plus $offset.
     * @return  bool
     */
    #function stream_seek($offset, $whence = SEEK_SET );

    /**
     * Retrieve the current position of a stream.
     * This method is called in response to ftell()
     *
     * @return int
     */
    #function stream_tell();

    /**
     * Retrieve the underlaying resource.
     *
     * @param   int     $castAs    Can be STREAM_CAST_FOR_SELECT when
     *                             stream_select() is calling stream_cast() or
     *                             STREAM_CAST_AS_STREAM when stream_cast() is
     *                             called for other uses.
     * @return  resource
     */
    #function stream_cast($cast_as);

    /**
     * Advisory file locking.
     * This method is called in response to flock(), when file_put_contents()
     * (when flags contains LOCK_EX), stream_set_blocking() and when closing the
     * stream (LOCK_UN).
     *
     * @param   int     $operation    Operation is one the following:
     *                                  * LOCK_SH to acquire a shared lock (reader) ;
     *                                  * LOCK_EX to acquire an exclusive lock (writer) ;
     *                                  * LOCK_UN to release a lock (shared or exclusive) ;
     *                                  * LOCK_NB if we don't want flock() to
     *                                    block while locking (not supported on
     *                                    Windows).
     * @return  bool
     */
    #function stream_lock($operation);

    /**
     * @param string  $path
     * @param int     $option
     * @param mixed   $value
     *
     * @return bool
     */
    #function stream_metadata($path, $option, $value );

    /**
     * Change stream options.
     * This method is called to set options on the stream.
     *
     * @param   int     $option    One of:
     *                               * STREAM_OPTION_BLOCKING, the method was
     *                                 called in response to
     *                                 stream_set_blocking() ;
     *                               * STREAM_OPTION_READ_TIMEOUT, the method
     *                                 was called in response to
     *                                 stream_set_timeout() ;
     *                               * STREAM_OPTION_WRITE_BUFFER, the method
     *                                 was called in response to
     *                                 stream_set_write_buffer().
     * @param   int     $arg1      If $option is:
     *                               * STREAM_OPTION_BLOCKING: requested blocking
     *                                 mode (1 meaning block, 0 not blocking) ;
     *                               * STREAM_OPTION_READ_TIMEOUT: the timeout
     *                                 in seconds ;
     *                               * STREAM_OPTION_WRITE_BUFFER: buffer mode
     *                                 (STREAM_BUFFER_NONE or
     *                                 STREAM_BUFFER_FULL).
     * @param   int     $arg2      If $option is:
     *                               * STREAM_OPTION_BLOCKING: this option is
     *                                 not set ;
     *                               * STREAM_OPTION_READ_TIMEOUT: the timeout
     *                                 in microseconds ;
     *                               * STREAM_OPTION_WRITE_BUFFER: the requested
     *                                 buffer size.
     * @return  bool
     */
    #function stream_set_option($option, $arg1, $arg2);

    /**
     * Retrieve information about a file resource.
     * This method is called in response to fstat()
     *
     * @return array
     */
    #function stream_stat();

    /**
     * Truncate a stream to a given length.
     *
     * @param int $new_size
     *
     * @return bool
     */
    #function stream_truncate($new_size);


    /**
     * Open directory handle.
     * This method is called in response to opendir().
     *
     * @param   string  $path       Specifies the URL that was passed to opendir().
     * @param   int     $options    Whether or not to enforce safe_mode (0x04).
     * @return  bool
     */
    #function dir_opendir($path, $options);

    /**
     * Read entry from directory handle.
     * This method is called in response to readdir().
     *
     * @return  mixed
     */
    #function dir_readdir();

    /**
     * Rewind directory handle.
     * This method is called in response to rewinddir().
     * Should reset the output generated by self::dir_readdir, i.e. the next
     * call to self::dir_readdir should return the first entry in the location
     * returned by self::dir_opendir.
     *
     * @return  bool
     */
    #function dir_rewinddir();

    /**
     * Close directory handle.
     * This method is called in to closedir().
     * Any resources which were locked, or allocated, during opening and use of
     * the directory stream should be released.
     *
     * @return  bool
     */
    #function dir_closedir();

    /**
     * Create a directory.
     * This method is called in response to mkdir().
     *
     * @param   string  $path       Directory which should be created.
     * @param   int     $mode       The value passed to mkdir().
     * @param   int     $options    A bitwise mask of values.
     *
     * @return  bool
     */
    #function mkdir($path, $mode, $options);

    /**
     * Remove a directory.
     * This method is called in response to rmdir().
     *
     * @param   string  $path       The directory URL which should be removed.
     * @param   int     $options    A bitwise mask of values.
     *
     * @return  bool
     */
    #function rmdir($path, $options);


    /**
     * Rename a file or directory.
     * This method is called in response to rename().
     * Should attempt to rename $from to $to.
     *
     * @param string $path_from The URL to current file.
     * @param string $path_to   The URL which $from should be renamed to.
     *
     * @return bool
     */
    #function rename($path_from, $path_to);

    /**
     * @param string $path
     *
     * @return bool
     */
    #function unlink($path);


    /**
     * Retrieve information about a file.
     * This method is called in response to all stat() related functions.
     *
     * @param   string  $path     The file URL which should be retrieve
     *                            information about.
     * @param   int     $flags    Holds additional flags set by the streams API.
     *                            It can hold one or more of the following
     *                            values OR'd together.
     *                            STREAM_URL_STAT_LINK: for resource with the
     *                            ability to link to other resource (such as an
     *                            HTTP location: forward, or a filesystem
     *                            symlink). This flag specified that only
     *                            information about the link itself should be
     *                            returned, not the resource pointed to by the
     *                            link. This flag is set in response to calls to
     *                            lstat(), is_link(), or filetype().
     *                            STREAM_URL_STAT_QUIET: if this flag is set,
     *                            our wrapper should not raise any errors. If
     *                            this flag is not set, we are responsible for
     *                            reporting errors using the trigger_error()
     *                            function during stating of the path.
     * @return  array
     */
    #function url_stat($path, $flags);
}
