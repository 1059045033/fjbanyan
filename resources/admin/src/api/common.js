import request from '@/utils/request'

export function getAllRegions(query) {
  return request({
    url: '/admin/region/regions_all',
    method: 'get',
    params: query
  })
}

