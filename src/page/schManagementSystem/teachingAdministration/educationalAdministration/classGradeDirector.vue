<template>
  <div class="classGradeDirector">
    <h3>班/年级主任</h3>
    <el-row :gutter="60">
      <el-col :span="10">
        <el-row class="classGradeDirector_row">
          <el-form :inline="true">
            <el-form-item label="年级：">
              <el-select v-model="selectParam.gradeid" placeholder="请选择" class="grade">
                <el-option
                  v-for="item in gradeList"
                  :key="item.gradeid"
                  :label="item.znName"
                  :value="item.gradeid">
                </el-option>
              </el-select>
            </el-form-item>
            <el-form-item>
              <el-button type="primary" icon="el-icon-search" class="search" @click="search">查询</el-button>
            </el-form-item>
          </el-form>
        </el-row>
        <el-row>
          <el-row class="alertsList">
            <el-table
              :data="headteacherData"
              max-height="600"
              style="width: 100%"
            >
              <el-table-column
                prop="name"
                label="年级/班级">
              </el-table-column>
              <el-table-column
                label="年级主任/班主任">
                <template slot-scope="scope">
                  <div class="teacherName" :class="{'edit':scope.row.checked}" @click="edit(scope.$index)">
                    {{scope.row.userName||'- -'}}
                  </div>
                </template>
              </el-table-column>
            </el-table>
          </el-row>
        </el-row>
      </el-col>
      <el-col :span="14">
        <el-row type="flex" align="middle" class="classGradeDirector_row teacherTip">
          操作方式：先点击须填入班主任的单元格，再选择右侧表格中的教师姓名。
        </el-row>
        <el-row type="flex" align="middle" class="teacherHeader">
          <el-col :span="10">
            <el-button type="primary" class="teacherTitle" @click="openList">班主任、年级主任一览表</el-button>
          </el-col>
          <el-col :span="14">
            <div class="g-fuzzyInput">
              <el-input
                placeholder="请输入信息"
                suffix-icon="el-icon-search"
                v-model="teacherParam.valueData"
                @change="goSearch">
              </el-input>
            </div>
          </el-col>
        </el-row>
        <el-row class="teacherList">
          <el-table
            :data="tableData"
            style="width: 100%"
            max-height="600"
            border
            @sort-change="sort"
            v-loading="loading"
            element-loading-text="拼命加载中"
          >
            <el-table-column
              type="index"
              width="120"
              label="序号">
            </el-table-column>
            <el-table-column
              prop="jobNumber"
              min-width="150"
              label="工号" sortable="custom">
            </el-table-column>
            <el-table-column
              prop="post"
              min-width="120"
              label="职位" sortable="custom">
            </el-table-column>
            <el-table-column
              prop="name"
              min-width="120"
              label="姓名" sortable="custom">
              <template slot-scope="scope">
                <span class="setName" @click="setTeacherName(scope.$index)">{{scope.row.name}}</span>
              </template>
            </el-table-column>
            <el-table-column
              prop="phone"
              min-width="150"
              label="联系电话" sortable="custom">
            </el-table-column>
          </el-table>
        </el-row>
      </el-col>
    </el-row>
    <el-row class="createBtn">
      <el-button @click="clear">清空</el-button>
      <el-button type="primary" @click="save">保存</el-button>
    </el-row>
    <el-dialog
      title="班级主任、年级主任一览表"
      :visible.sync="dialogVisible"
      :modal="false"
      :before-close="handleClose">
      <el-row>
        <el-row type="flex" align="middle" class="alertsBtn">
          <el-button class="delete" title="导出" @click="operationCheckData('out')">
            <img class="delete_unactive"
                 src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png"
                 alt="">
            <img class="delete_active"
                 src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png"
                 alt="">
          </el-button>
          <el-button-group class="secBtn-group">
            <el-button class="filt" title="复制" @click="operationCheckData('copy')">
              <img class="filt_unactive"
                   src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png"
                   alt="">
              <img class="filt_active"
                   src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png"
                   alt="">
            </el-button>
            <el-button class="delete" title="打印" @click="operationCheckData('print')">
              <img class="delete_unactive"
                   src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png"
                   alt="">
              <img class="delete_active"
                   src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png"
                   alt="">
            </el-button>
          </el-button-group>
        </el-row>
        <el-row class="teacherList">
          <el-table
            :data="checklist.data"
            max-height="500"
            style="width:100%;"
            border
            v-loading="loading1"
            element-loading-text="拼命加载中"
          >
            <el-table-column
              :label="checkData.gradeName" v-for="(checkData,index) in checklist.grade" :key="index">
              <el-table-column
                :prop="'class'+checkData.gradeId"
                label="名称">
              </el-table-column>
              <el-table-column
                :prop="'teacherName'+checkData.gradeId"
                label="班主任">
              </el-table-column>
            </el-table-column>
          </el-table>
        </el-row>
      </el-row>
    </el-dialog>
  </div>
