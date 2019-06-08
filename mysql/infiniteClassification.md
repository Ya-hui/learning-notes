# 什么是左右值无限极分类?
左右值无限级分类，也称为预排序树无限级分类，是一种有序的树状结构，位于这些树状结构中的每一个节点都有一个“左值”和“右值”，其规则是：每一个后代节点的左值总是大于父类左值，右值总是小于父类右值。
测试数据
```sql
CREATE TABLE `tree` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '分类名称',
  `lft` int(11) NOT NULL COMMENT '左值',
  `rgt` int(11) NOT NULL COMMENT '右值',
  `deep` int(11) NOT NULL COMMENT '深度',
  PRIMARY KEY (`id`),
  KEY `lft` (`lft`) USING BTREE,
  KEY `rgt` (`rgt`) USING BTREE,
  KEY `deep`(`deep`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
INSERT INTO `tree` VALUES ('1', '电子产品', '1', '16', '1'), ('2', '电脑', '2', '7', '2'), ('3', '笔记本', '3', '4', '3'), ('4', '台式机', '5', '6', '3'), ('5', '手机', '8', '15', '2'), ('6', '小米', '9', '12', '3'), ('7', 'vivo', '13', '14', '3'), ('8', 'mix系列', '10', '11', '4');
```
![发现规律没有？ 所有大于左值和小于右值的是不是就是它的所有子孙节点?](https://github.com/Ya-hui/learning-notes/raw/master/mysql/tree.png)

## 插入

插入子节点：它的左右值与它的父级有关，左值=父级的右值，右值=当前的左值+1，这时要更新的数据有：所有左值大于父级右值的，右值大于父级右值的节点都应该+2
```sql
-- 往笔记本下面插入联想笔记本(已知父级左值,右值,深度分别是:3, 4, 3)
UPDATE tree SET lft = lft + 2 where lft > 4
UPDATE tree SET rgt = rgt + 2 WHERE rgt > 4
INSERT INTO tree(name, lft, rgt, deep)VALUES('联想笔记本', 4, 5, 4)
```
![](https://github.com/Ya-hui/learning-notes/raw/master/mysql/tree1.png)
插入顶级节点: 它的左右值与该树中最大的右值有关：左值=1，右值=最大右值+2

## 查询

查出某个节点下面的所有子孙节点
```sql
SELECT * FROM tree WHERE lft > 8 AND rgt < 15;
```
查出某个节点下面的直接子节点
```sql
SELECT * FROM tree WHERE lft > 8 AND rgt < 15 AND deep = 2 + 1
```
查出所有叶子节点(一棵树当中没有子结点的结点，称为叶子结点)，只要满足rgt = lft + 1 的节点都可以判定为没有子节点
```sql
SELECT * FROM tree WHERE rgt = lft + 1;
```
计算所有子孙节点的数量
子类节点中每个节点占用两个值，而这些值都是不一样且连续的，那么就可以计算出子节点的数量=(右值-左值-1)/2。减少1的原因是排除该节点(你可以想像一个，一个单节点，左值是1，右值是2，没有子类节点，而这时它的右值-左值=1)
拿手机为例 手机的左右值分别是8，15。(15 - 8 - 1) / 2 = 3

## 删除

参数上面添加逻辑, 添加成功后左值大于父级右值的，右值大于父级右值的节点
删除所有子孙节点, 右值 - 左值 + 1 或 已知删除节点个数 * 2  理解这里的2是什么意思吗？每次添加节点时所有左值大于父级右值的，右值大于父级右值的节点都会+2 所以这里获取到删除的节点个数 * 2 = 之前修改过的总值
```sql
-- 删除笔记本这个节点，已知笔记本左右值分别是3, 6
DELETE FROM tree WHERE lft >= 3 AND rgt <= 6
UPDATE tree SET WHERE lft = lft - (6 - 3 + 1) AND lft > 6
UPDATE tree SET WHERE rgt = rgt - (6 - 3 + 1) AND rgt > 6
```
![](https://github.com/Ya-hui/learning-notes/raw/master/mysql/tree2.png)

总结一下：
    优点：解决了传统无限极分类的递归操作，而且查询条件是基于整数，效率很高
    缺点：节点的删除、修改、添加改动数据较多
    如果是以查询为主该方案较比较传统父子继承关系的无限分类更为适用，如果是分类经常改动不建议适用
