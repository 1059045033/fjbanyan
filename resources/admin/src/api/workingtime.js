import request from '@/utils/request'
// 这个是人员的
export function fetchWorkingTimeAllList(query) {
  return request({
    url: '/admin/workingtime/lists',
    method: 'get',
    params: query
  })
}


export function createWorkingTime(data) {
  console.log('发起创建用户上班时间请求')
  return request({
    url: '/admin/workingtime/create',
    method: 'post',
    data
  })
}

export function deleteWorkingTime(data) {
  console.log('发起删除人员请求')
  return request({
    url: '/admin/workingtime/delete',
    method: 'post',
    data
  })
}
