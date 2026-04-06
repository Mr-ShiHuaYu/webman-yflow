let $ = layui.$;

// OA 请假申请 API 接口
var LeaveApi = {
  // 查询 OA 请假申请列表
  listLeave: function (query, callback) {
    $.ajax({
      url: '/app/yflow/leave/list',
      method: 'get',
      data: query,
      dataType: 'json',
      success: callback
    });
  },

  // 查询 OA 请假申请详细
  getLeave: function (id, callback) {
    $.ajax({
      url: '/app/yflow/leave/get/' + id,
      method: 'get',
      dataType: 'json',
      success: callback
    });
  },

  // 查询用户列表
  listUser: function (callback) {
    $.ajax({
      url: '/app/admin/admin/select',
      method: 'get',
      dataType: 'json',
      success: callback
    });
  },

  // 新增 OA 请假申请
  addLeave: function (data, flowStatus, callback) {
    data = Object.assign(data, {flow_status: flowStatus});
    $.ajax({
      url: '/app/yflow/leave/add',
      method: 'post',
      data: data,
      dataType: 'json',
      success: callback
    });
  },

  // 修改 OA 请假申请
  updateLeave: function (data, callback) {
    $.ajax({
      url: '/app/yflow/leave/edit',
      method: 'post',
      data: data,
      dataType: 'json',
      success: callback
    });
  },

  // 删除 OA 请假申请
  delLeave: function (id, callback) {
    $.ajax({
      url: '/app/yflow/leave/remove',
      method: 'post',
      data: {ids: [id]},
      dataType: 'json',
      success: callback
    });
  },

  // 批量删除 OA 请假申请
  batchDelLeave: function (ids, callback) {
    $.ajax({
      url: '/app/yflow/leave/remove',
      method: 'post',
      data: {ids: ids},
      dataType: 'json',
      success: callback
    });
  },

  // 提交审批 OA 请假申请
  submit: function (id, flowStatus, callback) {
    $.ajax({
      url: '/app/yflow/leave/submit',
      method: 'post',
      data: {
        id: id,
        flow_status: flowStatus
      },
      dataType: 'json',
      success: callback
    });
  },

  // 办理 OA 请假申请
  handle: function (data, callback) {
    $.ajax({
      url: '/app/yflow/leave/handle',
      method: 'post',
      data: data,
      dataType: 'json',
      success: callback
    });
  },

  // 驳回到上一个任务
  rejectLast: function (data, callback) {
    $.ajax({
      url: '/app/yflow/leave/rejectLast',
      method: 'post',
      data: data,
      dataType: 'json',
      success: callback
    });
  },

  // 拿回到最近办理的任务
  taskBack: function (data, callback) {
    $.ajax({
      url: '/app/yflow/leave/taskBack',
      method: 'post',
      data: data,
      dataType: 'json',
      success: callback
    });
  },

  // 撤销流程
  revoke: function (id, callback) {
    $.ajax({
      url: '/app/yflow/leave/revoke/' + id,
      method: 'post',
      dataType: 'json',
      success: callback
    });
  },

  // 拿回到最近办理的任务（按实例 ID）
  taskBackByInsId: function (id, callback) {
    $.ajax({
      url: '/app/yflow/leave/taskBackByInsId/' + id,
      method: 'get',
      dataType: 'json',
      success: callback
    });
  },

  // 终止流程
  termination: function (data, callback) {
    $.ajax({
      url: '/app/yflow/leave/termination',
      method: 'post',
      data: data,
      dataType: 'json',
      success: callback
    });
  }
};
