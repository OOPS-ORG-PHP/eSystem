<?php
// $Id$

class eSystem_system
{
	// {{{ properties
	public $tmpdir = '/tmp';
	public $tmpname = 'eSystem_system_';
	public $_stdout;
	public $_stderr;
	public $_retint = 0;
	// }}}

	// {{{ function _system ($_cmd, $_out = 0)
	/*
	 * define origin proto function
	 * start function name _
	 */
	function _system ($_cmd, $_out = 0) {
		$_err = tempnam ($this->tmpdir, $this->tmpname);
		$_cmd = $_cmd . ' 2> ' . $_err . '; echo "RET_VAL:$?"';

		$pd = popen ($_cmd, "r");

		while ( ! feof ($pd) ) :
			$_r = rtrim (fgets ($pd, 1024));

			if ( preg_match ("/RET_VAL:([0-9]+)$/", $_r, $_match) ) :
				$this->_retint = $_match[1];

				if ( ! preg_match ("/^RET_VAL/", $_r) ) :
					$_r = preg_replace ('/RET_VAL:.*/', '', $_r);
					$this->_stdout[] = $_r;

					if ( $_out ) :
						echo $_r . "\n";
						flush ();
					endif;
				endif;
				break;
			else :
				$this->_stdout[] = $_r;

				if ( $_out ) :
					echo $_r . "\n";
					flush ();
				endif;
			endif;
		endwhile;
		pclose ($pd);

		if ( filesize ($_err) > 0 ) :
			$this->_stderr = rtrim (file_get_contents ($_err));
		endif;
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
