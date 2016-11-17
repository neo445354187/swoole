<?php
namespace swoole\db;

/**
 * 数据库类
 */
use PDO;
use PDOException;
use swoole\base\Object;

class Connection extends Object
{
    /**
     * [$prefix 表前缀]
     * @var [type]
     */
    public $prefix; //表前缀

    /**
     * 存储PDO实例
     * @var [type]
     */
    public $pdo;

    /**
     * [$config 储存PDO连接参数]
     * @var array
     */
    public $config = array();

    /**
     * [$build_query 储存构建前的sql各部分]
     * @var [type]
     */
    public $build_query = [
        'table'   => '', #以后可以在init()给table赋值，实现默认表
        'field'   => '* ',
        'join'    => '',
        'where'   => '',
        'groupBy' => '',
        'having'  => '',
        'orderBy' => '',
        'limit'   => '',
        'update'  => '', #用户UPDATE SET后面部分
        'insert'  => '', #用户INSERT INTO后面部分
    ];

    /**
     * [$_sql 执行的上一条sql]
     * @var string
     */
    public $_sql = '';

    /**
     * [$stmt 储存prepare返回的对象]
     * @var [type]
     */
    public $stmt;

    /**
     * 初始化
     * @return [type] [description]
     */
    public function init()
    {
        try {
            $this->pdo = new PDO($this->config['dsn'], $this->config['user'], $this->config['password'], $this->config['driver_options']);
        } catch (PDOException $e) {
            //改成记录到日志文件，开启守护后，自动写入
            echo "error：\n" . $this->config['dsn'] . $e->getMessage();
        }
    }