</template>
<script>
  import req from '@/assets/js/common'

  export default {
    data() {
      return {
        gradeList: [],
        selectParam: {
          gradeid: ''
        },
        teacherLeftParamAct: {
          gradeId: ''
        },
        teacherParam: {
          type: 'getTeacherList',
          sort: '',
          sortType: '',
          valueData: ''
        },
        tableData: [],
        headteacherData: [],
        checklist: [],
        actIdx: '',
        dialogVisible: false,
        loading:false,
        loading1:false
      }
    },
    created: function () {
      var self = this;
      req.ajaxSend('/school/Educational/getSubjectList?type=getGradeList', 'get', '', function (res) {
        self.gradeList = res.data;
      });
      self.loadData(self.teacherParam);
    },
    methods: {
      search() {  //请求年级班主任
        var self = this;
        if (!self.selectParam.gradeid) {
          self.vmMsgWarning('请选择年级！');
          return false;
        }
        self.teacherLeftParamAct.gradeId = self.selectParam.gradeid;
        req.ajaxSend('/school/Educational/classHeadAndGradeHead?type=getList', 'get', self.selectParam, function (res) {
          for (let obj of res.data) {
            obj.checked = false;
          }
          self.headteacherData = res.data;
        });
      },
      goSearch() {  //查询教师表
        this.teacherParam.sort = '';
        this.teacherParam.sortType = '';
        this.loadData(this.teacherParam);
      },
      sort(column) {
        this.teacherParam.sort = column.order || '';
        this.teacherParam.sortType = column.prop || '';
        this.loadData(this.teacherParam);
      },
      openList() {  //打开班主任、年级主任一览表
        var self = this;
        self.dialogVisible = true;
        self.loading1=true;
        req.ajaxSend('/school/Educational/classHeadAndGradeHead?type=classHeadAndGradeHeader', 'get', '', function (res) {
          self.checklist = res;
          self.loading1=false;
        })
      },
      handleClose(done) {
        done();
      },
      edit(idx) {
        for (let obj of this.headteacherData) {
          obj.checked = false;
        }
        this.headteacherData[idx].checked = true;
        this.actIdx = idx;
      },
      setTeacherName(idx) {
        if (typeof this.actIdx != 'string') {
          this.headteacherData[this.actIdx].userName = this.tableData[idx].name;
          this.headteacherData[this.actIdx].userId = this.tableData[idx].id;
        }
      },
      operationCheckData(type) {
        var tablestr = '<table class="table table=bordered" style="margin-bottom:0"><thead>';
        let tableThTr = '<tr>', tableTd = '';
        for (let obj of this.checklist.grade) {
          tableThTr += '<th colspan="2">' + obj.gradeName + '</th>';
        }
        for (let obj of this.checklist.data) {
          let tTr = '<tr>';
          for (let mbj of this.checklist.grade) {
            tTr += '<td>' + (obj['class' + mbj.gradeId] || '') + ' </td><td>' + (obj['teacherName' + mbj.gradeId] || '') + ' </td>'
          }
          tTr += '</tr>';
          tableTd += tTr;
        }
        tableThTr += '</tr>';
        tablestr += tableThTr + '</thead><tbody>';
        tablestr += tableTd;
        tablestr += '</tbody></table>';
        if (type == 'out') {
          req.downloadFile('.classGradeDirector', '/school/educational/classHeadAndGradeHead?type=export', 'post');
        } else if (type == 'copy') {
          if ($("#bstableCopy").length < 1) {
            var element = "<textarea id='bstableCopy' cols='20' rows='10' style='opacity: 0;position: fixed;'>复制内容</textarea>";
            $('.classGradeDirector').append(element);
          }
          $("#bstableCopy").html(tablestr);
          $("#bstableCopy").select(); // 选择对象
          document.execCommand("Copy");
          alert('复制成功,请粘贴到Excel表格中！');
        } else {
          var printCssStr = ".con{text-align:center;color: #343434;}.rostrum{width: 150px;padding: 8px 0;border: 1px solid #343434;margin:20px 0}";
          var printStyle, printContent;
          printStyle = "<style>" + printcss() + printCssStr + "</style>";
          printContent = printStyle + "<body>" + tablestr + "</body>";

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
      },
      save() {
        var self = this, data = {
          gradeid: self.selectParam.gradeid,
          data: self.headteacherData
        };
        req.ajaxSend('/school/Educational/classHeadAndGradeHead?type=create', 'post', data, function (res) {
          if (res.statu == 1) {
            self.vmMsgSuccess('保存成功！');
          } else {
            self.vmMsgError(res.message);
          }
        })
      },
      clear() {
        var self = this;
        if (self.headteacherData.length == 0) {
          self.vmMsgWarning('没有可清空的数据！');
          return false;
        }
        self.$confirm('可能有数据还未保存，确定清空数据?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/Educational/classHeadAndGradeHead?type=clearsHeader', 'post', self.teacherLeftParamAct, function (res) {
            if (res.statu == 1) {
              self.vmMsgSuccess('清空成功！');
              for (let obj of self.headteacherData) {
                obj.userName = '';
                obj.userId = '';
              }
            } else {
              self.vmMsgError(res.message);
            }
          })
        }).catch(() => {
        });
      },
      loadData(data) {
        var self = this;
        self.loading=true;
        req.ajaxSend('/school/Educational/getSubjectList', 'get', data, function (res) {
          self.tableData = res.data;
          self.loading=false;
        })
      }
    }
  }
