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
// $Id: eSystem.php,v 1.5 2007-02-18 18:05:25 oops Exp $

require_once 'PEAR.php';

$_SERVER['CLI'] = $_SERVER['DOCUMENT_ROOT'] ? '' : 'yes';

/**
 * PEAR's eSystem:: interface. Defines the php extended system mapping function
 * and any utility mapping function
 *
 * @access public
 * @version $Revision: 1.5 $
 * @package eSystem
 */
class eSystem extends PEAR
{
	var $system;
	var $fs;
	var $prints;
	var $getopt;
	var $man;
	var $tmpdir = '/tmp';

	function autoload (&$obj, $f, $cname = '') {
		if ( ! $cname ) :
			$cname = $f;
		endif;

		require_once ('eSystem/' . $f . '.php');
		if ( ! is_object ($obj) ) :
			$obj = new $cname;
		endif;
	}

	# mapping php system function arguments
	# $_var is return code and must use by reference (&)
	function system ($_cmd, $_returncode = NULL) {
		$this->autoload (&$this->system, 'system', 'systems');

		$this->system->tmpdir = $this->tmpdir;
		$this->system->_system ($_cmd, 1);

		$_returncode = $this->system->retint;

		$_no = count ($this->system->stdout);
		return $this->system->stdout[--$_no];
	}

	# mapping php exec function arguments.
	# $_ouput and $_returncode must use by reference (&)
	function exec ($_cmd, $_output = NULL, $_returncode = NULL) {
		$this->autoload (&$this->system, 'system', 'systems');

		$this->system->tmpdir = $this->tmpdir;
		$this->system->_system ($_cmd);

		$_output = $this->system->stdout;
		$_returncode = $this->system->retint;
		$_no = count ($this->system->stdout);

		return $this->system->stdout[--$_no];
	}

	# mapping system command mkdir -p
	#
	# return 1 => path is none
	#        2 => path is not directory
	#        3 => create failed
	#        0 => success
	function mkdir_p ($path, $mode = 0755) {
		$this->autoload (&$this->fs, 'filesystem');
		return $this->fs->mkdir_p ($path, $mode);
	}

	# safely unlink function
	#
    # return 0 => success
	#        1 => removed failed
    #        2 => file not found
    #        3 => file is directory
	function unlink ($path) {
		$this->autoload (&$this->fs, 'filesystem');
		return $this->fs->safe_unlink ($path);
	}

	# mapping system command rm -rf
	function unlink_r ($path) {
		$this->autoload (&$this->fs, 'filesystem');

		if ( ! file_exists ($path) ) :
			return 1;
		endif;

		$list = glob ($path);
		if ( $list === FALSE ) :
			return 1;
		endif;

		$n = count ($list);
		foreach ( $list as $_v ) :
			if ( $this->fs->unlink_r ($_v) ) :
				if ( $n > 0 ) :
					$this->fs->printe ("unlink_r error: %s remove failed", $_v);
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
		$this->autoload (&$this->fs, 'filesystem');
			
		return $this->fs->tree ($dir);
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
		$this->autoload (&$this->fs, 'filesystem');
		return $this->fs->find ($path, $type, $norecursive);
	}

	# print given string with ansi color 
	# Supported Color is follows
	#  => gray, red, green, yellow, blue, megenta, cyan, white
	# color white is same boldStr function.
	function putColor ($str, $color = '') {
		$this->__nocli ();

		$this->autoload (&$this->prints, 'print', 'prints');
		return $this->prints->putColor ($str, $color);
	}

	# print given string with white ansi color
	function boldStr ($str) {
		$this->__nocli();

		return $this->putColor ($str, 'white');
	}

	function backSpace ($no) {
		$this->__nocli();

		$this->autoload (&$this->prints, 'print', 'prints');
		$this->prints->backSpace ($no);
	}

	# print string to stderr
	function printe ($format, $msg = '') {
		$this->autoload (&$this->prints, 'print', 'prints');
		$this->prints->printe ($format, $msg);
	}

	# extended getopt function
	#
	# declear two variabes $gno $optcno set -1 before use getopt
	#
	function getopt ($no, $arry, $optstrs ) {
		global $optarg, $optcmd, $longopt;
		global $gno, $optcno;

		$this->__nocli();

		$this->autoload (&$this->getopt, 'getopt', 'getopts');
		return $this->getopt->getopt ($no, $arry, $optstrs);
	}

	# print man page
	function manPath ($_name, $_path = '/usr/share/man', $_sec = 0) {
		$this->autoload (&$this->man, 'man', 'mans');
		$this->man->tmpdir = $this->tmpdir;
		return $this->man->manPath ($_name, $_path, $_sec);
	}

	function man ($_name, $_no, $_int = NULL, $__base = null, $_s = 0) {
		if ( ! extension_loaded ("zlib")) :
			echo "Error: man function requires zlib extension!";
			exit (1);
		endif;

		$this->autoload (&$this->man, 'man', 'mans');
		$this->man->tmpdir = $this->tmpdir;
		return $this->man->man ($_name, $_no, $_int, $__base, $_s);
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
