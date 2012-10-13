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
// $Id: eSystem.php,v 1.1.1.1 2005-07-11 05:24:11 oops Exp $

require_once 'PEAR.php';

$_SERVER['CLI'] = $_SERVER['DOCUMENT_ROOT'] ? '' : 'yes';

/**
 * PEAR's eSystem:: interface. Defines the php extended system mapping function
 * and any utility mapping function
 *
 * @access public
 * @version $Revision: 1.1.1.1 $
 * @package eSystem
 */
class eSystem extends PEAR
{
	# mapping php system function arguments
	# $_var is return code and must use by reference (&)
	function _system ($_cmd, $_returncode = NULL) {
		require_once 'eSystem/system.php';
		return _command::__system ($_cmd, $_returncode);
	}

	# mapping php exec function arguments.
	# $_ouput and $_returncode must use by reference (&)
	function _exec ($_cmd, $_output = NULL, $_returncode = NULL) {
		require_once 'eSystem/system.php';
		$p = _command::__system ($_cmd, $_returncode, 1);
		$p = rtrim ($p);
		$_output = explode ("\n", $p);

		$_size = count ($_output);

		return $_output[$_size - 1];
	}

	# mapping system command mkdir -p
	#
	# return 1 => path is none
	#        2 => path is not directory
	#        3 => create failed
	#        0 => success
	function mkdir_p ($path, $mode = 0755) {
		require_once 'eSystem/filesystem.php';
		return _sysCommand::mkdir_p ($path, $mode);
	}

	# safely unlink function
	#
    # return 0 => success
	#        1 => removed failed
    #        2 => file not found
    #        3 => file is directory
	function _unlink ($path) {
		require_once 'eSystem/filesystem.php';
		return _sysCommand::safe_unlink ($path);
	}

	# mapping system command rm -rf
	function unlink_r ($path) {
		require_once 'eSystem/filesystem.php';

		$list = glob ($path);
		$n = count ($list);
		foreach ( $list as $_v ) :
			if ( _sysCommand::unlink_r ($_v) ) :
				if ( $n > 1 ) :
					eSystem::print_e ("%s remove failed\n", $_v);
				endif;
				return 1;
			endif;
		endforeach;

		return 0;
	}

	# print directory tree
	# mapping system command tree
	# return Array direcotory number and file number
	#
	function tree ($dir = '.') {
		require_once 'eSystem/filesystem.php';
		return _sysCommand::tree ($dir);
	}

	# mapping system command find
	# path  -> directory path
	# type  -> 
	#          f  : list only files
	#          d  : list only directories
	#          l  : list only link
	#          fd : list only files and directories
	#          fl : list only files and link
	#          dl : list only directories and link
	#          not set list files and links and directories
	#          if '/regex/' use, you cat use pecl regular expression
	# norecursive -> don't recursively
	#
	function find ($path = './', $type = '', $norecursive = 0) {
		require_once 'eSystem/filesystem.php';
		return _sysCommand::find ($path, $type, $norecursive);
	}

	# print given string with ansi color 
	# Supported Color is follows
	#  => gray, red, green, yellow, blue, megenta, cyan, white
	# color white is same boldStr function.
	function putColor ($str, $color = '') {
		eSystem::__nocli();

		require_once 'eSystem/print.php';
		return _sysColor::putColor ($str, $color);
	}

	# print given string with white ansi color
	function boldStr ($str) {
		eSystem::__nocli();

		require_once 'eSystem/print.php';
		return _sysColor::putColor ($str, 'white');
	}

	function backSpace ($no) {
		eSystem::__nocli();

		require_once 'eSystem/print.php';
		_output::backSpace ($no);
	}

	# print string to stderr
	function printe ($format, $msg = '') {
		require_once 'eSystem/print.php';
		_output::printe ($format, $msg);
	}

	# extended getopt function
	function getopt ($no, $arry, $optstrs ) {
		global $optarg, $optcmd, $longopt;

		eSystem::__nocli();

		require_once 'eSystem/getopt.php';
		return _getopt::__getopt ($no, $arry, $optstrs);
	}

	# print man page
	function man ($_name, $_no, $_int = NULL, $__base = null, $_s = 0) {
		if ( !extension_loaded ("zlib")) :
			echo "Error: man function requires zlib extension!";
			exit (1);
		endif;

		require_once 'eSystem/man.php';
		return _eMan::man($_name, $_no, $_int, $__base, $_s);

		return $_r;
	}

	# don't use :-)
	function __nocli () {
		if ( ! $_SERVER['CLI'] ) {
			echo "<script type=\"text/javascript\">\n" .
                 "  alert('_getopt method only used on CLI mode');\n" .
                 "  history.back();\n" .
                 "</script>\n";
			exit;
		}
	}
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
