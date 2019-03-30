### 系统的环境需求

 1. 可用的 www 服务器，如 Apache、IIS、nginx, 推荐使用性能高效的 Apache 或 nginx.
 2. PHP 5.2.2 及以上
 3. MySQL 5.0 及以上, 服务器需要支持 MySQLi 或 PDO_MySQL
 4. GD 图形库支持或 ImageMagick 支持, 推荐使用 ImageMagick, 在处理大文件的时候表现良好

### 系统的安装

 1. 上传 upload 目录中的文件到服务器
 2. 设置目录属性（windows 服务器可忽略这一步）
以下这些目录需要可读写权限
> ./
> ./system
> ./system/config 含子目录
>
> ./cache
>
> ./tmp
>
> ./uploads

 3. 访问站点开始安装
 4. 参照页面提示，进行安装，直至安装完毕
