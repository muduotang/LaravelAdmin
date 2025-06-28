# 前端错误处理机制说明

## 概述

本项目已经实现了统一的前后端错误处理机制，前端能够正确处理后端返回的错误信息并显示给用户，而不是抛出异常。

## 后端响应格式

后端统一返回以下格式的JSON响应：

### 成功响应
```json
{
  "status": "success",
  "code": 200,
  "message": "操作成功",
  "data": {}
}
```

### 错误响应
```json
{
  "status": "error",
  "code": 422,
  "message": "验证失败",
  "data": null,
  "errors": {
    "username": ["用户名不能为空"]
  }
}
```

## 前端错误处理机制

### 1. Axios响应拦截器

在 `frontend/src/axios/config.ts` 中配置了响应拦截器：

- 成功响应（`status: 'success'`）：直接返回数据
- 错误响应（`status: 'error'`）：显示错误消息并返回格式化的错误对象
- 401错误：自动执行登出操作

### 2. 网络错误处理

在 `frontend/src/axios/service.ts` 中处理网络错误：

- HTTP错误：显示相应的错误消息
- 网络连接失败：显示"网络连接失败"消息
- 其他错误：显示通用错误消息

### 3. 错误处理工具函数

在 `frontend/src/utils/index.ts` 中提供了以下工具函数：

#### `handleApiError(error: any)`
统一处理API错误，返回格式化的错误信息：
```typescript
{
  code: number,
  message: string,
  data: any,
  errors?: any
}
```

#### `isApiSuccess(response: any): boolean`
检查响应是否为成功状态

#### `isApiError(response: any): boolean`
检查响应是否为错误状态

## 使用示例

### 在组件中处理API调用

```typescript
import { handleApiError } from '@/utils'
import { loginApi } from '@/api/auth'

const handleLogin = async () => {
  try {
    const res = await loginApi({
      username: 'admin',
      password: 'password'
    })
    
    if (res && res.data) {
      // 处理成功响应
      console.log('登录成功:', res.data)
    }
  } catch (error: any) {
    // 使用统一的错误处理函数
    const errorInfo = handleApiError(error)
    console.log('错误详情:', errorInfo)
    
    // 根据错误码进行特殊处理
    if (errorInfo.code === 422) {
      console.log('表单验证错误:', errorInfo.errors)
    } else if (errorInfo.code === 401) {
      console.log('未授权，需要重新登录')
    }
  }
}
```

### 表单验证错误处理

对于422验证错误，可以获取具体的字段错误信息：

```typescript
if (errorInfo.code === 422 && errorInfo.errors) {
  // errorInfo.errors 包含具体的字段错误
  Object.keys(errorInfo.errors).forEach(field => {
    console.log(`${field}: ${errorInfo.errors[field].join(', ')}`)
  })
}
```

## 错误消息显示

所有的错误消息都会通过 `ElMessage.error()` 自动显示给用户，无需在组件中手动处理错误消息的显示。

## 注意事项

1. **不要重复显示错误消息**：错误消息已经在axios拦截器中显示，组件中只需要处理错误逻辑即可
2. **使用统一的错误处理函数**：推荐使用 `handleApiError()` 函数来处理错误
3. **根据错误码进行特殊处理**：不同的错误码可能需要不同的处理逻辑
4. **保持错误处理的一致性**：所有API调用都应该遵循相同的错误处理模式

## 扩展

如果需要添加新的错误处理逻辑，可以：

1. 在axios拦截器中添加新的错误类型处理
2. 在错误处理工具函数中添加新的处理逻辑
3. 在组件中根据具体的错误码进行特殊处理