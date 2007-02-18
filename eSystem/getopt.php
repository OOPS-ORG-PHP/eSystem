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
// $Id: getopt.php,v 1.3 2007-02-18 18:05:25 oops Exp $

require_once "eSystem/print.php";

class getopts extends prints
{
	function getopt ( $no, $arry, $optstrs ) {
		global $optarg, $optcmd, $longopt, $optend;
		global $gno, $optcno;

		if ( $gno < 0 ) $gno = 1;
		if ( $optcno < 0 ) $optcno = 0;
		if ( $optend < 0 ) $optend = 0;

		$this->err = $this->putColor (_("ERROR"), 'white');

		while ( 1 ) :
			if ( $gno == $no ) return -1;

			/* case by long option */
			if ( preg_match ( "/^--[a-z]/i", $arry[$gno] ) && ! $optend ) :
				$longops = explode ('=', $arry[$gno]);
				$longname = trim (str_replace ('--', '', $longops[0]));
				$optarg = trim ($longops[1]);

				$opt = $longopt[$longname];
				$errArg = array ($this->err, $longname);

				if ( ! $opt ) :
					$this->printe (_("%s: option --%s don't support"), $errArg);
					return -2;
				endif;

				if ( preg_match ("/{$opt}:/", $optstrs) ) :
					$optarg = $optarg ? $optarg : $arry[$gno + 1];
					if ( ! trim ($optarg) ) :
						$this->printe (_("%s: option --%s must need values"), $errArg);
						return -2;
					endif;

					if ( ! preg_match ('/=/', $arry[$gno]) ) $gno++;
				endif;
				break;
			/* case by short option */
			elseif ( preg_match ( "/^-[a-z]/i", $arry[$gno] ) && ! $optend ) :
				$opt = $arry[$gno][1];
				$optvalue_c = $arry[$gno][2];
				$errArg = array ($this->err, $opt);

				if ( preg_match ("/{$opt}:/", $optstrs) ) :
					if ( $optvalue_c ) :
						$optarg = preg_replace ('/^-[a-z]/i', '', $arry[$gno]);
					else :
						$nextvalue = $arry[$gno + 1];

						if ( preg_match ('/^-[a-z-]/', $nextvalue) ) {
							$this->printe ("%s: option -%s must need values", $errArg);
							return -2;
						}

						$optarg = $nextvalue;
						$gno++;
					endif;

					if ( ! trim ($optarg) ) :
						$this->printe (_("%s: option -%s must need values"), $errArg);
						return -2;
					endif;
				else :
					if ( $optvalue_c ) :
						$this->printe (_("%s: option must have not any value"), $this->err);
						return -2;
					endif;

					$tmp = preg_replace ('/[a-z]:/i', '', $optstrs);
					$tlen = strlen ($tmp);

					$_optok = 0;
					for ($i=0; $i<$tlen; $i++) :
						if ( $tmp[$i] == $opt ) :
							$_optok = 1;
							break;
						endif;
					endfor;

					if ( ! $_optok ) :
						$this->printe (_("%s: option -%s don't support"), $errArg);
						return -2;
					endif;
				endif;
				break;
			/* case by command arg */
			else :
				if ( $arry[$gno] == '--' ) :
					$optend = 1;
					continue;
					#$this->printe (_("%s: - is unknown option"), $this->err);
					#return -2;
				endif;

				$optcmd[$optcno] = $arry[$gno];
				$optcno++;
				$gno++;
				continue;
			endif;
		endwhile;

		$gno++;

		return $opt;
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
