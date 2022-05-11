import request from '@/utils/request'
// 这个是人员的
export function fetchRecodLogsAllList(query) {
  return request({
    url: '/admin/optrecord/lists',
    method: 'get',
    params: query
  })
}
