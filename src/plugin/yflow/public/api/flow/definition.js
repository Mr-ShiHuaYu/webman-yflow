let $ = layui.$;
// 流程定义API接口
var DefinitionApi = {
  // 查询流程定义列表
  listUrl: "/app/yflow/definition/select",

  // 查询流程定义详细
  getDefinition: function (id, callback) {
    $.ajax({
      url: '/app/yflow/definition/get/' + id,
      method: 'get',
      dataType: 'json',
      success: callback
    });
  },
  // 导出流程定义详细
  exportDefinition: function (id) {
    fetch('/app/yflow/definition/exportDefinition/' + id)
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        return response.blob();
      })
      .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = 'flow_definition_' + id + '.json';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
      })
      .catch(error => {
        console.error('Error downloading file:', error);
        layer.msg('下载失败', {icon: 2});
      });
  },

  // 新增流程定义
  addDefinition: function (data, callback) {
    $.ajax({
      url: '/app/yflow/definition/insert',
      method: 'post',
      data: data,
      dataType: 'json',
      success: callback
    });
  },

  // 修改流程定义
  updateDefinition: function (data, callback) {
    $.ajax({
      url: '/app/yflow/definition/update',
      method: 'post',
      data: data,
      dataType: 'json',
      success: callback
    });
  },

  // 删除流程定义
  delDefinition: function (id, callback) {
    $.ajax({
      url: '/app/yflow/definition/delete/' + id,
      method: 'post',
      dataType: 'json',
      success: callback
    });
  },

  // 发布流程定义
  publish: function (id, callback) {
    $.ajax({
      url: '/app/yflow/definition/publish/' + id,
      method: 'post',
      dataType: 'json',
      success: callback
    });
  },

  // 取消发布流程定义
  unPublish: function (id, callback) {
    $.ajax({
      url: '/app/yflow/definition/unPublish/' + id,
      method: 'post',
      dataType: 'json',
      success: callback
    });
  },

  // 复制流程定义
  copyDef: function (id, callback) {
    $.ajax({
      url: '/app/yflow/definition/copy/' + id,
      method: 'get',
      dataType: 'json',
      success: callback
    });
  },

  // 激活流程
  active: function (definitionId, callback) {
    $.ajax({
      url: '/app/yflow/definition/active/' + definitionId,
      method: 'get',
      dataType: 'json',
      success: callback
    });
  },

  // 挂起流程
  unActive: function (definitionId, callback) {
    $.ajax({
      url: '/app/yflow/definition/unActive/' + definitionId,
      method: 'get',
      dataType: 'json',
      success: callback
    });
  },

  // 查询已发布表单定义列表
  publishedList: function (callback) {
    $.ajax({
      url: '/app/yflow/definition/publishedList',
      method: 'get',
      dataType: 'json',
      success: callback
    });
  }
};
