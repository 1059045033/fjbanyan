import request from '@/utils/request'
// 这个是人员的
export function companylist() {
  return request({
    url: '/admin/company/lists',
    method: 'get',
  })
}


