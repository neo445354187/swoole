<?php
return [
	'components' => [ //放在组件的$_components属性中，获取某个组件时，自动调用__get()，再通过响应方法从定义中获取配置创建实例
	    
	    //权限
	    'auth'       => [
	        'class' => 'swoole\rbac\Auth',
	    ],
	],
	'property' => [

	],
];