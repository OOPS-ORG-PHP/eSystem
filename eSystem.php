<?php
/**
 * Project: eSystem:: The Extended file system<br>
 * File:    eSystem.php
 *
 * Defines the php extended system mapping function and any utility mapping function
 *
 * This Class requires {@link eFilesystem 'pear.oops.org/eFilesystem'} and
 * {@link oGetopt 'pear.oops.org/oGetopt'}
 *
 * @category    System
 * @package     eSystem
 * @author      JoungKyun.Kim <http://oops.org>
 * @copyright   (c) 2018, OOPS.org
 * @license     BSD
 * @link        http://pear.oops.org/package/eSystem
 * @since       File available since relase 0.8
 * @example     eSystem/test.php Sample codes for eSystem class
 * @filesource
 */

/**
 * Dependency on {@link eFilesystem 'pear.oops.org/eFilesystem'} pear package over 1.0.0
 */
require_once 'eFilesystem.php';

/**
 * Base class for system mapping api
 *
 * @access public
 * @version $Revision$
 * @package eSystem
 */
class eSystem
{
	// {{{ properties
	private $system;
	//private $man;

	/**#@+*/
	/**
	 * @access public
	 */
	/**
	 * Location for create tmp file
	 *
	 * @var string
	 */
	public $tmpdir = '/tmp';

	/**
	 * Standard Error file description
	 * @var reousrce
	 */
	public $stderr;
	/**
	 * Standard Out file description
	 * @var reousrce
	 */
	public $stdout;
	/**
	 * Shell return code
	 * @var integer 
	 */
	public $retint;
	/**#@-*/
	// }}}

	// {{{ function autoload (&$obj, $f, $cname = '')
	/**
	 * Autoload the specify class name
	 *
	 * @access private
	 * @return void
	 * @param  object loading object that call this api
	 * @param  string file that is loaded
	 * @param  string (optional) name of class
	 */
	private function autoload (&$obj, $f, $cname = '') {
		if ( ! $cname )
			$cname = $f;

		require_once 'eSystem/' . $f . '.php';
		if ( ! is_object ($obj) ) {
			$objname = "eSystem_" . $cname;
			$obj = new $objname;
		}
	}
	// }}}

	// {{{ function system ($_cmd, &$_returncode = NULL)
	/**
	 * Wrapping system function
	 *
	 * If server admin disables php system() function, this method
	 * has same execution with php system().
	 *
	 * You can use this api that change only system(...) to $obj->system(...)
	 *
	 * @access public
	 * @return string Returns the last line of the command output on success,
	 *                and FALSE  on failure. 
	 * @param  string The command that will be executed.
	 * @param  int    (optional) If the _returncode  argument is present,
	 *                then the return status of the executed command will
	 *                be written to this variable. 
	 */
	function system ($_cmd, &$_returncode = NULL) {
		$this->autoload ($this->system, 'system');

		$this->system->tmpdir = $this->tmpdir;
		$this->system->_stdout = array ();
		$this->system->_stderr = '';
		$this->system->_retint = 0;

		$this->system->_system ($_cmd, 1);

		$_returncode = $this->system->_retint;
		$this->stderr = $this->system->_stderr;

		$_no = count ($this->system->_stdout);
		return $this->system->_stdout[--$_no];
	}
	// }}}

