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
 * @subpackage eSystem_system
 * @author     JoungKyun.Kim <http://oops.org>
 * @copyright  (c) 2016, JoungKyun.Kim
 * @license    BSD
 * @version    $Id: system.php 83 2016-01-06 21:03:22Z oops $
 * @link       http://pear.oops.org/package/KSC5601
 * @filesource
 */

/**
 * alternative sysem class that based eSystem class
 *
 * @package eSystem
 */
class eSystem_system
{
	// {{{ properties
	public $tmpdir = '/tmp';
	public $_stdout;
	public $_stderr;
	public $_retint = 0;

	private $tmpname = 'eSystem_system_';
	// }}}

	// {{{ function _system ($_cmd, $_out = 0)
	/**
	 *
	 * Proto method of eSystem exec functions.
	 *
	 * @access public
	 * @return void
	 * @param  string command that execute an external program and display the output
	 * @param  int    whether saving output message on self::$_stdout
	 */
	function _system ($_cmd, $_out = 0) {
		$_err = tempnam ($this->tmpdir, $this->tmpname);
		$_cmd = $_cmd . ' 2> ' . $_err . '; echo "RET_VAL:$?"';

		$pd = popen ($_cmd, "r");

		while ( ! feof ($pd) ) {
			$_r = rtrim (fgets ($pd, 1024));

			if ( preg_match ("/RET_VAL:([0-9]+)$/", $_r, $_match) ) {
				$this->_retint = $_match[1];

				if ( ! preg_match ("/^RET_VAL/", $_r) ) {
					$_r = preg_replace ('/RET_VAL:.*/', '', $_r);
					$this->_stdout[] = $_r;

					if ( $_out ) {
						echo $_r . "\n";
						flush ();
					}
				}
				break;
			} else {
				$this->_stdout[] = $_r;

				if ( $_out ) {
					echo $_r . "\n";
					flush ();
				}
			}
		}
		pclose ($pd);

		if ( filesize ($_err) > 0 ) {
			$this->_stderr = rtrim (file_get_contents ($_err));
		}
		unlink ($_err);
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
