<?php

/**
* 批量发送邮件
* SMTP发送邮件，先安装 MAIL, Net_SMTP, Mail_Mime
* smtp是文本协议，其实也可以手动组装，但是有现成的为什么不用呢？
*/

require_once("Mail.php");

$input = './user.txt';  // 邮箱账号信息文件
$contents = file($input);

$subject = '标题党';

$smtp = Mail::factory('smtp', array(
	'host' => '192.168.1.3', // 邮箱ip，也可以是域名；端口默认是25
	'username' => 'test@example.com', // 登录账号(如果smtp服务器要求登录的话)
	'password' => '123456',   // 登录密码
));

$mime = Mail::factory('mime', array(
	'text_charset' => 'UTF-8',	
	'head_charset' => 'UTF-8',	
	''
));

$arrHeaders = $mime->headers(array(
	'Subject' => $subject,
	'From' => 'no-reply@camera360.com',
	'Content-Transfer-Encoding' => 'quoted-printable',  // 如果邮件body使用了传输编码，要在header里指明，否则邮件展示有问题 
	'Content-type' => 'text/plain; charset=UTF-8',  // 纯文本
));

$num = 0;
foreach($contents as $line) {
	$num ++;
	$line = trim($line);
	if (empty($line)) continue;
	$arrTmp = explode(' ', $line);
	if ($arrTmp[0][0] == '#') { 
		print "$line\n";
		continue;
	}
	$username = $arrTmp[0];
	$mailbox = $arrTmp[1];
	$arrHeaders['To'] = $mailbox;
	$content = "Hi $username,\n\nHello World！"; 
	$mime->setTXTBody($content);
	//$mime->setHTMLBody('<b>hello world</b>');  // 如果同时设置了HTMLBody，那么Content-type会变化
	//print_r($mime->isMultipart());
	$body = $mime->get();
	$ret = $smtp->send($mailbox, $arrHeaders, $body);
	if ($ret) {
		echo "send $mailbox OK\n";
	} else {
		echo "send $mailbox FAILED\n";
	}
}
