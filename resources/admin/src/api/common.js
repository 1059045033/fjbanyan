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

//文件上传
export function fileUpload(data) {
  console.log('上传文件')
  return request({
    url: '/fileUpdate',
    headers:{"Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"},//设置响应投
    method: 'POST',
    data:data,
    onUploadProgress: function (progressEvent) { //原生获取上传进度的事件
      if (progressEvent.lengthComputable) {
        //属性lengthComputable主要表明总共需要完成的工作量和已经完成的工作是否可以被测量
        //如果lengthComputable为false，就获取不到progressEvent.total和progressEvent.loaded
        let upLoadProgress = progressEvent.loaded / progressEvent.total * 100
        console.log(upLoadProgress);
      }
    }
  });
}
export function fetchVersion(data) {
  console.log('获取版本管理请求')
  return request({
    url: '/version',
    headers:{"Content-Type": "application/json"},//设置响应投
    method: 'POST',
    data:data,
  })
}

export function saveVersion(data) {
  console.log('保存版本管理请求')
  return request({
    url: '/version/save',
    headers:{"Content-Type": "application/json"},//设置响应投
    method: 'POST',
    data:data,
  })
}


