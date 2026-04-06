## PHP开源工作流引擎yflow的webman插件

## 安装使用

**要求 php>=8.1**

### 1.确保已经安装了webman

webman安装地址:

https://www.workerman.net/doc/webman/install.html

```
# 默认使用交互式安装向导
composer create-project workerman/webman:~2.0
# 禁用交互式安装向导
composer create-project workerman/webman:~2.0 --no-interaction
```

## 2.安装引擎(可以省略)

```
composer require ysh/yflow
```

> 因为webman-yflow内部已经依赖了 yflow 引擎

## 3.安装webman-admin插件

```php
composer require -W webman/admin ~2.0
```

重启webman,参考 https://www.workerman.net/doc/webman/install.html#2.%20%E8%BF%90%E8%A1%8C

访问 http://127.0.0.1:8787/app/admin/ 完成数据库相关配置
> 无法在composer.josn中依赖webman-admin插件，因为webman-yflow插件安装时,需要写菜单到数据库中,所以,必须保证webman-admin插件安装成功
>

## 4.安装webman-yflow插件

```
composer require ysh/webman-yflow
```

## 5.修改config\database.php下的数据库配置

```php
'database'  => 'test_yflow',
'username'  => 'root',
'password'  => 'root',
'charset'   => 'utf8mb4',
'collation' => 'utf8mb4_general_ci',
```

重新访问: http://127.0.0.1:8787/app/admin

可以导入流程定义json文件,位于 插件目录下/测试流程json/leaveFlow-serial1.json

## yflow 引擎开源地址:

https://github.com/Mr-ShiHuaYu/yflow

## 非常感谢 java版本的 warm-flow 项目，为 本项目带来的灵感.

java warm-flow 项目地址:
https://gitee.com/dromara/warm-flow

## 引擎概览

![warm-flow](https://foruda.gitee.com/images/1754530281717340950/b531c256_2218307.png)

![warm-flow](https://foruda.gitee.com/images/1754530582498275502/be3acb55_2218307.png)

![warm-flow](https://foruda.gitee.com/images/1742803956071384899/eb563152_2218307.png)

![warm-flow](https://www.workerman.net/upload/img/20260406/0669d32bd93f9b.jpg)
