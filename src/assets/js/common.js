/**
 * Created by Administrator on 2017/8/2.
 */
const domain = '/api';
import '../../../static/jsontotable/jQuery.jsontotable'
export default {
  ajaxSendOther(url, type, param, fun, asy) {
    $.ajax({   //普通请求
      url: domain + url,
      type: type,
      data: param,
      dataType: 'json',
      async: asy || true,
      success: function (res) {
        fun(res);
      },
      error: function () {
        console.log('请求失败');
      }
    })
  },
  /*
   * url 请求路径
   * type 请求方式
   * param 请求参数
   * fun 请求成功的回调函数
   * asy 同步还是异步，默认为异步
   * */
  ajaxSend(url, type, param, fun, asy) {
    $.ajax({   //普通请求
      url: domain + url,
      type: type,
      data: param,
      dataType: 'json',
      async: asy || true,
      success: function (res) {
        if (res&&res.statu == 9) {
          sessionStorage.userInfo = '';
          location.reload();
        } else {
          fun(res);
        }
      },
      error: function () {
        console.log('请求失败');
      }
    })
  },
  ajaxFile(url, type, param, fun) {   //有文件上传时的请求
    $.ajax({
      url: domain + url,
      type: type,
      data: param,
      dataType: 'json',
      async: true,
      processData: false,   //  告诉jquery不要处理发送的数据
      contentType: false,    // 告诉jquery不要设置content-Type请求头
      success: function (res) {
        if (res.statu == 9) {
          sessionStorage.userInfo = '';
          location.reload();
        } else {
          fun(res);
        }
      },
      error: function () {
        console.log('请求失败');
      }
    })
  },
  /*
   * ele   body元素
   * url  请求地址
   * type 请求方式
   * */
  downloadFile(ele, url, type) {   //下载文件
    var $eleForm;
    if ($('#download').length > 0) {
      $eleForm = $('#download');
    } else {
      $eleForm = $("<form method=" + type + " id='download'></form>");
      $(ele).append($eleForm);
    }
    $eleForm.attr("action", domain + url);
    $eleForm.submit();   //提交表单，实现下载
  },

  getRootName() {
    return domain;
  },
  //复制
  /*
   * [{ className: "班级名称", classStuNum: "班级人数", courseTypeName: "科类", majorName: "班级专业", classTypeName: "班级级别" }]
   * */
  copyTableData(ele, data){
    if ($("#bstableCopy").length < 1) {
      var element = "<textarea id='bstableCopy' cols='20' rows='10' style='opacity: 0;position: fixed;'>复制内容</textarea>";
      $(ele).append(element);
    }
    var tablestr = $.jsontotable(data);
    $("#bstableCopy").html(tablestr);
    $("#bstableCopy").select(); // 选择对象
    document.execCommand("Copy");
    alert('复制成功,请粘贴到Excel表格中！');
  },
  //打印
  lodop(tableData) {
    var printCssStr = ".con{text-align:center;color: #343434;}.rostrum{width: 150px;padding: 8px 0;border: 1px solid #343434;margin:20px 0}";
    var tableStr = "<table class='table table=bordered' style='margin-bottom:0'><tr>";
    var hdData = tableData[0],printStyle,printContent;
    for (let [key, obj] of tableData.entries()) {
      if (key == 0) {
        for (let name in hdData) {
          tableStr += "<th>" + hdData[name] + "</th>";
        }
        tableStr += "</tr>";
      } else {
        let tableBody = "<tr>";
        for (let name in hdData) {
          tableBody += "<td>" + obj[name] + "</td>"
        }
        tableBody+="</tr>";
        tableStr += tableBody;
      }
    }
    printStyle = "<style>" + printcss() + printCssStr + "</style>";
    printContent = printStyle + "<body>" + tableStr + "</body>";

    var newWin = window.open("");//新打开一个空窗口
    newWin.document.write(printContent);//将表格添加进新的窗口
    newWin.document.close();//在IE浏览器中使用必须添加这一句
    newWin.focus();//在IE浏览器中使用必须添加这一句

    newWin.print();//打印
    newWin.close();//关闭窗口

    function printcss() {
      var css = ".table-bordered {border: 1px solid #000;}.table {width: 100%;max-width: 100%;margin-bottom: 40px;}.table {border-spacing: 0;border-collapse: collapse;}.table td{border: 1px solid #000;vertical-align: middle;text-align:center;font-size:15px;height:30px;}.table th{padding:6px; vertical-align: top; border: 1px solid #000;text-align:center;  font-weight:bold;  font-size:13px;}.table div{  text-align:center; } .table .w_35{width:35px; }.table .w_100{width:100px; }.editdiv{ width: 100%;  border: 0;  height: 30px;   font-size: 15px;  font-weight: normal;  text-align: center;  line-height:30px; }.ng-table-filters{display:none;};input{width: 100%;border: 0;height:30px;line-height:30px;font-size: 15px;font-weight: normal;text-align: center;}";
      return css;
    }
  }
}
export const fileTypeCheck = (fileName, arr) => {
  const fileArr = arr;
  const fileCheck = fileName.slice(fileName.indexOf('.')).toLowerCase();
  return fileArr.some((value) => {
    if (value == fileCheck) {
      return true;
    } else {
      return false;
    }
  });
};
export const handlerAjaxData = (data) => {
  if (data.statu) {
    return data.data
  } else {
    this.$message({
      message: '请求失败，请重试!',
      type: 'error',
      showClose: true
    });
    return [];
  }
};
export const getFileUrl = (sourceId) => {
  let url;
  if (navigator.userAgent.indexOf("MSIE") >= 1) { // IE
    url = document.getElementById(sourceId).value;
  } else if (navigator.userAgent.indexOf("Firefox") > 0) { // Firefox
    url = window.URL.createObjectURL(document.getElementById(sourceId).files.item(0));
  } else if (navigator.userAgent.indexOf("Chrome") > 0) { // Chrome
    url = window.URL.createObjectURL(document.getElementById(sourceId).files.item(0));
  }
  return url;
};



