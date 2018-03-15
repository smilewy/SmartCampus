<template>
  <div class="leaveSchoolConfirmation">
    <h3>离校确认</h3>
    <el-row class="leaveSchoolConfirmation_row">
      <el-form ref="form" :model="form" :rules="formRules" :inline="true" class="formInline">
        <el-form-item label="年级：" prop="gradeid" class="grade">
          <el-select v-model="form.gradeid" placeholder="请选择年级" @change="chooseClass">
            <el-option :label="grade.znName" :value="grade.gradeid" v-for="grade in gradeList"
                       :key="grade.gradeid"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="班级：" class="grade" prop="classids">
          <el-select multiple v-model="form.classids" placeholder="请选择班级">
            <el-option :label="data.classname" :value="data.classid" v-for="data in classList"
                       :key="data.classid"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="创建日期：" class="createDate">
          <el-row class="createTime">
            <el-col :span="11">
              <el-form-item>
                <el-date-picker type="date" :editable="false" placeholder="选择日期" v-model="form.startTime"
                                :picker-options="pickerBeginDateBefore"
                                style="width: 100%;"></el-date-picker>
              </el-form-item>
            </el-col>
            <el-col class="line" :span="2">-</el-col>
            <el-col :span="11">
              <el-form-item>
                <el-date-picker type="date" :editable="false" placeholder="选择日期" v-model="form.endTime"
                                :picker-options="pickerBeginDateAfter"
                                style="width: 100%;"></el-date-picker>
              </el-form-item>
            </el-col>
          </el-row>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" icon="el-icon-search" class="searchBtn" @click="search">查询</el-button>
        </el-form-item>
      </el-form>
    </el-row>
    <el-row class="d_line"></el-row>
    <el-row type="flex" align="middle" class="alertsBtn">
      <el-button class="delete" title="导出" @click="operationTable('out')">
        <img class="delete_unactive"
             src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png"
             alt="">
        <img class="delete_active"
             src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png"
             alt="">
      </el-button>
      <el-button-group class="secBtn-group">
        <el-button class="filt" title="复制" @click="operationTable('copy')">
          <img class="filt_unactive"
               src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png"
               alt="">
          <img class="filt_active"
               src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png"
               alt="">
        </el-button>
        <el-button class="delete" title="打印" @click="operationTable('print')">
          <img class="delete_unactive"
               src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png"
               alt="">
          <img class="delete_active"
               src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png"
               alt="">
        </el-button>
      </el-button-group>
    </el-row>
    <el-row class="alertsList">
      <el-table
        :data="tableData"
        style="width: 100%"
        @sort-change="sort"
        v-loading="loading"
        element-loading-text="拼命加载中"
      >
        <el-table-column
          prop="title"
          label="标题" sortable>
        </el-table-column>
        <el-table-column
          prop="leaveTypeId"
          label="类型" sortable>
          <template slot-scope="scope">
            <span v-if="scope.row.leaveTypeId=='1'">事假</span>
            <span v-if="scope.row.leaveTypeId=='2'">病假</span>
            <span v-if="scope.row.leaveTypeId=='3'">其他</span>
          </template>
        </el-table-column>
        <el-table-column
          prop="times"
          label="天数" sortable>
        </el-table-column>
        <el-table-column
          prop="userName"
          label="申请人" sortable>
        </el-table-column>
        <el-table-column
          prop="state"
          label="审批状态">
          <template slot-scope="scope">
            <span v-if="scope.row.state=='0'">待审批</span>
            <span v-if="scope.row.state=='1'">已通过</span>
            <span v-if="scope.row.state=='2'">未通过</span>
          </template>
        </el-table-column>
        <el-table-column
          prop="createTime"
          label="创建时间" sortable>
        </el-table-column>
        <el-table-column
          label="操作">
          <template slot-scope="scope">
            <span v-if="scope.row.leaveState=='0'" class="edit" @click="confirmLeave(scope.$index)">确认离校</span>
            <span v-if="scope.row.leaveState=='1'">已离校</span>
          </template>
        </el-table-column>
      </el-table>
    </el-row>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  import moment from 'moment'

  export default {
    data() {
      return {
        tableData: [],
        gradeList: [],
        classList: [],
        selectParam: {
          startTime: '',
          endTime: '',
          classid: '',
          gradeid: ''
        },
        form: {
          startTime: '',
          endTime: '',
          classids: [],
          gradeid: ''
        },
        formRules: {
          classids: [
            {required: true, type: 'array', message: '请选择班级', trigger: 'change'}
          ],
          gradeid: [
            {required: true, message: '请选择年级', trigger: 'change'}
          ],
        },
        pickerBeginDateBefore: {
          disabledDate: (time) => {
            let beginDateVal = this.form.endTime;
            if (beginDateVal) {
              return time.getTime() > beginDateVal;
            }
          }
        },
        pickerBeginDateAfter: {
          disabledDate: (time) => {
            let beginDateVal = this.form.startTime;
            if (beginDateVal) {
              return time.getTime() < beginDateVal;
            }
          }
        },
        loading: false
      }
    },
    created: function () {
      var self = this;
      req.ajaxSend('/school/Studentleave/leaveApproval?type=getGrade', 'get', '', function (res) {
        self.gradeList = res.data;
      })
    },
    methods: {
      chooseClass() {
        var self = this, data = {
          gradeid: self.form.gradeid
        };
        self.form.classid = '';
        req.ajaxSend('/school/Studentleave/leaveApproval?type=getClass', 'get', data, function (res) {
          self.classList = res.data;
        })
      },
      search() {
        var self = this;
        self.$refs['form'].validate((valid) => {
          if (valid) {
            self.selectParam.startTime = self.form.startTime ? moment(self.form.startTime).format('YYYY-MM-DD') : '';
            self.selectParam.endTime = self.form.endTime ? moment(self.form.endTime).format('YYYY-MM-DD') : '';
            self.selectParam.classid = self.form.classids.join(',');
            self.selectParam.gradeid = self.form.gradeid;
            self.loading = true;
            req.ajaxSend('/school/Studentleave/leaveSchool?type=approvalList', 'get', self.selectParam, function (res) {
              self.tableData = res.data;
              self.loading = false;
            })
          } else {
            return false;
          }
        });
      },
      sort(column) {
      },
      operationTable(type) {
        this.$refs['form'].validate((valid) => {
          if (valid) {
            let sAy = [], hdData;
            hdData = {
              title: '标题',
              leaveTypeId: '类型',
              times: '天数',
              userName: '申请人',
              state: '审批状态',
              createTime: '创建时间'
            };
            sAy.push(hdData);
            for (let obj of this.tableData) {
              let d = {};
              for (let name in hdData) {
                if (name == 'leaveTypeId') {
                  switch (obj[name]) {
                    case '1':
                      d[name] = '事假';
                      break;
                    case '2':
                      d[name] = '病假';
                      break;
                    case '3':
                      d[name] = '其他';
                      break;
                  }
                }
                if (name == 'state') {
                  switch (obj[name]) {
                    case '0':
                      d[name] = '未审批';
                      break;
                    case '1':
                      d[name] = '已审批';
                      break;
                    case '2':
                      d[name] = '未通过';
                      break;
                  }
                } else {
                  d[name] = obj[name] || '';
                }
              }
              sAy.push(d)
            }
            this.selectParam.startTime = moment(this.form.startTime).format('YYYY-MM-DD');
            this.selectParam.endTime = moment(this.form.endTime).format('YYYY-MM-DD');
            if (type == 'out') {
              req.downloadFile('.leaveSchoolConfirmation', '/school/Studentleave/leaveSchool?type=export&startTime=' + this.selectParam.startTime + '&endTime=' + this.selectParam.endTime + '&gradeid=' + this.selectParam.gradeid + '&classid=' + this.selectParam.classid, 'post')
            } else if (type == 'copy') {
              req.copyTableData('.leaveSchoolConfirmation', sAy);
            } else {
              req.lodop(sAy);
            }
          } else {
            return false;
          }
        })
      },
      confirmLeave(idx) {
        var self = this, data = {
          leaveId: self.tableData[idx].leaveId
        };
        self.$confirm('学生是否离校?', '提示', {
          confirmButtonText: '是',
          cancelButtonText: '否',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/Studentleave/leaveSchool?type=leaveSchoolHandle', 'post', data, function (res) {
            if (res.stata == 1) {
              self.vmMsgSuccess('确认成功！');
              self.search();
            } else {
              self.vmMsgError(res.message);
            }
          })
        }).catch(() => {
        });
      }
    }
  }
</script>
<style>
  .leaveSchoolConfirmation {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .leaveSchoolConfirmation h3 {
    font-size: 1.25rem;
  }

  .leaveSchoolConfirmation .leaveSchoolConfirmation_row {
    margin: 2rem 0 0;
  }

  .leaveSchoolConfirmation .searchBtn {
    border-radius: 20px;
    padding: 10px 25px;
  }

  .leaveSchoolConfirmation .el-form--inline .el-form-item {
    margin-right: 2rem;
  }

  .leaveSchoolConfirmation .el-form--inline .grade .el-select {
    width: 8.75rem;
  }

  .leaveSchoolConfirmation .line {
    text-align: center;
  }

  .leaveSchoolConfirmation .alertsBtn {
    margin: 1.25rem 0;
  }

  .leaveSchoolConfirmation .edit {
    color: #4da1ff;
    cursor: pointer;
  }

  .leaveSchoolConfirmation .el-table th, .leaveSchoolConfirmation .el-table td {
    text-align: center;
  }

  .leaveSchoolConfirmation .createTime {
    width: 30rem;
  }

  .leaveSchoolConfirmation .createTime .el-form-item {
    margin-right: 0;
  }
</style>