	// {{{ function exec ($_cmd, &$_output = NULL, &$_returncode = NULL)
	/**
	 * Wrapping exec() function
	 *
	 * If server admin disables php exec() function, this method
	 * has same execution with php exec().
	 *
	 * You can use this api that change only exec(...) to $obj->exec(...)
	 *
	 * @access public
	 * @return string Returns the last line of the command output on success, and FALSE  on failure.
	 * @param  string The command that will be executed. 
	 * @param  array   (optional) If the _output  argument is present, then the specified
	 *                 array will be filled with every line of output from the command.
	 *                 Trailing whitespace, such as \n, is not included in this array.
	 *                 Note that if the array already contains some elements, exec()
	 *                 will append to the end of the array. If you do not want the
	 *                 function to append elements, call unset() on the array before
	 *                 passing it to exec(). 
	 * @param  integer (optional) If the _returncode  argument is present, then the return
	 *                 status of the executed command will be written to this variable. 
	 */
	# mapping php exec function arguments.
	function exec ($_cmd, &$_output = NULL, &$_returncode = NULL) {
		$this->autoload ($this->system, 'system');

		$this->system->tmpdir = $this->tmpdir;
		$this->system->_stdout = array ();
		$this->system->_stderr = '';
		$this->system->_retint = 0;

		$this->system->_system ($_cmd);

		$_output = $this->system->_stdout;
		$_returncode = $this->system->_retint;
		$this->stderr = $this->system->_stderr;
		$_no = count ($this->system->_stdout);

		return $this->system->_stdout[--$_no];
	}
	// }}}

	// {{{ function execl ($_cmd, $_output = NULL, $_returncode = NULL)
	/**
	 * Wrapping exec() function
	 *
	 * This method same $this->exec(), but return value of 2th argument is not array,
	 * it's plain text strings.
	 *
	 * @access public
	 * @return string Returns the last line of the command output on success, and FALSE  on failure.
	 * @param  string The command that will be executed. 
	 * @param  string  (optional) If the _output  argument is present, this argument contains
	 *                 output from the command.
	 * @param  integer (optional) If the _returncode  argument is present, then the return
	 *                 status of the executed command will be written to this variable. 
	 */
	function execl ($_cmd, $_output = NULL, $_returncode = NULL) {
		$this->autoload ($this->system, 'system');

		$r = $this->exec ($_cmd, $_outputs, $_returncode);

		if ( is_array ($_outputs) && ($c = count ($_outputs)) ) {
			$_output = '';
			for ( $i=0; $i<$c; $i++ ) {
				$_output .= $_outputs[$i] . "\n";
			}
		}

		$_output = preg_replace ("/\n$/", '', $_output);

		return $r;
	}
	// }}}

	// {{{ function mkdir_p ($path, $mode = 0755)
	/**
	 * Attempts to create the directory specified by pathname.
	 * If does not parent directory, this API create success.
	 * This means that same operate with mkdir (path, mode, true) of php
	 *
	 * @deprecated deprecated since 1.0.1. Use {@link eFilesystem::mkdir_p()} instead
	 *             of this method
	 * @see     eFilesystem::mkdir_p()
	 * @access  public
	 * @return  boolean return false, create error by other error.<br>
	 *                  return true, create success.
	 * @param   string  given path
	 * @param   int     (optional) The mode is 0777 by default, which means the widest
	 *                  possible access. For more information on modes, read
	 *                  the details on the chmod() page.
	 */ 
	function mkdir_p ($path, $mode = 0755) {
		return eFilesystem::mkdir_p ($path, $mode);
	}
	// }}}

	// {{{ function unlink ($path)
	/**
	 * Deletes a file. If given file is directory, no error and return false.
	 *
	 * @deprecated deprecated since 1.0.1. Use {@link eFilesystem::safe_unlnk()} instead
	 *             of this method
	 * @see     eFilesystem::safe_unlink()
	 * @access public
	 * @return int  return true, success<br>
	 *              return false, remove false<br>
	 *              return 2, file not found<br>
	 *              return 3, file is directory
	 * @param  string  given file path
	 */
	function unlink ($path) {
		return eFilesystem::safe_unlink ($path);
	}
	// }}}

	// {{{ function unlink_r ($path)
	/**
	 * Deletes a file or directory that include some files
	 *
	 * @deprecated deprecated since 1.0.1. Use {@link eFilesystem::unlink_r()} instead
	 *             of this method
	 * @see     eFilesystem::unlink_r()
	 * @access  public
	 * @return  boolean
	 * @param   string  Given path.
	 *                  You can use Asterisk(*) or brace expand({a,b}) on path.
	 */
	function unlink_r ($path) {
		return eFilesystem::unlink_r ($path);
	}
	// }}}

