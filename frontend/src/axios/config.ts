import { AxiosResponse, InternalAxiosRequestConfig } from './types'
import { ElMessage } from 'element-plus'
import qs from 'qs'
import { SUCCESS_CODE, TRANSFORM_REQUEST_DATA } from '@/constants'
import { useUserStoreWithOut } from '@/store/modules/user'
import { objToFormData } from '@/utils'

const defaultRequestInterceptors = (config: InternalAxiosRequestConfig) => {
  // 添加Authorization头
  const token = localStorage.getItem('token')
  if (token) {
    config.headers['Authorization'] = `Bearer ${token}`
  }
  if (
    config.method === 'post' &&
    config.headers['Content-Type'] === 'application/x-www-form-urlencoded'
  ) {
    config.data = qs.stringify(config.data)
  } else if (
    TRANSFORM_REQUEST_DATA &&
    config.method === 'post' &&
    config.headers['Content-Type'] === 'multipart/form-data' &&
    !(config.data instanceof FormData)
  ) {
    config.data = objToFormData(config.data)
  }
  if (config.method === 'get' && config.params) {
    let url = config.url as string
    url += '?'
    const keys = Object.keys(config.params)
    for (const key of keys) {
      if (config.params[key] !== void 0 && config.params[key] !== null) {
        url += `${key}=${encodeURIComponent(config.params[key])}&`
      }
    }
    url = url.substring(0, url.length - 1)
    config.params = {}
    config.url = url
  }
  return config
}

const defaultResponseInterceptors = (response: AxiosResponse) => {
  if (response?.config?.responseType === 'blob') {
    // 如果是文件流，直接过
    return response
  } else if (response.data.status === 'success') {
    // 后端成功响应，返回数据
    return response.data
  } else if (response.data.status === 'error') {
    // 后端错误响应，显示错误信息但不抛出异常
    ElMessage.error(response.data.message || '请求失败')
    if (response.data.code === 401) {
      const userStore = useUserStoreWithOut()
      userStore.logout()
    }
    // 返回错误响应数据而不是抛出异常
    return Promise.reject({
      code: response.data.code,
      message: response.data.message,
      data: response.data.data,
      errors: response.data.errors
    })
  } else {
    // 兼容其他格式的响应
    return response.data
  }
}

export { defaultResponseInterceptors, defaultRequestInterceptors }
