hydra
==========

基于beanstalk封装的php分布式发布订阅消息系统,完全去除了对rigger与pylon的依赖
只依赖的组件有pheanstalk、monolog、composer、phpunit等

## 项目目录组织

### data
存放数据,其中hydra存放binlog文件,subscribe存放订阅文件

### sdk
sdk文件，供需要发事件的client使用
* MsgDTO        消息传输对象
* Producer      消息产生者

### src
* BStalk beanstalk  实现类
* Cmd           命令对象
* Commander     命令类
* ConfLoader    配置加载类
* Constant      常量定义
* Consumer      消息消费者
* Dispather     消息分发者
* Manager       订阅关系管理类
* Monologger    日志实现类

### test
测试用例
* MainTest 测试主流程是否正常
* PerformanceTest 测试性能