	// {{{ function tree ($dir = '.')
	/**
	 * print directory tree for given path
	 *
	 * @deprecated deprecated since 1.0.1. Use {@link eFilesystem::tree()} instead
	 *             of this method
	 * @see     eFilesystem::tree()
	 * @access  public
	 * @return  object object
	 *                  members are follow:<br>
	 *                  obj->file is number of files.<br>
	 *                  obj->dir is number of directories.
	 * @param   string  (optional) Given path. Defaults to current directory (./).
	 */
	function tree ($dir = '.') {
		return eFilesystem::tree ($dir);
	}
	// }}}

	// {{{ function find ($path = './', $type = '', $norecursive = false)
	/**
	 * get file list that under given path
	 *
	 * @deprecated deprecated since 1.0.1. Use {@link eFilesystem::find()} instead
	 *             of this method
	 * @see     eFilesystem::find()
	 * @access  public
	 * @return  array   return array of file list. If given path is null or don't exist, return false.
	 * @param   string  (optional) Given path. Defaults to current directory (./)
	 * @param   string  (optional) list type. Defaults to all.<br>
	 *                  f (get only files),<br>
	 *                  d (get only directories),<br>
	 *                  l (get only links),<br>
	 *                  fd (get only files and directories),<br>
	 *                  fl (get only files and links),<br>
	 *                  dl (get only directories and links)<br>
	 *                  /regex/ (use regular expression)
	 * @param   boolean (optional) Defaults to false.
	 *                  set true, don't recursive search.
	 */
	function find ($path = './', $type = '', $norecursive = false) {
		return eFilesystem::find ($path, $type, $norecursive);
	}
	// }}}

	// {{{ function putColor ($str, $color = '')
	/**
	 * Return given string with ansi code about specified color
	 *
	 * Color strings support follows:
	 *   gray, red, green, yellow, blue, megenta, cyan, white
	 *
	 * @deprecated deprecated since 1.0.1. Use {@link ePrint::asPrintf()} instead
	 *             of this method
	 * @see    ePrint::asPrintf()
	 * @access public
	 * @return string
	 * @param  string Input strings
	 * @param  string color string for anci code. Defaults to 'gray'
	 */
	function putColor ($str, $color = 'gray') {
		$this->__nocli ('putColor');

		return ePrint::asPrintf ($color, $str);
	}
	// }}}

	// {{{ function boldStr ($str)
	/**
	 * Return given string with white ansi code
	 *
	 * @deprecated deprecated since 1.0.1. Use
	 *             {@link ePrint::asPrintf() ePrint::asPrintf (string, white)} instead
	 *             of this method
	 * @see    ePrint::asPrintf()
	 * @access public
	 * @return string
	 * @param  string Input strings
	 */
	function boldStr ($str) {
		$this->__nocli('boldStr');

		return $this->putColor ($str, 'white');
	}
	// }}}

	// {{{ function makeWhiteSpace ($no)
	/**
	 * Print white space about given number
	 *
	 * @deprecated deprecated since 1.0.1. Use {@link ePrint::whiteSpace()} instead
	 *             of this method
	 * @see     ePrint::whiteSpace()
	 * @access  public
	 * @return  strings
	 * @param   integer number of space charactor
	 */
	function makeWhiteSpace ($no) {
		return ePrint::whiteSpace ($no, true);
	}
	// }}}

	// {{{ function backSpace ($no)
	/**
	 * print backspace
	 *
	 * This API is Deprecated. Use ePrint::backSpace instead of this method
	 *
	 * @deprecated deprecated since 1.0.1. Use {@link ePrint::backSpace()} instead
	 *             of this method
	 * @see     ePrint::backSpace()
	 * @access  public
	 * @return  void
	 * @param   integer number of space charactor
	 */
	function backSpace ($no) {
		ePrint::backSpace ($no);
	}
	// }}}

