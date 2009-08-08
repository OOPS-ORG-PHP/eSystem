<?
# Id: $
require_once ('eSystem.php');

$e = new eSystem;

/*
 *  GETOPT test
 */

echo "\n";
echo "-- Getopt Test -------------------------------------------\n\n";

# init getopt global variables
$longopt = array (
	'longa' => 'a',
	'longb' => 'b',
	'longc' => 'c',
	'longd' => 'd'
);

while ( ($opt = $e->getopt ($argc, $argv, "abc:d")) != -1 ) :
	switch ($opt) :
		case 'a' :
			$a++;
			break;
		case 'b' :
			$b++;
			break;
		case 'c' :
			$c = $optarg;
			break;
		case 'd' :
			$d++;
			break;
		default:
			echo "getopt failed. option is abc:d ($opt)\n";
			exit (1);
	endswitch;
endwhile;

echo "Option a => $a\n";
echo "Option b => $b\n";
echo "Option c => $c\n";
echo "Option a => $d\n";
echo "Optcno   => $optcno\n";
echo "Optcmd array:\n";
print_r ($optcmd);

echo "\n";
echo "-- _system test ------------------------------------------\n\n";

/*
 * _system test
 */

$v = $e->system ("ls -al", &$r);
echo "Last Line: $v\n";
unset ($v);

/*
 * _exec test
 */

echo "\n";
echo "-- _exec test --------------------------------------------\n\n";
$v = $e->exec ("ls -al", &$o, &$err);

print_r ($o);
echo "Last Line : $v\n";
echo "ERROR CODE: $err\n";

/*
 * mkdirp_p test
 */

echo "\n";
echo "-- mkdir_p test ------------------------------------------\n\n";

$v = $e->mkdir_p ("./ppp/yyy");
echo "mkdir_p test: ";
if ( $v === true ) {
	if ( is_dir ("./ppp/yyy") ) echo "OK";
	else echo "Fail";
	$v = 'true';
} else {
	$v = ( $v === false ) ? 'false' : $v;
}
echo " ==> Return Code: $v\n";

/*
 * unlink test
 */

echo "\n";
echo "-- unlink test ------------------------------------------\n\n";

touch ("unlinktest");
$v = $e->unlink ("unlinktests");
$v = ( $v === true ) ? 'true' : ($v === false) ? 'false' : $v;
echo "Unlink unlinktests: $v\n";
$v = $e->unlink ("unlinktest");
$v = ( $v === true ) ? 'true' : ($v === false) ? 'false' : $v;
echo "Unlink unlinktest: $v\n";
$v = $e->unlink ("ppp/yyy");
$v = ( $v === true ) ? 'true' : ($v === false) ? 'false' : $v;
echo "Unlink ppp/yyy: $v\n";

/*
 * unlink_r test
 */

echo "\n";
echo "-- unlink_R test ------------------------------------------\n\n";

$v = $e->unlink_r ("ppp");
$v = ( $v === true ) ? 'true' : 'false';
echo "Remove ppp: $v\n";

/*
 * tree test
 */

echo "\n";
echo "-- tree test ----------------------------------------------\n\n";

$v = $e->tree (".");
print_r ($v);

/*
 * find test
 */

echo "\n";
echo "-- find test ----------------------------------------------\n\n";

$v = $e->find ("."); #, '', 0);
print_r ($v);


/*
 * color test
 */

echo "\n";
echo "-- color test ---------------------------------------------\n\n";

echo $e->putColor ('Color test red', 'red') . "\n";

/*
 * backspace test
 */

echo "\n";
echo "-- backspace test ---------------------------------------------\n\n";

echo "print abcdefghijklmn and backspace 3 times\n";
echo "abcdefghijklmn\n";
echo "abcdefghijklmn";
$e->backSpace (3);
echo "\n";

/*
 * man test
 */

echo "\n";
echo "-- man test ---------------------------------------------------\n\n";

echo "bash man path:\n";
print_r ($e->manPath ('bash'));

echo "view bash man page\n";
echo $e->man ('free', 1);

?>
