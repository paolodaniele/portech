<?php
echo "PORTECH MV-370 SMS Communicator\n";
echo "Version 1.3\n";
echo "Paolo Daniele - admin at paolodaniele dot it\n";
echo "===============================\n";

echo "\n\n";

$host = 'your_portech_ip';
$username = 'your_portech_user';
$password = 'your_portech_pwd';

// Do you need to see debug?
$debug = true;

// Delete read messages?
$delete_read = true;

function parse_csv($str, $options = null)

{
	$delimiter = empty ( $options ['delimiter'] ) ? "," : $options ['delimiter'];
	$to_object = empty ( $options ['to_object'] ) ? false : true;
	$expr = "/,(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/"; // added
	$fields = preg_split ( $expr, trim ( $str ) ); // added
	$fields = preg_replace ( "/^\"(.*)\"$/", "$1", $fields ); // added
	return $fields;
}
function caller_name($number) {
  //TODO  Function to Lookup Caller Name from Contact Database...
	return '';
}
function add_sms($from, $date, $body) {
	$fromname = "$from";
	$name = caller_name ( $from );
	if ($name != '')
		$fromname = "$name ($from)";
	$from_email = 'sender@email.com';
	$to_email = 'receiver@email.com';
	echo "==========\nNEW SMS: $date\nFROM: $fromname\n\n$body\n==========\n";
	mail ( $to_email, "New SMS from: $fromname", "NEW SMS: $date \nFROM: $fromname\n$body\n", "from: $from_email\r\n" );
}

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
sleep ( 1 );
if ($debug)
	echo fread ( $fp, 128 );
sleep ( 1 );

// List SMS Messages... REC UNREAD only unread messages - ALL show all messages
$cmd = "AT+CMGL=\"REC UNREAD\"\r";
// $cmd = "AT+CMGL=\"ALL\"\r";
fputs ( $fp, $cmd, strlen ( $cmd ) );
sleep ( 1 );
$res = " ";
$ttlres = "";
stream_set_timeout ( $fp, 5 ); // 5 seconds read timeout
while ( $res != "" ) {
	$res = fread ( $fp, 256 );
	if ($debug)
		echo $res;
	$ttlres .= $res;
}

echo "SMS Read Finished!\n";
$sms_list = explode ( '+CMGL: ', $ttlres );
// print_r($sms_list);
foreach ( $sms_list as $sms ) {
	$sms_hdr = explode ( "\n", $sms, 2 );
	$arr_sms = parse_csv ( $sms_hdr [0] );
	
	// print_r($arr_sms);
	if ((count ( $arr_sms ) > 2)) {
		add_sms ( $arr_sms [2], $arr_sms [4], trim ( $sms_hdr [1] ) );
		if ($delete_read) {
			// Delete Message...
			$cmd = "at+cmgd={$arr_sms[0]}\r";
			fputs ( $fp, $cmd, strlen ( $cmd ) );
			sleep ( 1 );
		}
	}
	if ($debug)
		echo fread ( $fp, 128 );
}
fclose ( $fp );
?>
