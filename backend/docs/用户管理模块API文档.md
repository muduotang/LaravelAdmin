# 用户管理模块 API 文档

## 概述

用户管理模块提供了完整的管理员账户管理功能，包括用户的增删改查、角色分配、密码管理和状态控制等功能。

## 基础信息

- **基础路径**: `/api/admins`
- **认证方式**: JWT Token (Bearer Token)
- **响应格式**: JSON

## API 接口列表

### 1. 获取管理员列表

**接口地址**: `GET /api/admins`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| page | integer | 否 | 页码，默认为1 |
| per_page | integer | 否 | 每页数量，默认为15 |
| keyword | string | 否 | 搜索关键词（用户名、昵称、邮箱） |
| status | integer | 否 | 状态筛选（0=禁用，1=启用） |
| role_id | integer | 否 | 角色ID筛选 |

**响应示例**:
```json
{
    "code": 200,
    "message": "获取管理员列表成功",
    "data": {
        "data": [
            {
                "id": 1,
                "username": "admin",
                "email": "admin@example.com",
                "nick_name": "超级管理员",
                "note": "系统超级管理员",
                "icon": null,
                "status": 1,
                "status_text": "启用",
                "roles": [
                    {
                        "id": 1,
                        "name": "super_admin",
                        "description": "超级管理员"
                    }
                ],
                "roles_text": "super_admin",
                "created_at": "2024-01-01 12:00:00",
                "updated_at": "2024-01-01 12:00:00"
            }
        ],
        "current_page": 1,
        "per_page": 15,
        "total": 1,
        "last_page": 1
    }
}
```

### 2. 创建管理员

**接口地址**: `POST /api/admins`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| username | string | 是 | 用户名（唯一，只能包含字母、数字、下划线） |
| email | string | 是 | 邮箱（唯一） |
| password | string | 是 | 密码（至少6位） |
| password_confirmation | string | 是 | 确认密码 |
| nick_name | string | 是 | 昵称 |
| note | string | 否 | 备注 |
| icon | string | 否 | 头像URL |
| status | integer | 是 | 状态（0=禁用，1=启用） |
| role_ids | array | 否 | 角色ID数组 |

**请求示例**:
```json
{
    "username": "newadmin",
    "email": "newadmin@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "nick_name": "新管理员",
    "note": "这是一个新管理员",
    "status": 1,
    "role_ids": [1, 2]
}
```

**响应示例**:
```json
{
    "code": 200,
    "message": "创建管理员成功",
    "data": {
        "id": 2,
        "username": "newadmin",
        "email": "newadmin@example.com",
        "nick_name": "新管理员",
        "note": "这是一个新管理员",
        "icon": null,
        "status": 1,
        "status_text": "启用",
        "roles": [],
        "roles_text": "",
        "created_at": "2024-01-01 12:00:00",
        "updated_at": "2024-01-01 12:00:00"
    }
}
```

### 3. 获取管理员详情

**接口地址**: `GET /api/admins/{id}`

**路径参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | integer | 是 | 管理员ID |

**响应示例**:
```json
{
    "code": 200,
    "message": "获取管理员详情成功",
    "data": {
        "id": 1,
        "username": "admin",
        "email": "admin@example.com",
        "nick_name": "超级管理员",
        "note": "系统超级管理员",
        "icon": null,
        "status": 1,
        "status_text": "启用",
        "roles": [
            {
                "id": 1,
                "name": "super_admin",
                "description": "超级管理员"
            }
        ],
        "roles_text": "super_admin",
        "created_at": "2024-01-01 12:00:00",
        "updated_at": "2024-01-01 12:00:00"
    }
}
```

### 4. 更新管理员

**接口地址**: `PUT /api/admins/{id}`

**路径参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | integer | 是 | 管理员ID |

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| username | string | 是 | 用户名（唯一） |
| email | string | 是 | 邮箱（唯一） |
| password | string | 否 | 密码（更新时可选） |
| password_confirmation | string | 否 | 确认密码 |
| nick_name | string | 是 | 昵称 |
| note | string | 否 | 备注 |
| icon | string | 否 | 头像URL |
| status | integer | 是 | 状态（0=禁用，1=启用） |
| role_ids | array | 否 | 角色ID数组 |

### 5. 删除管理员

**接口地址**: `DELETE /api/admins/{id}`

**路径参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | integer | 是 | 管理员ID |

**注意**: 不能删除自己的账号

**响应示例**:
```json
{
    "code": 200,
    "message": "删除管理员成功",
    "data": null
}
```

### 6. 分配角色

**接口地址**: `POST /api/admins/{id}/roles`

**路径参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | integer | 是 | 管理员ID |

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| role_ids | array | 是 | 角色ID数组 |

**请求示例**:
```json
{
    "role_ids": [1, 2, 3]
}
```

### 7. 重置密码

**接口地址**: `POST /api/admins/{id}/reset-password`

**路径参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | integer | 是 | 管理员ID |

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| password | string | 是 | 新密码（至少6位） |
| password_confirmation | string | 是 | 确认密码 |

**请求示例**:
```json
{
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

### 8. 更新状态

**接口地址**: `POST /api/admins/{id}/status`

**路径参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | integer | 是 | 管理员ID |

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| status | integer | 是 | 状态（0=禁用，1=启用） |

**注意**: 不能禁用自己的账号

**请求示例**:
```json
{
    "status": 0
}
```

## 错误码说明

| 错误码 | 说明 |
|--------|------|
| 200 | 成功 |
| 400 | 请求参数错误 |
| 401 | 未登录或登录已过期 |
| 403 | 权限不足 |
| 404 | 资源不存在 |
| 422 | 表单验证失败 |
| 500 | 服务器内部错误 |

## 权限说明

用户管理模块需要以下权限：

- `admins.index` - 查看管理员列表
- `admins.show` - 查看管理员详情
- `admins.store` - 创建管理员
- `admins.update` - 更新管理员
- `admins.destroy` - 删除管理员
- `admins.assignRoles` - 分配角色
- `admins.resetPassword` - 重置密码
- `admins.updateStatus` - 更新状态

## 使用示例

### 获取管理员列表

```bash
curl -X GET "http://localhost:8000/api/admins?page=1&per_page=10&keyword=admin" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Accept: application/json"
```

### 创建管理员

```bash
curl -X POST "http://localhost:8000/api/admins" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "username": "newadmin",
    "email": "newadmin@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "nick_name": "新管理员",
    "status": 1
  }'
```

### 分配角色

```bash
curl -X POST "http://localhost:8000/api/admins/2/roles" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "role_ids": [1, 2]
  }'
```