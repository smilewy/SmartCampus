<template>
  <div class="turningClassApproved">
    <el-row type="flex" align="middle">
      <h3>调课申请审批</h3>
      <span class="l_gap">
        <router-link tag="span" to="/turningClassPendingApproved" class="turningClassApproved_bread">待审批</router-link>
        <span class="turningClassApproved_bread active">已审批</span>
        <router-link tag="span" to="/turningClassAllApproved" class="turningClassApproved_bread">全部</router-link>
      </span>
    </el-row>
    <el-row class="turningClassApproved_row">
      <el-form ref="form" :inline="true" :model="form" class="formInline">
        <el-form-item label="申请日期：" class="dTime">
          <el-col :span="11">
            <el-form-item>
              <el-date-picker type="date" :editable="false" placeholder="选择日期" v-model="form.sTime"
                              :picker-options="pickerBeginDateBefore"
                              style="width: 100%;"></el-date-picker>
            </el-form-item>
          </el-col>
          <el-col class="line" :span="2">-</el-col>
          <el-col :span="11">
            <el-form-item>
              <el-date-picker type="date" :editable="false" placeholder="选择日期" v-model="form.eTime"
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
    <el-row type="flex" justify="end" class="turningClassApproved_secRow">
      <div class="g-fuzzyInput">
        <el-input
          placeholder="请输入关键字"
          suffix-icon="el-icon-search"
          v-model="selectParam.valueData"
          @change="goSearch">
        </el-input>
      </div>
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
          type="index"
          width="80"
          label="序号">
        </el-table-column>
        <el-table-column
          prop="applicantName"
          label="申请人" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="advice"
          label="说明" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="createTime"
          label="申请时间" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="result"
          label="审批结果" sortable="custom">
          <template slot-scope="scope">
            <span v-if="scope.row.result=='1'">同意</span>
            <span v-if="scope.row.result=='0'">不同意</span>
          </template>
        </el-table-column>
        <el-table-column
          label="操作">
          <template slot-scope="scope">
            <span class="leaveRecordDetail" @click="showDetail(scope.$index)">详情</span>
          </template>
        </el-table-column>
      </el-table>
    </el-row>
    <el-dialog
      title="调课申请详情"
      :visible.sync="dialogVisible"
      :modal="false"
      :before-close="handleClose">
      <el-row class="recordDetail">
        <h4>#审批结果#</h4>
        <el-row class="recordDetail_row">
          <el-row type="flex" align="middle" class="recordDetail_items">
            <el-col :span="10" class="recordDetail_item">申请人</el-col>
            <el-col :span="14" class="recordDetail_item">{{applicationData.applicantName||'--'}}</el-col>
          </el-row>
          <el-row type="flex" align="middle" class="recordDetail_items">
            <el-col :span="10" class="recordDetail_item">原上课信息</el-col>
            <el-col :span="14" class="recordDetail_item">{{applicationData.old}}</el-col>
          </el-row>
          <el-row type="flex" align="middle" class="recordDetail_items">
            <el-col :span="10" class="recordDetail_item">新上课信息</el-col>
            <el-col :span="14" class="recordDetail_item">{{applicationData.new}}</el-col>
          </el-row>
          <el-row type="flex" align="middle" class="recordDetail_items">
            <el-col :span="10" class="recordDetail_item">申请时间</el-col>
            <el-col :span="14" class="recordDetail_item">{{applicationData.createTime}}</el-col>
          </el-row>
        </el-row>
        <el-row class="recordDetail_row">
          <span class="annex">审批状态</span>
        </el-row>
        <el-row type="flex" justify="center">
          <el-col :span="20">
            <el-form ref="formDetail" label-width="100px">
              <el-form-item label="审批人：">
                <span>{{applicationData.appoveName}}</span>
              </el-form-item>
              <el-form-item label="审批结果：">
                <span class="result result_active_not" v-if="applicationData.result=='0'">不同意</span>
                <span class="result result_active" v-if="applicationData.result=='1'">同意</span>
              </el-form-item>
              <el-form-item label="审批意见：">
                <span>{{applicationData.advice}}</span>
              </el-form-item>
              <el-form-item label="审批时间：">
                <span>{{applicationData.appoveTime}}</span>
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
  export default{
    data(){
      return {
        tableData: [],
        form: {
          sTime: '',
          eTime: ''
        },
        selectParam: {
          sort: '',
          sortData: '',
          startTime: '',
          endTime: '',
          valueData: ''
        },
        dialogVisible: false,
        applicationData: {},
        recordMsg: {
          result: true,
          use: '',
          advice: '',
          tkId: ''
        },
        pickerBeginDateBefore: {
          disabledDate: (time) => {
            let beginDateVal = this.form.eTime;
            if (beginDateVal) {
              return time.getTime() > beginDateVal;
            }
          }
        },
        pickerBeginDateAfter: {
          disabledDate: (time) => {
            let beginDateVal = this.form.sTime;
            if (beginDateVal) {
              return time.getTime() < beginDateVal;
            }
          }
        },
        loading: false
      }
    },
    created: function () {
      this.search();
    },
    methods: {
      search(){
        this.selectParam.startTime = this.form.sTime ? moment(this.form.sTime).format('YYYY-MM-DD') : '';
        this.selectParam.endTime = this.form.eTime ? moment(this.form.eTime).format('YYYY-MM-DD') : '';
        this.selectParam.valueData = '';
        this.selectParam.sortData = '';
        this.selectParam.sort = '';
        this.loadData(this.selectParam);
      },
      goSearch(){
        this.selectParam.startTime = this.form.sTime ? moment(this.form.sTime).format('YYYY-MM-DD') : '';
        this.selectParam.endTime = this.form.eTime ? moment(this.form.eTime).format('YYYY-MM-DD') : '';
        this.selectParam.sortData = '';
        this.selectParam.sort = '';
        this.loadData(this.selectParam);
      },
      sort(column){
        this.selectParam.sortData = column.order || '';
        this.selectParam.sort = column.prop || '';
        this.loadData(this.selectParam);
      },
      handleClose(done) {
        done();
      },
      showDetail(idx){
        this.dialogVisible = true;
        $.extend(this.applicationData, this.tableData[idx]);
        this.recordMsg.tkId = this.tableData[idx].tkId;
      },
      loadData(data){
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/classreplacement/tkApprover?type=haveApprover', 'get', data, function (res) {
          self.tableData = res.data;
          self.loading = false;
        })
      }
    }
  }
