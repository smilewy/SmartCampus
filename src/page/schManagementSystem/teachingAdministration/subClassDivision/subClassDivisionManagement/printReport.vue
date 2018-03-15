<template>
  <div class="printReport">
    <el-row type="flex" align="middle" class="subClassDivision_title">
      <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
        src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
        alt=""><span class="returnTxt">返回流程图</span></el-button>
      <h3>打印报表</h3>
    </el-row>
    <!--<el-row>
      <el-form :inline="true" class="formInline">
        <el-form-item label="报表类型：">
          <el-select v-model="reportType" placeholder="请选择" class="reportType">
            <el-option
              v-for="item in options"
              :key="item.value"
              :label="item.label"
              :value="item.value">
            </el-option>
          </el-select>
        </el-form-item>
      </el-form>
    </el-row>-->
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
    <el-row
      v-loading="loading"
      element-loading-text="拼命加载中">
      <el-row class="volunteerConfirmList_row" v-for="(data,ix) in tableData" :key="ix">
        <el-row class="listTitle">
          <p>新班学生名单</p>
          <p>{{data.grade}} {{data.className}} {{data.bm}}（<span class="pNumber">{{data.total}}人</span>）</p>
        </el-row>
        <el-row :gutter="40" type="flex" justify="center">
          <el-col :span="10">
            <el-table
              :data="data.tableDataL"
              style="width: 100%"
              border
            >
              <el-table-column
                prop="proSerialNumber"
                min-width="100"
                label="班级序号">
              </el-table-column>
              <el-table-column
                prop="name"
                min-width="100"
                label="姓名">
              </el-table-column>
              <el-table-column
                prop="sex"
                min-width="100"
                label="性别">
              </el-table-column>
              <el-table-column
                prop="branch"
                min-width="100"
                label="科类">
              </el-table-column>
              <el-table-column
                prop="major"
                min-width="100"
                label="专业">
              </el-table-column>
              <el-table-column
                prop="preClass"
                min-width="100"
                label="原班级">
              </el-table-column>
            </el-table>
          </el-col>
          <el-col :span="10" v-show="data.tableDataR.length!=0">
            <el-table
              :data="data.tableDataR"
              style="width: 100%"
              border
            >
              <el-table-column
                prop="proSerialNumber"
                min-width="100"
                label="班级序号">
              </el-table-column>
              <el-table-column
                prop="name"
                min-width="100"
                label="姓名">
              </el-table-column>
              <el-table-column
                prop="sex"
                min-width="100"
                label="性别">
              </el-table-column>
              <el-table-column
                prop="branch"
                min-width="100"
                label="科类">
              </el-table-column>
              <el-table-column
                prop="major"
                min-width="100"
                label="专业">
              </el-table-column>
              <el-table-column
                prop="preClass"
                min-width="100"
                label="原班级">
              </el-table-column>
            </el-table>
          </el-col>
        </el-row>
      </el-row>
      <el-row class="volunteerConfirmList_row">
        <el-row class="listTitle">
          <p>未参与分班学生名单</p>
          <p>{{tableDataNot.className}}（<span class="pNumber">{{tableDataNot.total}}人</span>）</p>
        </el-row>
        <el-row :gutter="40" type="flex" justify="center">
          <el-col :span="10">
            <el-table
              :data="tableDataNot.notJoinPersonL"
              style="width: 100%"
              border
            >
              <el-table-column
                min-width="100"
                label="班级序号">
                <template slot-scope="scope">--</template>
              </el-table-column>
              <el-table-column
                prop="name"
                min-width="100"
                label="姓名">
              </el-table-column>
              <el-table-column
                prop="sex"
                min-width="100"
                label="性别">
              </el-table-column>
              <el-table-column
                prop="branch"
                min-width="100"
                label="科类">
              </el-table-column>
              <el-table-column
                prop="major"
                min-width="100"
                label="专业">
              </el-table-column>
              <el-table-column
                prop="preClass"
                min-width="100"
                label="原班级">
              </el-table-column>
            </el-table>
          </el-col>
          <el-col :span="10" v-if="tableDataNot.notJoinPersonR.length!=0">
            <el-table
              :data="tableDataNot.notJoinPersonR"
              style="width: 100%"
              border
            >
              <el-table-column
                min-width="100"
                label="班级序号">
                <template slot-scope="scope">--</template>
              </el-table-column>
              <el-table-column
                prop="name"
                min-width="100"
                label="姓名">
              </el-table-column>
              <el-table-column
                prop="sex"
                min-width="100"
                label="性别">
              </el-table-column>
              <el-table-column
                prop="branch"
                min-width="100"
                label="科类">
              </el-table-column>
              <el-table-column
                prop="major"
                min-width="100"
                label="专业">
              </el-table-column>
              <el-table-column
                prop="preClass"
                min-width="100"
                label="原班级">
              </el-table-column>
            </el-table>
          </el-col>
        </el-row>
      </el-row>
    </el-row>
  </div>
