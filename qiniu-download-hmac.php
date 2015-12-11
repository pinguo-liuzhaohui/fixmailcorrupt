<?php

// 七牛bucket访问日志开启后保存到一个bucket里(通常会设置为private)
// 下载的时候需要生成访问token来授权 （使用HMAC）
// HMAC refer to https://en.wikipedia.org/wiki/Hash-based_message_authentication_code
// or refer to RFC2104（http://tools.ietf.org/rfc/rfc2104.txt）

$MY_ACCESS_KEY = 'access_key_here'; 
$MY_SECRET_KEY = 'secret_key_here';
$domainName = 'xxx.qiniucdn.com'; // domain name for the bucket
$objKey = $argv[1]; // an object

// private bucket, need download token 
// refer to : http://developer.qiniu.com/docs/v6/api/reference/security/download-token.html
$expireTime = time() + 30;
$urlBeforeSign = 'http://' . $domainName . '/' . $objKey . '?e=' . $expireTime;
$sign = hash_hmac('sha1', $urlBeforeSign, $MY_SECRET_KEY, true);
$encodedSign = base64_encode($sign);
$encodedSign = str_replace(array('+','/'), array('-','_'), $encodedSign);
$token = "$MY_ACCESS_KEY:$encodedSign";
$downloadUrl = $urlBeforeSign . "&token=$token";

$storeKey = str_replace('/', '_', $objKey);
$downloadCmd = "wget -T 3 -t 2 -O $storeKey '$downloadUrl'";  // -T: timeout seconds; -t: retry count; -O: output destination
echo "$downloadCmd";
//$lastRetLine = system($downloadCmd, $ret);

/*
#!/bin/bash
# file contains objKey, one per line.
cat file | while read line; do
$d=`php qiniu-download-hmac.php $line`
eval $d
done
*/



