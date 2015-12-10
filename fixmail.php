<?php
/*
* Fix mail by deleting corrupted mails.
* Use USER and PASS 
* POP3 protocol refer RFC1939 (http://tools.ietf.org/rfc/rfc1939.txt)
* POP3 Extension Mechanism refer RFC2449（http://tools.ietf.org/rfc/rfc2449.txt）
*/

$host = "127.0.0.1";
$port = "110"; // secure port is 995

if ($argc != 3) {
	print "Usage: \n";
	print "\t" . $argv[0] . " user pass\n";
	print "\n";
	exit;
}

$username = $argv[1];
$password = $argv[2];


function sendQuery($socket, $CMD, $data = '')
{
	$data = trim($data);
	if (empty($data)) {
		$send = "$CMD\r\n";
	} else {
		$send = "$CMD $data\r\n";
	}
	
    socket_send($socket, $send, strlen($send), 0);
    return;
}

function getResult($socket, $CMD = '')
{
	$retBuffer = '';
	while(($len = @socket_recv($socket, $buffer, 1024, 0)) >0) {
		//print $buffer;
		//print "\n";
		$retBuffer .= $buffer;
	    $n = strlen($retBuffer);
	    if (($CMD == 'LIST' || $CMD == 'RETR' || $CMD == 'CAPA' || $CMD == 'TOP' || $CMD == 'UIDL') && 
	    	($retBuffer[0] == '+' && $retBuffer[1] == 'O' && $retBuffer[2] == 'K') &&
	    	! ($retBuffer[$n-1] == "\n" && $retBuffer[$n-2] == "\r" && $retBuffer[$n-3] == '.')) {
	    	// POP3协议返回数据
	    	// 第一行为状态码 +OK 或者 -ERR
	    	// 若有数据，数据以 ".\r\n" 结束
	    	$buffer = '';
	    	continue;
	    }
	    break;
	}
	return $retBuffer;
}

function getConnection($host, $port)
{
	$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)
      or die("Unable to create socket\n");

    socket_connect($socket, $host, $port);
    $r = getResult($socket); // Get greeting info
    print $r;

    return $socket;
}

function getLoginSession($host, $port, $user, $pass)
{
	$socket = getConnection($host, $port);

	sendQuery($socket, 'USER', $user);
	$r = getResult($socket, 'USER');
	print $r;

	sendQuery($socket, 'PASS', $pass);	
	$r = getResult($socket, 'PASS');
	print $r;

	return $socket;
}

function getMailIdList($socket) 
{
	sendQuery($socket, 'LIST');
	$r = getResult($socket, 'LIST');

	$lines = explode("\r\n", $r);
	$lines = array_filter($lines);
	$arrMailId = array();
	foreach($lines as $v) {
		if ($v[0] == '+' && $v[1] == 'O' && $v[2] == 'K') continue;
		if ($v[0] == '.') continue;
		$v = trim($v);
		if (empty($v)) continue;
		$mid = explode(' ', $v);
		//print "$v\n";
		$arrMailId[] = $mid[0];
	}
	return $arrMailId;
}

function checkMailCorrupt($socket, $mailId)
{
	sendQuery($socket, 'TOP', "$mailId 0");
	$r = getResult($socket, 'TOP');
	$r = trim($r);
	if ($r == '-ERR Message corrupted') {
		print "$mailId $r\n";
		return 1;
	} else if ($r[0] == '-' && $r[1] == 'E' && $r[2] == 'R' && $r[3] == 'R') {
		print "$mailId $r\n";
		return 2;
	}
	return 0;
}

function deleteMail($socket, $mailId)
{
	sendQuery($socket, 'DELE', "$mailId");
	$r = getResult($socket, 'DELE');
	print "DELE $mailId " . $r;
	return;
}

function quitAndClose(& $socket)
{
	sendQuery($socket, 'QUIT');
	$r = getResult($socket, 'QUIT');
	socket_close($socket);
	$socket = null;
	print $r;
	return;
}

// START
// get mail id list
$socket = getLoginSession($host, $port, $username, $password);
$arrMailId = getMailIdList($socket);
quitAndClose($socket);

$totalMail = count($arrMailId);
print "\nTotal mail count $totalMail\n\n";

// delete corrupt mail
// 删除之后id会重新补齐，所以从最大id开始
// 同一封邮件id在不同会话之间不一定相同, 但可以通过UIDL来获得邮件唯一ID标识
// 所以邮件客户端会先获取UIDL, 然后再获取LIST, 最后RETR获取邮件
$totalCorrupt = 0;
$cur = $totalMail - 1;
while ($cur >= 0) {
	$n = 6;
	$socket = getLoginSession($host, $port, $username, $password);
	while(($n > 0) && ($cur >= 0)) {
		$id = $arrMailId[$cur];
		$r = checkMailCorrupt($socket, $id);
		if ($r == 1) {
			$totalCorrupt ++;
			deleteMail($socket, $id);
			$n --;
		} else if ($r != 0) {
			$n --;
		}
		$cur --;
	}
	quitAndClose($socket);
}

print "\nTotal mail corrupt count $totalCorrupt\n\n";

