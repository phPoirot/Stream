<?php
namespace Poirot\Stream;

use Poirot\Std\ConfigurableSetter;
use Poirot\Stream\Context\ContextStreamSocket;
use Poirot\Stream\Exception\exConnectionTimeout;
use Poirot\Stream\Interfaces\Context\iContextStream;
use Poirot\Stream\Interfaces\iStreamable;
use Poirot\Stream\Interfaces\iStreamServer;

class StreamServer
    extends ConfigurableSetter
    implements iStreamServer
{
    /** @var string */
    protected $socketUri;

    /** @var boolean */
    protected $noneBlocking;

    /** @var float */
    protected $timeout;

    /** @var iContextStream */
    protected $context;

    /** @var resource */
    public  $__socket_connected;

    /**
     * Construct
     *
     * Note: When specifying a numerical IPv6 address (e.g. fe80::1),
     *       you must enclose the IP in square brackets—for example,
     *       tcp://[fe80::1]:80
     *
     * @param string|array   $serverAddressOrSetter Socket Uri
     * @param iContextStream $context               Context Options
     */
    function __construct($serverAddressOrSetter, $context = null)
    {
        $setters = array();

        if (\Poirot\Std\isStringify($serverAddressOrSetter)) {
            ## maybe using some stringify like pathuri as input
            $setters['server_address'] = (string) $serverAddressOrSetter;

            if ($context !== null)
                $setters['context'] = $context;
        }

        parent::__construct($setters);
    }

    /**
     * Open Socket Connection To Socket Uri and Bind Server
     * Socket To Specific Port
     *
     * - Initiates a stream or datagram connection to the
     *   destination specified by socketUri.
     *   The type of socket created is determined by the
     *   transport specified using standard URL formatting:
     *   transport://target
     *
     * - store socket server resource inside class
     * - each time bind was calling the resource
     *   created again
     *
     * ! Port eq to zero let system to select unused port
     * ! Most systems require root access to create
     *   a server socket on a port below 1024
     *
     *   ! For Internet Domain sockets (AF_INET) such as
     *     TCP and UDP, the target portion of the socketUri
     *     parameter should consist of a hostname or IP address
     *     followed by a colon and a port number.
     *     For Unix domain sockets, the target portion should
     *     point to the socket file on the filesystem
     *
     * Note: The stream will by default be opened in blocking mode.
     *
     * Note: For UDP sockets, you must use STREAM_SERVER_BIND as
     *       the flags parameter.
     *
     * Note: Most systems require root access to create a server
     *       socket on a port below 1024.
     *
     * Warning UDP sockets will sometimes appear to have opened without
     * an error, even if the remote host is unreachable. The error will
     * only become apparent when you read or write data to/from the socket.
     * The reason for this is because UDP is a "connectionless" protocol,
     * which means that the operating system does not try to establish a
     * link for the socket until it actually needs to send or receive data
     *
     * @throws \Exception On Connection Failed
     * @return $this
     */
    function bind()
    {
        $sockUri = $this->getServerAddress();

        // knowing transport/wrapper:
        $scheme  = parse_url($sockUri, PHP_URL_SCHEME);
        if (!in_array($scheme, stream_get_transports()))
            throw new \Exception(sprintf(
                'Transport "%s" not supported.'
                , $scheme
            ));

        if ($scheme == 'udp')
            $socket = @stream_socket_server($sockUri, $errno, $errstr, STREAM_SERVER_BIND);
        else
            $socket = @stream_socket_server($sockUri, $errno, $errstr);

        if (!$socket)
            throw new \Exception(sprintf(
                'Server %s, %s.'
                ,$this->getServerAddress()
                ,$errstr
            ), $errno);

        $this->__socket_connected = $socket;
        return $this;
    }

    /**
     * Is Server Binding On Socket?
     *
     * @return boolean
     */
    function isBinding()
    {
        return is_resource($this->__socket_connected);
    }

    /**
     * Listen On Port To Accept Data On That Port
     * From Client
     *
     * Warning with UDP server sockets. use stream_socket_recvfrom()
     * and stream_socket_sendto().
     *
     * @throws \Exception         Not Bind Or Error Receive Data
     *         \TimeoutException  Listen Connection Timeout
     *
     * @return iStreamable
     */
    function listen()
    {
        $sockUri = $this->getServerAddress();
        if (!$this->isBinding())
            throw new \Exception('Server not bind as local server.');

        // knowing transport/wrapper:
        $scheme  = parse_url($sockUri, PHP_URL_SCHEME);
        if ($scheme == 'udp')
            $resource = $this->_listen_to_connectLessTransport();
        else
            $resource = $this->_listen_to_connectionOrientatedTransports();

        if ($resource === false)
            throw new \Exception(sprintf(
                'Failed To Accept Connection, %s.'
                , error_get_last()['message']
            ));

        return new Streamable($resource);
    }
    
    /**
     * Shutdown Server And Close Connections
     *
     * @return void
     */
    function shutdown()
    {
        fclose($this->__socket_connected);

        #stream_socket_shutdown($this->__socket_connected, STREAM_SHUT_WR);
    }
    
    // Options:

    /**
     * Immutable Set Socket Uri
     *
     * Note: When specifying a numerical IPv6 address (e.g. fe80::1),
     *       you must enclose the IP in square brackets—for example,
     *       tcp://[fe80::1]:80
     *
     * @param string $socketUri
     * 
     * @return $this
     * @throws \Exception
     */
    function setServerAddress($socketUri)
    {
        if ($this->socketUri)
            throw new \Exception(sprintf(
                'Server Address is Immutable; currently have value: (%s).'
                , $this->socketUri
            ));
        
        $this->socketUri = (string) $socketUri;
        return $this;
    }
    
    /**
     * Get Current Socket Uri That Stream Built With
     *
     * @return string
     */
    function getServerAddress()
    {
        return $this->socketUri;
    }

    /**
     * Context Options
     *
     * @param iContextStream $context
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    function setContext(iContextStream $context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Get Context Options
     *
     * @return iContextStream
     */
    function getContext()
    {
        if (!$this->context)
            $this->setContext(new ContextStreamSocket);

        return $this->context;
    }

    /**
     * Set blocking/non-blocking mode on a stream
     *
     * ! This function works for any stream that supports
     *   non-blocking mode (currently, regular files and socket streams)
     *
     * @param bool $flag
     *
     * @return $this
     */
    function setNoneBlocking($flag = true)
    {
        $this->noneBlocking = (boolean) $flag;
        return $this;
    }

    /**
     * Indicate Where Stream Is Built With None-Blocking Mode?
     *
     * @return boolean
     */
    function isNoneBlocking()
    {
        return $this->noneBlocking;
    }

    /**
     * Set timeout period on a stream
     *
     * - must store time in float mode
     *   @see self::getTimeout
     *
     * @param float|array $seconds In Form Of time.utime
     *
     * @return $this
     */
    function setTimeout($seconds)
    {
        if (is_array($seconds))
            $seconds = implode('.', $seconds);

        $this->timeout = $seconds;
        return $this;
    }

    /**
     * Get Timeout
     *
     * @return float
     */
    function getTimeout()
    {
        if (!$this->timeout)
            $this->setTimeout(ini_get('default_socket_timeout'));

        return $this->timeout;
    }

    
    // ..
    
    /**
     * such as udp
     */
    function _listen_to_connectLessTransport()
    {
        stream_socket_recvfrom($this->__socket_connected, 1, 0, $remotePeer);

        $sockUri = $this->getServerAddress();
        $scheme  = parse_url($sockUri, PHP_URL_SCHEME);

        $client   = new StreamClient($scheme.'://'.$remotePeer);
        $resource = $client->getConnect();
        return $resource;
    }

    /**
     * such as tcp
     */
    function _listen_to_connectionOrientatedTransports()
    {
        $conn = @stream_socket_accept($this->__socket_connected, $this->getTimeout());
        if ($conn === false)
            throw new exConnectionTimeout('Connection Timeout.');

        $resource = new ResourceStream($conn);
        return $resource;
    }
    
    function __destruct()
    {
        $this->shutdown();
    }
}