	// {{{ function printe ($format, $msg = '')
	/**
	 * Output a formatted string to stderr
	 *
	 * @deprecated deprecated since 1.0.1. Use {@link ePrint::ePrintf()} instead
	 *             of this method
	 * @see     ePrint::ePrintf()
	 * @access  public
	 * @return  int     length of output messages.
	 * @param   string  same format of printf
	 * @param   mixed   (optional) format value.<br>
	 *                  If only one format argument, $msg is strings or array.<br>
	 *                  For multiple format arguments, $msg is array.
	 */
	function printe ($format, $msg = '') {
		return ePrint::ePrintf ($format, $msg);
	}
	// }}}

	// {{{ function print_f ($file, $format, $msg = '')
	/**
	 * Save a formatted string to file
	 *
	 * A newline is not automatically added to the end of the message string. 
	 *
	 * @deprecated deprecated since 1.0.1. Use {@link ePrint::ePrintf()} or
	 *             {@link ePrint::lPrintf()} instead of this method
	 * @see    ePrint::ePrintf()
	 * @see    ePrint::lPrintf()
	 * @access  public
	 * @return  int     length of print string
	 * @param   string  $path   target file
	 * @param   string  $format same format of printf
	 * @param   mixed   (optional) format value.<br>
	 *                  If only one format argument, $msg is strings or array.<br>
	 *                  For multiple format arguments, $msg is array.
	 */
	function print_f ($file, $format, $msg = '') {
		return ePrint::ePrintf ($format, $msg, 3, $file);
	}
	// }}}

	// {{{ function print_s ($msg, $width = 75, $indent = 0, $ul = '', $to_stderr = 0)
	/**
	 * print with indent.
	 *
	 * @deprecated deprecated since 1.0.1. Use {@link ePrint::printi()} instead
	 *             of this method
	 * @see     ePrint::printi()
	 * @access  public
	 * @return  int     Length of print string. If on error, return false
	 * @param   mixed   output string
	 * @param   integer (optional) location of line brek. default 80
	 * @param   integer (optional) indent of each line
	 * @param   string  (optional) list itme
	 * @param   boolean (optional) set true, print stderr
	 */
	function print_s ($msg, $width = 75, $indent = 0, $ul = '', $to_stderr = 0) {
		return ePrint::printi ($msg, $width, $indent, $ul, ($to_stderr === 0) ? false : true);
	}
	// }}}

	// {{{ function wordwrap ($msg, $width = 75, $break = "\n", $cut = 0)
	/**
	 * Wraps a string to a given number of characters. Difference with wordwarp of
	 * PHP, wrapped line joins next line and rewraps.
	 *
	 * @deprecated deprecated since 1.0.1. Use {@link ePrint::wordwrap()} instead
	 *             of this method
	 * @see     ePrint::wordwrap()
	 * @access  public
	 * @return  strings
	 * @param   string  input string
	 * @param   integer (optional) The column widht. Defaults to 75
	 * @param   string  (optional) The line is broken using the optional break parameter.
	 *                  Defaults to '\n'.
	 * @param   integer (optional) If the cut is set to TRUE, the string is always wrapped
	 *                  at or before the specified width. So if you have a word
	 *                  that is larger than the given width, it is broken apart.
	 *                  Seealso wordwrap of php
	 */
	function wordwrap ($msg, $width = 75, $break = "\n", $cut = 0) {
		return ePrint::wordwrap ($msg, $width, $break, $cut);
	}
	// }}}

	// {{{ function file_nr ($f, $use_include_path = false, $resource = null)
	/**
	 * Reads entire file into an array
	 * file_nr api runs same file function of php. But file_nr has
	 * no \r\n or \n character on array members.
	 *
	 * @deprecated deprecated since 1.0.1. Use {@link ePrint::file_nr()} instead
	 *             of this method
	 * @see     ePrint::file_nr()
	 * @access  public
	 * @return  array    Array or false if not found file path nor file resource.
	 * @param   string   file path
	 * @param   boolean  (optional) Search file path on include_path of php.
	 *                   Defaults is false.
	 * @param   resource (optional) already opend file description resource
	 *                   Defaults is null.
	 */
	function file_nr ($f, $use_include_path = false, $resource = null) {
		return eFilesystem::file_nr ($f, $use_include_path, $resource);
	}
	// }}}

