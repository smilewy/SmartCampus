<template>
  <div class="dormitoryPrintReport">
    <el-row type="flex" align="middle">
      <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
        src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
        alt=""><span class="returnTxt">返回流程图</span></el-button>
      <h3>打印报表</h3>
    </el-row>
    <el-row class="dormitoryPrintReport_row">
      <el-form :inline="true" class="formInline">
        <el-form-item label="报表类型：">
          <el-select v-model="reportType" placeholder="请选择" class="reportType" @change="selectData">
            <el-option
              v-for="item in options"
              :key="item.value"
              :label="item.label"
              :value="item.value">
            </el-option>
          </el-select>
        </el-form-item>
      </el-form>
    </el-row>
    <el-row class="d_line"></el-row>
    <el-row type="flex" align="middle" class="alertsBtn">
      <el-button-group>
        <el-button class="filt" title="复制" @click="operationData('copy')">
          <img class="filt_unactive"
               src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png"
               alt="">
          <img class="filt_active"
               src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png"
               alt="">
        </el-button>
        <el-button class="delete" title="打印" @click="operationData('print')">
          <img class="delete_unactive"
               src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png"
               alt="">
          <img class="delete_active"
               src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png"
               alt="">
        </el-button>
      </el-button-group>
    </el-row>
    <el-row class="volunteerConfirmList_row"
            v-loading="loading"
            element-loading-text="拼命加载中">
      <el-row type="flex" justify="center">
        <el-col :span="16" class="typeStyleOne" v-show="reportType==1">
          <el-row class="listTitle">
            <h5>粘贴总名单</h5>
          </el-row>
          <el-table
            :data="tableData"
            style="width: 100%"
            border
          >
            <el-table-column
              prop="stuName"
              label="姓名">
            </el-table-column>
            <el-table-column
              prop="sex"
              label="性别">
            </el-table-column>
            <el-table-column
              prop="name"
              label="宿舍楼名称">
            </el-table-column>
            <el-table-column
              prop="number"
              label="栋号">
            </el-table-column>
            <el-table-column
              prop="dormNumber"
              label="宿舍号">
            </el-table-column>
            <el-table-column
              prop="dormName"
              label="宿舍名称">
            </el-table-column>
          </el-table>
        </el-col>
        <el-col class="alertsList" v-show="reportType==2">
          <el-table
            :data="tableData"
            style="width: 100%"
            @sort-change="sort"
          >
            <el-table-column
              type="index"
              label="序号"
              width="80">
            </el-table-column>
            <el-table-column
              label="姓名"
              prop="stuName"
              min-width="100"
              sortable>
            </el-table-column>
            <el-table-column
              prop="sex"
              label="性别"
              min-width="100"
              sortable>
            </el-table-column>
            <el-table-column
              prop="grade"
              label="年级"
              min-width="100"
              sortable>
            </el-table-column>
            <el-table-column
              prop="class"
              label="班级"
              min-width="100"
              sortable>
            </el-table-column>
            <el-table-column
              prop="phone"
              min-width="150" sortable
              label="手机号">
            </el-table-column>
            <el-table-column
              prop="name" sortable
              min-width="150"
              label="宿舍楼名称">
            </el-table-column>
            <el-table-column
              prop="number" sortable
              min-width="100"
              label="栋号">
            </el-table-column>
            <el-table-column
              prop="floor" sortable
              min-width="100"
              label="楼层">
            </el-table-column>
            <el-table-column
              prop="dormNumber" sortable
              min-width="100"
              label="宿舍号">
            </el-table-column>
            <el-table-column
              prop="teaName" sortable
              min-width="150"
              label="生活老师">
            </el-table-column>
          </el-table>
        </el-col>
        <el-col :span="16" class="typeStyleOne typeStyleTwo" v-show="reportType==3">
          <el-row class="listTitle">
            <h5>各宿舍学生名单</h5>
          </el-row>
          <el-row v-for="(data,ix) in tableData" :key="ix">
            <el-row>
              <h6>生活老师：{{data.name}}</h6>
            </el-row>
            <el-row class="typeStyleTwoList" v-for="(dorm,idx) in data.dorm" :key="idx">
              <p>
                <span class="tipRow">>></span>{{dorm.name}} {{dorm.number}} {{dorm.floor}}
                {{dorm.dormNumber}}（{{dorm.stu.length}}/{{dorm.capacity}}）
                <span v-if="dorm.dormType=='1'">女生宿舍</span>
                <span v-if="dorm.dormType=='2'">男生宿舍</span>
                <span v-if="dorm.dormType=='3'">混合宿舍</span>
                <span v-if="dorm.dormType=='4'">其他</span>
              </p>
              <el-table
                :data="dorm.stu"
                style="width: 100%"
                border
              >
                <el-table-column
                  prop="stuName"
                  label="姓名">
                </el-table-column>
                <el-table-column
                  prop="grade"
                  label="年级">
                </el-table-column>
                <el-table-column
                  prop="class"
                  label="班级">
                </el-table-column>
                <el-table-column
                  prop="sex"
                  label="性别">
                </el-table-column>
                <el-table-column
                  prop="remark"
                  label="备注">
                </el-table-column>
              </el-table>
            </el-row>
          </el-row>
        </el-col>
      </el-row>
    </el-row>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  export default{
    data(){
      return {
        tableData: [],
        options: [{
          value: 1,
          label: '粘贴总名单'
        }, {
          value: 2,
          label: '宿舍分配总名单'
        }, {
          value: 3,
          label: '各宿舍学生名单'
        }],
        reportType: '',
        loading: false
      }
    },
    methods: {
      returnFlowchart(){
        this.$router.go(-1);
      },
      sort(column){
        var self = this;
      },
      selectData(){
        var self = this, data = {
          planId: self.$route.params.planId,
          option: self.reportType
        };
        self.loading = true;
        req.ajaxSend('/school/StudentDorm/reportForm', 'post', data, function (res) {
          self.tableData = res;
          self.loading = false;
        })
      },
      operationData(type){
        let sAy = [], hdData;
        switch (this.reportType) {
          case 1:
            hdData = {
              stuName: '姓名',
              sex: '性别',
              name: '宿舍楼名称',
              number: '栋号',
              dormNumber: '宿舍号',
              dormName: '宿舍名称'
            };
            sAy.push(hdData);
            for (let obj of this.tableData) {
              let d = {};
              for (let name in hdData) {
                d[name] = obj[name] || '';
              }
              sAy.push(d)
            }
            if (type == 'copy') {
              req.copyTableData('.dormitoryPrintReport', sAy);
            } else {
              req.lodop(sAy);
            }
            break;
          case 2:
            hdData = {
              stuName: '姓名',
              sex: '性别',
              grade: '年级',
              class: '班级',
              phone: '手机号',
              name: '宿舍楼名称',
              number: '栋号',
              floor: '楼层',
              dormNumber: '宿舍号',
              teaName: '生活老师'
            };
            sAy.push(hdData);
            for (let obj of this.tableData) {
              let d = {};
              for (let name in hdData) {
                d[name] = obj[name] || '';
              }
              sAy.push(d)
            }
            if (type == 'copy') {
              req.copyTableData('.dormitoryPrintReport', sAy);
            } else {
              req.lodop(sAy);
            }
            break;
          case 3:
            var con = '';
            for (let obj of this.tableData) {
              for (let mbj of obj.dorm) {
                var tablestr = '<table class="table table=bordered" style="margin-bottom:30px"><thead>',
                  tableThTr = '<tr><th colspan="5">', tableTd = '';
                let str = '';
                if (mbj.dormType == 1) {
                  str = '女生宿舍';
                } else if (mbj.dormType == 2) {
                  str = '男生宿舍';
                } else if (mbj.dormType == 3) {
                  str = '混合宿舍';
                } else if (mbj.dormType == 4) {
                  str = '其他';
                }
                tableThTr += '生活老师：' + obj.name + ' ' + mbj.name + ' ' + mbj.number + ' ' + mbj.floor + ' ' + mbj.dormNumber + '（' + mbj.stu.length + '/' + mbj.capacity + '）' + ' ' + str
                  + '</th></tr><tr><th>姓名</th><th>年级</th><th>班级</th><th>性别</th><th>备注</th></tr>';
                for (let nbj of mbj.stu) {
                  tableTd += '<tr><td>' + (nbj.stuName || '') + '</td><td>' + (nbj.grade || '') + '</td><td>' + (nbj.class || '') + '</td><td>' + (nbj.sex || '') + '</td><td>' + (nbj.remark || '') + '</td></tr>';
                }
                tablestr += tableThTr + '</thead><tbody>';
                tablestr += tableTd;
                tablestr += '</tbody></table>';
                con += tablestr;
              }
            }
            if (type == 'copy') {
              if ($("#bstableCopy").length < 1) {
                var element = "<textarea id='bstableCopy' cols='20' rows='10' style='opacity: 0;position: fixed;'>复制内容</textarea>";
                $('.dormitoryPrintReport').append(element);
              }
              $("#bstableCopy").html(con);
              $("#bstableCopy").select(); // 选择对象
              document.execCommand("Copy");
              alert('复制成功,请粘贴到Excel表格中！');
            } else {
              var printCssStr = ".con{text-align:center;color: #343434;}.rostrum{width: 150px;padding: 8px 0;border: 1px solid #343434;margin:20px 0}";
              var printStyle, printContent;
              printStyle = "<style>" + printcss() + printCssStr + "</style>";
              printContent = printStyle + "<body>" + con + "</body>";

              var newWin = window.open("");//新打开一个空窗口
              newWin.document.write(printContent);//将表格添加进新的窗口
              newWin.document.close();//在IE浏览器中使用必须添加这一句
              newWin.focus();//在IE浏览器中使用必须添加这一句

              newWin.print();//打印
              newWin.close();//关闭窗口
            }
            break;
        }
        function printcss() {
          var css = ".table-bordered {border: 1px solid #000;}.table {width: 100%;max-width: 100%;margin-bottom: 40px;}.table {border-spacing: 0;border-collapse: collapse;}.table td{border: 1px solid #000;vertical-align: middle;text-align:center;font-size:15px;height:30px;}.table th{padding:6px; vertical-align: top; border: 1px solid #000;text-align:center;  font-weight:bold;  font-size:13px;}.table div{  text-align:center; } .table .w_35{width:35px; }.table .w_100{width:100px; }.editdiv{ width: 100%;  border: 0;  height: 30px;   font-size: 15px;  font-weight: normal;  text-align: center;  line-height:30px; }.ng-table-filters{display:none;};input{width: 100%;border: 0;height:30px;line-height:30px;font-size: 15px;font-weight: normal;text-align: center;}";
          return css;
        }
      }
    }
  }
