# Laravel 后台权限管理设计文档

## 一、设计目标

- 实现灵活的多角色、多用户、多资源的权限控制
- 支持菜单权限、接口资源权限的细粒度分配
- 支持前端动态菜单渲染与页面缓存控制
- 支持资源分类，便于权限分配和管理
- 资源权限支持基于路由名称的通配符规则
- 记录后台用户所有操作，便于安全审计和追踪

---

## 二、核心功能

1. **菜单管理**  
   - 管理后台左侧菜单，支持显示/隐藏、排序、图标、名称、是否缓存等
2. **资源管理**  
   - 基于 Laravel 路由名称的动态权限控制，支持资源分类和通配符规则
3. **角色管理**  
   - 自定义角色，分配菜单和资源
4. **后台用户管理**  
   - 管理后台用户，分配角色
5. **后台用户操作日志**  
   - 记录后台用户所有操作，包括登录、登出、增删改查等

---

## 三、数据库表结构

### 1. 用户表（admins）

| 字段名      | 类型         | 说明         |
| ----------- | ------------ | ------------ |
| id          | bigint       | 主键         |
| username    | varchar(64)  | 用户名       |
| password    | varchar(128) | 密码         |
| icon        | varchar(500) | 头像         |
| email       | varchar(100) | 邮箱         |
| nick_name   | varchar(200) | 昵称         |
| note        | varchar(500) | 备注信息     |
| status      | int(1)       | 帐号启用状态 |
| created_at  | datetime     | 创建时间     |
| updated_at  | datetime     | 更新时间     |

### 2. 角色表（roles）

| 字段名      | 类型         | 说明     |
| ----------- | ------------ | -------- |
| id          | bigint       | 主键     |
| name        | varchar(100) | 名称     |
| description | varchar(500) | 描述     |
| admin_count | int(11)      | 用户数   |
| status      | int(1)       | 启用状态 |
| sort        | int(4)       | 排序     |
| created_at  | datetime     | 创建时间 |
| updated_at  | datetime     | 更新时间 |

### 3. 用户-角色关联表（admin_role）

| 字段名   | 类型   | 说明   |
| -------- | ------ | ------ |
| id       | bigint | 主键   |
| admin_id | bigint | 用户ID |
| role_id  | bigint | 角色ID |
| created_at  | datetime     | 创建时间 |
| updated_at  | datetime     | 更新时间 |

### 4. 菜单表（menus）

| 字段名      | 类型         | 说明         |
| ----------- | ------------ | ------------ |
| id          | bigint       | 主键         |
| parent_id   | bigint       | 父级菜单ID   |
| title       | varchar(100) | 菜单名称     |
| level       | int(4)       | 菜单级数     |
| sort        | int(4)       | 排序         |
| name        | varchar(100) | 前端路由名称 |
| icon        | varchar(200) | 图标         |
| hidden      | int(1)       | 是否隐藏     |
| keep_alive  | tinyint(1)   | 是否缓存页面（1=缓存，0=不缓存） |
| created_at  | datetime     | 创建时间     |
| updated_at  | datetime     | 更新时间     |

> **keep_alive**：前端根据此字段决定标签栏导航是否缓存页面。

### 5. 资源分类表（resource_categories）

| 字段名      | 类型         | 说明     |
| ----------- | ------------ | -------- |
| id          | bigint       | 主键     |
| name        | varchar(200) | 分类名称 |
| sort        | int(4)       | 排序     |
| created_at  | datetime     | 创建时间 |
| updated_at  | datetime     | 更新时间 |

### 6. 资源表（resources）

| 字段名      | 类型         | 说明                       |
| ----------- | ------------ | -------------------------- |
| id          | bigint       | 主键                       |
| category_id | bigint       | 资源分类ID                 |
| name        | varchar(200) | 资源名称（如“用户列表”）   |
| route_name  | varchar(200) | 路由名称（如 users.index，支持通配符） |
| description | varchar(500) | 描述                       |
| created_at  | datetime     | 创建时间                   |
| updated_at  | datetime     | 更新时间                   |

### 7. 角色-菜单关联表（role_menu）

| 字段名   | 类型   | 说明   |
| -------- | ------ | ------ |
| id       | bigint | 主键   |
| role_id  | bigint | 角色ID |
| menu_id  | bigint | 菜单ID |
| created_at  | datetime     | 创建时间 |
| updated_at  | datetime     | 更新时间 |

### 8. 角色-资源关联表（role_resource）

| 字段名      | 类型   | 说明     |
| ----------- | ------ | -------- |
| id          | bigint | 主键     |
| role_id     | bigint | 角色ID   |
| resource_id | bigint | 资源ID   |
| created_at  | datetime     | 创建时间 |
| updated_at  | datetime     | 更新时间 |

### 9. 后台用户操作日志表（admin_operation_logs）

| 字段名      | 类型         | 说明                   |
| ----------- | ------------ | ---------------------- |
| id          | bigint       | 主键                   |
| admin_id    | bigint       | 用户ID                 |
| operation   | varchar(100) | 操作类型（如 login、create_user 等） |
| detail      | text         | 操作详情（JSON 格式）  |
| ip          | varchar(64)  | 操作IP                 |
| address     | varchar(100) | 操作地址（可选）       |
| user_agent  | varchar(200) | 浏览器/客户端信息       |
| created_at  | datetime     | 创建时间               |
| updated_at  | datetime     | 更新时间               |

---

## 四、权限分配与控制逻辑

### 1. 菜单权限

