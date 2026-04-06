var FlowDict = {
  // 流程状态（不含HTML标签）
  flowStatus: {
    '0': '待提交',
    '1': '审批中',
    '2': '审批通过',
    '3': '自动完成',
    '4': '终止',
    '5': '作废',
    '6': '撤销',
    '8': '已完成',
    '9': '已退回',
    '10': '失效',
    '11': '拿回'
  },

  // 根据流程状态获取HTML标签
  flowStatusMap: function(status) {
    let map = {
      '0': '<span class="layui-badge">待提交</span>',
      '1': '<span class="layui-badge layui-bg-blue">审批中</span>',
      '2': '<span class="layui-badge layui-bg-green">审批通过</span>',
      '3': '<span class="layui-badge layui-bg-green">自动完成</span>',
      '4': '<span class="layui-badge layui-bg-red">终止</span>',
      '5': '<span class="layui-badge layui-bg-gray">作废</span>',
      '6': '<span class="layui-badge layui-bg-orange">撤销</span>',
      '8': '<span class="layui-badge layui-bg-green">已完成</span>',
      '9': '<span class="layui-badge layui-bg-orange">已退回</span>',
      '10': '<span class="layui-badge layui-bg-gray">失效</span>',
      '11': '<span class="layui-badge layui-bg-blue">拿回</span>'
    };
    return map[status] || status || '-';
  },

  // 协作类型（不含HTML标签）
  cooperateType: {
    '1': '无',
    '2': '转办',
    '3': '委派',
    '4': '会签',
    '5': '票签',
    '6': '加签',
    '7': '减签'
  },

  // 根据协作类型获取HTML标签
  cooperateTypeMap: function(type) {
    let map = {
      '1': '<span class="layui-badge layui-bg-green">无</span>',
      '2': '<span class="layui-badge layui-bg-orange">转办</span>',
      '3': '<span class="layui-badge layui-bg-green">委派</span>',
      '4': '<span class="layui-badge layui-bg-blue">会签</span>',
      '5': '<span class="layui-badge layui-bg-cyan">票签</span>',
      '6': '<span class="layui-badge layui-bg-blue">加签</span>',
      '7': '<span class="layui-badge layui-bg-red">减签</span>'
    };
    return map[type] || type || '-';
  },

  // 动态填充流程状态下拉选项
  // selectId: select元素的ID
  // options: 可选配置项 {firstOption: '请选择流程状态', selectedValue: ''}
  initFlowStatusSelect: function(selectId, options) {
    let defaultOptions = {
      firstOption: '请选择流程状态',
      selectedValue: ''
    };
    options = Object.assign({}, defaultOptions, options || {});

    let select = document.getElementById(selectId);
    if (!select) {
      console.error('未找到ID为' + selectId + '的select元素');
      return;
    }

    select.innerHTML = '';

    if (options.firstOption) {
      var firstOpt = document.createElement('option');
      firstOpt.value = '';
      firstOpt.textContent = options.firstOption;
      select.appendChild(firstOpt);
    }

    for (var key in FlowDict.flowStatus) {
      let opt = document.createElement('option');
      opt.value = key;
      opt.textContent = FlowDict.flowStatus[key];
      if (String(key) === String(options.selectedValue)) {
        opt.selected = true;
      }
      select.appendChild(opt);
    }

    if (typeof layui !== 'undefined' && layui.form) {
      layui.form.render('select');
    }
  },

  // 动态填充协作类型下拉选项
  // selectId: select元素的ID
  // options: 可选配置项 {firstOption: '请选择协作类型', selectedValue: ''}
  initCooperateTypeSelect: function(selectId, options) {
    let defaultOptions = {
      firstOption: '请选择协作类型',
      selectedValue: ''
    };
    options = Object.assign({}, defaultOptions, options || {});

    let select = document.getElementById(selectId);
    if (!select) {
      console.error('未找到ID为' + selectId + '的select元素');
      return;
    }

    select.innerHTML = '';

    if (options.firstOption) {
      let firstOpt = document.createElement('option');
      firstOpt.value = '';
      firstOpt.textContent = options.firstOption;
      select.appendChild(firstOpt);
    }

    for (var key in FlowDict.cooperateType) {
      let opt = document.createElement('option');
      opt.value = key;
      opt.textContent = FlowDict.cooperateType[key];
      if (String(key) === String(options.selectedValue)) {
        opt.selected = true;
      }
      select.appendChild(opt);
    }

    if (typeof layui !== 'undefined' && layui.form) {
      layui.form.render('select');
    }
  }
};
