
# change password
# 参考 http://dev.mysql.com/doc/refman/5.7/en/alter-user.html
alter user 'root'@'localhost' IDENTIFIED BY 'xxxx'; 

# 创建用户/授权
# 参考 http://dev.mysql.com/doc/refman/5.7/en/grant.html
GRANT CREATE,INSERT,SELECT,LOCK TABLES,UPDATE ON db1.* TO 'app'@'localhost' IDENTIFIED BY 'xxxx';
GRANT CREATE,INSERT,SELECT,LOCK TABLES,UPDATE ON db1.* TO 'app'@'*' IDENTIFIED BY 'xxxx';
GRANT ALL PRIVILEGES ON db1.* TO 'userx'@'localhost' IDENTIFIED BY 'xxxx';

# 查看用户权限
SHOW GRANTS FOR 'app'@'localhost';
