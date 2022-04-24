import request from '@/utils/request'
// 这个是人员的
export function userlist(query) {
  console.log('发起用户请求')
  return request({
    url: '/admin/user/lists',
    method: 'get',
    params: query
  })
}

createUser
export function createUser(data) {
  console.log('发起用户请求')
  return request({
    url: '/admin/user/create',
    method: 'post',
    data
  })
}


