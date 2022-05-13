import request from '@/utils/request'
// 这个是 管理人员的
export function login(data) {
  console.log('发起login请求')
  return request({
    // url: '/vue-element-admin/user/login',
    url: '/admin/login',
    method: 'post',
    data
  })
}

export function getInfo(token) {
  console.log('发起getInfo请求')
  return request({
    // url: '/vue-element-admin/user/info',
    url: '/admin/info',
    method: 'get',
    params: { token }
  })
}

export function updateInfo(data) {
  console.log('发起updateInfo请求')
  return request({
    url: '/admin/update',
    method: 'post',
    data
  })
}

export function logout() {
  return request({
    url: '/admin/logout',
    method: 'post'
  })
}
