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
// $Id: filesystem.php,v 1.2 2005-07-11 05:57:12 oops Exp $

require_once "eSystem/print.php";

class _sysCommand
{
	# return 1 => path is none
	#        2 => path is not directory
	#        3 => create failed
	#        0 => success
	function mkdir_p ($path, $mode) {
		if ( ! trim ($path) ) return 1;
		$_path = explode ('/', $path);

		for ( $i=0; $i<count($_path); $i++ ) :
			$mknewpath .= "{$_path[$i]}/";
			$mkpath = preg_replace ("!/$!", "", $mknewpath);

			if ( is_dir ($mkpath) || ! trim ($mkpath)) :
				continue;
			elseif ( file_exists ($mkpath) ) :
				$ret = 2;
				break;
			else :
				$ret = mkdir ($mkpath, $mode);
				if ( $ret == FALSE ) :
					$ret = 3;
					break;
				endif;
			endif;
		endfor;

		return 0;
	}

	# return 0 => success
	#        1 => remove false
	#        2 => file not found
	#        3 => file is directory
	function safe_unlink ($f) {
		if ( file_exists ($f) ) :
			if ( is_dir ($f) ) :
				return 3;
			endif;

			$_r = @unlink ($f);

			$_r = ( $_r === FALSE ) ? 1 : 0;
		else :
			return 2;
		endif;

		return $_r;
	}

	function unlink_r ($dir) {
		if ( ! trim($dir) ) { return 0; }
		if ( ! file_exists ($dir) ) { return 0; }
		if ( file_exists ($dir) && ! is_dir ($dir) ) :
			$_r = _sysCommand::safe_unlink ($dir);
			return $_r;
		endif;

		$dh = @opendir ($dir);

		if ( $dh ) :
			while ( $file = readdir ($dh) ) :
				if( $file != "." && $file != ".." ) :
					$fullpath = $dir . "/" . $file;
					if ( !is_dir ($fullpath) ) :
						$_r = _sysCommand::safe_unlink ($fullpath);
					else :
						$_r = _sysCommand::unlink_r ($fullpath);
					endif;
				endif;
			endwhile;

			closedir ($dh);
		endif;

		if ( ! rmdir ($dir) ) :
			return 1;
		endif;

		return 0;
	}

	function tree ($dir = '.', $prefix = '', $recursive = 0) {
		$n['file'] = 0;
		$n['dir'] = 0;

		if ( ! is_dir ($dir) ) { return ""; }
		$dir = preg_replace ('!/$!', '', $dir);

		if ( ! $recursive ) :
			if ( $_SERVER['CLI'] ) :
				echo _sysColor::putColor ("{$dir}/", 'blue') . "\n";
			else :
				echo "$dir/\n";
			endif;
		endif;

		$list = _sysCommand::dirlist ($dir);

		if ( is_array ($list) )
			sort ($list);

		$listno = count ($list);

		for ( $i=0; $i<$listno; $i++ ) :
			$fullpath = $dir . '/' . $list[$i];
			$last = ( $i == ($listno -1 ) ) ? 1 : 0;

			$_prefix = $last ? '`-- ' : '|-- ';

			if ( $_SERVER['CLI'] && is_dir ($fullpath) ) :
				$fname = _sysColor::putColor ("{$list[$i]}/", 'blue');
			else :
				$fname = $list[$i];
				if ( is_dir ($fullpath) ) :
					$fname .= '/';
				endif;
			endif;

			printf ("%s%s%s\n", $prefix, $_prefix, $fname);
			$_prefix = $prefix . preg_replace ('/`|-/', ' ', $_prefix);

			if ( is_dir ($fullpath) ) : 
				$n['dir']++;
				$_n = _sysCommand::tree ($fullpath, $_prefix, 1);
				$n['dir'] += $_n['dir'];
				$n['file'] += $_n['file'];
			else :
				$n['file']++;
			endif;
		endfor;

		return $n;
	}

	# 디렉토리의 파일 리스트를 받는 함수
	# path  -> 파일리스트를 구할 디렉토리 경로
	# regex -> 리스트를 받을 목록
	#          f  : 지정한 디렉토리의 파일만 받음
	#          d  : 지정한 디렉토리의 디렉토리만 받음
	#          l  : 지정한 디렉토리의 링크만 받음
	#          fd : 지정한 디렉토리의 파일과 디렉토리만 받음
	#          fl : 지정한 디렉토리의 파일과 링크만 받음
	#          dl : 지정한 디렉토리의 디렉토리와 링크만 받음
	#          아무것도 지정하지 않았을 경우에는 fdl 모두 받음
	#          /정규식/ 으로 지정하여 정규식 사용 가능
	#
	function find ($path = './', $regex = '', $norecursive = 0) {
		$path = preg_replace ('!/$!', '', $path);
		$_r = _sysCommand::dirlist ($path, 1);

		if ( ! count ($_r) ) :
			return array();
		endif;

		foreach ( $_r as $v ) :
			switch ( $regex ) :
				case 'f' :
					if ( is_file ($v) && ! is_link ($v) )
						$file[] = $v;
					break;
				case 'd' :
					if ( is_dir ($v) )
						$file[] = $v;
					break;
				case 'l' :
					if ( is_link ($v) )
						$file[] = $v;
					break;
				case 'fd' :
					if ( is_file ($v) || is_dir ($v) )
						$file[] = $v;
					break;
				case 'fl' :
					if ( is_file ($v) || is_link ($v) )
						$file[] = $v;
					break;
				case 'dl' :
					if ( is_dir ($v) || is_link ($v) )
						$file[] = $v;
					break;
				default :
					if ( $regex ) :
						$_v = basename ($v);
						if ( preg_match ($regex, $_v) ) :
							$file[] = $v;
						endif;
					else :
						$file[] = $v;
					endif;
			endswitch;

			if ( is_dir ($v) && ! $norecursive ) :
				$_rr = _sysCommand::find ($v, $regex);
	
				if ( is_array ($_rr) ) :
					$file = array_merge ($file, $_rr);
				endif;
			endif;
		endforeach;

		return $file;
	}

	function dirlist ($path, $fullpath = 0) {
		$path = preg_replace ('!/$!', '', $path);

		if ( $p = @opendir ($path) ) :
			while ( $_list = readdir ($p) ) :
				if ( $_list == '.' || $_list == '..' ) :
					continue;
				endif;

				$list[] = $fullpath ? "$path/$_list" : $_list;
			endwhile;

		closedir ($p);
		endif;

		return $list;
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
