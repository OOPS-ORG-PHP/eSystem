<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 4														|
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group								|
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,	  |
// | that is bundled with this package in the file LICENSE, and is		|
// | available at through the world-wide-web at						   |
// | http://www.php.net/license/2_02.txt.								 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to		  |
// | license@php.net so we can mail you a copy immediately.			   |
// +----------------------------------------------------------------------+
// | Author: JoungKyun Kim <http://www.oops.org>						  |
// +----------------------------------------------------------------------+
//
// $Id: print.php,v 1.12 2009-08-06 18:50:46 oops Exp $

class eSystem_print extends eSystem_output
{
	// {{{ function setColor ($color)
	function setColor ($color) {
		$color = $color ? strtolower ($color) : '';

		switch ($color) :
			case 'gray'	: return '[1;30m'; break;
			case 'red'	 : return '[1;31m'; break;
			case 'green'   : return '[1;32m'; break;
			case 'yellow'  : return '[1;33m'; break;
			case 'blue'	: return '[1;34m'; break;
			case 'megenta' : return '[1;35m'; break;
			case 'cyan'	: return '[1;36m'; break;
			case 'white'   : return '[1;37m'; break;
			case 'end'	 : return '[7;0m'; break;
		endswitch;

		return '[1;30m';
	}
	// }}}

	// {{{ function putColor ($str, $color = '')
	function putColor ($str, $color = '') {
		$color = $this->setColor ($color);
		$end = $this->setColor ('end');

		return $color . $str . $end;
	}
	// }}}
}

class eSystem_output
{
	// {{{ function makeWhiteSpace ($no)
	function makeWhiteSpace ($no) {
		if ( ! is_numeric ($no) )
			return '';

		for ( $i = 0; $i<$no; $i++ )
			$_r .= ' ';

		return $_r;
	}
	// }}}

	// {{{ function backSpace ($no)
	function backSpace ($no) {
		if ( ! is_numeric ($no) )
			return '';

		for ( $i=0; $i<$no; $i++ ) :
			printf ("%c %c", '008', '008');
			#echo "12" ^ "9";
			#echo ' ';
			#echo "12" ^ "9";
		endfor;
	}
	// }}}

	// {{{ function printe ($format, $msg = '', $type = 0, $des = '', $extra_headers = '')
	function printe ($format, $msg = '', $type = 0, $des = '', $extra_headers = '') {
		/*
		if ( function_exists ('fprintf') ) :
			fprintf ('stderr', $format, $msg);
		else :
		*/
			if ( $msg ) :
				$functionName = is_array ($msg) ? 'vsprintf' : 'sprintf';
				$format = $functionName ($format, $msg);
			endif;

			$r = error_log ($format, $type, $des, $extra_headers);
		//endif;
		return $r;
	}
	// }}}

	// {{{ function printe_f ($f, $format, $msg = '')
	function printe_f ($f, $format, $msg = '') {
		$r = $this->printe ($format, $msg, 3, $f);
		return $r;
	}
	// }}}

	// {{{ function print_s ($msg, $width = 80, $indent = 0, $ul = '', $to_stderr = 0)
	function print_s ($msg, $width = 80, $indent = 0, $ul = '', $to_stderr = 0) {
		if ( $width === 0 ) :
			$width = 10000; /* means unlimits */
		endif;

		if ( $indent > 0 ) :
			$width -= $indent;
			if ( $width <= 0 ) :
				$this->printe ('Plez make string length more than current length');
				return FALSE;
			endif;
		endif;

		$l = strlen ($ul);
		if ( $l > 0 ) :
			$width -= $l + 1;
			$ul .= ' ';
		endif;

		if ( is_array ($msg) ) :
			if ( !count ($msg) ) :
				return;
			endif;

			foreach ($msg as $v) :
				$_msg .= rtrim ($v);
			endforeach;
		else :
			if ( ! trim ($msg) ) :
				return;
			endif;
			$_msg = preg_replace ('/[\r]/', '', $msg);
		endif;

		unset ($msg);
		$msg = rtrim ($this->_wordwrap ($_msg, $width));
		if ( preg_match ('/-newline$/', $msg) ) :
			$msg = preg_replace ('/-newline$/', '', $msg);
			$new_line = 0;
		else :
			$new_line = 1;
		endif;

		unset ($_msg);
		$_msg = split ("[\n]", $msg);
		$_line = count ($_msg);

		for ( $i=0; $i<$_line; $i++ ) :
			if ( $i == 1 ) :
				$_ul = strlen ($ul);
				$ul = str_repeat (' ', $_ul);
			endif;
			if ( $to_stderr ) :
				$this->printe ("%s%s%s", array (str_repeat (' ', $indent), $ul, $_msg[$i]));
			else :
				printf ("%s%s%s%s",
						str_repeat (' ', $indent),
						$ul,
						str_replace ('&nbsp;', ' ', $_msg[$i]),
						$new_line ? "\n" : '');
			endif;
		endfor;

		return TRUE;
	}
	// }}}

	// {{{ function _wordwrap ($msg, $width = 75, $break = "\n", $cut = 0)
	function _wordwrap ($msg, $width = 75, $break = "\n", $cut = 0) {
		$msg = wordwrap ($msg, $width, $break, $cut);

		$_msg = split ("[{$break}]", $msg);
		$_msgl = count ($_msg);

		for ( $i=0; $i<$_msgl; $i++ ) :
			$current = rtrim ($_msg[$i]);
			$l = strlen ($current);
			$chk = $width - $l;

			if ( $i == 0 ) :
				$v = $current . "\n";
				continue;
			endif;

			if ( $chk <= ($width * 0.2) || $chk == $width ) :
				$v .= $current . "\n";
			else : 
				if ( $i == ($_msgl - 1 ) ) :
					$v .= $current . "\n"; 
					break;
				endif;

				$next = rtrim ($_msg[++$i]);

				if ( ! strlen ($next) ) :
					$v .= "\n";
					continue;
				endif;

				$current .= ' ' . $next;

				$current = wordwrap ($current, $width, $break, $cut);
				$_c = split ("[{$break}]", $current);
				$_cl = count ($_c);

				$v .= $_c[0] . "\n";

				if ( $_cl > 1 ) :
					$_msg[--$i] = $_c[1];
				endif;
			endif;
		endfor;

		return $v;
	}
	// }}}

	// {{{ function _file_nr ($f, $in = 0, $res = '')
	function _file_nr ($f, $in = 0, $res = '') {
		$fp = is_resource ($res) ? $res : fopen ($f, 'rb');

		if ( ! is_resource ($fp) ) :
			return FALSE;
		endif;

		$i = 0;
		while ( ! feof ($fp) ) :
			$buf = preg_replace ("/\r?\n$/", '', fgets ($fp, 1024));
			$_buf[$i++] = $buf;
		endwhile;

		if ( ! is_resource ($res) ) :
			fclose ($fp);
		endif;

		if ( ! $_buf[--$i] ) :
			unset ($_buf[$i]);
		endif;

		return $_buf;
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
