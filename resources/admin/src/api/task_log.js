import request from '@/utils/request'

// 获取所有的轨迹信息
export function fetchTaskLogAllList(query) {
  return request({
    url: '/admin/task/logs',
    method: 'get',
    params: query
  })
}


export function exportTaskLogAllList(query) {
  return request({
    url: '/admin/task/export',
    method: 'get',
    params: query
  })
}

