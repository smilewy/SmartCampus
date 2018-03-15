<template>
  <div class="leaveApproved">
    <el-row type="flex" align="middle">
      <h3>请假审批</h3>
      <span class="l_gap">
        <router-link tag="span" to="/leaveNotApproved" class="leaveApproved_bread">未审批</router-link>
        <span class="leaveApproved_bread active">已审批</span>
      </span>
    </el-row>
    <el-row class="leaveApproved_row">
      <el-form ref="form" :model="form" :rules="formRules" :inline="true" class="formInline">
        <el-form-item label="年级：" prop="gradeid">
          <el-select v-model="form.gradeid" placeholder="请选择年级" class="grade" @change="chooseClass">
            <el-option :label="grade.znName" :value="grade.gradeid" v-for="grade in gradeList"
                       :key="grade.gradeid"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="班级：" prop="classid">
          <el-select v-model="form.classid" placeholder="请选择班级" class="class">
            <el-option :label="data.classname" :value="data.classid" v-for="data in classList"
                       :key="data.classid"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="创建日期：">
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
          <template slot-scope="scope">
            <span class="leaveRecordDetail" @click="showDetail(scope.$index)">{{scope.row.title}}</span>
          </template>
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
          prop="userName"
          label="申请人" sortable>
        </el-table-column>
        <el-table-column
          prop="times"
          label="请假天数" sortable>
        </el-table-column>
        <el-table-column
          prop="createTime"
          label="创建时间" sortable>
        </el-table-column>
        <el-table-column
          label="审批结果">
          <template slot-scope="scope">
            <span v-if="scope.row.state=='0'">未审批</span>
            <span v-if="scope.row.state=='1'">同意</span>
            <span v-if="scope.row.state=='2'">不同意</span>
          </template>
        </el-table-column>
      </el-table>
    </el-row>
    <el-dialog
      title="请假明细详情"
      :visible.sync="dialogVisible"
      :modal="false"
      :before-close="handleClose">
      <el-row class="recordDetail">
        <h4>#{{recordMsg.title}}#</h4>
        <el-row class="recordDetail_row">
          <el-row type="flex" align="middle" class="recordDetail_items">
            <el-col :span="10" class="recordDetail_item">起始时间</el-col>
            <el-col :span="14" class="recordDetail_item">{{recordMsg.startTime}}</el-col>
          </el-row>
          <el-row type="flex" align="middle" class="recordDetail_items">
            <el-col :span="10" class="recordDetail_item">结束时间</el-col>
            <el-col :span="14" class="recordDetail_item">{{recordMsg.endTime}}</el-col>
          </el-row>
          <el-row type="flex" align="middle" class="recordDetail_items">
            <el-col :span="10" class="recordDetail_item">请假天数</el-col>
            <el-col :span="14" class="recordDetail_item">{{recordMsg.times}}</el-col>
          </el-row>
          <el-row type="flex" align="middle" class="recordDetail_items">
            <el-col :span="10" class="recordDetail_item">请假类型</el-col>
            <el-col :span="14" class="recordDetail_item">
              <span v-if="recordMsg.leaveTypeId=='1'">事假</span>
              <span v-if="recordMsg.leaveTypeId=='2'">病假</span>
              <span v-if="recordMsg.leaveTypeId=='3'">其他</span>
            </el-col>
          </el-row>
          <el-row type="flex" align="middle" class="recordDetail_items">
            <el-col :span="10" class="recordDetail_item">请假原因</el-col>
            <el-col :span="14" class="recordDetail_item">{{recordMsg.reason||'--'}}</el-col>
          </el-row>
        </el-row>
        <el-row class="recordDetail_row">
          <span class="annex">审批状态</span>
        </el-row>
        <el-row>
          <el-col :offset="2" :span="20">
            <el-form ref="form" label-width="100px">
              <el-form-item label="审批人：">
                {{recordMsg.appName}}
              </el-form-item>
              <el-form-item label="审批结果：" :class="{'approvalResult':recordMsg.state=='1'}">
                <span v-if="recordMsg.state=='0'">未审批</span>
                <span v-if="recordMsg.state=='1'">同意</span>
                <span v-if="recordMsg.state=='2'">不同意</span>
              </el-form-item>
              <el-form-item label="审批意见：">
                {{recordMsg.advice}}
              </el-form-item>
              <el-form-item label="审批时间：">
                {{recordMsg.appTime}}
              </el-form-item>
            </el-form>
          </el-col>
        </el-row>
      </el-row>
    </el-dialog>
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
          gradeid: '',
          state: ''
        },
        form: {
          startTime: '',
          endTime: '',
          classid: '',
          gradeid: '',
          state: 1
        },
        dialogVisible: false,
        recordMsg: {},
        formRules: {
          classid: [
            {required: true, message: '请选择班级', trigger: 'change'}
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
            for (let name in self.form) {
              if (name == 'startTime' || name == 'endTime') {
                self.selectParam[name] = self.form[name] ? moment(self.form[name]).format('YYYY-MM-DD') : '';
              } else {
                self.selectParam[name] = self.form[name];
              }
            }
            self.loading = true;
            req.ajaxSend('/school/Studentleave/leaveApproval?type=approvalList', 'get', self.selectParam, function (res) {
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
              userName: '申请人',
              times: '请假天数',
              createTime: '创建时间',
              state: '审批结果'
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
                      d[name] = '同意';
                      break;
                    case '2':
                      d[name] = '不同意';
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
              req.downloadFile('.leaveApproved', '/school/Studentleave/leaveApproval?type=export&startTime=' + this.selectParam.startTime + '&endTime=' + this.selectParam.endTime + '&gradeid=' + this.selectParam.gradeid + '&classid=' + this.selectParam.classid + '&state=' + this.selectParam.state, 'post')
            } else if (type == 'copy') {
              req.copyTableData('.leaveApproved', sAy);
            } else {
              req.lodop(sAy);
            }
          } else {
            return false;
          }
        })
      },
      handleClose(done) {
        done();
      },
      showDetail(idx) {
        this.dialogVisible = true;
        this.recordMsg.leaveId = this.tableData[idx].leaveId;
        $.extend(this.recordMsg, this.tableData[idx]);
      },
      setReason() {
        this.recordMsg.advice = this.recordMsg.use;
      }
    }
  }