	// {{{ function getopt ($argc, $argv, $optstrs)
    /**
	 * Wrapping o_getopt on Oops C library
	 * This class is supported alternative getopt function.
	 *
	 * @deprecated deprecated since 1.0.1. Use {@link oGetopt::exec()} instead
	 *             of this method
	 * @see     oGetopt::exec()
	 * @access public
	 * @return string return short option.<br>
	 *                If return -1, end of getopt processing.
	 *                If wrong option, print error message and return null
	 * @param  integer Number of command line arguments
	 * @param  array   Command line arguments
	 * @param  string  Option format. See also 'man 3 getopt'
	 */
	function getopt ($argc, $argv, $optstrs) {
		global $optarg, $optcmd, $longopt;
		global $optcno;

		if ( ! class_exists ('oGetopt') ) {
			/**
			 * Dependency on {@link oGetopt 'pear.oops.org/oGetopt'}
			 * pear package over 1.0.0
			 */
			require_once 'oGetopt.php';

			oGetopt::init ();
			$optcno = -1;

			if ( ! is_object ($longopt) ) {
				$_longopt = (object) $longopt;
				unset ($longopt);
				$longopt = $_longopt;
			}

			oGetopt::$longopt = &$longopt;
			oGetopt::$optcno  = &$optcno;
			oGetopt::$optcmd  = &$optcmd;
			oGetopt::$optarg  = &$optarg;
		}

		$r = oGetopt::exec ($argc, $argv, $optstrs);
		if ( $r === false )
			return -1;

		return $r;
	}
	// }}}

	// {{{ function manPath ($_name, $_path = '/usr/share/man', $_sec = 0)
	/**
	 * Return man page file path with man page section and name
	 *
	 * The exmaple:
	 * {@example eSystem/test.php 170 2}
	 *
	 * @access public
	 * @return string Returns man page file path
	 * @param  string Name of man page for searching
	 * @param  string (optional) Base man page base path
	 * @param  integer (optional) Section of man page
	 */
	function manPath ($_name, $_path = '/usr/share/man', $_sec = 0) {
		$this->autoload ($this->man, 'man');
		$this->man->tmpdir = $this->tmpdir;
		return $this->man->manPath ($_name, $_path, $_sec);
	}
	// }}}

	// {{{ function man ($_name, $_no, $_int = NULL, $__base = null, $_s = 0)
	/**
	 * Return man page contents for human readable
	 *
	 * The exmaple:
	 * {@example eSystem/test.php 173 2}
	 *
	 * @access public
	 * @return string Returns man page file path
	 * @param  string  name of man page
	 * @param  int     Section of man page
	 * @param  string  (optional) L10n code for international man pages
	 * @param  string  (optional) Base man page base path
	 * @param  boolean (optional) Defaults to 0. Set true, even if result
	 *                 is array, force convert plain text strings.
	 */
	function man ($_name, $_no, $_int = NULL, $__base = null, $_s = false) {
		if ( ! extension_loaded ("zlib")) {
			echo "Error: man function requires zlib extension!";
			exit (1);
		}

		$this->autoload ($this->man, 'man');
		$this->man->tmpdir = $this->tmpdir;
		return $this->man->man ($_name, $_no, $_int, $__base, $_s);
	}
	// }}}

	// {{{ function __nocli ($n = '')
	/**
	 * If call this method, exit when php sapi is not cli.
	 *
	 * @access private
	 * @return void
	 * @param  string (optional) method name
	 */
	private function __nocli ($n = '') {
		$method = $n ? $n : 'this';
		if ( php_sapi_name () != 'cli' ) {
			echo "<script type=\"text/javascript\">\n" .
				"  alert('{$method} method only used on CLI mode');\n" .
				"  history.back();\n" .
				"</script>\n";
			exit;
		}
	}
	// }}}
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
?>