</template>
<script>
  import req from '@/assets/js/common'

  export default {
    data() {
      return {
        tableData: [],
        tableDataNot: {
          notJoinPersonR: [],
          notJoinPersonL: []
        },
        loading: false
      }
    },
    created: function () {
      var self = this, data = {
        planId: self.$route.params.planId
      };
      self.loading = true;
      req.ajaxSend('/school/DivideBranch/printReport', 'post', data, function (res) {
        self.loading = false;
        let len = res.not.stu.length, b = (len % 2 == 0 ? len / 2 : Number.parseInt(len / 2) + 1);
        res.not.notJoinPersonL = res.not.stu.slice(0, b);
        res.not.notJoinPersonR = res.not.stu.slice(b, len);
        for (let obj of res.data) {
          let l = obj.stu.length, a = (l % 2 == 0 ? l / 2 : Number.parseInt(l / 2) + 1);
          obj.tableDataL = obj.stu.slice(0, a);
          obj.tableDataR = obj.stu.slice(a, l);
        }
        self.tableData = res.data;
        self.tableDataNot = res.not;
      })
    },
    methods: {
      returnFlowchart() {
        this.$router.push('/subClassDivisionHome');
      },
      operationData(type){
        var con = '';
        for (let obj of this.tableData) {
          var tablestr = '<table class="table table=bordered" style="margin-bottom:30px"><thead>',
            tableThTr = '<tr><th colspan="6">', tableTd = '', tTr = '';
          tableThTr += obj.grade + ' ' + obj.className + ' ' + obj.bm + ' （' + obj.total + '人）' + '</th></tr><tr><th>班级序号</th><th>姓名</th><th>性别</th><th>科类</th><th>专业</th><th>原班级</th></tr>';
          for (let mbj of obj.stu) {
            tTr += '<tr><td>' + (mbj.proSerialNumber || '') + '</td><td>' + (mbj.name || '') + '</td><td>' + (mbj.sex || '') + '</td><td>' + (mbj.branch || '') + '</td><td>' + (mbj.major || '') + '</td><td>' + (mbj.preClass || '') + '</td></tr>';
          }
          tableTd += tTr;
          tablestr += tableThTr + '</thead><tbody>';
          tablestr += tableTd;
          tablestr += '</tbody></table>';
          con += tablestr;
        }
        var tablestr1 = '<table class="table table=bordered" style="margin-bottom:30px"><thead>',
          tableThTr1 = '<tr><th colspan="6">', tableTd1 = '', tTr1 = '';
        tableThTr1 += this.tableDataNot.className + ' （' + this.tableDataNot.total + '人）' + '</th></tr><tr><th>班级序号</th><th>姓名</th><th>性别</th><th>科类</th><th>专业</th><th>原班级</th></tr>';
        for (let mbj of this.tableDataNot.stu) {
          tTr1 += '<tr><td>' + (mbj.proSerialNumber || '') + '</td><td>' + (mbj.name || '') + '</td><td>' + (mbj.sex || '') + '</td><td>' + (mbj.branch || '') + '</td><td>' + (mbj.major || '') + '</td><td>' + (mbj.preClass || '') + '</td></tr>';
        }
        tableTd1 += tTr1;
        tablestr1 += tableThTr1 + '</thead><tbody>';
        tablestr1 += tableTd1;
        tablestr1 += '</tbody></table>';
        con += tablestr1;
        if (type == 'copy') {
          if ($("#bstableCopy").length < 1) {
            var element = "<textarea id='bstableCopy' cols='20' rows='10' style='opacity: 0;position: fixed;'>复制内容</textarea>";
            $('.printReport').append(element);
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

          function printcss() {
            var css = ".table-bordered {border: 1px solid #000;}.table {width: 100%;max-width: 100%;margin-bottom: 40px;}.table {border-spacing: 0;border-collapse: collapse;}.table td{border: 1px solid #000;vertical-align: middle;text-align:center;font-size:15px;height:30px;}.table th{padding:6px; vertical-align: top; border: 1px solid #000;text-align:center;  font-weight:bold;  font-size:13px;}.table div{  text-align:center; } .table .w_35{width:35px; }.table .w_100{width:100px; }.editdiv{ width: 100%;  border: 0;  height: 30px;   font-size: 15px;  font-weight: normal;  text-align: center;  line-height:30px; }.ng-table-filters{display:none;};input{width: 100%;border: 0;height:30px;line-height:30px;font-size: 15px;font-weight: normal;text-align: center;}";
            return css;
          }
        }
      }
    }
  }
</script>
<style>
  .printReport .volunteerConfirmList_row {
    margin-bottom: 3.5rem;
  }

  .printReport .alertsBtn {
    margin: 1.25rem 0;
  }

  .printReport .el-table th {
    background-color: #deeefe;
    height: 2.5rem;
  }

  .printReport .el-table td {
    height: 2.5rem;
    font-size: .875rem;
  }

  .printReport .el-table__footer-wrapper thead div, .printReport .el-table__header-wrapper thead div {
    background-color: #deeefe;
    color: #282828;
  }

  .printReport .listTitle {
    text-align: center;
    margin-bottom: 1.5rem;
  }

  .printReport .listTitle > p:first-child {
    font-size: 1.5rem;
  }

  .printReport .listTitle > p:last-child {
    font-size: 1.25rem;
    margin-top: 1rem;
  }

  .printReport .reportType {
    width: 12.5rem;
  }

  .printReport .pNumber {
    color: #4da1ff;
  }
</style>
