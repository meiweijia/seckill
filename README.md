# PHP workerman redis lua 高性能秒杀示例

### 安装 workerman
```shell
composer install
```

### 启动服务
```shell
php server.php start
```

### 浏览器访问 [http://localhost:2345](http://localhost:2345/)

### 性能测试
```shell
ab -n 10000 -c 100 http://localhost:2345/
```