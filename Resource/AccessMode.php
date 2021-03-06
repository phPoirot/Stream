<?php
namespace Poirot\Stream\Resource;

use Poirot\Stream\Interfaces\Resource\iAccessModeToResourceStream;

class AccessMode 
    implements iAccessModeToResourceStream
{
    /*++
    Stream File Open, Words Stand For:

    BASE:    R = Read                 | W = Write
    -----------------------------------------------------------------------------
    POINTER: A = Pointer at end       | B = Pointer at beginning
    -----------------------------------------------------------------------------
    CREATE : C = Create if not exists | X = Create file only if not exists, otherwise fail
    -----------------------------------------------------------------------------
    BIN:     T = Truncate file
    -----------------------------------------------------------------------------

    @see http://php.net/manual/en/function.fopen.php
    ++*/

    protected $mode_available = array(
        'RB'    => self::MODE_RB,
        'RWB'   => self::MODE_RWB,
        'WBCT'  => self::MODE_WBCT,
        'RWBCT' => self::MODE_RWBCT,
        'WAC'   => self::MODE_WAC,
        'RWAC'  => self::MODE_RWAC,
        'WBX'   => self::MODE_WBX,
        'RWBX'  => self::MODE_RWBX,
        'WBC'   => self::MODE_WBC,
        'RWBC'  => self::MODE_RWBC,
    );

    protected $mode_xxx = array(
        'read'    => null, # R | null
        'write'   => null, # W | null

        'pointer' => 'B', # A | B

        'create'  => null, # C | X

        'bin'     => null, # T | null
    );

    /** @var boolean */
    protected $isBinary;

    
    /**
     * Construct
     *
     * - use toString method
     *
     * @param null|string $modeStr
     */
    function __construct($modeStr = null)
    {
        if ($modeStr !== null)
            $this->fromString($modeStr);
    }

    /**
     * Set From String
     *
     * [code:]
     *  fromString('bRWB');
     *  fromString('bw+');
     * [code]
     *
     * - reset object
     *
     * @param string $modStr
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    function fromString($modStr)
    {
        $this->_reset();

        if (strpos($modStr, 'b') !== false) {
            $this->asBinary();
            $modStr = str_replace('b', '', $modStr); // remove binary sign
        }

        // default modes (r+):
        $modXXX = array_search(strtolower($modStr), $this->mode_available);

        // Mode XXX (RWB):
        $modXXX = ($modXXX!==false) ? $modXXX : strtoupper($modStr);
        for($i=0; $i < strlen($modXXX); $i++) {
            $c = $modXXX[$i];
            switch ($c) {
                // BASE:
                case 'R':
                    $this->openForRead();
                    break;
                case 'W':
                    $this->openForWrite();
                    break;
                // POINTER:
                case 'A':
                    $this->withPointerAtEnd();
                    break;
                case 'B':
                    $this->withPointerAtBeginning();
                    break;
                // CREATE:
                case 'C':
                    $this->createFile();
                    break;
                case 'X':
                    $this->createXFile();
                    break;
                // BIN:
                case 'T':
                    $this->doTruncate();
                    break;
                default:
                    throw new \InvalidArgumentException(sprintf(
                        'Invalid Open Mode Format For (%s) Contains Unknown Char (%s).',
                        $modStr, $c
                    ));
            }
        }

        return $this;
    }

    protected function _reset()
    {
        $this->isBinary = null;

        $this->mode_xxx = array(
            'read'    => null, # R | null
            'write'   => null, # W | null

            'pointer' => 'B', # A | B

            'create'  => null, # C | X

            'bin'     => null, # T | null
        );
    }

    /**
     * Open File For Write
     * @return $this
     */
    function openForWrite()
    {
        $this->mode_xxx['write'] = 'W';

        if (!$this->hasCreate() && !$this->hasXCreate())
            ## write to file must has create or xcreate by default
            $this->createFile();

        return $this;
    }

    /**
     * Open File For Read
     * @return $this
     */
    function openForRead()
    {
        $this->mode_xxx['read'] = 'R';
        return $this;
    }

    /**
     * Indicates whether the mode allows to read
     * @return boolean
     */
    function hasAllowRead()
    {
        return $this->mode_xxx['read'] == 'R';
    }

    /**
     * Indicates whether the mode allows to write
     * @return boolean
     */
    function hasAllowWrite()
    {
        return $this->mode_xxx['write'] == 'W';
    }

    /**
     * Open Stream as Binary Mode
     * @return $this
     */
    function asBinary()
    {
        $this->isBinary = true;
        return $this;
    }

    /**
     * Open Stream as Plain Text
     *
     * @see http://php.net/manual/en/function.fopen.php
     *      look at first note
     *
     * @return $this
     */
    function asText()
    {
        $this->isBinary = false;
        return $this;
    }

    /**
     * Indicates whether the stream is in binary mode
     *
     * @return boolean
     */
    function isBinary()
    {
        return $this->isBinary;
    }

    /**
     * Indicates whether the stream is in text mode
     * @return boolean
     */
    function isText()
    {
        return !$this->isBinary();
    }

    /**
     * Place the file pointer at the end of the file
     * @return $this
     */
    function withPointerAtEnd()
    {
        $this->mode_xxx['pointer'] = 'A';
        return $this;
    }

    /**
     * Place the file pointer at the beginning of the file
     * @return $this
     */
    function withPointerAtBeginning()
    {
        $this->mode_xxx['pointer'] = 'B';
        return $this;
    }

    /**
     * Indicates whether the mode implies positioning the cursor at the
     * beginning of the file
     *
     * @return boolean
     */
    function isAtTop()
    {
        return $this->mode_xxx['pointer'] == 'B';
    }

    /**
     * Indicates whether the mode implies positioning the cursor at the end of
     * the file
     *
     * @return boolean
     */
    function isAtEnd()
    {
        return !$this->isAtTop();
    }

    /**
     * Create File If the file does not exist
     * @return $this
     */
    function createFile()
    {
        $this->mode_xxx['create'] = 'C';
        return $this;
    }

    /**
     * Create file only if not exists
     *
     * - not create if file exists
     *
     * ! otherwise fail
     *
     * @return $this
     */
    function createXFile()
    {
        $this->mode_xxx['create'] = 'X';
        return $this;
    }

    /**
     * Indicates whether the mode allows to create a new file
     *
     * @return boolean
     */
    function hasCreate()
    {
        return $this->mode_xxx['create'] == 'C';
    }

    /**
     * Indicates whether the mode allows to open an existing file
     *
     * @return boolean
     */
    function hasXCreate()
    {
        return $this->mode_xxx['create'] == 'X';
    }

    /**
     * Truncate file after open
     * @return $this
     */
    function doTruncate()
    {
        $this->mode_xxx['bin'] = 'T';
        return $this;
    }

    /**
     * Indicates whether the mode implies to delete the
     * existing content of the file
     *
     * @return boolean
     */
    function hasTruncate()
    {
        return $this->mode_xxx['bin'] == 'T';
    }

    /**
     * Get Access Mode As String
     *
     * - usually in format of W, r+, rb+, ...
     *
     * @throws \Exception If not complete statement
     * @return string
     */
    function toString()
    {
        $ModeXXX = implode('', $this->mode_xxx);
        if (!array_key_exists($ModeXXX, $this->mode_available))
            throw new \Exception(sprintf(
                'Invalid Open Mode Statement (%s). it`s must readable/writable or both with optional flags.'
                .' like: r+, W, rb+, ...'
                , $ModeXXX
            ));

        $mode = $this->mode_available[$ModeXXX];
        if ($this->isBinary()) {
            $base = substr($mode, 0, 1);
            // r|b|+
            $mode = $base.'b'.substr($mode, 1);
        }

        return $mode;
    }

    function __toString()
    {
        return $this->toString();
    }
}
