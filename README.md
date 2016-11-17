1、思路：
a 前台发送具有路由和需要被后端操作数据的数据，由onMessage()监控到后，进行路由解析到具体的/front/worker或者/back/worker中的操作，当然真实被操作的数据也会到传递到具体方法上；

b 如果需要异步操作，需要调用\Task::create()投递任务到task进程处理，注意该方法的用法；

备注：
1）只有worker文件夹(无论是前台还是后台的)才能被直接路由访问到；
2）worker文件夹和task文件夹中的所有方法必须是静态的，当然同时要求变量也得是类的静态变量；

2、swoole开发与平常php开发区别
1）swoole开发类似于常规php开启了opcache缓存，swoole的所有代码必须常驻内存（可以在start()之前，也可以在worker或者task进程开启时，引入文件）。

2）与常规php类似，一个进程处理同一时间只能一个请求，不同的是，常规php的进程是数量变化的，根据请求而定，swoole的进程数量是固定的，这也说明swoole会阻塞，所以需要通过task进程，异步处理耗时任务。

3）触发服务端绑定事件类似于常规php一个请求，即会执行绑定事件中的代码，如websocket服务端，客户端发来信息，触发绑定的message事件，执行该绑定事件代码。


2、框架目录说明
swoole	
	|---- index.php 			入口文件
		
	|---- config 				其中server-config.php在$ws->start()之前加载

	|---- log 					日志文件夹

	|---- back 					后台文件夹

	|---- front 				前台文件夹

加载顺序：
/config/server-config.php 		服务器配置文件
/server 						服务器执行文件
以上在$ws->start()之前
在onWorkstart()中加载前后台配置以及前后台代码，并且把所有其他功能先服务定位到对象属性上，onOpen只负责路由




3、框架功能模块
c) 处理操作模块WORK，路由

5) 日志模块，错误接管，还有用户操作日志(可能要用数据库储存)

a) 安全模块：XSS，CSRF，PDO

b) 数据库访问模块TASK，还包括sql拼装(除了建立连接代码放在task内，尝试把sql拼装代码放在$ws->start()之前)

d) 缓存模块，只用redis

f) 权限管理，看以后利用session用redis存储权限

把配置文件的代码放在work进程中引入，其余所有代码都先引入			


## 待做
1. 将投递任务序列化时用魔术方法__sleep减少传递信息（可能不行，因为都是类方法）