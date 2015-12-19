
# change password
alter user 'root'@'localhost' IDENTIFIED BY 'xxxx'; 

# 创建用户/授权
GRANT CREATE,INSERT,SELECT,LOCK TABLES,UPDATE ON db1.* TO 'app'@'localhost' IDENTIFIED BY 'xxxx';
GRANT CREATE,INSERT,SELECT,LOCK TABLES,UPDATE ON db1.* TO 'app'@'*' IDENTIFIED BY 'xxxx';
GRANT ALL PRIVILEGES ON db1.* TO 'userx'@'localhost' IDENTIFIED BY 'xxxx';