</script>
<style>
  .turningClassApproved {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .turningClassApproved h3 {
    font-size: 1.25rem;
    display: inline-block;
  }

  .turningClassApproved .l_gap {
    margin-left: 1rem;
  }

  .turningClassApproved .turningClassApproved_bread {
    padding: 0 1.25rem;
    font-size: 1.125rem;
    cursor: pointer;
  }

  .turningClassApproved .turningClassApproved_bread + .turningClassApproved_bread {
    border-left: 2px solid #d2d2d2;
  }

  .turningClassApproved .turningClassApproved_bread.active {
    color: #4da1ff;
  }

  .turningClassApproved .turningClassApproved_row {
    margin: 2rem 0 1.25rem;
  }

  .turningClassApproved .turningClassApproved_secRow {
    margin: 1.25rem 0;
  }

  .turningClassApproved .searchBtn {
    border-radius: 20px;
    padding: 10px 25px;
  }

  .turningClassApproved .el-form--inline .el-form-item {
    margin-bottom: 0;
    margin-right: 2rem;
  }

  .turningClassApproved .line {
    text-align: center;
  }

  .turningClassApproved .el-table th, .turningClassApproved .el-table td {
    text-align: center;
  }

  .turningClassApproved .leaveRecordDetail {
    cursor: pointer;
    color: #4da1ff;
  }

  .turningClassApproved .el-dialog--small {
    width: 600px;
  }

  .turningClassApproved .recordDetail {
    height: 400px;
    overflow: auto;
  }

  .turningClassApproved .recordDetail h4 {
    font-size: 16px;
    text-align: center;
  }

  .turningClassApproved .recordDetail .recordDetail_items {
    border-top: 1px solid #d2d2d2;
  }

  .turningClassApproved .recordDetail .recordDetail_items:last-child {
    border-bottom: 1px solid #d2d2d2;
  }

  .turningClassApproved .recordDetail .recordDetail_item {
    text-align: center;
    padding: 12px 0;
  }

  .turningClassApproved .recordDetail .recordDetail_item + .recordDetail_item {
    border-left: 1px solid #d2d2d2;
  }

  .turningClassApproved .recordDetail .recordDetail_row {
    margin: 16px 0;
  }

  .turningClassApproved .recordDetail .annex {
    display: inline-block;
    padding: 8px 16px;
    background-color: #4ba8ff;
    color: #fff;
    border-radius: 0 18px 18px 0;
    -webkit-box-shadow: 0 5px 5px 1px #d2d2d2;
    -moz-box-shadow: 0 5px 5px 1px #d2d2d2;
    box-shadow: 0 5px 5px 1px #d2d2d2;
  }

  .turningClassApproved .recordDetail .el-form-item {
    margin-bottom: 12px;
  }

  .turningClassApproved .el-dialog__footer {
    -webkit-box-shadow: 0 -10px 20px -10px #d2d2d2;
    -moz-box-shadow: 0 -10px 20px -10px #d2d2d2;
    box-shadow: 0 -10px 20px -10px #d2d2d2;
  }

  .turningClassApproved .result.result_active {
    color: #09baa7;
  }

  .turningClassApproved .result.result_active_not {
    color: #ff5b5b;
  }

  .turningClassApproved .dTime .el-form-item {
    margin-right: 0;
  }
</style>