- 角色分配菜单，用户通过角色获得菜单权限
- 前端根据菜单权限动态渲染左侧菜单
- 菜单的 keep_alive 字段控制页面是否缓存

### 2. 资源权限（基于路由名称，支持通配符）

#### 2.1 资源定义方式

- **精确匹配**：如 `users.index`，只匹配该路由。
- **通配符匹配**：如 `users.*`，可匹配所有以 `users.` 开头的路由名称。
- **多级通配**：如 `users.show.*`，匹配所有以 `users.show.` 开头的路由。
- **全局通配**：`*` 匹配所有路由（超级管理员权限）。

#### 2.2 通配符规则（Laravel 风格）

| 规则示例         | 匹配说明                                               |
|------------------|--------------------------------------------------------|
| `users.index`    | 只匹配 `users.index`                                   |
| `users.*`        | 匹配所有以 `users.` 开头的路由，如 `users.create`      |
| `users.show.*`   | 匹配所有以 `users.show.` 开头的路由，如 `users.show.detail` |
| `*`              | 匹配所有路由（超级管理员权限）                         |
| `orders.*`       | 匹配所有订单相关路由，如 `orders.index`、`orders.edit` |

- 通配符 `*` 只能出现在末尾或单独使用，遵循 Laravel 路由分组命名习惯。
- 不支持中间通配（如 `user.*.edit`），仅支持前缀通配。

#### 2.3 匹配逻辑

- 判断当前请求的路由名称是否与资源表中的 route_name 匹配。
- 优先精确匹配，其次前缀通配符匹配，最后全局通配符（`*`）匹配。

##### 伪代码示例

```php
// $userPermissions 为用户所有拥有的 route_name 权限（如 ['users.*', 'orders.index']）
function hasPermission($routeName, $userPermissions) {
    foreach ($userPermissions as $perm) {
        if ($perm === '*') return true;
        if (Str::endsWith($perm, '.*')) {
            $prefix = substr($perm, 0, -2);
            if (Str::startsWith($routeName, $prefix . '.')) return true;
        }
        if ($perm === $routeName) return true;
    }
    return false;
}
```

#### 2.4 资源表 route_name 字段填写规范

- 精确权限：填写完整路由名称，如 `users.index`
- 模块权限：填写前缀+`.*`，如 `users.*`
- 超级权限：填写 `*`

#### 2.5 管理后台操作建议

- 资源管理页面可支持批量添加通配符资源
- 分配权限时可选择精确或通配符资源

#### 2.6 业务场景举例

- **用户管理员**：分配 `users.*`，可访问所有用户相关接口
- **订单查看员**：分配 `orders.index`、`orders.show`，仅能查看订单列表和详情
- **超级管理员**：分配 `*`，拥有所有接口权限

---

### 3. 权限分配流程

1. 超级管理员分配菜单/资源给角色
2. 普通管理员分配角色给用户
3. 用户登录后系统根据角色获取所有权限

### 4. 动态菜单与资源控制

- 不同角色登录后，左侧菜单和可访问接口不同
- 未分配资源的接口无法访问
- 前端根据 keep_alive 字段决定页面缓存策略

---

## 五、后台用户操作日志

- 记录所有后台用户操作，包括登录、登出、增删改查、分配权限等
- operation 字段标识操作类型，detail 字段记录详细内容（建议 JSON 格式）
- 便于安全审计和问题追踪

---

## 六、ER 图关系

你说得对，之前的 ER 图用文本画法表达得不够准确，容易让人误解。  
下面我用更清晰的文本 ER 图来表达各表之间的关系，并补充简要说明：

---

## 六、ER 图关系（文本版）

```text
+---------+        +-------------+        +-----------+
|  admins |<------>| admin_role  |<------>|  roles    |
+---------+        +-------------+        +-----------+
                                         /           \
                                        /             \
                              +----------------+   +------------------+
                              |  role_menu     |   |  role_resource   |
                              +----------------+   +------------------+
                                      |                   |
                                  +-------+         +-------------+
                                  | menus |         |  resources  |
                                  +-------+         +-------------+
                                                         |
                                                +----------------------+
                                                | resource_categories  |
                                                +----------------------+
```

### 关系说明

- **admins 与 roles**：多对多，通过 admin_role 关联表实现。
- **roles 与 menus**：多对多，通过 role_menu 关联表实现。
- **roles 与 resources**：多对多，通过 role_resource 关联表实现。
- **resources 与 resource_categories**：多对一，每个资源属于一个资源分类。

---

## 七、前后端配合说明

- 后端返回用户菜单权限和资源权限列表
- 前端根据菜单权限渲染菜单，并根据 keep_alive 字段决定页面是否缓存
- 前端路由与菜单 name 字段保持一致
- 后端接口通过中间件校验资源权限（基于路由名称和通配符规则）

---

## 八、业务流程示例

1. **资源录入**：可通过 Artisan 命令自动同步所有路由名称到资源表，减少手动维护。
2. **角色分配资源**：在角色管理页面，为角色分配可访问的资源（路由名称，支持通配符）。
3. **用户分配角色**：为用户分配角色，用户获得角色对应的所有资源权限。
4. **权限校验**：用户访问接口时，后端根据用户拥有的资源（路由名称及通配符）权限进行校验。
5. **操作日志记录**：每次用户操作（如登录、增删改查等）自动记录到操作日志表。

---

## 九、参考资料

- [mall 权限模块数据库表解析](https://www.macrozheng.com/mall/database/mall_ums_01.html)
