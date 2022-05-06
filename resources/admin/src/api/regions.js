import request from '@/utils/request'

export function fetchList(query) {
  return request({
    url: '/admin/region/regions',
    method: 'get',
    params: query
  })
}

export function fetchManagerList(query) {
  return request({
    url: '/admin/user/unarrange',
    method: 'get',
    params: query
  })
}

export function fetchRole10List(query) {
  console.log('发起获取三级人员请求')
  return request({
    url: '/admin/user/role_one',
    method: 'get',
    params: query
  })
}


export function createRegion(data) {
  return request({
    url: '/admin/region/create',
    method: 'post',
    data
  })
}

export function updateWorksUser(data) {
  console.log('发起修改区域归属人员请求')
  return request({
    url: '/admin/region/belong_to',
    method: 'post',
    data
  })
}


export function deleteRegion(data) {
  return request({
    url: '/admin/region/delete',
    method: 'post',
    data
  })
}



export function fetchArticle(id) {
  return request({
    url: '/vue-element-admin/article/detail',
    method: 'get',
    params: { id }
  })
}

export function fetchPv(pv) {
  return request({
    url: '/vue-element-admin/article/pv',
    method: 'get',
    params: { pv }
  })
}

export function createArticle(data) {
  return request({
    url: '/vue-element-admin/article/create',
    method: 'post',
    data
  })
}

export function updateArticle(data) {
  return request({
    url: '/vue-element-admin/article/update',
    method: 'post',
    data
  })
}