</script>
<style>
  .classGradeDirector {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .classGradeDirector h3 {
    font-size: 1.25rem;
  }

  .classGradeDirector .classGradeDirector_row {
    margin: 1.25rem 0;
  }

  .classGradeDirector .el-form--inline .el-form-item {
    margin-bottom: 0;
  }

  .classGradeDirector .el-table td, .classGradeDirector .el-table th {
    text-align: center;
  }

  .classGradeDirector .grade {
    width: 8.75rem;
  }

  .classGradeDirector .createBtn {
    text-align: right;
    margin-top: 2rem;
  }

  .classGradeDirector .search {
    padding: 10px 25px;
    border-radius: 20px;
  }

  .classGradeDirector .createBtn .el-button {
    padding: 10px 30px;
    border-radius: 20px;
  }

  .classGradeDirector .el-form--inline .el-form-item {
    margin-right: 2rem;
  }

  .classGradeDirector .el-dialog--small {
    width: 1000px;
  }

  .classGradeDirector .teacherList thead th {
    background-color: #fff;
    height: 3rem;
  }

  .classGradeDirector .teacherList thead > tr:first-child th {
    background-color: #deeefe;
  }

  .classGradeDirector .teacherList .el-table td {
    height: 3rem;
    font-size: .875rem;
  }

  .classGradeDirector .teacherList .el-table__footer-wrapper thead div, .classGradeDirector .teacherList .el-table__header-wrapper thead div {
    background-color: #fff;
  }

  .classGradeDirector .teacherList .el-table__footer-wrapper thead > tr:first-child div, .classGradeDirector .teacherList .el-table__header-wrapper thead > tr:first-child div {
    background-color: #deeefe;
  }

  .classGradeDirector .teacherTip {
    height: 36px;
    font-size: .875rem;
  }

  .classGradeDirector .teacherHeader {
    height: 3.5rem;
    background-color: #89bcf5;
    padding: 0 1.25rem;
  }

  .classGradeDirector .el-button.teacherTitle {
    background-color: #ff8686;
    color: #fff;
    border: 1px solid #fff;
    border-radius: 30px;
    padding: .5rem 1.5rem;
    font-size: .875rem;
  }

  .classGradeDirector .g-fuzzyInput {
    float: right;
  }

  .classGradeDirector .alertsBtn {
    margin: 0 0 1.25rem 0;
  }

  .classGradeDirector .edit {
    color: #4da1ff;
    border: 1px solid #4da1ff;
  }

  .classGradeDirector .setName {
    cursor: pointer;
  }
</style>
