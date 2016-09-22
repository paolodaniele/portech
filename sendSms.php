<?php
echo "PORTECH MV-370 SMS Communicator\n";
echo "Version 1.3\n";
echo "Paolo Daniele - admin at paolodaniele dot it\n";
echo "===============================\n";

echo "\n\n";

// Do you need to see debug?
$debug = true;
function send_sms($to, $body) {
	
	$host = 'your_portech_ip';
	$username = 'your_portech_user';
	$password = 'your_portech_pwd';
	
	
	$fp = fsockopen ( "$host", 23, $errno, $errstr, 30 );
	if (! $fp) {
		echo "$errstr ($errno)<br />\n";
		die ();
	}
	sleep ( 2 );
	$cmd = "$username\r";
	fputs ( $fp, $cmd, strlen ( $cmd ) );
	if ($debug)
		echo fread ( $fp, 128 );
	sleep ( 1 );
	$cmd = "$password\r";
	fputs ( $fp, $cmd, strlen ( $cmd ) );
	if ($debug)
		echo fread ( $fp, 128 );
	sleep ( 1 );
	$cmd = "module\r";
	fputs ( $fp, $cmd, strlen ( $cmd ) );
	if ($debug)
		echo fread ( $fp, 128 );
	sleep ( 2 );
	$cmd = "ate1\r";
	fputs ( $fp, $cmd, strlen ( $cmd ) );
	if ($debug)
		echo fread ( $fp, 128 );
	sleep ( 1 );
	
	// Select SMS Message Format... (0=PDU Mode, 1=Text Mode)
	$cmd = "at+cmgf=1\r";
	fputs ( $fp, $cmd, strlen ( $cmd ) );
	if ($debug)
		echo fread ( $fp, 128 );
	sleep ( 2 );
	
	// Send SMS Message...
	$cmd = "at+cmgs=\"$to\"\r";
	fputs ( $fp, $cmd, strlen ( $cmd ) );
	sleep ( 2 );
	
	// Body...
	$cmd = "$body\r\x1a"; // Ctrl-Z
	fputs ( $fp, $cmd, strlen ( $cmd ) );
	sleep ( 2 );
	if ($debug)
		echo fread ( $fp, 128 );
	fclose ( $fp );
}

$to = $argv[1];
$body = $argv[2];

send_sms($to, $body);

?>
