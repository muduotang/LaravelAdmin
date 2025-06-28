// 登录表单
export interface LoginForm {
  username: string
  password: string
}

// 登录响应
export interface LoginResponse {
  access_token: string
  token_type: string
  expires_in: number
  user: UserInfo
}

// 用户信息
export interface UserInfo {
  id: number
  name: string
  email: string
  email_verified_at?: string
  created_at: string
  updated_at: string
  roles?: Role[]
  permissions?: Permission[]
}

// 角色信息
export interface Role {
  id: number
  name: string
  display_name: string
  description?: string
  created_at: string
  updated_at: string
}

// 权限信息
export interface Permission {
  id: number
  name: string
  display_name: string
  description?: string
  created_at: string
  updated_at: string
}