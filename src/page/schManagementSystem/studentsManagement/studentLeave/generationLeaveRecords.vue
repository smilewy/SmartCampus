<template>
  <div class="generationLeaveRecords">
    <h3>代请假记录</h3>
    <el-row class="generationLeaveRecords_row">
      <el-form ref="form" :model="form" :inline="true" class="formInline">
        <el-form-item label="创建日期：" class="createTime">
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
          prop="lxTime"
          label="离校时间" sortable>
        </el-table-column>
        <el-table-column
          prop="createTime"
          label="创建时间" sortable>
        </el-table-column>
        <el-table-column
          label="审批状态" sortable>
          <template slot-scope="scope">
            <span v-if="scope.row.state=='0'">待审批</span>
            <span v-if="scope.row.state=='1'">已通过</span>
            <span v-if="scope.row.state=='2'">未通过</span>
          </template>
        </el-table-column>
      </el-table>
    </el-row>
    <el-dialog
      title="代请假记录详情"
      :visible.sync="dialogVisible"
      :modal="false"
      :before-close="handleClose">
      <el-row class="recordDetail">
        <h3>#{{recordMsg.title}}#</h3>
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
            <el-form label-width="100px">
              <el-form-item label="审批人：">
                {{recordMsg.appName}}
              </el-form-item>
              <el-form-item label="审批结果：">
                <span v-if="recordMsg.state=='0'">未审批</span>
                <span v-if="recordMsg.state=='1'">同意</span>
                <span v-if="recordMsg.state=='2'">不同意</span>
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
        selectParam: {
          startTime: '',
          endTime: ''
        },
        form: {
          startTime: '',
          endTime: ''
        },
        dialogVisible: false,
        recordMsg: {},
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
        loading:false
      }
    },
    created: function () {
      this.search();
    },
    methods: {
      search() {
        var self = this;
        self.selectParam.startTime = self.form.startTime ? moment(self.form.startTime).format('YYYY-MM-DD') : '';
        self.selectParam.endTime = self.form.endTime ? moment(self.form.endTime).format('YYYY-MM-DD') : '';
        self.loading=true;
        req.ajaxSend('/school/Studentleave/replaceRecord?type=getLeaveList', 'get', self.selectParam, function (res) {
          self.tableData = res.data;
          self.loading=false;
        })
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
              state: '审批状态'
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
              req.downloadFile('.generationLeaveRecords', '/school/Studentleave/replaceRecord?type=export&startTime=' + this.selectParam.startTime + '&endTime=' + this.selectParam.endTime, 'post')
            } else if (type == 'copy') {
              req.copyTableData('.generationLeaveRecords', sAy);
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
        $.extend(this.recordMsg, this.tableData[idx]);
      }
    }
  }
</script>
<style>
  .generationLeaveRecords {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .generationLeaveRecords h3 {
    font-size: 1.25rem;
  }

  .generationLeaveRecords .generationLeaveRecords_row {
    margin: 2rem 0 0;
  }

  .generationLeaveRecords .searchBtn {
    border-radius: 20px;
    padding: 10px 25px;
  }

  .generationLeaveRecords .el-form--inline .el-form-item {
    margin-right: 2rem;
  }

  .generationLeaveRecords .createTime .el-form-item {
    margin-right: 0;
  }

  .generationLeaveRecords .line {
    text-align: center;
  }

  .generationLeaveRecords .alertsBtn {
    margin: 1.25rem 0;
  }

  .generationLeaveRecords .el-table th, .generationLeaveRecords .el-table td {
    text-align: center;
  }

  .generationLeaveRecords .leaveRecordDetail {
    cursor: pointer;
    border-bottom: 1px dashed #4da1ff;
  }

  .generationLeaveRecords .el-dialog--small {
    width: 600px;
  }

  .generationLeaveRecords .recordDetail h3 {
    font-size: 16px;
    text-align: center;
  }

  .generationLeaveRecords .recordDetail .recordDetail_items {
    border-top: 1px solid #d2d2d2;
  }

  .generationLeaveRecords .recordDetail .recordDetail_items:last-child {
    border-bottom: 1px solid #d2d2d2;
  }

  .generationLeaveRecords .recordDetail .recordDetail_item {
    text-align: center;
    padding: 12px 0;
  }

  .generationLeaveRecords .recordDetail .recordDetail_item + .recordDetail_item {
    border-left: 1px solid #d2d2d2;
  }

  .generationLeaveRecords .recordDetail .recordDetail_row {
    margin: 16px 0;
  }

  .generationLeaveRecords .recordDetail .annex {
    display: inline-block;
    padding: 8px 16px;
    background-color: #4ba8ff;
    color: #fff;
    border-radius: 0 18px 18px 0;
    -webkit-box-shadow: 0 5px 5px 1px #d2d2d2;
    -moz-box-shadow: 0 5px 5px 1px #d2d2d2;
    box-shadow: 0 5px 5px 1px #d2d2d2;
  }

  .generationLeaveRecords .recordDetail .el-form-item {
    margin-bottom: 12px;
  }
</style>