</script>
<style>
  .dormitoryPrintReport .dormitoryPrintReport_row {
    margin-top: 2rem;
  }

  .dormitoryPrintReport .volunteerConfirmList_row {
    margin-bottom: 3.5rem;
  }

  .dormitoryPrintReport .alertsBtn {
    margin: 1.25rem 0;
  }

  .dormitoryPrintReport .typeStyleOne .el-table th {
    background-color: #deeefe;
    height: 2.5rem;
  }

  .dormitoryPrintReport .typeStyleOne .el-table td {
    height: 2.5rem;
    font-size: .875rem;
  }

  .dormitoryPrintReport .typeStyleOne .el-table__footer-wrapper thead div, .dormitoryPrintReport .typeStyleOne .el-table__header-wrapper thead div {
    background-color: #deeefe;
    color: #282828;
  }

  .dormitoryPrintReport .listTitle {
    text-align: center;
    margin-bottom: 1.5rem;
  }

  .dormitoryPrintReport .listTitle h5 {
    font-size: 1.5rem;
    font-weight: bold;
  }

  .dormitoryPrintReport .typeStyleTwo h6 {
    font-size: 1.125rem;
    font-weight: bold;
    margin: 2rem 0;
  }

  .dormitoryPrintReport .typeStyleTwo p {
    font-size: 14px;
    margin-bottom: 1.125rem;
  }

  .dormitoryPrintReport .typeStyleTwoList {
    margin-bottom: 2rem;
  }

  .dormitoryPrintReport .typeStyleTwo p .tipRow {
    color: #4da1ff;
    margin-right: .75rem;
  }

  .dormitoryPrintReport .reportType {
    width: 15.625rem;
  }
</style>
