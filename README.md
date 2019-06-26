> DNMP PLUS `Docker + Nginx + MySQL + PHP(xhprof、tideways) + Redis + MongDB + xhgui` 可能是 PHPer 最好用的开发环境

DNMP PLUS 项目特点，在 [DNMP](https://github.com/yeszao/dnmp) 的基础上新增：

* [PHP xhprof 扩展](https://github.com/phacility/xhprof) - Facebook 开发的 PHP 性能追踪及分析工具
* [PHP tideways 扩展](https://github.com/tideways/php-xhprof-extension) - xhprof 的分支，支持 PHP7
* PHP mongodb 扩展
* MongoDB 服务
* Mongo Express - MongoDB 服务管理系统
* [xhgui - XHProf](https://github.com/perftools/xhgui) 分析数据数据的 GUI 系统

## 目录结构

``` bash
├── conf                        配置文件目录
│   ├── conf.d                  Nginx 用户站点配置目录
│   ├── nginx.conf              Nginx 默认配置文件
│   ├── mysql.cnf               MySQL 用户配置文件
│   ├── php-fpm.conf            PHP-FPM 配置文件（部分会覆盖php.ini配置）
│   └── php.ini                 PHP 默认配置文件
├── docs                        文档目录
├── extensions                  PHP 扩展源码包
├── log                         日志目录
├── mongo                       MongoDB 数据目录
├── mysql                       MySQL 数据目录
├── www                         PHP 代码目录
├── travis-build.sh             Travis CI 构建文件
├── Dockerfile                  PHP 镜像构建文件
├── docker-compose-sample.yml   Docker 服务配置示例文件
├── env.smaple                  环境配置示例文件
└── travis-build.sh             Travis CI 构建文件
```

## 环境要求

* Docker
* Docker-compose
* Git
* Composer

## 快速使用

``` bash
$ git clone https://github.com/guanguans/dnmp-plus.git --recursive
$ cd dnmp-plus
$ cp env.sample .env
$ cp docker-compose-sample.yml docker-compose.yml
$ docker-compose up -d
```

默认 web 跟目录 `www/localhost/`，浏览器访问[http://localhost](http://localhost)

![](docs/localhost.png)

## 基本使用

lnmp-plus 自带 nginx、php72、php56、mysql、mongo、redis、phpmyadmin、phpredisadmin、mongo-express 这些镜像服务

``` bash
# 创建并且启动容器
$ docker-compose up 服务1 服务2 ...
# 创建并且启动所有容器
$ docker-compose up

# 创建并且已后台运行的方式启动容器
$ docker-compose up -d 服务1 服务2 ...
# 创建并且已后台运行的方式启动容器
$ docker-compose up -d 服务1 服务2 ...

# 启动服务
$ docker-compose start 服务1 服务2 ...

# 停止服务
$ docker-compose stop 服务1 服务2 ...

# 重启服务
$ docker-compose restart 服务1 服务2 ...

# 构建或者重新构建服务
$ docker-compose build 服务1 服务2 ...

# 进入命令行容器
$ docker-compose exec 服务 bash

# 删除并且停止容器
$ docker-compose rm 服务1 服务2 ...

# 停止并删除容器，网络，图像和挂载卷
$ docker-compose down 服务1 服务2 ...
```

## PHP 和扩展

### 切换 Nginx 使用的 PHP 版本

默认情况下，我们同时创建 `PHP5.6` 和 `PHP7.2` 2 个 PHP 版本的容器，

切换 PHP 仅需修改相应站点 Nginx 配置的 `fastcgi_pass` 选项，

例如，示例的 [http://localhost](http://localhost) 用的是 PHP7.2，Nginx 配置：

``` conf
fastcgi_pass   php72:9000;
```

要改用 PHP5.6，修改为：

``` conf
fastcgi_pass   php56:9000;
```

重启 Nginx 生效

``` bash
$ docker-compose restart nginx
```

### 安装 PHP 扩展

PHP 的很多功能都是通过扩展实现，而安装扩展是一个略费时间的过程，
所以，除 PHP 内置扩展外，在`env.sample`文件中我们仅默认安装少量扩展，
如果要安装更多扩展，请打开你的`.env`文件修改如下的 PHP 配置，
增加需要的 PHP 扩展：

``` bash
PHP72_EXTENSIONS=pdo_mysql,opcache,redis,xdebug,mongodb,tideways
PHP56_EXTENSIONS=opcache,redis,xdebug,mongodb,xhprof
```

然后重新 build PHP 镜像

``` bash
docker-compose build php72
docker-compose up -d
```

### Host 中使用 php 命令行（php-cli）

打开主机的 `~/.bashrc` 或者 `~/.zshrc` 文件，加上：

``` bash
php () {
    tty=
    tty -s && tty=--tty
    docker run \
        $tty \
        --interactive \
        --rm \
        --volume $PWD:/var/www/html:rw \
        --workdir /var/www/html \
        dnmp_php72 php "$@"
}
```

让文件起效：

``` bash
$ source ~/.bashrc
```

然后就可以在主机中执行 php 命令了：

``` bash
$ php -v
PHP 7.2.13 (cli) (built: Dec 21 2018 02:22:47) ( NTS )
Copyright (c) 1997-2018 The PHP Group
Zend Engine v3.2.0, Copyright (c) 1998-2018 Zend Technologies
    with Zend OPcache v7.2.13, Copyright (c) 1999-2018, by Zend Technologies
    with Xdebug v2.6.1, Copyright (c) 2002-2018, by Derick Rethans
```

## 使用 Log

Log 文件生成的位置依赖于 conf 下各 log 配置的值。

### Nginx 日志

Nginx 日志是我们用得最多的日志，所以我们单独放在根目录 `log` 下。

`log` 会目录映射 Nginx 容器的 `/var/log/nginx` 目录，所以在 Nginx 配置文件中，需要输出 log 的位置，我们需要配置到 `/var/log/nginx` 目录，如：

``` conf
error_log  /var/log/nginx/nginx.localhost.error.log  warn;
```

### MySQL 日志

因为 MySQL 容器中的 MySQL 使用的是 `mysql` 用户启动，它无法自行在 `/var/log` 下的增加日志文件。所以，我们把 MySQL 的日志放在与 data 一样的目录，即项目的`mysql`目录下，对应容器中的 `/var/lib/mysql/` 目录。

mysql.conf 中的日志文件的配置：

``` conf
slow-query-log-file     = /var/lib/mysql/mysql.slow.log
log-error               = /var/lib/mysql/mysql.error.log
```

## 数据库管理

* MySql `root` 用户默认密码 `123456`, 默认 phpMyAdmin 地址：http://localhost:8080
* 默认 phpRedisAdmin 地址：http://localhost:8081
* 默认 Mongo Express 地址：http://localhost:8082

## License

[MIT](LICENSE)
