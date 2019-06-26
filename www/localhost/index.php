<?php

echo '<h1 style="text-align: center;">欢迎使用DNMP-PLUS！</h1>';
echo '<h2>版本信息</h2>';

echo '<ul>';
echo '<li>PHP版本：', PHP_VERSION, '</li>';
echo '<li>Nginx版本：', $_SERVER['SERVER_SOFTWARE'], '</li>';
echo '<li>MySQL服务器版本：', getMysqlVersion(), '</li>';
echo '<li>Redis服务器版本：', getRedisVersion(), '</li>';
echo '<li style="color: #FF9632;">MongoDB服务器版本：', getMongoDBVersion(), '</li>';
echo '</ul>';

echo '<h2>已安装扩展</h2>';
printExtensions();


/**
 * 获取MySQL版本
 */
function getMysqlVersion()
{
    if (!extension_loaded('PDO_MYSQL')) {
        return 'PDO_MYSQL 扩展未安装 ×';
    }

    try {
        $dbh  = new PDO('mysql:host=mysql;dbname=mysql', 'root', '123456');
        $sth  = $dbh->query('SELECT VERSION() as version');
        $info = $sth->fetch();
    } catch (PDOException $e) {
        return $e->getMessage();
    }

    return $info['version'];
}

/**
 * 获取MongoDB版本
 */
function getMongoDBVersion()
{
    if (!extension_loaded('mongodb')) {
        return 'Mongodb 扩展未安装 ×';
    }

    try {
        $manager = new MongoDB\Driver\Manager('mongodb://mongo:27017');
        $command = new MongoDB\Driver\Command(['buildinfo' => true]);
        $cursor  = $manager->executeCommand('admin', $command)->toArray();

        return $cursor[0]->version;
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

/**
 * 获取Redis版本
 */
function getRedisVersion()
{
    if (!extension_loaded('redis')) {
        return 'Redis 扩展未安装 ×';
    }

    try {
        $redis = new Redis();
        $redis->connect('redis', 6379);
        $info = $redis->info();

        return $info['redis_version'];
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

/**
 * 获取已安装扩展列表
 */
function printExtensions()
{
    $getLoadedExtensions = get_loaded_extensions();
    echo '<ol>';
    foreach ($getLoadedExtensions as $name) {
        if ($name === 'mongodb'
            || $name === 'mongo'
            || $name === 'tideways_xhprof'
            || $name === 'tideways'
            || $name === 'xhprof'
        ) {
            echo "<li style='color: #FF9632;'>", $name, '=', phpversion($name), '</li>';
        } else {
            echo "<li>", $name, '=', phpversion($name), '</li>';
        }
    }
    echo '</ol>';
}