    /**
     * 封装pdo操作到DB类中，现在DB类对象可以像PDO类对象一样使用了；
     * @param  [type] $method [description]
     * @param  [type] $args   [description]
     * @return [type]         [description]
     */
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->pdo, $method), $args);
    }

    /**
     * [设置涉及到的字段，如果有表别名，需要直接写出]
     * eg：field('alias.name,alias.age' )
     * @param  [mixed] $fields [涉及字段]
     * @return object        [本对象]
     */
    public function field($fields)
    {
        if (is_string($fields)) {
            $this->build_query['field'] = "{$fields} ";
        } elseif (is_array($fields)) {
            $this->build_query['field'] = implode(' , ', $fields) . ' ';
        }
        return $this;
    }

    /**
     * 输入要操作的table，即FROM 部分
     * eg: table('__USER__') ，表示prefix_user表，即带前缀
     * @param  [string] $tables [要操作的表]
     * @return object        [本对象]
     */
    public function table($table)
    {
        if (is_string($table)) {
            $this->build_query['table'] = "{$table} ";
        }
        return $this;
    }

    /**
     * [join JOIN ON 部分，其中以'__USER__'表示prefix_user表，即带前缀]
     * eg：join('INNER JOIN user article.uid=user.uid INNER JOIN type ON article.tid=type.tid')
     * eg: join('INNER JOIN user article.uid=user.uid')->join('INNER JOIN type ON article.tid=type.tid')
     * @param  [type] $join [description]
     * @return object        [本对象]
     */
    public function join($join)
    {
        if (is_string($join)) {
            $this->build_query['join'] .= "{$join} ";
        }
        return $this;
    }

    /**
     * [where WHERE 部分]
     * @param  [string] $where 注意：只能是字符串哦，并不与TP这些类似
     * 需要进行数据绑定，eg: where('name=:name and age=:age')即可；
     * @return [type]        [description]
     */
    public function where($where)
    {
        if (is_string($where)) {
            $this->build_query['where'] = "WHERE {$where} ";
        }

        return $this;
    }

    /**
     * [groupBy GROUP BY 部分]
     * @param  [string] $group_by  GROUP BY 部分
     * @return [type]           [description]
     */
    public function groupBy($group_by)
    {
        if (is_string($group_by)) {
            $this->build_query['groupBy'] = "GROUP BY {$group_by} ";
        }
        return $this;
    }

    /**
     * [having HAVING 部分]
     * @param  [string] $having having 部分
     * @return [type]           [description]
     */
    public function having($having)
    {
        if (is_string($having)) {
            $this->build_query['having'] = "HAVING {$having} ";
        }
        return $this;
    }

    /**
     * [orderBy ORDER BY 部分]
     * eg: orderBy('age DESC,score ASC')
     * @param  [string] $order_by []
     * @return [type]           [description]
     */
    public function orderBy($order_by)
    {
        if (is_string($order_by)) {
            $this->build_query['orderBy'] = "ORDER BY {$order_by} ";
        }
        return $this;
    }

    /**
     * [limit LIMIT部分]
     * @param  [int|string]  $limit  [limit部分]
     * eg: limit('0,10') 从第一条记录开始，取十条
     * eg: limit(0,10) 作用同上
     * @param  boolean $offset [description]
     * @return [type]          [description]
     */
    public function limit($limit, $offset = false)
    {
        if ($offset) {
            $this->build_query['limit'] = "LIMIT {$limit} ";
        } else {
            $this->build_query['limit'] = "LIMIT {$limit} , {$offset} ";
        }
        return $this;
    }

    /**
     * [insert 插入记录，必需为索引数组]
     * eg:['name'=>'chao','age'=>12]
     * @param  [type] $data [插入数据]
     * @return [int]       [返回插入记录的id]
     */
    public function insert($data)
    {
        $keys                        = array_keys($data);
        $this->build_query['insert'] = '(' . implode(' , ', $keys) . ') VALUES (:' . implode(' , :', $keys) . ')';
        $this->buildQuery('insert');
        $this->exec($data);
        return $this->pdo->lastInsertId();
    }

    /**
     * [insertAll 插入多条数据]
     * eg:[['name'=>'chao','age'=>12],['name'=>'chao','age'=>12]]
     * @param  [type] $data [多条数据]
     * @return [int]       [返回是否插入成功]
     */
    public function insertAll($data)
    {
        $keys                        = array_keys($data[0]);
        $this->build_query['insert'] = '(' . implode(' , ', $keys) . ') VALUES (:' . implode(' , :', $keys) . ')';
        $this->buildQuery('insert');
        foreach ($data as $value) {
            $this->exec($value);
        }
        return $this->stmt->errorCode() === '00000'; #获取跟上一次语句句柄操作相关的 SQLSTATE
    }

    /**
     * [delete 删除记录]
     * @param  [array] $data [预编译数组]
     * @return [int]       [返回删除行数]
     */
    public function delete($data = array())
    {
        $this->buildQuery('delete');
        $this->exec($data);
        return $this->stmt->rowCount();
    }

    /**
     * [update 更新记录]
     * @param  [array] $data [更新数组数组]
     * @param   array   $ignore 设置不组装进sql中set部分的值，update(['id'=>1,'user_id'=>8,'content'=>'test','type'=>8],['id'])
     * @return [int]       [返回影响记录数]
     */
    public function update($data=array(), $ignore = array())
    {
        $keys = array_keys($data);
        foreach ($keys as $key => $value) {
            if (in_array($value, $ignore)) {
                unset($keys[$key]);
                continue;
            }
            $keys[$key] = $value . "=:" . $value;
        }
        $this->build_query['update'] = implode(' , ', $keys);
        $this->buildQuery('update');
        $this->exec($data);
        return $this->stmt->rowCount();
    }

    /**
     * [fetch 获取记录]
     * @param  array $data          [数组，预编译的数组]
     * @return [type]              [description]
     */
    public function fetch($data = array())
    {
        $this->buildQuery('select');
        $this->exec($data);
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * [fetchAll 获取记录]
     * @param  array $data          [数组，预编译的数组]
     * @return [type]              [description]
     */
    public function fetchAll($data = array())
    {
        $this->buildQuery('select');
        $this->exec($data);
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * [fetchColumn 获取一条记录中的某一列值]
     * @param  array $data          [数组，预编译的数组]
     * @param  integer $column_number [列编号，从0开始]
     * @return [type]                 [description]
     */
    public function fetchColumn($data = array(), $column_number = 0)
    {
        $this->buildQuery('select');
        $this->exec($data);
        return $this->stmt->fetchColumn($column_number);
    }

    /**
     * [buildQuery 组装sql，注意这里并没有组装完成最终的sql]
     * @return [type] [description]
     */
    public function buildQuery($sql_mode)
    {
        switch ($sql_mode) {
            case 'insert':
                $this->_sql = "INSERT INTO {$this->build_query['table']}{$this->build_query['insert']}";
                break;

            case 'delete':
                $this->_sql = "DELETE FROM {$this->build_query['table']}{$this->build_query['where']}{$this->build_query['orderBy']}{$this->build_query['limit']}";
                break;

            case 'update':
                $this->_sql = "UPDATE {$this->build_query['table']} SET {$this->build_query['update']} {$this->build_query['where']}{$this->build_query['orderBy']}{$this->build_query['limit']}";
                break;

            case 'select':
                $this->_sql = "SELECT {$this->build_query['field']} FROM {$this->build_query['table']}{$this->build_query['join']}{$this->build_query['where']}{$this->build_query['groupBy']}{$this->build_query['having']}{$this->build_query['orderBy']}{$this->build_query['limit']}";
                break;
        }

        $this->_sql = preg_replace_callback('/__(\w{1,})__/', function ($arr) {return $this->prefix . strtolower($arr[1]);}, $this->_sql);
        #清空$this->build_query
        foreach ($this->build_query as $key => $value) {
            $this->build_query[$key] = '';
        }

    }

    /**
     * [exec 执行预编译]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function exec($data)
    {
        //mysql服务端断开连接后，自动重连
        try {
            $this->stmt = $this->pdo->prepare($this->_sql);
            $this->stmt->execute($data);
        }catch(PDOException $e){
            $this->init();
            $this->stmt = $this->pdo->prepare($this->_sql);
            $this->stmt->execute($data);
        }
    }

}
