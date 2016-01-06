<?php
/**
 * Project: eSystem:: The Extended file system<br>
 * File:    eSystem/system.php
 *
 * Sub pcakge of eSystem package. This package includes extended system
 * methods.
 *
 * @category   System
 * @package    eSystem
 * @subpackage eSystem_man
 * @author     JoungKyun.Kim <http://oops.org>
 * @copyright  (c) 2016, JoungKyun.Kim
 * @license    BSD
 * @version    $Id$
 * @link       http://pear.oops.org/package/KSC5601
 * @filesource
 */


/**
 * include eSystem_system class
 */
require_once 'eSystem/system.php';

/**
 * Man contorol class api
 *
 * @package eSystem
 */
class eSystem_man extends eSystem_system
{
	// {{{ properties
	public $tmpdir = "/tmp";
	// }}}

	// {{{ function so_man ($_file, $_base, $_int = '') {
	/**
	 * Valid real man file
	 *
	 * @access public
	 * @return string path
	 * @param  string path of man file
	 * @param  string base path of man page
	 * @param  string  (optional) L10n code for international man pages
	 */
	function so_man ($_file, $_base, $_int = '') {
		$_dotso = array ();

		if ( preg_match ('/\.gz$/', $_file) ) {
			$_func = 'gzfile';
			$_ext  = '.gz';
		} else {
			$_func = 'file';
			$_ext  = '';
		}

		if ( preg_match ('!/man[0-9]+$!', $_base) )
			$_base = dirname ($_base);

		if ( ! file_exists ($_file) )
			return $_file;

		$_dotso = $_func($_file);

		foreach ($_dotso as $_v)
			$dotso .= $_v;

		if ( preg_match ("/\.so (.+)/m", $dotso, $_match) )
			$_file = "{$_base}/{$_int}{$_match[1]}{$_ext}";

		return $_file;
	}
	// }}}

	/*
	 * User level function
	 */

	// {{{ function manPath ($_name, $_path = '/usr/share/man', $_sec = 0)
	/**
	 * Return man page file path with man page section and name
	 *
	 * The exmaple:
	 * {@example pear_eSystem/test.php 170 2}
	 *
	 * @access public
	 * @return string Returns man page file path
	 * @param  string Name of man page for searching
	 * @param  string (optional) Base man page base path
	 * @param  integer (optional) Section of man page
	 */
	function manPath ($_name, $_path = '/usr/share/man', $_sec = 0) {
		$_path = ! $_path ? '/usr/share/man/' : $_path;

		if ( $_sec ) {
			$_f   = "{$_path}/man{$_sec}/{$_name}.{$_sec}";
			$_fgz = "{$_path}/man{$_sec}/{$_name}.{$_sec}.gz";

			if ( file_exists ($_f) )
				return $_f;
			elseif ( file_exists ($_fgz) )
				return $_fgz;
		} else {
			$_fa = array();
			$_name = preg_quote ($_name);
			$_fa = eFilesystem::find ($_path, "!/{$_name}\.[0-9](\.gz)*$!");
			$_fac = count ($_fa);

			if ( $_fac )
				return ($_fac > 1 ) ? $_fa : $_fa[0];
		}

		return '';
	}
	// }}}

	// {{{ function man ($_name, $_no, $_int = NULL, $__base = null, $_s = false)
	/**
	 * Return man page contents for human readable
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
			echo "Error: man function requires zlib extension!\n";
			exit (1);
		}

		$__base  = $__base ? $__base : '/usr/share/man';
		$_mdir   = "man{$_no}";
		$_man    = "{$_name}.{$_no}";
		$_int    = $_int ? "{$_int}/" : '';

		$_base   = "{$__base}/{$_int}{$_mdir}";
		$_file   = "{$_base}/{$_man}";
		$_gzfile = "{$_base}/{$_man}.gz";

		if ( file_exists ('/usr/bin/nroff') )
			$mancmd = '/usr/bin/nroff -man';
		else
			$mancmd = '/usr/bin/groff -S -Wall -mtty-char -Tascii -man';

		if ( file_exists ($_gzfile) ) {
			$_gzfile = $this->so_man ($_gzfile, $__base, $_int);
			$_gz = array ();

			if ( ! file_exists ($_gzfile) )
				return '';

			$_gz = gzfile ($_gzfile);

			foreach ($_gz as $_v)
				$_gztmp .= $_v;

			$tmpfile = tempnam ($this->tmpdir, "man-");
			if ( @file_put_contents ($tmpfile, $_gztmp) === false ) {
				unlink ($tmpfile);
				echo "Error: Can't write $tmpfile\n";
				exit (1);
			}

			$this->_system ("$mancmd $tmpfile");
			$_r = $this->_stdout;
			unlink ($tmpfile);
		} elseif ( file_exists ($_file) ) {
			$_file = $this->so_man ($_file, $__base, $_int);
			$this->_system ("$mancmd $_file");
			$_r = $this->_stdout;
		} else
			return '';

		if ( ! $_s ) {
			if ( is_array ($_r) ) {
				foreach ($_r as $_v )
					$v .= $_v ."\n";
			}
			return $v;
		}

		return $_r;
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
