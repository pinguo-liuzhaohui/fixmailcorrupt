#!/usr/bin/awk -f 
# 从(日志)文件里找出指定IP的访问记录
# 假设第一列为访问IP(如nginx日志)

BEGIN{
	ipArr["127.0.0.1"] = 1;
	ipArr["192.168.1.1"] = 1;
}
{
	if($1 in ipArr) print $0; 
}
