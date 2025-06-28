import service from '@/axios/service'
import type { LoginForm, LoginResponse, UserInfo } from './types'

// 用户登录
export const loginApi = (data: LoginForm) => {
  return service.request<LoginResponse>({
    url: '/auth/login',
    method: 'POST',
    data
  })
}

// 用户登出
export const logoutApi = () => {
  return service.request({
    url: '/auth/logout',
    method: 'POST'
  })
}

// 刷新token
export const refreshTokenApi = () => {
  return service.request({
    url: '/auth/refresh',
    method: 'POST'
  })
}

// 获取用户信息
export const getUserInfoApi = () => {
  return service.request<UserInfo>({
    url: '/auth/me',
    method: 'GET'
  })
}

// 更新用户资料
export const updateProfileApi = (data: Partial<UserInfo>) => {
  return service.request<UserInfo>({
    url: '/auth/updateProfile',
    method: 'PUT',
    data
  })
}