import request from '@/utils/request'

// 获取所有的轨迹信息
export function fetchAllList(query) {
  return request({
    url: '/admin/tracks/all_lists',
    method: 'get',
    params: query
  })
}


