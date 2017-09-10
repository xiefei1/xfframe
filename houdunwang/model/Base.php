<?php

namespace houdunwang\model;

use PDO;
use PDOException;
use Exception;

class  Base
{
//    定义一个静态属性给个默认值为空，用来存储对象
    private static $pdo = null;
//    定义一个属性，用来存储表名用
    private $table;
//    where条件
    private $where = '';
//    查询结构的数据
    private $data;
//    获取指定字段
    private $field = '';

//    构造方法将model接收的值传进去用来获取表名
    public function __construct($class)
    {
//        判断是否连接数据库如果以连接不需要重新连接
        if (is_null(self::$pdo)) {
//            静态调用连接数据的方法
            self::connect();
        }
//        传过来的$class是带命名空间的类我需要截取一下，一个类对应一张表类名就是表名
        $info = strtolower(ltrim(strrchr($class, '\\'), '\\'));
//        获得表明存到属性里
        $this->table = $info;

    }

//    链接数据库方法
    private static function connect()
    {
        try {
//            拼接服务器的地址调用助手函数的函数获得服务器信息
            $dsn = c('database.driver') . ":host=" . c('database.host') . ";dbname=" . c('database.dbname');
//           拼接数据库的用户名调用助手里的函数获得用户名
            $username = c('database.username');
//            拼接数据库的密码调用助手里的函数获的密码
            $password = c('database.password');
//            链接数据库
            self::$pdo = new \PDO($dsn, $username, $password);
//           设置字符集
            self::$pdo->query('set names utf8');
//           设置错误属性
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {
//            抛出详细错误
            throw new Exception($e->getMessage());
        }
    }

    public function update(array $data)
    {
        if (empty($this->where)) {
            return false;
        }

        $fields = '';
        foreach ($data as $k => $v) {
            if (is_int($v)) {
                $fields .= "$k=$v" . ',';

            } else {
                $fields .= "$k='$v'" . ',';
            }
        }
        $fields = rtrim($fields, ',');
//        p($fields);die;
        $sql = "update {$this->table} set {$fields} {$this->where}";
        return $this->exec($sql);
    }

    public function insert(array $data)
    {
        $fields = '';
        $values = '';
        foreach ($data as $k => $v) {
            $fields .= $k . ',';
            if (is_int($v)) {
                $values .= $v . ',';
            } else {
                $values .= "'$v'" . ',';
            }
        }
        $fields = rtrim ( $fields , ',' );
        $values = rtrim ( $values , ',' );
        $sql = "insert into {$this->table} ({$fields}) values ({$values})";
        return $this->exec($sql);
    }

    public function destory($pk = '')
    {
        p($pk);
        if (empty($this->where || empty($pk))) {
            if (empty($this->where)) {
                $priKey = $this->getPriKey();
                $this->where("{$priKey}={$pk}");
            }
            $sql = "delete from {$this->table} {$this->where}";
            return $this->exec($sql);

        } else {
            return false;
        }

    }

    public function getAll()
    {
        $field = $this->field ?: '*';
        $sql = "select {$field} from {$this->table} {$this->where}";
        $data = $this->query($sql);
        if (!empty($data)) {
            $this->data = $data;
            return $this;
        }
        return [];
    }


//    找到一条数据的方法
    public function find($id)
    {
//        获取主键的方法

        $key = $this->getPriKey();
//        调用where方法传入健名和键值
        $this->where("$key={$id}");
        $field = $this->field ?: '*';
        $sql = "select {$field} from {$this->table} {$this->where}";
        $data = $this->query($sql);
        if (!empty($data)) {
            $this->data = current($data);
            return $this;
        }
        return $this;
        return [];

    }

//    将对象转换为数组
    public function toArray()
    {

        if ($this->data) {

            return $this->data;
        }
        return [];
    }

//    where条件方法
    public function where($where)
    {
//        把where条件存到where属性里
        $this->where = "where {$where}";
//        返回对象用来链式操作
        return $this;

    }

//    获得主键的方法
    public function getPriKey()
    {
//        拼接查询表结构
        $sql = "desc " . $this->table;
//        调用查询的方法
        $data = $this->query($sql);
//        用来存储返回的结果主键的的结果
        $key = '';
//        循环表结构是一个数组
        foreach ($data as $v) {
//            判断是否为主键
            if ($v['Key'] == 'PRI') {
//                获得主键叫什么
                $key = $v['Field'];
                break;
            }
        }
//        将主键返出去

        return $key;
    }


//    执行有结果的sq语句
    public function query($sql)
    {
        try {
//            实例化一个对象调用方法执行sql语句
            $res = self::$pdo->query($sql);
//         将查询的数据返出去是一个数组
            return $row = $res->fetchAll(PDO::FETCH_ASSOC);
//        抛出错误
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

//    执行没有结果集的sq语句
    public function exec($sql)
    {
        try {
//            执行sq语句
            $res = self::$pdo->exec($sql);
//            判断是否添加如果是添加返回主键id
            if ($lastInsertId = self::$pdo->lastInsertId()) {
                return $lastInsertId;

            } else {
//                返回受影响的sq语句的条数
                return $res;
            }
//            抛出异常
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }

    }

}