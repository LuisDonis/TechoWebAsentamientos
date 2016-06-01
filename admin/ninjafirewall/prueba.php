<?php
var_dump(dirname(dirname( __DIR__ )));
var_dump("expression");
$htaccess_data= '# BEGIN NinjaFirewall' . "\n" .
				'<IfModule mod_php5.c>' . "\n" .
				'   php_value auto_prepend_file ' . __DIR__ . '/firewall.php' . "\n" .
				'</IfModule>' . "\n" .
				'# END NinjaFirewall' . "\n";
var_dump($htaccess_data);
die;