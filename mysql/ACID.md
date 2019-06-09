### 事务的基本要素

- 原子性
> 一个事务（transaction）中的所有操作，要么全部完成，要么全部不完成，不会结束在中间某个环节。事务在执行过程中发生错误，会被回滚（Rollback）到事务开始前的状态，就像这个事务从来没有执行过一样
- 一致性
> 在事务开始之前和事务结束以后，数据库的完整性没有被破坏
- 隔离性
> 数据库允许多个并发事务同时对其数据进行读写和修改的能力，隔离性可以防止多个事务并发执行时由于交叉执行而导致数据的不一致
- 持久性
> 事务完成后，事务对数据库的所有更新将被保存到数据库，不能回滚

### 事务隔离性

如果不考虑事务隔离性可能会造成以下问题
1. 脏读：一个事务内修改了数据，另一个事务读取并使用了这个数据
2. 幻读：一个事务内修改了涉及全表的数据，另一个事务往这个表里面插入了新的数据，比如:A将表中age字段全部改为18，B就在这个时候插入了一条19的记录，当A改结束后发现还有一条记录没有改过来，就好像发生了幻觉一样，这就叫幻读
3. 不可重复读：一个事务内连续读了两次数据，中间另一个事务修改了这个数据，导致第一个事务前后两次读的数据不一致

ps： 幻读和不可重复读很容易混淆，大致的区别在于幻读是由另一个事务插入或删除引起的，而不可重复读是由于另一个事务的更改造成的，解决幻读需要锁表，解决不可重复读只需要锁住满足条件的行

隔离级别：

| 隔离级别 | 脏读 | 不可重复读 | 幻读 |
| --- | --- | --- | --- |
| 未提交读（Read Uncommitted） | √ | √  | √ |
| 提交读（Read Committed） | × | √ | √ |
| 可重复读（Repeatable Read） | × | × | √ |
| 串行化（Serializable） | × | × | × |

ps: 隔离级别越高，越能保证数据的完整性和一致性，但是对并发性能的影响也越大，mysql默认隔离级别为可重复读

下面测试下各个隔离级别的情况：
```sql
CREATE TABLE `user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `balance` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
INSERT INTO `user` VALUES ('1', '小明', '150'), ('2', '小红', '300'), ('3', '小李', '400');
```
1. 未提交读

    ![未提交度](https://github.com/Ya-hui/learning-notes/raw/master/mysql/test.png)

    从上图可看出客户端B还未提交，客户端A已经读到了客户端B修改过的数据，一旦客户端B的事务回滚，那么客户端A查询到的数据其实就是脏数据，要想解决这个问题可以采用提交读的隔离级别

2. 提交读

    ![提交读](https://github.com/Ya-hui/learning-notes/raw/master/mysql/test1.png)

    从上图可看出提交读解决了脏读的问题但是又产生了不可重复读

3. 可重复读

    ![可重复读](https://github.com/Ya-hui/learning-notes/raw/master/mysql/test2.png)

    第10步接着执行了update user set balance = balance - 50 where id = 1; balance没有变成 100 - 50 = 50，很明显balance值用的是步骤7计算的所以是0
    数据的一致性没有被破坏，可重复读的隔离级别下使用了MVCC机制，select操作不会更新版本号，是快照读（历史版本）；insert、update和delete会更新版本号，是当前读（当前版本）
    在第12步开启了一个事务新增一条记录，在第16步修改表数据显示受影响行数4 出现幻读问题

4. 串行化

    ![串行化](https://github.com/Ya-hui/learning-notes/raw/master/mysql/test3.png)

    mysql中事务隔离级别为serializable时会锁表，因此不会出现幻读的情况，这种隔离级别并发性极低，开发中很少会用到
