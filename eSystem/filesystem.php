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
// $Id: filesystem.php,v 1.9 2007-03-05 13:02:27 oops Exp $

require_once "eSystem/print.php";

class eSystem_filesystem extends eSystem_print
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
			$_r = $this->safe_unlink ($dir);
			return $_r;
		endif;

		$dh = @opendir ($dir);

		if ( $dh ) :
			while ( $file = @readdir ($dh) ) :
				if( $file != "." && $file != ".." ) :
					$fullpath = $dir . "/" . $file;
					if ( !is_dir ($fullpath) ) :
						$_r = $this->safe_unlink ($fullpath);
					else :
						$_r = $this->unlink_r ($fullpath);
					endif;
				endif;
			endwhile;

			closedir ($dh);
		else :
			return 1;
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
			#if ( $_SERVER['CLI'] ) :
			if ( php_sapi_name () == 'cli' ) :
				echo $this->putColor ("{$dir}/", 'blue') . "\n";
			else :
				echo "$dir/\n";
			endif;
		endif;

		$list = $this->dirlist ($dir);

		if ( is_array ($list) )
			sort ($list);

		$listno = count ($list);

		for ( $i=0; $i<$listno; $i++ ) :
			$fullpath = $dir . '/' . $list[$i];
			$last = ( $i == ($listno -1 ) ) ? 1 : 0;

			$_prefix = $last ? '`-- ' : '|-- ';

			#if ( $_SERVER['CLI'] && is_dir ($fullpath) ) :
			if ( php_sapi_name () == 'cli' && is_dir ($full_path) ) :
				$fname = $this->putColor ("{$list[$i]}/", 'blue');
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
				$_n = $this->tree ($fullpath, $_prefix, 1);
				$n['dir'] += $_n['dir'];
				$n['file'] += $_n['file'];
			else :
				$n['file']++;
			endif;
		endfor;

		return $n;
	}

	# ���丮�� ���� ����Ʈ�� �޴� �Լ�
	# path  -> ���ϸ���Ʈ�� ���� ���丮 ���
	# regex -> ����Ʈ�� ���� ���
	#          f  : ������ ���丮�� ���ϸ� ����
	#          d  : ������ ���丮�� ���丮�� ����
	#          l  : ������ ���丮�� ��ũ�� ����
	#          fd : ������ ���丮�� ���ϰ� ���丮�� ����
	#          fl : ������ ���丮�� ���ϰ� ��ũ�� ����
	#          dl : ������ ���丮�� ���丮�� ��ũ�� ����
	#          �ƹ��͵� �������� �ʾ��� ��쿡�� fdl ��� ����
	#          /���Խ�/ ���� �����Ͽ� ���Խ� ��� ����
	#
	function find ($path = './', $regex = '', $norecursive = 0) {
		$path = preg_replace ('!/$!', '', $path);

		$_r = $this->dirlist ($path, 1);

		if ( ! count ($_r) ) :
			return array();
		endif;

		$file = array ();
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
						if ( preg_match ($regex, $v) ) :
							$file[] = $v;
						endif;
					else :
						$file[] = $v;
					endif;
			endswitch;

			if ( is_dir ($v) && ! $norecursive ) :
				$_rr = $this->find ($v, $regex);
	
				if ( is_array ($_rr) ) :
					if ( ! $file ) array ();
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

	function filewrite ($f, $v) {
		$fp = fopen ($f, 'wb');
		if ( is_resource ($fp) ) :
			fwrite ($fp, $v, strlen ($v));
			fclose ($fp);
		else :
			return -1;
		endif;

		return 0;
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
