<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: JoungKyun Kim <http://www.oops.org>                          |
// +----------------------------------------------------------------------+
//
// $Id: eSystem.php,v 1.20 2009-08-06 20:26:32 oops Exp $

require_once 'PEAR.php';
require_once 'eFilesystem.php';

$_SERVER['CLI'] = $_SERVER['DOCUMENT_ROOT'] ? '' : 'yes';

/**
 * PEAR's eSystem:: interface. Defines the php extended system mapping function
 * and any utility mapping function
 *
 * @access public
 * @version $Revision: 1.20 $
 * @package eSystem
 */
class eSystem extends PEAR
{
	// {{{ properties
	var $system;
	var $fs;
	var $prints;
	var $getopt;
	var $man;
	var $tmpdir = '/tmp';

	var $stderr;
	var $stdout;
	var $retint;
	// }}}

	// {{{ function autoload (&$obj, $f, $cname = '')
	function autoload (&$obj, $f, $cname = '') {
		if ( ! $cname ) :
			$cname = $f;
		endif;

		require_once ('eSystem/' . $f . '.php');
		if ( ! is_object ($obj) ) :
			$objname = "eSystem_" . $cname;
			$obj = new $objname;
		endif;
	}
	// }}}

	// {{{ function system ($_cmd, $_returncode = NULL)
	# mapping php system function arguments
	# $_var is return code and must use by reference (&)
	function system ($_cmd, $_returncode = NULL) {
		$this->autoload (&$this->system, 'system');

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

	// {{{ function exec ($_cmd, $_output = NULL, $_returncode = NULL)
	# mapping php exec function arguments.
	# $_ouput and $_returncode must use by reference (&)
	function exec ($_cmd, $_output = NULL, $_returncode = NULL) {
		$this->autoload (&$this->system, 'system');

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
	# same $this->exec, but $_output is not array
	#
	function execl ($_cmd, $_output = NULL, $_returncode = NULL) {
		$this->autoload (&$this->system, 'system');

		$r = $this->exec ($_cmd, &$_outputs, &$_returncode);

		if ( is_array ($_outputs) && ($c = count ($_outputs)) ) :
			$_output = '';
			for ( $i=0; $i<$c; $i++ ) :
				$_output .= $_outputs[$i] . "\n";
			endfor;
		endif;

		$_output = preg_replace ("/\n$/", '', $_output);

		return $r;
	}
	// }}}

	// {{{ function mkdir_p ($path, $mode = 0755)
	/**
	 * Attempts to create the directory specified by pathname.
	 * If does not parent directory, this API create success.
	 * This means that same operate with mkdir (path, mode, true) of php
	 * @access  public
	 * @return  boolean|int return 1, already exists given path.<br>
	 *                      return 2, given path is existed file.<br>
	 *                      return false, create error by other error.<br>
	 *                      return true, create success.
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
	 * @access  public
	 * @return  bolean|int  return true, success<br>
	 *              return false, remove false<br>
	 *              return 2, file not found<br>
	 *              return 3, file is directory
	 * @param   string  given file path
	 */
	function unlink ($path) {
		return eFilesystem::safe_unlink ($path);
	}
	// }}}

	// {{{ function unlink_r ($path)
	/**
	 * Deletes a file or directory that include some files
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
	 * get directory tree for given path
	 * @access  public
	 * @return  object  obj->file is number of files.<br>
	 *                  obj->dir is number of directories.
	 * @param   string  (optional) Given path. Defaults to current directory (./).
	 */
	function tree ($dir = '.') {
		return eFilesystem::tree ($dir);
	}
	// }}}

	// {{{ function find ($path = './', $type = '', $norecursive = 0)
	/**
	 * get file list that under given path
	 * @access  public
	 * @return  array|false return array of file list. If given path is null or don't exist, return false.
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
	function find ($path = './', $type = '', $norecursive = 0) {
		return eFilesystem::find ($path, $type, $norecursive);
	}
	// }}}

	// {{{ function putColor ($str, $color = '')
	# print given string with ansi color 
	# Supported Color is follows
	#  => gray, red, green, yellow, blue, megenta, cyan, white
	# color white is same boldStr function.
	function putColor ($str, $color = '') {
		$this->__nocli ('putColor');

		return ePrint::asPrintf (!$color ? 'gray' : $color, $str);
	}
	// }}}

	// {{{ function boldStr ($str)
	# print given string with white ansi color
	function boldStr ($str) {
		$this->__nocli('boldStr');

		return $this->putColor ($str, 'white');
	}
	// }}}

	// {{{ function makeWhiteSpace ($no)
	/**
	 * Print white space about given number
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
	 * This API is Deprecated. Use ePrint::ePrintf method instead of this method
	 *
	 * @access  public
	 * @return  int     length of output messages.
	 * @param   string  same format of printf
	 * @param   mixed   (optional) format value.<br>
	 *                  If only one format argument, $msg is strings or array.<br>
	 *                  For multiple format arguments, $msg is array.
	 * @param   integer (optional) this is 3th argument of error_log
	 * @param   string  (optional) this is 4th argument of error_log
	 * @param   string  (optional) this is 5th argument of error_log
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
	 * This API is Deprecated. Use ePrint::ePrintf or ePrint::lPrintf method instead of this method
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
	 * This API is Deprecated. Use ePrint::printi method instead of this method
	 * @access  public
	 * @return  int|false   Length of print string. If on error, return false
	 * @param   string|array    output string
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
	 * @access  public
	 * @return  array|false     Array or false if not found file path nor file resource.
	 * @param   string      file path
	 * @param   boolean     (optional) Search file path on include_path of php.
	 *                      Defaults is false.
	 * @param   resource    (optional) already opend file description resource
	 *                      Defaults is null.
	 */
	function file_nr ($f, $use_include_path = false, $resource = null) {
		return eFilesystem::file_nr ($f, $use_include_path, $resource);
	}
	// }}}

	// {{{ function getopt ($no, $arry, $optstrs )
	# extended getopt function
	#
	# declear two variabes $gno $optcno set -1 before use getopt
	#
	function getopt ($no, $arry, $optstrs ) {
		global $optarg, $optcmd, $longopt;
		global $gno, $optcno;

		$this->__nocli('getopt');

		$this->autoload (&$this->getopt, 'getopt');
		return $this->getopt->getopt ($no, $arry, $optstrs);
	}
	// }}}

	// {{{ function manPath ($_name, $_path = '/usr/share/man', $_sec = 0)
	# print man page
	function manPath ($_name, $_path = '/usr/share/man', $_sec = 0) {
		$this->autoload (&$this->man, 'man');
		$this->man->tmpdir = $this->tmpdir;
		return $this->man->manPath ($_name, $_path, $_sec);
	}
	// }}}

	// {{{ function man ($_name, $_no, $_int = NULL, $__base = null, $_s = 0)
	function man ($_name, $_no, $_int = NULL, $__base = null, $_s = 0) {
		if ( ! extension_loaded ("zlib")) :
			echo "Error: man function requires zlib extension!";
			exit (1);
		endif;

		$this->autoload (&$this->man, 'man');
		$this->man->tmpdir = $this->tmpdir;
		return $this->man->man ($_name, $_no, $_int, $__base, $_s);
	}
	// }}}

	// {{{ function __nocli ($n = '')
	# don't use :-)
	function __nocli ($n = '') {
		$method = $n ? $n : 'this';
		#if ( ! $_SERVER['CLI'] ) :
		if ( php_sapi_name () != 'cli' ) :
			echo "<script type=\"text/javascript\">\n" .
				"  alert('{$method} method only used on CLI mode');\n" .
				"  history.back();\n" .
				"</script>\n";
			exit;
		endif;
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
