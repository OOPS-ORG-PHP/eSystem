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
// $Id: man.php,v 1.5 2007-02-18 18:31:35 oops Exp $

require_once 'eSystem/system.php';
require_once 'eSystem/filesystem.php';

class eSystem_man extends eSystem_system
{
	var $tmpdir = "/tmp";
	var $ofs;

	function eSystem_man () {
		if ( ! is_object ($ofs) ) :
			$this->ofs = new eSystem_filesystem;
		endif;
	}

	function so_man ($_file, $_base, $_int = '') {
		$_dotso = array ();

		if ( preg_match ('/\.gz$/', $_file) ) :
			$_func = 'gzfile';
			$_ext  = '.gz';
		else :
			$_func = 'file';
			$_ext  = '';
		endif;

		if ( ! file_exists ($_file) ) :
			return $_file;
		endif;

		$_dotso = $_func($_file);

		foreach ($_dotso as $_v) :
			$dotso .= $_v;
		endforeach;

		if ( preg_match ("/\.so (.+)/m", $dotso, $_match) ) :
			$_file = "{$_base}/{$_int}{$_match[1]}{$_ext}";
		endif;

		return $_file;
	}

	/*
	 * User level function
	 */

	function manPath ($_name, $_path = '/usr/share/man', $_sec = 0) {
		$_path = ! $_path ? '/usr/share/man/' : $_path;

		if ( $_sec ) :
			$_f   = "{$_path}/man{$_sec}/{$_name}.{$_sec}";
			$_fgz = "{$_path}/man{$_sec}/{$_name}.{$_sec}.gz";

			if ( file_exists ($_f) ) :
				return $_f;
			elseif ( file_exists ($_fgz) ) :
				return $_fgz;
			endif;
		else :
			$_fa = array();
			$_name = preg_quote ($_name);
			$_fa = $this->ofs->find ($_path, "!^{$_name}\.[0-9](\.gz)*$!");
			$_fac = count ($_fa);

			if ( $_fac ) :
				return ($_fac > 1 ) ? $_fa : $_fa[0];
			endif;
		endif;
	}

	function man ($_name, $_no, $_int = NULL, $__base = null, $_s = 0) {
		if ( ! extension_loaded ("zlib")) :
			echo "Error: man function requires zlib extension!";
			exit (1);
		endif;

		$__base  = $__base ? $__base : "/usr/share/man";
		$_mdir   = "man{$_no}";
		$_man    = "{$_name}.{$_no}";
		$_int    = $_int ? "{$_int}/" : '';

		$_base   = "{$__base}/{$_int}{$_mdir}";
		$_file   = "{$_base}/{$_man}";
		$_gzfile = "{$_base}/{$_man}.gz";

		if ( file_exists ($_gzfile) ) :
			$_gzfile = $this->so_man ($_gzfile, $__base, $_int);
			$_gz = array ();

			if ( ! file_exists ($_gzfile) ) :
				return "";
			endif;

			$_gz = gzfile ($_gzfile);

			foreach ($_gz as $_v)
				$_gztmp .= $_v;

			$tmpfile = tempnam ($this->tmpdir, "man-");
			if ( @$this->ofs->filewrite ($tmpfile, $_gztmp) == -1 ) :
				unlink ($tmpfile);
				echo "Error: Can't write $tmpfile\n";
				exit (1);
			endif;

			$this->_system ("/usr/bin/groff -S -Wall -mtty-char -Tascii8 -man $tmpfile");
			$_r = $this->stdout;
			unlink ($tmpfile);
		elseif ( file_exists ($_file) ) :
			$_file = $this->so_man ($_file, $__base, $_int);
			$this->_system ("/usr/bin/groff -S -Wall -mtty-char -Tascii8 -man $_file");
			$_r = $this->stdout;
		else :
			return "";
		endif;

		if ( ! $_s ) :
			if ( is_array ($_r) ) :
				foreach ($_r as $_v ) :
					$v .= $_v ."\n";
				endforeach;
			endif;
			return $v;
		endif;

		return $_r;
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
