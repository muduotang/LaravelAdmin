# Laravel Admin

基于 Laravel 10 开发的现代化后台管理系统，采用前后端分离架构。

## 技术栈

### 后端
- Laravel 10.x
- PHP 8.1+
- MySQL 8.0+
- JWT 认证
- RESTful API

### 前端
- Vue 3
- TypeScript
- Vite
- Element Plus

## 功能特性

- [x] JWT 认证系统
  - 登录/登出
  - 刷新令牌
  - 获取用户信息
- [ ] RBAC 权限管理
  - 用户管理
  - 角色管理
  - 权限管理
  - 菜单管理
- [ ] 系统管理
  - 操作日志
  - 登录日志
  - 系统配置

## 快速开始

### 环境要求

- PHP >= 8.1
- Composer
- MySQL >= 8.0
- Node.js >= 16

### 后端安装

```bash
# 进入后端目录
cd backend

# 安装依赖
composer install

# 复制环境配置文件
cp .env.example .env

# 生成应用密钥
php artisan key:generate

# 生成 JWT 密钥
php artisan jwt:secret

# 运行数据库迁移和填充
php artisan migrate --seed

# 启动开发服务器
php artisan serve
```

### 前端安装

```bash
# 进入前端目录
cd frontend

# 安装依赖
npm install

# 启动开发服务器
npm run dev

# 构建生产版本
npm run build
```

## 测试

```bash
cd backend
php artisan test
```

## API 文档

API 文档使用 Swagger/OpenAPI 规范，可以在开发环境下访问：

```
http://localhost:8000/api/documentation
```

## 贡献指南

1. Fork 本仓库
2. 创建你的特性分支 (`git checkout -b feature/AmazingFeature`)
3. 提交你的改动 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 打开一个 Pull Request

## 许可证

[MIT License](LICENSE) 