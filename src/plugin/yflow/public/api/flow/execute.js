// 流程执行API接口
var ExecuteApi = {

  // 查询用户列表 - 转办 | 加签 | 委派 | 减签
  interactiveTypeSysUserUrl: '/app/yflow/execute/interactiveTypeSysUser',

  // 查询待办任务列表
  toDoPageUrl: '/app/yflow/execute/toDoPage',

  // 查询已办任务列表
  donePageUrl:'/app/yflow/execute/donePage',

  // 查询抄送任务列表
  copyPageUrl: '/app/yflow/execute/copyPage',

  // 查询跳转任意节点列表
  anyNodeList: function (instanceId, callback) {
    layui.$.ajax({
      url: '/app/yflow/execute/anyNodeList/' + instanceId,
      method: 'get',
      dataType: 'json',
      success: callback
    });
  },

  // 转办|加签|委派|减签
  interactiveType: function (taskId, assigneePermission, operatorType, callback) {
    layui.$.ajax({
      url: '/app/yflow/execute/interactiveType',
      method: 'post',
      data: {
        taskId: taskId,
        addHandlers: assigneePermission,
        operatorType: operatorType
      },
      dataType: 'json',
      success: callback
    });
  },

  // 根据taskId查询代表任务
  getTaskById: function (taskId, callback) {
    layui.$.ajax({
      url: '/app/yflow/execute/getTaskById/' + taskId,
      method: 'get',
      dataType: 'json',
      success: callback
    });
  },

  // 激活流程
  active: function (instanceId, callback) {
    layui.$.ajax({
      url: '/app/yflow/execute/active/' + instanceId,
      method: 'get',
      dataType: 'json',
      success: callback
    });
  },

  // 挂起流程
  unActive: function (instanceId, callback) {
    layui.$.ajax({
      url: '/app/yflow/execute/unActive/' + instanceId,
      method: 'get',
      dataType: 'json',
      success: callback
    });
  },

};
