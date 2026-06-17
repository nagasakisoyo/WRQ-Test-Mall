# WRQTestMall 靶场设计文档

> **版本**: v1.0  
> **日期**: 2026-06-17  
> **性质**: Intentionally Vulnerable Web Application — 仅供安全学习与授权测试  

---

## 目录

1. [项目概述与业务模型](#1-项目概述与业务模型)
2. [技术架构](#2-技术架构)
3. [数据库 ER 设计](#3-数据库-er-设计)
4. [功能模块与页面清单](#4-功能模块与页面清单)
5. [漏洞设计详单](#5-漏洞设计详单)
6. [接口设计清单](#6-接口设计清单)
7. [安全边界声明](#7-安全边界声明)
8. [部署说明](#8-部署说明)

---

## 1. 项目概述与业务模型

### 1.1 项目背景

WRQTestMall 是一个**故意包含安全漏洞的 PHP 电商靶场**，参考开源项目 uzy-ssm-mall 的业务形态设计。该靶场面向安全学习者，提供真实电商场景下的渗透测试练习环境，所有漏洞均为刻意设计，覆盖 OWASP Top 10 中的典型漏洞类型。

### 1.2 业务模型

仿照 uzymall 电商系统，WRQTestMall 包含完整的 B2C 电商业务流程：

```
┌─────────────────────────────────────────────────────────────────┐
│                        WRQTestMall 业务模型                       │
├──────────────────────┬──────────────────────────────────────────┤
│     前台（买家端）     │              后台（管理端）                  │
├──────────────────────┼──────────────────────────────────────────┤
│  ● 用户注册 / 登录    │  ● 管理员登录                              │
│  ● 忘记密码 / 重置    │  ● 仪表盘（订单统计/用户统计）               │
│  ● 首页商品展示       │  ● 商品管理（CRUD / 上下架）                 │
│  ● 商品分类浏览       │  ● 分类管理                                │
│  ● 商品详情 / 属性    │  ● 订单管理（发货/关闭）                     │
│  ● 商品搜索          │  ● 用户管理（查看/禁用）                      │
│  ● 购物车管理        │  ● 管理员资料（头像上传/昵称修改）              │
│  ● 订单创建 / 支付    │  ● 公告管理                                │
│  ● 订单列表 / 确认    │                                           │
│  ● 个人中心 / 资料    │                                           │
│  ● 商品评价          │                                           │
└──────────────────────┴──────────────────────────────────────────┘
```

### 1.3 核心业务流程

```
用户注册 → 登录 → 浏览商品 → 查看详情 → 加入购物车 → 提交订单 → 支付 → 确认收货 → 评价
                                                ↑
管理员登录 → 管理商品/分类 → 处理订单（发货）→ 查看用户 → 管理公告
```

---

## 2. 技术架构

### 2.1 技术栈

| 层次 | 技术选型 | 说明 |
|------|---------|------|
| 语言 | PHP 7.x (原生) | 不使用框架，便于直观展示漏洞原理 |
| 数据库 | MySQL 5.7+ | `localhost:3306`，账号 `root`，密码 `123456`，数据库 `wrqtestmall` |
| Web 服务 | Apache / Nginx + PHP-FPM | 或使用 `php -S` 内置服务器 |
| 前端 | Bootstrap 4 + jQuery 3.x | 响应式布局，AJAX 交互 |

### 2.2 目录结构

```
wrq-test-mall/
├── DESIGN.md                       # 本设计文档
├── index.php                       # 前台入口（路由分发）
├── admin.php                       # 后台入口（路由分发）
├── config/
│   └── database.php                # 数据库连接配置
├── includes/
│   ├── db.php                      # PDO/mysqli 连接封装
│   ├── auth.php                    # 前台会话鉴权函数
│   ├── admin_auth.php              # 后台会话鉴权函数
│   └── functions.php               # 通用工具函数
├── api/
│   ├── user_profile.php            # 用户个人信息接口（水平越权漏洞点）
│   ├── product_detail.php          # 商品详情展开接口（SQL注入漏洞点）
│   ├── order_create.php            # 订单创建接口（支付逻辑漏洞点）
│   ├── cart.php                    # 购物车操作接口
│   ├── search.php                  # 商品搜索接口
│   ├── review.php                  # 商品评价接口
│   ├── address.php                 # 地址联动接口
│   ├── forgot_password.php         # 忘记密码接口（任意密码重置漏洞点）
│   └── internal/
│       └── _sys_user_query.php     # 隐藏未授权接口（JS逆向发现）
├── admin/
│   ├── login.php                   # 管理员登录页
│   ├── dashboard.php               # 仪表盘
│   ├── products.php                # 商品管理
│   ├── categories.php              # 分类管理
│   ├── orders.php                  # 订单管理
│   ├── users.php                   # 用户管理
│   ├── announcements.php           # 公告管理（XSS展示触发点）
│   ├── profile.php                 # 管理员资料（存储型XSS + 文件上传漏洞点）
│   └── upload.php                  # 头像上传处理（文件上传漏洞点）
├── pages/
│   ├── home.php                    # 前台首页
│   ├── login.php                   # 用户登录
│   ├── register.php                # 用户注册
│   ├── forgot_password.php         # 忘记密码页面
│   ├── product_list.php            # 商品列表
│   ├── product_detail.php          # 商品详情（含"展开更多"按钮 → SQL注入触发点）
│   ├── cart.php                    # 购物车页面
│   ├── order_create.php            # 订单确认 / 提交
│   ├── order_pay.php               # 订单支付
│   ├── order_list.php              # 订单列表
│   ├── user_center.php             # 个人中心（水平越权触发点）
│   └── review.php                  # 商品评价
├── static/
│   ├── css/
│   │   ├── bootstrap.min.css
│   │   ├── style.css               # 全站自定义样式
│   │   ├── admin.css               # 后台样式
│   │   └── fore.css                # 前台样式
│   ├── js/
│   │   ├── jquery-3.6.0.min.js
│   │   ├── bootstrap.bundle.min.js
│   │   ├── app.js                  # 前台通用逻辑
│   │   ├── admin_app.js            # 后台通用逻辑
│   │   ├── product_detail.js       # 商品详情页逻辑（含 SQL注入触发的 AJAX）
│   │   ├── order.js                # 订单相关逻辑（含支付金额客户端提交）
│   │   ├── upload.js               # 上传逻辑（仅前端校验后缀）
│   │   ├── user_center.js          # 个人中心逻辑
│   │   └── utils.js                # 工具函数（隐藏接口地址藏于此处）
│   └── images/
│       └── ...                     # 静态图片资源
├── uploads/
│   ├── avatars/                    # 用户/管理员头像（可写目录，上传 webshell 落地点）
│   └── products/                   # 商品图片
└── sql/
    └── wrqtestmall.sql             # 数据库初始化脚本
```

### 2.3 请求路由机制

采用简单的 **query-string 路由**，不依赖 URL 重写：

```
前台: index.php?action=home              → pages/home.php
      index.php?action=product&id=5      → pages/product_detail.php
      index.php?action=login             → pages/login.php

后台: admin.php?action=dashboard         → admin/dashboard.php
      admin.php?action=products          → admin/products.php

API:  api/product_detail.php?id=1        → 直接访问 PHP 文件
      api/user_profile.php?uid=1         → 直接访问 PHP 文件
```

### 2.4 数据库连接方式

```php
// config/database.php
<?php
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_USER', 'root');
define('DB_PASS', '123456');
define('DB_NAME', 'wrqtestmall');

// includes/db.php — 提供两种连接方式
// 1. mysqli（用于漏洞点：支持 multi_query 堆叠查询，os-shell 依赖此特性）
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// 2. PDO（用于正常安全业务代码对比学习）
$pdo = new PDO("mysql:host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_NAME, DB_USER, DB_PASS);
```

> **关键设计**: SQL 注入漏洞点使用 `mysqli` 并开启 `multi_query`，为 sqlmap `--os-shell` 提供堆叠查询支持。

---

## 3. 数据库 ER 设计

### 3.1 ER 关系图

```
┌─────────────┐     ┌─────────────────┐     ┌──────────────┐
│   category   │────<│     product      │────<│ product_image│
│─────────────│     │─────────────────│     │──────────────│
│ id (PK)      │     │ id (PK)          │     │ id (PK)      │
│ name         │     │ name             │     │ type         │
│ image_src    │     │ title            │     │ src          │
└─────────────┘     │ price            │     │ product_id FK│
                    │ sale_price       │     └──────────────┘
                    │ category_id (FK) │
                    │ is_enabled       │     ┌──────────────┐
                    │ create_time      │────<│property_value│
                    └────────┬────────┘     │──────────────│
                             │              │ id (PK)      │
┌──────────────┐             │              │ value        │
│   property   │─────────────┘              │ property_id  │
│──────────────│                            │ product_id   │
│ id (PK)      │                            └──────────────┘
│ name         │
│ category_id  │
└──────────────┘

┌──────────────┐     ┌──────────────────┐     ┌───────────────────┐
│     user     │────<│  product_order   │────<│ product_order_item│
│──────────────│     │──────────────────│     │───────────────────│
│ id (PK)      │     │ id (PK)          │     │ id (PK)           │
│ username     │     │ order_code       │     │ number            │
│ nickname     │     │ address_detail   │     │ price             │
│ password     │     │ post_code        │     │ product_id (FK)   │
│ realname     │     │ receiver         │     │ order_id (FK)     │
│ gender       │     │ mobile           │     │ user_id (FK)      │
│ birthday     │     │ pay_date         │     │ user_message      │
│ address      │     │ delivery_date    │     └───────────────────┘
│ avatar_src   │     │ confirm_date     │
│ phone        │     │ status           │     ┌──────────────┐
│ email        │     │ user_id (FK)     │     │    review    │
│ security_q   │     │ total_price      │     │──────────────│
│ security_a   │     └──────────────────┘     │ id (PK)      │
└──────┬───────┘                              │ content      │
       │                                      │ create_time  │
       │         ┌──────────────┐             │ user_id (FK) │
       │         │    admin     │             │ product_id   │
       │         │──────────────│             │ order_item_id│
       │         │ id (PK)      │             └──────────────┘
       │         │ username     │
       │         │ nickname     │     ┌──────────────────┐
       │         │ password     │     │   announcement   │
       │         │ avatar_src   │     │──────────────────│
       │         │ role         │     │ id (PK)          │
       │         └──────────────┘     │ title            │
       │                              │ content          │
       │         ┌──────────────┐     │ admin_id (FK)    │
       └────────>│ upload_file  │     │ create_time      │
                 │──────────────│     │ is_visible       │
                 │ id (PK)      │     └──────────────────┘
                 │ original_name│
                 │ saved_name   │
                 │ file_path    │
                 │ file_size    │
                 │ mime_type    │
                 │ upload_time  │
                 │ user_type    │  -- 'user' 或 'admin'
                 │ user_id      │
                 └──────────────┘
```

### 3.2 数据表详细定义

#### 3.2.1 `user` — 前台用户表

| 字段 | 类型 | 约束 | 说明 |
|------|------|------|------|
| `id` | INT | PK, AUTO_INCREMENT | 用户ID |
| `username` | VARCHAR(50) | UNIQUE, NOT NULL | 登录用户名 |
| `nickname` | VARCHAR(50) | | 昵称 |
| `password` | VARCHAR(64) | NOT NULL | 密码（MD5 存储，故意弱哈希） |
| `realname` | VARCHAR(50) | | 真实姓名 |
| `gender` | TINYINT | DEFAULT 0 | 性别: 0-未知 1-男 2-女 |
| `birthday` | DATE | | 生日 |
| `address` | VARCHAR(200) | | 收货地址 |
| `phone` | VARCHAR(20) | | 手机号 |
| `email` | VARCHAR(100) | | 邮箱 |
| `avatar_src` | VARCHAR(255) | | 头像路径 |
| `security_question` | VARCHAR(200) | | 密保问题 |
| `security_answer` | VARCHAR(200) | | 密保答案 |
| `status` | TINYINT | DEFAULT 0 | 0-正常 1-禁用 |
| `create_time` | DATETIME | DEFAULT CURRENT_TIMESTAMP | 注册时间 |

#### 3.2.2 `admin` — 管理员表

| 字段 | 类型 | 约束 | 说明 |
|------|------|------|------|
| `id` | INT | PK, AUTO_INCREMENT | 管理员ID |
| `username` | VARCHAR(50) | UNIQUE, NOT NULL | 登录用户名 |
| `nickname` | VARCHAR(200) | | 昵称（**存储型 XSS 载体**，故意 VARCHAR(200) 容纳 payload） |
| `password` | VARCHAR(64) | NOT NULL | 密码（MD5，弱口令 admin/admin123） |
| `avatar_src` | VARCHAR(255) | | 头像路径 |
| `role` | VARCHAR(20) | DEFAULT 'admin' | 角色标识 |

#### 3.2.3 `category` — 商品分类表

| 字段 | 类型 | 约束 | 说明 |
|------|------|------|------|
| `id` | INT | PK, AUTO_INCREMENT | 分类ID |
| `name` | VARCHAR(50) | NOT NULL | 分类名称 |
| `image_src` | VARCHAR(255) | | 分类图片 |

#### 3.2.4 `product` — 商品表

| 字段 | 类型 | 约束 | 说明 |
|------|------|------|------|
| `id` | INT | PK, AUTO_INCREMENT | 商品ID |
| `name` | VARCHAR(100) | NOT NULL | 商品名称 |
| `title` | VARCHAR(200) | | 商品副标题 |
| `price` | DECIMAL(10,2) | NOT NULL | 原价 |
| `sale_price` | DECIMAL(10,2) | NOT NULL | 售价 |
| `category_id` | INT | FK → category.id | 所属分类 |
| `is_enabled` | TINYINT | DEFAULT 0 | 0-上架 1-下架 |
| `stock` | INT | DEFAULT 999 | 库存 |
| `description` | TEXT | | 商品详细描述 |
| `create_time` | DATETIME | DEFAULT CURRENT_TIMESTAMP | 创建时间 |

#### 3.2.5 `product_image` — 商品图片表

| 字段 | 类型 | 约束 | 说明 |
|------|------|------|------|
| `id` | INT | PK, AUTO_INCREMENT | 图片ID |
| `type` | TINYINT | NOT NULL | 0-展示图 1-详情图 |
| `src` | VARCHAR(255) | NOT NULL | 图片路径 |
| `product_id` | INT | FK → product.id | 所属商品 |

#### 3.2.6 `property` — 商品属性名表

| 字段 | 类型 | 约束 | 说明 |
|------|------|------|------|
| `id` | INT | PK, AUTO_INCREMENT | 属性ID |
| `name` | VARCHAR(50) | NOT NULL | 属性名称（如"颜色""尺码"） |
| `category_id` | INT | FK → category.id | 所属分类 |

#### 3.2.7 `property_value` — 商品属性值表

| 字段 | 类型 | 约束 | 说明 |
|------|------|------|------|
| `id` | INT | PK, AUTO_INCREMENT | 属性值ID |
| `value` | VARCHAR(100) | | 属性值（如"红色""XL"） |
| `property_id` | INT | FK → property.id | 所属属性 |
| `product_id` | INT | FK → product.id | 所属商品 |

#### 3.2.8 `product_order` — 订单表

| 字段 | 类型 | 约束 | 说明 |
|------|------|------|------|
| `id` | INT | PK, AUTO_INCREMENT | 订单ID |
| `order_code` | VARCHAR(30) | UNIQUE | 订单编号 |
| `address_detail` | VARCHAR(255) | | 详细收货地址 |
| `post_code` | VARCHAR(10) | | 邮编 |
| `receiver` | VARCHAR(50) | | 收货人 |
| `mobile` | VARCHAR(20) | | 手机号 |
| `pay_date` | DATETIME | | 支付时间 |
| `delivery_date` | DATETIME | | 发货时间 |
| `confirm_date` | DATETIME | | 确认时间 |
| `status` | TINYINT | DEFAULT 0 | 0-待付 1-待发 2-待收 3-完成 4-关闭 |
| `user_id` | INT | FK → user.id | 买家ID |
| `total_price` | DECIMAL(10,2) | | 订单总价（**支付漏洞关键字段**） |
| `create_time` | DATETIME | DEFAULT CURRENT_TIMESTAMP | 创建时间 |

#### 3.2.9 `product_order_item` — 订单项表

| 字段 | 类型 | 约束 | 说明 |
|------|------|------|------|
| `id` | INT | PK, AUTO_INCREMENT | 订单项ID |
| `number` | SMALLINT | DEFAULT 1 | 数量 |
| `price` | DECIMAL(10,2) | | 小计金额 |
| `product_id` | INT | FK → product.id | 商品ID |
| `order_id` | INT | FK → product_order.id, NULLABLE | 订单ID（NULL 表示在购物车中） |
| `user_id` | INT | FK → user.id | 用户ID |
| `user_message` | VARCHAR(255) | | 买家留言 |

#### 3.2.10 `review` — 商品评价表

| 字段 | 类型 | 约束 | 说明 |
|------|------|------|------|
| `id` | INT | PK, AUTO_INCREMENT | 评价ID |
| `content` | TEXT | | 评价内容 |
| `create_time` | DATETIME | DEFAULT CURRENT_TIMESTAMP | 评价时间 |
| `user_id` | INT | FK → user.id | 评价用户 |
| `product_id` | INT | FK → product.id | 评价商品 |
| `order_item_id` | INT | FK → product_order_item.id | 关联订单项 |

#### 3.2.11 `announcement` — 公告表

| 字段 | 类型 | 约束 | 说明 |
|------|------|------|------|
| `id` | INT | PK, AUTO_INCREMENT | 公告ID |
| `title` | VARCHAR(200) | NOT NULL | 公告标题 |
| `content` | TEXT | | 公告内容（展示管理员昵称，XSS触发点） |
| `admin_id` | INT | FK → admin.id | 发布管理员 |
| `create_time` | DATETIME | DEFAULT CURRENT_TIMESTAMP | 发布时间 |
| `is_visible` | TINYINT | DEFAULT 1 | 1-可见 0-隐藏 |

#### 3.2.12 `upload_file` — 文件上传记录表

| 字段 | 类型 | 约束 | 说明 |
|------|------|------|------|
| `id` | INT | PK, AUTO_INCREMENT | 记录ID |
| `original_name` | VARCHAR(255) | | 原始文件名 |
| `saved_name` | VARCHAR(255) | | 保存文件名 |
| `file_path` | VARCHAR(255) | | 服务器存储路径 |
| `file_size` | INT | | 文件大小(bytes) |
| `mime_type` | VARCHAR(100) | | MIME 类型 |
| `upload_time` | DATETIME | DEFAULT CURRENT_TIMESTAMP | 上传时间 |
| `user_type` | ENUM('user','admin') | | 上传者类型 |
| `user_id` | INT | | 上传者ID |

### 3.3 初始数据

| 表 | 初始数据说明 |
|----|------------|
| `admin` | `admin / admin123`（MD5: `0192023a7bbd73250516f069df18b500`），弱口令 |
| `user` | 5 个测试用户 (user1~user5 / 123456)，各有不同个人信息 |
| `category` | 5 个分类：手机数码、电脑办公、家用电器、服饰鞋包、食品生鲜 |
| `product` | 每个分类 3~5 个商品，含图片、属性、价格 |
| `product_order` | 若干历史订单，覆盖各状态 |
| `announcement` | 2~3 条公告，展示管理员昵称 |

---

## 4. 功能模块与页面清单

### 4.1 前台页面

| 编号 | 页面 | URL | 功能描述 |
|------|------|-----|---------|
| F01 | 首页 | `index.php?action=home` | 轮播图、分类导航、热销推荐、公告滚动 |
| F02 | 用户注册 | `index.php?action=register` | 用户名/密码/昵称/密保问题注册 |
| F03 | 用户登录 | `index.php?action=login` | 用户名 + 密码登录 |
| F04 | 忘记密码 | `index.php?action=forgot` | 输入用户名 → 回答密保问题 → 重置密码 |
| F05 | 商品列表 | `index.php?action=products&cid=1` | 按分类/搜索词展示商品列表，分页 |
| F06 | 商品详情 | `index.php?action=product&id=5` | 商品图片、属性、描述、评价、**"展开更多信息"按钮** |
| F07 | 购物车 | `index.php?action=cart` | 购物车列表、数量修改、删除、结算 |
| F08 | 订单确认 | `index.php?action=order_confirm` | 确认收货地址、商品清单、总价、**提交订单** |
| F09 | 订单支付 | `index.php?action=order_pay&code=xxx` | 显示订单金额、模拟支付按钮 |
| F10 | 订单列表 | `index.php?action=orders` | 按状态筛选订单，分页 |
| F11 | 个人中心 | `index.php?action=user_center` | 查看/编辑个人资料、头像 |
| F12 | 商品评价 | `index.php?action=review&oid=1` | 提交评价内容 |

### 4.2 后台页面

| 编号 | 页面 | URL | 功能描述 |
|------|------|-----|---------|
| A01 | 管理员登录 | `admin.php?action=login` | 用户名 + 密码登录 |
| A02 | 仪表盘 | `admin.php?action=dashboard` | 订单统计图表、商品/用户/订单总数 |
| A03 | 商品管理 | `admin.php?action=products` | 商品列表、添加/编辑/上下架 |
| A04 | 分类管理 | `admin.php?action=categories` | 分类 CRUD |
| A05 | 订单管理 | `admin.php?action=orders` | 订单列表、发货/关闭 |
| A06 | 用户管理 | `admin.php?action=users` | 用户列表、禁用/启用 |
| A07 | 公告管理 | `admin.php?action=announcements` | 发布/编辑公告（**XSS触发点**：展示管理员昵称） |
| A08 | 管理员资料 | `admin.php?action=profile` | **头像上传**、**昵称修改**（XSS + 文件上传漏洞点） |

### 4.3 功能交互流程

```
F06 商品详情页:
  ┌─────────────────────────────────────┐
  │  商品名称 / 价格 / 图片             │
  │  ──────────────────────────         │
  │  基本属性: 颜色: 黑色  尺码: XL     │
  │                                     │
  │  [▼ 展开更多信息]  ← 点击触发 AJAX  │
  │  ─ ─ ─ ─ ─ ─ ─ ─ ─                │
  │  (AJAX: api/product_detail.php?id=5)│ ← SQL 注入点
  │  展开后显示: 产地、材质、包装等      │
  │  ──────────────────────────         │
  │  商品评价列表                       │
  │  [加入购物车]  [立即购买]           │
  └─────────────────────────────────────┘
```

---

## 5. 漏洞设计详单

### 5.1 SQL 注入（"展开更多信息"按钮）

| 项目 | 内容 |
|------|------|
| **漏洞编号** | VULN-001 |
| **漏洞类型** | SQL Injection (Union / Stacked / Error-based) |
| **位置** | 前台商品详情页 → "展开更多信息"按钮 → `api/product_detail.php` |
| **触发条件** | 点击"展开更多信息"按钮，前端通过 AJAX 发送 `GET api/product_detail.php?id=<商品ID>` 请求 |
| **漏洞代码** | 见下方 |
| **利用路径** | 1. 在商品详情页点击"展开更多信息"<br>2. 拦截请求，修改 `id` 参数为注入 payload<br>3. 使用 sqlmap 自动化注入<br>4. 使用 `--os-shell` 获取系统 Shell |
| **预期效果** | 可获取数据库所有数据；通过 `--os-shell` 可执行任意系统命令 |
| **学习要点** | 参数拼接型 SQL 注入原理；sqlmap 自动化工具使用；`--os-shell` 的触发条件 |

**漏洞代码设计**:

```php
// api/product_detail.php
<?php
require_once '../includes/db.php';

$id = $_GET['id'];  // 未做任何过滤和参数化

// 故意使用 mysqli::multi_query 支持堆叠查询
$sql = "SELECT p.*, pv.value as prop_value, pr.name as prop_name 
        FROM product p 
        LEFT JOIN property_value pv ON p.id = pv.product_id 
        LEFT JOIN property pr ON pv.property_id = pr.id 
        WHERE p.id = " . $id;  // 直接拼接

$result = $mysqli->multi_query($sql);
// ... 返回 JSON
```

**sqlmap `--os-shell` 可行性分析**:

| 条件 | 满足方式 |
|------|---------|
| **堆叠查询** | 使用 `mysqli::multi_query()` 而非 `mysqli::query()`，允许一次执行多条 SQL |
| **FILE 权限** | MySQL 以 `root` 账户运行，拥有 `FILE` 权限 (可 `SELECT ... INTO OUTFILE`) |
| **`secure_file_priv`** | 靶场部署时需设置 `secure_file_priv = ""` (空，允许任意路径写入) |
| **Web 可写目录** | `uploads/` 目录设置 777 权限，Web 服务器可写 |
| **已知物理路径** | 通过报错信息或固定路径 `/var/www/html/wrq-test-mall/uploads/` 可得知 |
| **目录可解析 PHP** | `uploads/` 目录下 Apache/Nginx 未禁止 PHP 解析 |

> sqlmap 的 `--os-shell` 工作原理：利用堆叠查询执行 `SELECT '<?php system($_GET["cmd"]);?>' INTO OUTFILE '/var/www/html/wrq-test-mall/uploads/tmpshell.php'`，然后通过 HTTP 访问该 webshell 执行命令。

**sqlmap 示例命令**:
```bash
# 检测注入
sqlmap -u "http://target/wrq-test-mall/api/product_detail.php?id=1" --batch

# 获取 os-shell
sqlmap -u "http://target/wrq-test-mall/api/product_detail.php?id=1" \
       --os-shell \
       --web-root="/var/www/html/wrq-test-mall/uploads" \
       --batch
```

---

### 5.2 文件上传（管理员后台头像上传）

| 项目 | 内容 |
|------|------|
| **漏洞编号** | VULN-002 |
| **漏洞类型** | Unrestricted File Upload |
| **位置** | 后台管理员资料页 `admin.php?action=profile` → 头像上传 → `admin/upload.php` |
| **触发条件** | 管理员登录后，在个人资料页上传头像 |
| **利用路径** | 1. 以 admin 登录后台<br>2. 进入个人资料页，点击"上传头像"<br>3. 前端 JS 检测文件后缀为图片才允许提交（仅前端校验）<br>4. 使用 Burp 拦截请求，修改文件名后缀为 `.php` 并替换文件内容为 webshell<br>5. 上传成功后访问 `uploads/avatars/xxx.php` 执行代码 |
| **预期效果** | 成功上传 PHP webshell 并解析执行，获取服务器控制权 |
| **学习要点** | 前端校验 vs 服务端校验；MIME 类型伪造；Burp 拦截修改请求 |

**前端校验代码（仅此一道防线）**:

```javascript
// static/js/upload.js
function checkFile(file) {
    var allowExt = ['jpg', 'jpeg', 'png', 'gif'];
    var ext = file.name.split('.').pop().toLowerCase();
    if (allowExt.indexOf(ext) === -1) {
        alert('仅允许上传 jpg/png/gif 格式的图片！');
        return false;
    }
    // 同时检查 MIME（也仅在前端）
    var allowMime = ['image/jpeg', 'image/png', 'image/gif'];
    if (allowMime.indexOf(file.type) === -1) {
        alert('文件类型不正确！');
        return false;
    }
    return true;
}
```

**服务端代码（无任何校验）**:

```php
// admin/upload.php
<?php
session_start();
if (!isset($_SESSION['admin_id'])) { exit('Unauthorized'); }

$file = $_FILES['avatar'];
$uploadDir = '../uploads/avatars/';

// 直接使用原始文件名（无后缀检查、无内容检查、无重命名）
$fileName = time() . '_' . $file['name'];
$targetPath = $uploadDir . $fileName;

move_uploaded_file($file['tmp_name'], $targetPath);

echo json_encode([
    'success' => true,
    'fileUrl' => '/wrq-test-mall/uploads/avatars/' . $fileName
]);
```

---

### 5.3 水平越权（前台个人信息）

| 项目 | 内容 |
|------|------|
| **漏洞编号** | VULN-003 |
| **漏洞类型** | IDOR (Insecure Direct Object Reference) / Horizontal Privilege Escalation |
| **位置** | 前台个人中心 `index.php?action=user_center` → `api/user_profile.php` |
| **触发条件** | 前台用户登录后，访问个人中心页面时加载个人信息 |
| **利用路径** | 1. 以 user1 登录<br>2. 打开个人中心，观察网络请求 `api/user_profile.php?uid=1`<br>3. 修改 `uid` 参数为其他用户 ID（2, 3, 4...）<br>4. 遍历获取其他用户的完整个人信息 |
| **预期效果** | 可查看任意用户的用户名、真实姓名、手机号、邮箱、地址等敏感信息 |
| **学习要点** | 水平越权原理；IDOR 漏洞检测；正确的鉴权实现方式 |

**漏洞代码设计**:

```php
// api/user_profile.php
<?php
session_start();
require_once '../includes/db.php';

// 仅检查是否登录，不校验 uid 是否属于当前登录用户
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => '请先登录']);
    exit;
}

$uid = intval($_GET['uid']);  // 来自请求参数，非 session

$stmt = $pdo->prepare("SELECT id, username, nickname, realname, gender, birthday, 
                        address, phone, email, avatar_src 
                        FROM user WHERE id = ?");
$stmt->execute([$uid]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// 直接返回，不检查 $uid == $_SESSION['user_id']
echo json_encode(['success' => true, 'data' => $user]);
```

**前端调用**:

```javascript
// static/js/user_center.js
$(document).ready(function() {
    var uid = $('#user-id').val(); // 从隐藏字段取当前用户 ID
    $.get('/wrq-test-mall/api/user_profile.php', { uid: uid }, function(res) {
        // 渲染个人信息...
    });
});
```

---

### 5.4 支付逻辑漏洞（提交订单 0 元购）

| 项目 | 内容 |
|------|------|
| **漏洞编号** | VULN-004 |
| **漏洞类型** | Business Logic Flaw — Price Manipulation |
| **位置** | 前台提交订单 `api/order_create.php` |
| **触发条件** | 用户在订单确认页点击"提交订单"按钮 |
| **利用路径** | 1. 用户选择商品加入购物车，进入订单确认页<br>2. 点击"提交订单"，拦截 POST 请求<br>3. 修改请求体中的 `total_price` 字段为 `0` 或 `0.01`<br>4. 服务端以篡改后的金额创建订单 |
| **预期效果** | 成功创建总价为 0 元（或 0.01 元）的订单，实现"0 元购" |
| **学习要点** | 客户端传入金额的不可信性；服务端必须重新计算价格；业务逻辑漏洞的通用检测思路 |

**漏洞代码设计**:

```javascript
// static/js/order.js — 前端提交订单
function submitOrder() {
    var data = {
        address: $('#address').val(),
        receiver: $('#receiver').val(),
        mobile: $('#mobile').val(),
        items: getCartItems(),
        total_price: $('#total-price').text()  // 从页面 DOM 取总价并提交
    };
    $.post('/wrq-test-mall/api/order_create.php', data, function(res) {
        if (res.success) {
            window.location.href = res.pay_url;
        }
    });
}
```

```php
// api/order_create.php
<?php
session_start();
require_once '../includes/db.php';

$userId = $_SESSION['user_id'];
$address = $_POST['address_detail'];
$receiver = $_POST['receiver'];
$mobile = $_POST['mobile'];
$items = json_decode($_POST['items'], true);
$totalPrice = $_POST['total_price'];  // 直接信任客户端传入的金额！

$orderCode = date('YmdHis') . '0' . $userId;

// 用客户端提交的金额直接入库
$stmt = $pdo->prepare("INSERT INTO product_order 
    (order_code, address_detail, receiver, mobile, status, user_id, total_price, pay_date, create_time) 
    VALUES (?, ?, ?, ?, 0, ?, ?, NOW(), NOW())");
$stmt->execute([$orderCode, $address, $receiver, $mobile, $userId, $totalPrice]);

$orderId = $pdo->lastInsertId();

// 插入订单项（价格也信任客户端）
foreach ($items as $item) {
    $stmt = $pdo->prepare("INSERT INTO product_order_item 
        (number, price, product_id, order_id, user_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$item['number'], $item['price'], $item['product_id'], $orderId, $userId]);
}

echo json_encode([
    'success' => true,
    'pay_url' => '/wrq-test-mall/index.php?action=order_pay&code=' . $orderCode
]);
```

---

### 5.5 存储型 XSS（管理员昵称）

| 项目 | 内容 |
|------|------|
| **漏洞编号** | VULN-005 |
| **漏洞类型** | Stored Cross-Site Scripting (XSS) |
| **位置** | 后台管理员资料页 `admin.php?action=profile` → 昵称修改 |
| **触发条件** | 管理员修改自己的昵称为恶意脚本 |
| **利用路径** | 1. 以 admin 登录后台<br>2. 进入个人资料页，修改昵称为 `<script>alert('XSS')</script>`<br>3. 保存成功，脚本被存入数据库 `admin.nickname` 字段<br>4. 访问前台首页公告区（公告显示"发布者: {admin.nickname}"）触发弹窗<br>5. 访问后台管理员列表页同样触发 |
| **预期效果** | 在公告展示页面、管理员列表页面触发 JavaScript 执行（弹窗/Cookie窃取等） |
| **学习要点** | 存储型 XSS 的完整生命周期（输入→存储→输出）；输出编码的必要性 |

**漏洞代码设计**:

```php
// admin/profile.php — 保存昵称（不过滤）
<?php
$nickname = $_POST['nickname'];  // 不做任何 htmlspecialchars/strip_tags 处理
$stmt = $pdo->prepare("UPDATE admin SET nickname = ? WHERE id = ?");
$stmt->execute([$nickname, $_SESSION['admin_id']]);
```

```php
// pages/home.php — 前台首页公告区（未转义输出）
<?php foreach ($announcements as $ann): ?>
<div class="announcement-item">
    <h5><?= $ann['title'] ?></h5>
    <p><?= $ann['content'] ?></p>
    <small>发布者: <?= $ann['admin_nickname'] ?></small>  <!-- 直接输出，未 htmlspecialchars -->
</div>
<?php endforeach; ?>
```

```php
// admin/users.php 或 admin/dashboard.php — 后台管理员信息展示
<span class="admin-name"><?= $admin['nickname'] ?></span>  <!-- 同样未转义 -->
```

---

### 5.6 弱口令（管理员账号）

| 项目 | 内容 |
|------|------|
| **漏洞编号** | VULN-006 |
| **漏洞类型** | Weak Credentials |
| **位置** | 后台管理员登录 `admin.php?action=login` |
| **触发条件** | 直接尝试常见弱口令组合 |
| **利用路径** | 1. 访问后台登录页<br>2. 使用 `admin / admin123` 尝试登录<br>3. 成功进入后台管理系统 |
| **预期效果** | 成功登录后台，获取管理员权限 |
| **学习要点** | 弱口令的危害；暴力破解工具（Burp Intruder / Hydra）的使用；密码策略的重要性 |

**设计要点**:
- 管理员登录无验证码、无登录频率限制、无账号锁定机制
- 密码以 MD5 存储（无 salt），可被彩虹表快速破解
- 登录接口无 CSRF Token 保护，便于自动化爆破

---

### 5.7 隐藏未授权接口（JS 逆向）

| 项目 | 内容 |
|------|------|
| **漏洞编号** | VULN-007 |
| **漏洞类型** | Unauthenticated API + Information Disclosure (via JS Reverse) |
| **位置** | `static/js/utils.js` 中隐藏的接口地址 → `api/internal/_sys_user_query.php` |
| **触发条件** | 安全人员通过审计前端 JS 代码发现隐藏接口 |
| **利用路径** | 1. 查看前端页面源码，发现引入了 `static/js/utils.js`<br>2. 阅读 `utils.js`，在混淆/编码的字符串中找到隐藏接口路径<br>3. 通过参数 fuzz 确定传参格式（`uid` 参数）<br>4. 遍历 `uid` 参数，获取全站用户信息 |
| **预期效果** | 无需任何认证即可通过接口遍历获取所有用户的完整信息 |
| **学习要点** | 前端 JS 逆向分析；API 安全；未授权接口发现方法论 |

**隐藏方式设计 — `static/js/utils.js`**:

```javascript
// static/js/utils.js

// ... 大量正常工具函数 ...

function formatDate(ts) {
    var d = new Date(ts);
    return d.getFullYear() + '-' + (d.getMonth()+1) + '-' + d.getDate();
}

function debounce(fn, delay) {
    var timer = null;
    return function() {
        clearTimeout(timer);
        timer = setTimeout(fn, delay);
    };
}

// 接口地址通过 Base64 编码 + 字符串拼接隐藏
// 解码后为: /wrq-test-mall/api/internal/_sys_user_query.php
var _0xb4c2 = ['L3dycS10ZXN0LW1hbGwv', 'YXBpL2ludGVybmFsLw==', 'X3N5c191c2VyX3F1ZXJ5LnBocA=='];
var _sysPath = (function() {
    return atob(_0xb4c2[0]) + atob(_0xb4c2[1]) + atob(_0xb4c2[2]);
})();

// 以下函数在正常业务流程中不被任何按钮/链接调用
function _initSysCheck() {
    // debug helper — not used in production
    if (typeof window.__debug !== 'undefined') {
        fetch(_sysPath + '?uid=1')
            .then(function(r) { return r.json(); })
            .then(function(d) { console.log(d); });
    }
}

// ... 更多正常工具函数 ...
```

**隐藏接口服务端代码**:

```php
// api/internal/_sys_user_query.php
<?php
// 内部调试接口 — 故意无任何鉴权
require_once '../../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$uid = isset($_GET['uid']) ? intval($_GET['uid']) : 0;

if ($uid <= 0) {
    echo json_encode(['error' => 'invalid parameter']);
    exit;
}

$stmt = $pdo->prepare("SELECT id, username, nickname, realname, gender, birthday, 
                        phone, email, address, avatar_src, create_time 
                        FROM user WHERE id = ?");
$stmt->execute([$uid]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo json_encode(['code' => 0, 'data' => $user]);
} else {
    echo json_encode(['code' => 404, 'msg' => 'user not found']);
}
```

**发现与利用流程**:

```
Step 1: 查看页面源码，找到 <script src="static/js/utils.js">
Step 2: 审计 utils.js，注意到 _0xb4c2 数组和 atob() 调用
Step 3: 控制台执行 atob('L3dycS10ZXN0LW1hbGwv') 等解码
Step 4: 拼接得到 /wrq-test-mall/api/internal/_sys_user_query.php
Step 5: 尝试 ?uid=1, ?uid=2 ... 遍历获取用户信息
Step 6: 编写脚本批量获取:
        for i in range(1, 100):
            requests.get(f"http://target/...?uid={i}")
```

---

### 5.8 任意用户密码重置（修改响应包绕过密保验证）

| 项目 | 内容 |
|------|------|
| **漏洞编号** | VULN-008 |
| **漏洞类型** | Authentication Bypass via Response Manipulation |
| **位置** | 前台忘记密码 `index.php?action=forgot` → `api/forgot_password.php` |
| **触发条件** | 用户在忘记密码页面输入用户名后回答密保问题 |
| **利用路径** | 1. 访问忘记密码页面，输入目标用户名<br>2. 服务端返回该用户的密保问题（如"你的出生城市？"）<br>3. 随意输入密保答案，提交验证请求<br>4. 拦截响应包，将 `{"success": false, "msg": "答案错误"}` 修改为 `{"success": true}`<br>5. 前端根据 `success: true` 跳转到密码重置页面<br>6. 在重置页面设置新密码，完成密码重置 |
| **预期效果** | 绕过密保验证，重置任意用户的登录密码 |
| **学习要点** | 客户端逻辑判断的不可信性；响应包篡改；认证流程的安全设计 |

**漏洞代码设计**:

忘记密码分为三个步骤，每步通过 AJAX 与后端交互：

```
Step 1: 输入用户名 → 后端返回密保问题
Step 2: 回答密保问题 → 后端验证答案（漏洞点：前端依赖响应判断是否放行）
Step 3: 设置新密码 → 后端直接重置（仅检查 session 中的 username，不二次验证答案）
```

```php
// api/forgot_password.php
<?php
session_start();
require_once '../includes/db.php';

$step = $_POST['step'] ?? '';

if ($step === 'check_user') {
    // Step 1: 检查用户是否存在，返回密保问题
    $username = $_POST['username'];
    $stmt = $pdo->prepare("SELECT id, security_question FROM user WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $_SESSION['reset_username'] = $username;
        echo json_encode(['success' => true, 'question' => $user['security_question']]);
    } else {
        echo json_encode(['success' => false, 'msg' => '用户不存在']);
    }
    exit;
}

if ($step === 'verify_answer') {
    // Step 2: 验证密保答案
    $username = $_SESSION['reset_username'] ?? '';
    $answer = $_POST['answer'];
    $stmt = $pdo->prepare("SELECT security_answer FROM user WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && $user['security_answer'] === $answer) {
        // 正确答案 —— 但关键问题：这里没有在 session 中标记"验证通过"
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'msg' => '密保答案错误']);
    }
    // 注意：无论答案对错，session 中都保留了 reset_username
    // 没有设置 $_SESSION['answer_verified'] = true 这样的标记
    exit;
}

if ($step === 'reset_password') {
    // Step 3: 重置密码 —— 仅检查 session 中有 reset_username，不验证 Step 2 是否通过
    $username = $_SESSION['reset_username'] ?? '';
    if (empty($username)) {
        echo json_encode(['success' => false, 'msg' => '会话已过期']);
        exit;
    }
    
    $newPassword = md5($_POST['new_password']);
    $stmt = $pdo->prepare("UPDATE user SET password = ? WHERE username = ?");
    $stmt->execute([$newPassword, $username]);
    
    unset($_SESSION['reset_username']);
    echo json_encode(['success' => true, 'msg' => '密码重置成功']);
    exit;
}
```

```javascript
// 前端忘记密码逻辑 — pages/forgot_password.php 内嵌
$('#verify-answer-btn').click(function() {
    $.post('/wrq-test-mall/api/forgot_password.php', {
        step: 'verify_answer',
        answer: $('#security-answer').val()
    }, function(res) {
        if (res.success) {       // ← 攻击者拦截响应改为 true 即可绕过
            $('#step2').hide();
            $('#step3').show();   // 显示重置密码表单
        } else {
            alert(res.msg);
        }
    });
});

$('#reset-password-btn').click(function() {
    $.post('/wrq-test-mall/api/forgot_password.php', {
        step: 'reset_password',
        new_password: $('#new-password').val()
    }, function(res) {
        if (res.success) {
            alert('密码重置成功！');
            window.location.href = '/wrq-test-mall/index.php?action=login';
        }
    });
});
```

**漏洞本质**: Step 3（重置密码）仅依赖 `$_SESSION['reset_username']` 的存在性，而该值在 Step 1 成功后就已写入 session，Step 2 的密保验证结果不影响 Step 3 的执行。前端通过响应中的 `success` 字段控制界面跳转，攻击者修改响应即可直接进入 Step 3。

---

### 5.9 漏洞总览矩阵

| 编号 | 漏洞类型 | 位置 | OWASP 分类 | 难度 |
|------|---------|------|-----------|------|
| VULN-001 | SQL 注入 (含 os-shell) | 商品详情 → 展开更多 | A03:2021 Injection | ★★☆ |
| VULN-002 | 文件上传 | 后台 → 头像上传 | A04:2021 Insecure Design | ★★☆ |
| VULN-003 | 水平越权 (IDOR) | 前台 → 个人中心 | A01:2021 Broken Access Control | ★☆☆ |
| VULN-004 | 支付逻辑 (0元购) | 前台 → 提交订单 | A04:2021 Insecure Design | ★★☆ |
| VULN-005 | 存储型 XSS | 后台 → 管理员昵称 | A03:2021 Injection | ★★☆ |
| VULN-006 | 弱口令 | 后台 → 管理员登录 | A07:2021 Auth Failures | ★☆☆ |
| VULN-007 | 未授权接口 (JS逆向) | JS → 隐藏API | A01:2021 Broken Access Control | ★★★ |
| VULN-008 | 任意密码重置 | 忘记密码 → 响应篡改 | A07:2021 Auth Failures | ★★☆ |

---

## 6. 接口设计清单

### 6.1 正常业务接口

#### 6.1.1 用户认证

| 方法 | 路径 | 参数 | 说明 | 需鉴权 |
|------|------|------|------|--------|
| POST | `api/user_login.php` | `username`, `password` | 用户登录 | 否 |
| POST | `api/user_register.php` | `username`, `password`, `nickname`, `security_question`, `security_answer` | 用户注册 | 否 |
| GET | `api/user_logout.php` | — | 用户登出 | 是(session) |
| POST | `api/forgot_password.php` | `step`, `username`/`answer`/`new_password` | 忘记密码（三步式） | 否 |

#### 6.1.2 商品相关

| 方法 | 路径 | 参数 | 说明 | 需鉴权 |
|------|------|------|------|--------|
| GET | `api/product_list.php` | `cid` (分类ID), `page`, `limit`, `keyword` | 商品列表 | 否 |
| GET | `api/product_detail.php` | `id` (商品ID) | **商品详情展开** (**SQL注入点**) | 否 |
| GET | `api/search.php` | `q` (搜索词), `page`, `limit` | 商品搜索 | 否 |
| GET | `api/category_list.php` | — | 分类列表 | 否 |

#### 6.1.3 购物车

| 方法 | 路径 | 参数 | 说明 | 需鉴权 |
|------|------|------|------|--------|
| GET | `api/cart.php?action=list` | — | 获取购物车列表 | 是 |
| POST | `api/cart.php?action=add` | `product_id`, `number` | 添加到购物车 | 是 |
| POST | `api/cart.php?action=update` | `item_id`, `number` | 更新购物车数量 | 是 |
| POST | `api/cart.php?action=delete` | `item_id` | 删除购物车项 | 是 |

#### 6.1.4 订单

| 方法 | 路径 | 参数 | 说明 | 需鉴权 |
|------|------|------|------|--------|
| POST | `api/order_create.php` | `address_detail`, `receiver`, `mobile`, `items`(JSON), **`total_price`** | **创建订单** (**支付漏洞点**) | 是 |
| GET | `api/order_list.php` | `page`, `limit`, `status` | 订单列表 | 是 |
| POST | `api/order_pay.php` | `order_code` | 确认支付 | 是 |
| POST | `api/order_confirm.php` | `order_code` | 确认收货 | 是 |

#### 6.1.5 用户中心

| 方法 | 路径 | 参数 | 说明 | 需鉴权 |
|------|------|------|------|--------|
| GET | `api/user_profile.php` | **`uid`** | **获取用户信息** (**水平越权点**) | 是(弱) |
| POST | `api/user_update.php` | `nickname`, `realname`, `gender`, `birthday`, `address`, `avatar_src` | 更新个人信息 | 是 |

#### 6.1.6 评价

| 方法 | 路径 | 参数 | 说明 | 需鉴权 |
|------|------|------|------|--------|
| GET | `api/review.php?action=list` | `product_id`, `page`, `limit` | 获取商品评价 | 否 |
| POST | `api/review.php?action=add` | `product_id`, `order_item_id`, `content` | 提交评价 | 是 |

#### 6.1.7 地址联动

| 方法 | 路径 | 参数 | 说明 | 需鉴权 |
|------|------|------|------|--------|
| GET | `api/address.php` | `parent_id` | 获取下级地址列表 | 否 |

### 6.2 后台管理接口

| 方法 | 路径 | 参数 | 说明 | 需鉴权 |
|------|------|------|------|--------|
| POST | `admin/login.php` | `username`, `password` | 管理员登录 | 否 |
| POST | `admin/upload.php` | `avatar` (FILE) | **头像上传** (**文件上传漏洞点**) | 是(admin) |
| POST | `admin/profile.php?action=update` | **`nickname`**, `avatar_src`, `password` | **更新管理员资料** (**XSS点**) | 是(admin) |
| GET | `admin/api/products.php` | `page`, `limit`, `cid` | 商品列表 | 是(admin) |
| POST | `admin/api/products.php` | 商品字段 | 添加/编辑商品 | 是(admin) |
| POST | `admin/api/orders.php?action=ship` | `order_id` | 订单发货 | 是(admin) |
| GET | `admin/api/users.php` | `page`, `limit` | 用户列表 | 是(admin) |
| POST | `admin/api/announcements.php` | `title`, `content` | 发布公告 | 是(admin) |

### 6.3 隐藏接口

| 方法 | 路径 | 参数 | 说明 | 需鉴权 |
|------|------|------|------|--------|
| GET | `api/internal/_sys_user_query.php` | `uid` (用户ID, INT) | **隐藏用户查询接口** (**未授权接口**) | **否** |

**隐藏接口详细说明**:

- **命名规则**: 以下划线开头 `_sys_user_query.php`，放在 `api/internal/` 子目录中
- **发现入口**: 仅在 `static/js/utils.js` 中通过 Base64 编码的字符串暴露
- **暴露方式**: 不绑定到任何 DOM 事件、按钮或链接；仅在 `window.__debug` 存在时由一个从不被调用的函数引用
- **参数格式**: `GET ?uid=<INT>`，需通过 fuzz 发现参数名
- **响应格式**:

```json
// 成功
{"code": 0, "data": {"id": 1, "username": "user1", "nickname": "小王", "realname": "王明", "gender": 1, "birthday": "1995-03-15", "phone": "13800001111", "email": "user1@test.com", "address": "北京市朝阳区xxx", "avatar_src": "/uploads/avatars/default.jpg", "create_time": "2026-01-01 00:00:00"}}

// 未找到
{"code": 404, "msg": "user not found"}

// 参数错误
{"error": "invalid parameter"}
```

---

## 7. 安全边界声明

```
╔══════════════════════════════════════════════════════════════════╗
║                        ⚠  安全边界声明  ⚠                        ║
╠══════════════════════════════════════════════════════════════════╣
║                                                                  ║
║  1. 本靶场（WRQTestMall）是一个故意包含安全漏洞的学习环境，      ║
║     所有漏洞均为刻意设计，仅供安全研究与学习使用。                ║
║                                                                  ║
║  2. 严禁将本靶场部署到互联网可访问的生产环境。                    ║
║     仅限在以下环境中运行：                                        ║
║     - 本地开发机 (localhost)                                      ║
║     - 隔离的虚拟机 / Docker 容器                                  ║
║     - 无公网暴露的内网 Lab 环境                                   ║
║                                                                  ║
║  3. 本靶场中的漏洞代码不代表安全编码规范，不可作为                ║
║     正式开发的参考。学习者应同时了解对应的安全修复方法。          ║
║                                                                  ║
║  4. 使用者需自行承担因不当部署或滥用造成的一切后果。              ║
║     项目作者不对任何直接或间接损失负责。                          ║
║                                                                  ║
║  5. 本靶场中的数据库凭据（root/123456）、管理员账号               ║
║     （admin/admin123）等均为靶场默认配置，                        ║
║     不可用于任何真实系统。                                        ║
║                                                                  ║
╚══════════════════════════════════════════════════════════════════╝
```

---

## 8. 部署说明

### 8.1 环境要求

| 组件 | 版本要求 | 说明 |
|------|---------|------|
| PHP | 7.0 ~ 7.4 | 需启用 `mysqli`、`pdo_mysql` 扩展 |
| MySQL | 5.7+ | 需设置 `secure_file_priv = ""` |
| Apache / Nginx | 任意 | 需配置 `uploads/` 目录允许 PHP 解析 |
| 操作系统 | Windows / Linux | 均可，路径配置略有不同 |

### 8.2 MySQL 配置要求

在 MySQL 配置文件 (`my.ini` / `my.cnf`) 中设置以下参数以满足 SQL 注入 `--os-shell` 条件：

```ini
[mysqld]
secure_file_priv = ""
```

> 设置为空字符串表示允许 MySQL 向任意路径写文件，这是 sqlmap `--os-shell` 利用 `INTO OUTFILE` 的前提条件。

### 8.3 部署步骤

#### Step 1: 准备 Web 根目录

```bash
# Linux
sudo cp -r wrq-test-mall /var/www/html/
sudo chown -R www-data:www-data /var/www/html/wrq-test-mall
sudo chmod -R 755 /var/www/html/wrq-test-mall
sudo chmod -R 777 /var/www/html/wrq-test-mall/uploads  # 可写目录

# Windows (XAMPP)
# 复制 wrq-test-mall 到 C:\xampp\htdocs\
```

#### Step 2: 初始化数据库

```bash
# 创建数据库并导入初始数据
mysql -u root -p123456 -e "CREATE DATABASE IF NOT EXISTS wrqtestmall DEFAULT CHARACTER SET utf8mb4;"
mysql -u root -p123456 wrqtestmall < wrq-test-mall/sql/wrqtestmall.sql
```

#### Step 3: 配置数据库连接

编辑 `config/database.php`，确认以下配置与本地环境一致：

```php
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_USER', 'root');
define('DB_PASS', '123456');
define('DB_NAME', 'wrqtestmall');
```

#### Step 4: Apache/Nginx 配置

**Apache** (`httpd.conf` 或 `.htaccess`):
```apache
# 确保 uploads 目录允许 PHP 解析（故意的安全缺陷）
<Directory "/var/www/html/wrq-test-mall/uploads">
    Options -Indexes
    AllowOverride None
    # 注意：故意不添加 php_flag engine off
</Directory>
```

**Nginx** (`nginx.conf`):
```nginx
server {
    listen 80;
    server_name localhost;
    root /var/www/html/wrq-test-mall;
    index index.php;

    location ~ \.php$ {
        # 所有目录（含 uploads）都允许 PHP 解析 — 故意的
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

#### Step 5: 使用 PHP 内置服务器（快速启动）

```bash
cd wrq-test-mall
php -S 0.0.0.0:8080
```

访问 `http://localhost:8080` 即可开始使用。

### 8.4 默认账号

| 角色 | 用户名 | 密码 | 入口 |
|------|--------|------|------|
| 管理员 | admin | admin123 | `admin.php?action=login` |
| 用户1 | user1 | 123456 | `index.php?action=login` |
| 用户2 | user2 | 123456 | `index.php?action=login` |
| 用户3 | user3 | 123456 | `index.php?action=login` |
| 用户4 | user4 | 123456 | `index.php?action=login` |
| 用户5 | user5 | 123456 | `index.php?action=login` |

### 8.5 漏洞验证清单

部署完成后，按以下清单逐项验证漏洞可用性：

| # | 漏洞 | 验证方法 | 预期结果 |
|---|------|---------|---------|
| 1 | SQL 注入 | `sqlmap -u "http://localhost:8080/api/product_detail.php?id=1" --batch --dbs` | 列出所有数据库 |
| 2 | 文件上传 | Burp 拦截上传请求，改后缀为 .php，内容为 `<?php phpinfo();?>` | 访问上传路径显示 phpinfo |
| 3 | 水平越权 | 登录 user1，请求 `api/user_profile.php?uid=2` | 返回 user2 的完整信息 |
| 4 | 支付漏洞 | Burp 拦截订单创建请求，改 `total_price=0` | 订单以 0 元创建成功 |
| 5 | 存储型 XSS | 管理员昵称改为 `<script>alert(1)</script>`，访问首页公告 | 弹窗 |
| 6 | 弱口令 | 使用 `admin/admin123` 登录后台 | 登录成功 |
| 7 | 隐藏接口 | 审计 `utils.js`，解码 Base64，访问 `_sys_user_query.php?uid=1` | 返回用户信息 |
| 8 | 密码重置 | Burp 拦截 `verify_answer` 响应，改 `success` 为 `true`，提交新密码 | 密码重置成功 |

---

> **文档结束** — WRQTestMall v1.0 设计文档
