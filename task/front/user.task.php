<?php
namespace task\front;

use swoole\Task;

/**
 *
 */
class User extends Task
{
    /**
     * 说明：异步任务获取数据库数据；
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    // public static function getUser($user_id)
    // {
    //     $statement = Task::$app['db']->prepare("SELECT name,email FROM ".Task::$app['db']->prefix."user WHERE id=:id");
    //     $statement->execute(['id'=>$user_id]);
    //     return $statement->fetch(\PDO::FETCH_ASSOC);
    // }
    public static function getUser($user_id)
    {
        //测试获取数据
        // var_dump(Task::$app['db']->table('__LOG__')->fetchAll());
        // var_dump(Task::$app['db']->_sql);
        // 
        // //测试增加数据
        // Task::$app['db']->table('__LOG__')->insert(['user_id'=>2,'content'=>'test','type'=>2]);
        // var_dump(Task::$app['db']->_sql);
        // Task::$app['db']->table('__LOG__')->insertAll([['user_id'=>3,'content'=>'test','type'=>3]]);
        // var_dump(Task::$app['db']->_sql);
        // 
        // //测试更改数据
        // var_dump(Task::$app['db']->table('__LOG__')->where('id=:id')->update(['id'=>3,'user_id'=>8,'content'=>'test111','type'=>8],['id']));
        // var_dump(Task::$app['db']->_sql);
        // Task::$app['db']->table('__LOG__')->where("id > :id")->delete(['id' => 8]);
        // var_dump(Task::$app['db']->_sql);
        // 
        // 连接查询
    	// var_dump(Task::$app['db']->table('__LOG__')->field('__LOG__.content as lc,__ADMIN_LOG__.content as ac')->join('LEFT JOIN __ADMIN_LOG__ ON __LOG__.id=__ADMIN_LOG__.id')->where('__LOG__.id < :id')->fetchAll(['id'=>5]));
    	// var_dump(Task::$app['db']->_sql);
    	
    	//执行事务
    	// Task::$app['db']->beginTransaction();
    	// $res1 = Task::$app['db']->table('__ADMIN_LOg__')->insert(['user_id'=>2,'content'=>'test','type'=>2]);
    	// $res2 = Task::$app['db']->table('__LOG__')->insertAll([['user_id'=>3,'content'=>'test','type'=>3]]);
    	// if ($res1 > 0 && $res2 == true) {
    	// 	Task::$app['db']->commit();
    	// } else {
    	// 	Task::$app['db']->rollBack();
    	// }
    	

        return Task::$app['db']->table('__USER__')->field('name,email')->where('id = :id')->fetch(['id' => $user_id]);
    }

}
