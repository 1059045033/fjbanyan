import request from '@/utils/request'
// 这个是人员的
export function companyList(query) {
  return request({
    url: '/admin/company/lists',
    method: 'get',
    params: query
  })
}

export function createCompany(data) {
  console.log('发起创建公司请求');
  return request({
    url: '/admin/company/create',
    method: 'post',
    data
  })
}

export function deleteCompany(data) {
  console.log('发起删除公司请求');
  return request({
    url: '/admin/company/delete',
    method: 'post',
    data
  })
}