</script>
<style>
  .leaveApproved {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .leaveApproved h3 {
    font-size: 1.25rem;
    display: inline-block;
  }

  .leaveApproved .l_gap {
    margin-left: 1rem;
  }

  .leaveApproved .leaveApproved_bread {
    padding: 0 1.25rem;
    font-size: 1.125rem;
  }

  .leaveApproved .leaveApproved_bread + .leaveApproved_bread {
    border-left: 2px solid #d2d2d2;
  }

  .leaveApproved .leaveApproved_bread.active {
    color: #4da1ff;
  }

  .leaveApproved .leaveApproved_row {
    margin: 2rem 0 0;
  }

  .leaveApproved .searchBtn {
    border-radius: 20px;
    padding: 10px 25px;
  }

  .leaveApproved .el-form--inline .el-form-item {
    margin-right: 2rem;
  }

  .leaveApproved .createTime {
    width: 30rem;
  }

  .leaveApproved .createTime .el-form-item {
    margin-right: 0;
  }

  .leaveApproved .line {
    text-align: center;
  }

  .leaveApproved .alertsBtn {
    margin: 1.25rem 0;
  }

  .leaveApproved .el-table th, .leaveApproved .el-table td {
    text-align: center;
  }

  .leaveApproved .leaveRecordDetail {
    cursor: pointer;
    border-bottom: 1px dashed #4da1ff;
  }

  .leaveApproved .el-dialog--small {
    width: 600px;
  }

  .leaveApproved .recordDetail h4 {
    font-size: 16px;
    text-align: center;
  }

  .leaveApproved .recordDetail .recordDetail_items {
    border-top: 1px solid #d2d2d2;
  }

  .leaveApproved .recordDetail .recordDetail_items:last-child {
    border-bottom: 1px solid #d2d2d2;
  }

  .leaveApproved .recordDetail .recordDetail_item {
    text-align: center;
    padding: 12px 0;
  }

  .leaveApproved .recordDetail .recordDetail_item + .recordDetail_item {
    border-left: 1px solid #d2d2d2;
  }

  .leaveApproved .recordDetail .recordDetail_row {
    margin: 16px 0;
  }

  .leaveApproved .recordDetail .annex {
    display: inline-block;
    padding: 8px 16px;
    background-color: #4ba8ff;
    color: #fff;
    border-radius: 0 18px 18px 0;
    -webkit-box-shadow: 0 5px 5px 1px #d2d2d2;
    -moz-box-shadow: 0 5px 5px 1px #d2d2d2;
    box-shadow: 0 5px 5px 1px #d2d2d2;
  }

  .leaveApproved .recordDetail .el-form-item {
    margin-bottom: 12px;
  }

  .leaveApproved .recordDetail .approvalResult {
    color: #09baa7;
  }

  .leaveApproved .grade, .leaveApproved .class {
    width: 10rem;
  }
</style>
