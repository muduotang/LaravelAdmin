# LaravelAdmin API 文档

## 目录

- [通用说明](#通用说明)
- [认证模块](#认证模块)
- [管理员模块](#管理员模块)
- [角色模块](#角色模块)
- [菜单模块](#菜单模块)
- [资源分类模块](#资源分类模块)
- [资源模块](#资源模块)

## 通用说明

### 基础URL

所有API的基础URL为：`/api`

### 认证方式

除了登录接口外，所有接口都需要在请求头中携带JWT令牌：

```
Authorization: Bearer {token}
```

### 响应格式

所有API的响应格式统一为：

```json
{
    "status": "success", // 或 "error"
    "code": 200, // HTTP状态码
    "message": "操作成功", // 提示信息
    "data": {} // 响应数据
}
```

分页响应格式：

```json
{
    "status": "success",
    "code": 200,
    "message": null,
    "data": [], // 当前页数据
    "meta": {
        "total": 100, // 总记录数
        "per_page": 15, // 每页记录数
        "current_page": 1, // 当前页码
        "last_page": 7 // 最后一页页码
    }
}
```

### 错误码

| 状态码 | 说明 |
| ----- | ---- |
| 200 | 成功 |
| 400 | 请求错误 |
| 401 | 未授权 |
| 403 | 禁止访问 |
| 404 | 资源不存在 |
| 422 | 验证失败 |
| 500 | 服务器错误 |

## 认证模块

### 登录

- **接口**：`POST /api/auth/login`
- **描述**：管理员登录
- **请求参数**：

| 参数名 | 类型 | 必填 | 描述 |
| ----- | ---- | ---- | ---- |
| username | string | 是 | 用户名 |
| password | string | 是 | 密码 |

- **响应示例**：

```json
{
    "status": "success",
    "code": 200,
    "message": "登录成功",
    "data": {
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "token_type": "bearer",
        "expires_in": 3600
    }
}
```

### 登出

- **接口**：`POST /api/auth/logout`
- **描述**：管理员登出
- **请求参数**：无
- **响应示例**：

```json
{
    "status": "success",
    "code": 200,
    "message": "登出成功",
    "data": null
}
```

### 获取当前管理员信息

- **接口**：`GET /api/auth/me`
- **描述**：获取当前登录管理员信息
- **请求参数**：无
- **响应示例**：

```json
{
    "status": "success",
    "code": 200,
    "message": null,
    "data": {
        "id": 1,
        "username": "admin",
        "nick_name": "超级管理员",
        "email": "admin@example.com",
        "icon": "http://example.com/avatar.jpg",
        "status": 1,
        "created_at": "2023-01-01T00:00:00.000000Z",
        "updated_at": "2023-01-01T00:00:00.000000Z"
    }
}
```

### 刷新令牌

- **接口**：`POST /api/auth/refresh`
- **描述**：刷新JWT令牌
- **请求参数**：无
- **响应示例**：

```json
{
    "status": "success",
    "code": 200,
    "message": null,
    "data": {
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "token_type": "bearer",
        "expires_in": 3600
    }
}
```

### 更新个人资料

- **接口**：`PUT /api/auth/profile`
- **描述**：更新当前登录管理员的个人资料
- **请求参数**：

| 参数名 | 类型 | 必填 | 描述 |
| ----- | ---- | ---- | ---- |
| email | string | 否 | 邮箱 |
| nick_name | string | 否 | 昵称 |
| icon | string | 否 | 头像URL |
| old_password | string | 否* | 原密码（修改密码时必填） |
| new_password | string | 否* | 新密码（修改密码时必填） |
| new_password_confirmation | string | 否* | 确认新密码（修改密码时必填） |

- **响应示例**：

```json
{
    "status": "success",
    "code": 200,
    "message": "个人资料更新成功",
    "data": {
        "id": 1,
        "username": "admin",
        "nick_name": "新昵称",
        "email": "newemail@example.com",
        "icon": "http://example.com/new-avatar.jpg",
        "status": 1,
        "created_at": "2023-01-01T00:00:00.000000Z",
        "updated_at": "2023-01-02T00:00:00.000000Z"
    }
}
```

## 管理员模块

### 获取管理员列表

- **接口**：`GET /api/admins`
- **描述**：获取管理员列表，支持分页
- **请求参数**：

| 参数名 | 类型 | 必填 | 描述 |
| ----- | ---- | ---- | ---- |
| page | integer | 否 | 页码，默认1 |
| per_page | integer | 否 | 每页记录数，默认15 |
| keyword | string | 否 | 搜索关键词 |

- **响应示例**：

```json
{
    "status": "success",
    "code": 200,
    "message": null,
    "data": [
        {
            "id": 1,
            "username": "admin",
            "nick_name": "超级管理员",
            "email": "admin@example.com",
            "icon": "http://example.com/avatar.jpg",
            "status": 1,
            "created_at": "2023-01-01T00:00:00.000000Z",
            "updated_at": "2023-01-01T00:00:00.000000Z"
        }
    ],
    "meta": {
        "total": 1,
        "per_page": 15,
        "current_page": 1,
        "last_page": 1
    }
}
```

### 创建管理员

- **接口**：`POST /api/admins`
- **描述**：创建新管理员
- **请求参数**：

| 参数名 | 类型 | 必填 | 描述 |
| ----- | ---- | ---- | ---- |
| username | string | 是 | 用户名，最大50字符，只能包含字母、数字和下划线 |
| email | string | 是 | 邮箱，最大100字符 |
| nick_name | string | 是 | 昵称，最大50字符 |
| password | string | 是 | 密码，最小6字符 |
| password_confirmation | string | 是 | 确认密码 |
| note | string | 否 | 备注，最大500字符 |
| icon | string | 否 | 头像URL，最大500字符 |
| status | integer | 是 | 状态，0-禁用，1-启用 |
| role_ids | array | 否 | 角色ID数组 |

- **响应示例**：

```json
{
    "status": "success",
    "code": 200,
    "message": "Created successfully",
    "data": {
        "id": 2,
        "username": "newadmin",
        "nick_name": "新管理员",
        "email": "newadmin@example.com",
        "icon": "http://example.com/avatar.jpg",
        "status": 1,
        "created_at": "2023-01-02T00:00:00.000000Z",
        "updated_at": "2023-01-02T00:00:00.000000Z"
    }
}
```

### 获取管理员详情

- **接口**：`GET /api/admins/{id}`
- **描述**：获取指定ID的管理员详情
- **请求参数**：无
- **响应示例**：

```json
{
    "status": "success",
    "code": 200,
    "message": null,
    "data": {
        "id": 1,
        "username": "admin",
        "nick_name": "超级管理员",
        "email": "admin@example.com",
        "icon": "http://example.com/avatar.jpg",
        "status": 1,
        "created_at": "2023-01-01T00:00:00.000000Z",
        "updated_at": "2023-01-01T00:00:00.000000Z",
        "roles": [
            {
                "id": 1,
                "name": "超级管理员",
                "description": "拥有所有权限"
            }
        ]
    }
}
```

### 更新管理员

- **接口**：`PUT /api/admins/{id}`
- **描述**：更新指定ID的管理员信息
- **请求参数**：

| 参数名 | 类型 | 必填 | 描述 |
| ----- | ---- | ---- | ---- |
| username | string | 是 | 用户名，最大50字符，只能包含字母、数字和下划线 |
| email | string | 是 | 邮箱，最大100字符 |
| nick_name | string | 是 | 昵称，最大50字符 |
| password | string | 否 | 密码，最小6字符 |
| password_confirmation | string | 否 | 确认密码 |
| note | string | 否 | 备注，最大500字符 |
| icon | string | 否 | 头像URL，最大500字符 |
| status | integer | 是 | 状态，0-禁用，1-启用 |
| role_ids | array | 否 | 角色ID数组 |

- **响应示例**：

```json
{
    "status": "success",
    "code": 200,
    "message": "Updated successfully",
    "data": {
        "id": 1,
        "username": "admin",
        "nick_name": "超级管理员（已更新）",
        "email": "admin@example.com",
        "icon": "http://example.com/avatar.jpg",
        "status": 1,
        "created_at": "2023-01-01T00:00:00.000000Z",
        "updated_at": "2023-01-03T00:00:00.000000Z"
    }
}
```

### 删除管理员

- **接口**：`DELETE /api/admins/{id}`
- **描述**：删除指定ID的管理员
- **请求参数**：无
- **响应示例**：

```json
{
    "status": "success",
    "code": 200,
    "message": "Deleted successfully",
    "data": null
}
```

### 分配角色

- **接口**：`POST /api/admins/{id}/roles`
- **描述**：为指定ID的管理员分配角色
- **请求参数**：

| 参数名 | 类型 | 必填 | 描述 |
| ----- | ---- | ---- | ---- |
| role_ids | array | 是 | 角色ID数组 |

- **响应示例**：

```json
{
    "status": "success",
    "code": 200,
    "message": "角色分配成功",
    "data": null
}
```

### 重置密码

- **接口**：`POST /api/admins/{id}/password/reset`
- **描述**：重置指定ID的管理员密码
- **请求参数**：

| 参数名 | 类型 | 必填 | 描述 |
| ----- | ---- | ---- | ---- |
| password | string | 是 | 新密码，最小6字符 |
| password_confirmation | string | 是 | 确认新密码 |

- **响应示例**：

```json
{
    "status": "success",
    "code": 200,
    "message": "密码重置成功",
    "data": null
}
```

### 更新状态

- **接口**：`POST /api/admins/{id}/status`
- **描述**：更新指定ID的管理员状态
- **请求参数**：

| 参数名 | 类型 | 必填 | 描述 |
| ----- | ---- | ---- | ---- |
| status | integer | 是 | 状态，0-禁用，1-启用 |

- **响应示例**：

```json
{
    "status": "success",
    "code": 200,
    "message": "状态更新成功",
    "data": null
}
```

## 角色模块

### 获取角色列表

- **接口**：`GET /api/roles`
- **描述**：获取角色列表，支持分页
- **请求参数**：

| 参数名 | 类型 | 必填 | 描述 |
| ----- | ---- | ---- | ---- |
| page | integer | 否 | 页码，默认1 |
| per_page | integer | 否 | 每页记录数，默认15 |
| keyword | string | 否 | 搜索关键词 |

- **响应示例**：

```json
{
    "status": "success",
    "code": 200,
    "message": null,
    "data": [
        {
            "id": 1,
            "name": "超级管理员",
            "description": "拥有所有权限",
            "created_at": "2023-01-01T00:00:00.000000Z",
            "updated_at": "2023-01-01T00:00:00.000000Z"
        }
    ],
    "meta": {
        "total": 1,
        "per_page": 15,
        "current_page": 1,
        "last_page": 1
    }
}
```

### 创建角色

- **接口**：`POST /api/roles`
- **描述**：创建新角色
- **请求参数**：

| 参数名 | 类型 | 必填 | 描述 |
| ----- | ---- | ---- | ---- |
| name | string | 是 | 角色名称，最大50字符，唯一 |
| description | string | 是 | 角色描述，最大255字符 |

- **响应示例**：

```json
{
    "status": "success",
    "code": 200,
    "message": "Created successfully",
    "data": {
        "id": 2,
        "name": "编辑",
        "description": "内容编辑人员",
        "created_at": "2023-01-02T00:00:00.000000Z",
        "updated_at": "2023-01-02T00:00:00.000000Z"
    }
}
```

### 更新角色

- **接口**：`PUT /api/roles/{id}`
- **描述**：更新指定ID的角色信息
- **请求参数**：

| 参数名 | 类型 | 必填 | 描述 |
| ----- | ---- | ---- | ---- |
| name | string | 是 | 角色名称，最大50字符，唯一 |
| description | string | 是 | 角色描述，最大255字符 |

- **响应示例**：

```json
{
    "status": "success",
    "code": 200,
    "message": "Updated successfully",
    "data": {
        "id": 2,
        "name": "高级编辑",
        "description": "高级内容编辑人员",
        "created_at": "2023-01-02T00:00:00.000000Z",
        "updated_at": "2023-01-03T00:00:00.000000Z"
    }
}
```

### 删除角色

- **接口**：`DELETE /api/roles/{id}`
- **描述**：删除指定ID的角色
- **请求参数**：无
- **响应示例**：

```json
{
    "status": "success",
    "code": 200,
    "message": "Deleted successfully",
    "data": null
}
```

### 分配菜单

- **接口**：`POST /api/roles/{id}/menus`
- **描述**：为指定ID的角色分配菜单
- **请求参数**：

| 参数名 | 类型 | 必填 | 描述 |
| ----- | ---- | ---- | ---- |
| menu_ids | array | 是 | 菜单ID数组 |

- **响应示例**：

```json
{
    "status": "success",
    "code": 200,
    "message": "菜单分配成功",
    "data": null
}
```

### 分配资源

- **接口**：`POST /api/roles/{id}/resources`
- **描述**：为指定ID的角色分配资源
- **请求参数**：

| 参数名 | 类型 | 必填 | 描述 |
| ----- | ---- | ---- | ---- |
| resource_ids | array | 是 | 资源ID数组 |

- **响应示例**：

```json
{
    "status": "success",
    "code": 200,
    "message": "资源分配成功",
    "data": null
}
```

### 获取角色菜单

- **接口**：`GET /api/roles/{id}/menus`
- **描述**：获取指定ID角色已分配的菜单ID列表
- **请求参数**：无
- **响应示例**：

```json
{
    "status": "success",
    "code": 200,
    "message": null,
    "data": [1, 2, 3, 4, 5]
}
```

### 获取角色资源

- **接口**：`GET /api/roles/{id}/resources`
- **描述**：获取指定ID角色已分配的资源ID列表
- **请求参数**：无
- **响应示例**：

```json
{
    "status": "success",
    "code": 200,
    "message": null,
    "data": [1, 2, 3, 4, 5]
}
```

## 菜单模块

### 获取菜单树

- **接口**：`GET /api/menus/tree`
- **描述**：获取菜单树形结构
- **请求参数**：无
- **响应示例**：

```json
{
    "status": "success",
    "code": 200,
    "message": null,
    "data": [
        {
            "id": 1,
            "parent_id": 0,
            "title": "系统管理",
            "level": 0,
            "sort": 0,
            "name": "system",
            "icon": "setting",
            "hidden": false,
            "keep_alive": true,
            "children": [
                {
                    "id": 2,
                    "parent_id": 1,
                    "title": "管理员管理",
                    "level": 1,
                    "sort": 0,
                    "name": "admin",
                    "icon": "user",
                    "hidden": false,
                    "keep_alive": true,
                    "children": []
                }
            ]
        }
    ]
}
```

### 获取菜单列表

- **接口**：`GET /api/menus`
- **描述**：获取菜单列表，支持分页
- **请求参数**：

| 参数名 | 类型 | 必填 | 描述 |
| ----- | ---- | ---- | ---- |
| page | integer | 否 | 页码，默认1 |
| per_page | integer | 否 | 每页记录数，默认15 |
| keyword | string | 否 | 搜索关键词 |

- **响应示例**：

```json
{
    "status": "success",
    "code": 200,
    "message": null,
    "data": [
        {
            "id": 1,
            "parent_id": 0,
            "title": "系统管理",
            "level": 0,
            "sort": 0,
            "name": "system",
            "icon": "setting",
            "hidden": false,
            "keep_alive": true,
            "created_at": "2023-01-01T00:00:00.000000Z",
            "updated_at": "2023-01-01T00:00:00.000000Z"
        }
    ],
    "meta": {
        "total": 1,
        "per_page": 15,
        "current_page": 1,
        "last_page": 1
    }
}
```

### 创建菜单

- **接口**：`POST /api/menus`
- **描述**：创建新菜单
- **请求参数**：

| 参数名 | 类型 | 必填 | 描述 |
| ----- | ---- | ---- | ---- |
| parent_id | integer | 否 | 父级菜单ID |
| title | string | 是 | 菜单标题，最大100字符 |
| level | integer | 是 | 菜单级数，最小0 |
| sort | integer | 是 | 排序，最小0 |
| name | string | 否 | 前端路由名称，最大100字符，唯一 |
| icon | string | 否 | 图标，最大200字符 |
| hidden | boolean | 是 | 是否隐藏 |
| keep_alive | boolean | 是 | 是否缓存 |

- **响应示例**：

```json
{
    "status": "success",
    "code": 200,
    "message": "Created successfully",
    "data": {
        "id": 3,
        "parent_id": 1,
        "title": "角色管理",
        "level": 1,
        "sort": 1,
        "name": "role",
        "icon": "peoples",
        "hidden": false,
        "keep_alive": true,
        "created_at": "2023-01-02T00:00:00.000000Z",
        "updated_at": "2023-01-02T00:00:00.000000Z"
    }
}
```

### 更新菜单

- **接口**：`PUT /api/menus/{id}`
- **描述**：更新指定ID的菜单信息
- **请求参数**：

| 参数名 | 类型 | 必填 | 描述 |
| ----- | ---- | ---- | ---- |
| parent_id | integer | 否 | 父级菜单ID |
| title | string | 是 | 菜单标题，最大100字符 |
| level | integer | 是 | 菜单级数，最小0 |
| sort | integer | 是 | 排序，最小0 |
| name | string | 否 | 前端路由名称，最大100字符，唯一 |
| icon | string | 否 | 图标，最大200字符 |
| hidden | boolean | 是 | 是否隐藏 |
| keep_alive | boolean | 是 | 是否缓存 |

- **响应示例**：

```json
{
    "status": "success",
    "code": 200,
    "message": "Updated successfully",
    "data": {
        "id": 3,
        "parent_id": 1,
        "title": "角色权限管理",
        "level": 1,
        "sort": 1,
        "name": "role",
        "icon": "peoples",
        "hidden": false,
        "keep_alive": true,
        "created_at": "2023-01-02T00:00:00.000000Z",
        "updated_at": "2023-01-03T00:00:00.000000Z"
    }
}
```

### 删除菜单

- **接口**：`DELETE /api/menus/{id}`
- **描述**：删除指定ID的菜单
- **请求参数**：无
- **响应示例**：

```json
{
    "status": "success",
    "code": 200,
    "message": "Deleted successfully",
    "data": null
}
```

## 资源分类模块

### 获取资源分类列表

- **接口**：`GET /api/resource-categories`
- **描述**：获取资源分类列表，支持分页
- **请求参数**：

| 参数名 | 类型 | 必填 | 描述 |
| ----- | ---- | ---- | ---- |
| page | integer | 否 | 页码，默认1 |
| per_page | integer | 否 | 每页记录数，默认15 |
| keyword | string | 否 | 搜索关键词 |

- **响应示例**：

```json
{
    "status": "success",
    "code": 200,
    "message": null,
    "data": [
        {
            "id": 1,
            "name": "后台管理接口",
            "sort": 0,
            "created_at": "2023-01-01T00:00:00.000000Z",
            "updated_at": "2023-01-01T00:00:00.000000Z"
        }
    ],
    "meta": {
        "total": 1,
        "per_page": 15,
        "current_page": 1,
        "last_page": 1
    }
}
```

### 创建资源分类

- **接口**：`POST /api/resource-categories`
- **描述**：创建新资源分类
- **请求参数**：

| 参数名 | 类型 | 必填 | 描述 |
| ----- | ---- | ---- | ---- |
| name | string | 是 | 分类名称，最大200字符，唯一 |
| sort | integer | 是 | 排序，最小0 |

- **响应示例**：

```json
{
    "status": "success",
    "code": 200,
    "message": "Created successfully",
    "data": {
        "id": 2,
        "name": "前台接口",
        "sort": 1,
        "created_at": "2023-01-02T00:00:00.000000Z",
        "updated_at": "2023-01-02T00:00:00.000000Z"
    }
}
```

### 更新资源分类

- **接口**：`PUT /api/resource-categories/{id}`
- **描述**：更新指定ID的资源分类信息
- **请求参数**：

| 参数名 | 类型 | 必填 | 描述 |
| ----- | ---- | ---- | ---- |
| name | string | 是 | 分类名称，最大200字符，唯一 |
| sort | integer | 是 | 排序，最小0 |

- **响应示例**：

```json
{
    "status": "success",
    "code": 200,
    "message": "Updated successfully",
    "data": {
        "id": 2,
        "name": "前台API接口",
        "sort": 1,
        "created_at": "2023-01-02T00:00:00.000000Z",
        "updated_at": "2023-01-03T00:00:00.000000Z"
    }
}
```

### 删除资源分类

- **接口**：`DELETE /api/resource-categories/{id}`
- **描述**：删除指定ID的资源分类
- **请求参数**：无
- **响应示例**：

```json
{
    "status": "success",
    "code": 200,
    "message": "Deleted successfully",
    "data": null
}
```

## 资源模块

### 获取资源列表

- **接口**：`GET /api/resources`
- **描述**：获取资源列表，支持分页
- **请求参数**：

| 参数名 | 类型 | 必填 | 描述 |
| ----- | ---- | ---- | ---- |
| page | integer | 否 | 页码，默认1 |
| per_page | integer | 否 | 每页记录数，默认15 |
| keyword | string | 否 | 搜索关键词 |
| category_id | integer | 否 | 资源分类ID |

- **响应示例**：

```json
{
    "status": "success",
    "code": 200,
    "message": null,
    "data": [
        {
            "id": 1,
            "category_id": 1,
            "name": "管理员列表",
            "route_name": "admin.index",
            "description": "获取管理员列表",
            "created_at": "2023-01-01T00:00:00.000000Z",
            "updated_at": "2023-01-01T00:00:00.000000Z",
            "category": {
                "id": 1,
                "name": "后台管理接口"
            }
        }
    ],
    "meta": {
        "total": 1,
        "per_page": 15,
        "current_page": 1,
        "last_page": 1
    }
}
```

### 创建资源

- **接口**：`POST /api/resources`
- **描述**：创建新资源
- **请求参数**：

| 参数名 | 类型 | 必填 | 描述 |
| ----- | ---- | ---- | ---- |
| category_id | integer | 是 | 资源分类ID |
| name | string | 是 | 资源名称，最大200字符 |
| route_name | string | 是 | 路由名称，最大200字符，唯一 |
| description | string | 否 | 描述，最大500字符 |

- **响应示例**：

```json
{
    "status": "success",
    "code": 200,
    "message": "Created successfully",
    "data": {
        "id": 2,
        "category_id": 1,
        "name": "创建管理员",
        "route_name": "admin.store",
        "description": "创建新管理员",
        "created_at": "2023-01-02T00:00:00.000000Z",
        "updated_at": "2023-01-02T00:00:00.000000Z"
    }
}
```

### 更新资源

- **接口**：`PUT /api/resources/{id}`
- **描述**：更新指定ID的资源信息
- **请求参数**：

| 参数名 | 类型 | 必填 | 描述 |
| ----- | ---- | ---- | ---- |
| category_id | integer | 是 | 资源分类ID |
| name | string | 是 | 资源名称，最大200字符 |
| route_name | string | 是 | 路由名称，最大200字符，唯一 |
| description | string | 否 | 描述，最大500字符 |

- **响应示例**：

```json
{
    "status": "success",
    "code": 200,
    "message": "Updated successfully",
    "data": {
        "id": 2,
        "category_id": 1,
        "name": "添加管理员",
        "route_name": "admin.store",
        "description": "添加新管理员账号",
        "created_at": "2023-01-02T00:00:00.000000Z",
        "updated_at": "2023-01-03T00:00:00.000000Z"
    }
}
```

### 删除资源

- **接口**：`DELETE /api/resources/{id}`
- **描述**：删除指定ID的资源
- **请求参数**：无
- **响应示例**：

```json
{
    "status": "success",
    "code": 200,
    "message": "Deleted successfully",
    "data": null
}
```