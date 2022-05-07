import request from '@/utils/request'

export function getAllRegions(query) {
  console.log('发起获取所有区域请求')
  return request({
    url: '/admin/region/regions_all',
    method: 'get',
    params: query
  })
}

export function getAllCompany(query) {
  console.log('发起获取所有区域请求')
  return request({
    url: '/admin/company/company_all',
    method: 'get',
    params: query
  })
}
export function fetchAttendanceAllList(query) {
  console.log('发起获取考勤明细的请求')
  return request({
    url: '/admin/attendance/lists',
    method: 'get',
    params: query
  })
}


