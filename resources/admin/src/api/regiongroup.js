import request from '@/utils/request'
// 这个是人员的
export function regionGroupList(query) {
  return request({
    url: '/admin/regiongroup/lists',
    method: 'get',
    params: query
  })
}

export function createRegionGroup(data) {
  console.log('发起创建公司请求');
  return request({
    url: '/admin/regiongroup/create',
    method: 'post',
    data
  })
}

export function deleteRegionGroup(data) {
  console.log('发起删除公司请求');
  return request({
    url: '/admin/regiongroup/delete',
    method: 'post',
    data
  })
}


export function fetchGroupList(query) {
  return request({
    url: '/admin/regiongroup/group_all',
    method: 'get',
    params: query
  })
}
