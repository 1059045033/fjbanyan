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


export function createUser(data) {
  console.log('发起创建用户请求')
  return request({
    url: '/admin/user/create',
    method: 'post',
    data
  })
}

export function updateUser(data) {
  console.log('发起修改用户请求')
  return request({
    url: '/admin/user/edit',
    method: 'post',
    data
  })
}

export function deleteUser(data) {
  console.log('发起删除人员请求');
  return request({
    url: '/admin/user/delete',
    method: 'post',
    data
  })
}


