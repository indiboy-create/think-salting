## ThinkPHP-PanGu - 盘古ThinkPHP开源索引!
面向 ThinkPHP 的开源工程索引：组件、框架、服务与发布工具，按能力组织，持续整理。
仓库地址：**<https://github.com/indiboy-create/ThinkPHP-PanGu/>**
在线站点：**<https://indiboy-create.github.io/ThinkPHP-PanGu/>**

# youvision/think-salting

ThinkPHP 8 密码加盐组件，提供随机盐生成与可配置前后缀的 MD5 加盐算法。

## 安装

```bash
composer require youvision/think-salting
```

ThinkPHP 8 会自动注册服务并合并 `config/salting.php` 默认配置。

## 配置

如需覆盖默认配置，在项目 `config/salting.php` 中按需定义，项目配置优先于组件默认值：

```php
<?php

return [
    'prefix'      => 'Y!o^U',
    'suffix'      => 'V,is*Io(N',
    'charset'     => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+[]{};:,.<>?/~|',
    'length'      => 8,
    'length2'     => 10,
    'salt_field'  => 'salt',
    'salt2_field' => 'salt2',
];
```

| 配置项 | 说明 |
| --- | --- |
| `prefix` | MD5 算法前缀，对应原算法中的 `Y!o^U`，上线后不建议修改 |
| `suffix` | MD5 算法后缀，对应原算法中的 `V,is*Io(N`，上线后不建议修改 |
| `charset` | `make_salt` 随机取值的字符池，含大小写字母、数字和符号，支持 UTF-8 |
| `length` | 未指定位数时 `make_salt` 的默认长度，也是 `makePair` 中 salt 的默认长度 |
| `length2` | `makePair` 中 salt2 的默认长度 |
| `salt_field` | 业务表中 salt 字段名约定 |
| `salt2_field` | 业务表中 salt2 字段名约定 |

## 生成随机盐

```php
// 使用配置中的默认长度
$salt = make_salt();

// 自定义位数
$salt = make_salt(12);

// 面向对象写法
$salt = \Youvision\ThinkSalting\Salting::makeSalt(12);

// 一次生成入库所需的两段盐
$pair = \Youvision\ThinkSalting\Salting::makePair(8, 10);
// ['salt' => '...', 'salt2' => '...']
```

## 密码哈希

```php
$password = 'user-password';
$hash     = admin_password($password, $salt, $salt2);

// 兼容原始函数名
$hash = AdminPassword($password, $salt, $salt2);

// 校验
$ok = admin_password_verify($password, $salt, $salt2, $hash);
```

算法与原始 `AdminPassword` 一致：

```php
md5($salt2 . 'Y!o^U' . md5(md5($password) . $salt) . 'V,is*Io(N' . $salt);
```

其中 `Y!o^U`、`V,is*Io(N` 均来自配置项 `prefix`、`suffix`。

## 从数据库读取

组件不直接查库，`salt`、`salt2` 由业务层从用户记录中取出后传入：

```php
use app\model\User;

class Login
{
    public function check(string $username, string $password): bool
    {
        $user = User::where('username', $username)->find();
        if (!$user) {
            return false;
        }

        return admin_password_verify(
            $password,
            $user->salt,
            $user->salt2,
            $user->password
        );
    }
}
```

注册时生成并保存：

```php
$pair = \Youvision\ThinkSalting\Salting::makePair();

$user = User::create([
    'username' => $username,
    'password' => admin_password($password, $pair['salt'], $pair['salt2']),
    'salt'     => $pair['salt'],
    'salt2'    => $pair['salt2'],
]);
```