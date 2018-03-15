<template>
  <div class="substituteRecords">
    <h3>代课申请记录</h3>
    <el-row class="substituteRecords_row">
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
    <el-row class="alertsBtn">
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
          prop="jie"
          min-width="200"
          label="代课节次" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="haveTime"
          min-width="200"
          label="有效期" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="applicantName"
          min-width="150"
          label="代课老师" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="result"
          min-width="120"
          label="审批结果" sortable="custom">
          <template slot-scope="scope">
            <span v-if="scope.row.result=='1'">同意</span>
            <span v-if="scope.row.result=='0'">不同意</span>
          </template>
        </el-table-column>
        <el-table-column
          prop="advice"
          min-width="150"
          label="审批意见" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="appoveName"
          min-width="150"
          label="审批人" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="appoveTime"
          min-width="150"
          label="审批时间" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="createTime"
          min-width="150"
          label="申请时间" sortable="custom">
        </el-table-column>
        <el-table-column
          min-width="150"
          fixed="right"
          label="操作">
          <template slot-scope="scope">
            <span class="leaveRecordDetail" @click="showDetail(scope.$index)">详情</span>
            <span class="leaveRecordOperation" v-if="scope.row.appoveStatu=='1'"
                  @click="operationRecord('delete',scope.$index)">删除</span>
            <span class="leaveRecordOperation" v-if="scope.row.appoveStatu=='0'"
                  @click="operationRecord('withdrawn',scope.$index)">撤回</span>
          </template>
        </el-table-column>
      </el-table>
    </el-row>
    <el-dialog
      title="代课申请详情"
      :visible.sync="dialogVisible"
      :modal="false"
      :before-close="handleClose">
      <el-row class="recordDetail">
        <h4>#申请详情#</h4>
        <el-row class="recordDetail_row">
          <el-table
            :data="[applicationData]"
            border
            style="width: 100%">
            <el-table-column
              prop="jie"
              label="代课节次">
            </el-table-column>
            <el-table-column
              prop="applicantName"
              label="代课教师">
            </el-table-column>
          </el-table>
        </el-row>
        <el-row class="recordDetail_row">
          <span class="annex">审批状态</span>
        </el-row>
        <el-row type="flex" justify="center">
          <el-col :span="20">
            <el-form ref="formDetail" label-width="100px">
              <el-form-item label="代课有效期：">
                <span>{{applicationData.haveTime}}</span>
              </el-form-item>
              <el-form-item label="申请时间：">
                <span>{{applicationData.createTime}}</span>
              </el-form-item>
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
      showDetail(idx){
        this.dialogVisible = true;
        $.extend(this.applicationData, this.tableData[idx]);
      },
      operationRecord(type, ix){
        var self = this, data = {
          tkId: self.tableData[ix].tkId
        };
        if (type == 'delete') {
          self.$confirm('确定删除该记录?', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            req.ajaxSend('/school/classreplacement/dkRecord?type=delete', 'post', data, function (res) {
              if (res.statu == 1) {
                self.vmMsgSuccess('删除成功！');
                self.tableData.splice(ix, 1);
              } else {
                self.vmMsgError(res.message);
              }
            })
          }).catch(() => {
          });
        } else {
          self.$confirm('确定撤回该申请?', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            req.ajaxSend('/school/classreplacement/dkRecord?type=revoke', 'post', data, function (res) {
              if (res.statu == 1) {
                self.vmMsgSuccess('撤回成功！');
                self.tableData.splice(ix, 1);
              } else {
                self.vmMsgError(res.message);
              }
            })
          }).catch(() => {
          });
        }
      },
      handleClose(done) {
        done();
      },
      loadData(data){
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/classreplacement/dkRecord?type=getList', 'get', data, function (res) {
          self.tableData = res.data;
          self.loading = false;
        })
      }
    }
  }
</script>
<style>
  .substituteRecords {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .substituteRecords h3 {
    font-size: 1.25rem;
    display: inline-block;
  }

  .substituteRecords .substituteRecords_row {
    margin: 2rem 0 1.25rem;
  }

  .substituteRecords .searchBtn {
    border-radius: 20px;
    padding: 10px 25px;
  }

  .substituteRecords .alertsBtn {
    margin: 1.25rem 0;
  }

  .substituteRecords .el-form--inline .el-form-item {
    margin-bottom: 0;
    margin-right: 2rem;
  }

  .substituteRecords .line {
    text-align: center;
  }

  .substituteRecords .el-table th, .substituteRecords .el-table td {
    text-align: center;
  }

  .substituteRecords .leaveRecordDetail {
    cursor: pointer;
    color: #4da1ff;
    padding: 0 1rem;
  }

  .substituteRecords .leaveRecordOperation {
    cursor: pointer;
    color: #ff5b5b;
    border-left: 2px solid #d2d2d2;
    padding: 0 1rem;
  }

  .substituteRecords .el-dialog--small {
    width: 600px;
  }

  .substituteRecords .recordDetail {
    height: 400px;
    overflow: auto;
  }

  .substituteRecords .recordDetail h4 {
    font-size: 16px;
    text-align: center;
  }

  .substituteRecords .recordDetail .recordDetail_row {
    margin: 16px 0;
  }

  .substituteRecords .recordDetail .annex {
    display: inline-block;
    padding: 8px 16px;
    background-color: #4ba8ff;
    color: #fff;
    border-radius: 0 18px 18px 0;
    -webkit-box-shadow: 0 5px 5px 1px #d2d2d2;
    -moz-box-shadow: 0 5px 5px 1px #d2d2d2;
    box-shadow: 0 5px 5px 1px #d2d2d2;
  }

  .substituteRecords .recordDetail .el-form-item {
    margin-bottom: 12px;
  }

  .substituteRecords .el-dialog__footer {
    -webkit-box-shadow: 0 -10px 20px -10px #d2d2d2;
    -moz-box-shadow: 0 -10px 20px -10px #d2d2d2;
    box-shadow: 0 -10px 20px -10px #d2d2d2;
  }

  .substituteRecords .result.result_active {
    color: #09baa7;
  }

  .substituteRecords .result.result_active_not {
    color: #ff5b5b;
  }

  .substituteRecords .recordDetail_row .el-table th {
    background-color: #deeefe;
  }

  .substituteRecords .recordDetail_row .el-table__footer-wrapper thead div, .substituteRecords .recordDetail_row .el-table__header-wrapper thead div {
    background-color: #deeefe;
  }

  .substituteRecords .dTime .el-form-item {
    margin-right: 0;
  }

  .substituteRecords .g-fuzzyInput {
    float: right;
  }
</style>
