<template>
  <div class="substitutePendingApproved">
    <el-row type="flex" align="middle">
      <h3>代课申请审批</h3>
      <span class="l_gap">
        <span class="substitutePendingApproved_bread active">待审批</span>
        <router-link tag="span" to="/substituteApproved" class="substitutePendingApproved_bread">已审批</router-link>
        <router-link tag="span" to="/substituteAllApproved" class="substitutePendingApproved_bread">全部</router-link>
      </span>
    </el-row>
    <el-row class="substitutePendingApproved_row">
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
    <el-row type="flex" justify="end" class="substitutePendingApproved_secRow">
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
          min-width="150"
          prop="applicantName"
          label="代课老师" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="applicantName"
          min-width="120"
          label="申请人" sortable="custom">
        </el-table-column>
        <el-table-column
          min-width="120"
          prop="type"
          label="类型" sortable="custom">
          <template slot-scope="scope">
            <span v-if="scope.row.type=='0'">非指定调课</span>
            <span v-if="scope.row.type=='1'">指定调课</span>
            <span v-if="scope.row.type=='2'">代课</span>
            <span v-if="scope.row.type=='3'">班级调课</span>
          </template>
        </el-table-column>
        <el-table-column
          prop="createTime"
          min-width="150"
          label="申请时间" sortable="custom">
        </el-table-column>
        <el-table-column
          min-width="100"
          fixed="right"
          label="操作">
          <template slot-scope="scope">
            <span class="leaveRecordDetail" @click="showDetail(scope.$index)">审批</span>
          </template>
        </el-table-column>
      </el-table>
    </el-row>
    <el-dialog
      title="代课申请审批"
      :visible.sync="dialogVisible"
      :modal="false"
      :before-close="handleClose">
      <el-row class="recordDetail">
        <h4>#审批#</h4>
        <el-row class="recordDetail_row">
          <el-row type="flex" align="middle" class="recordDetail_items">
            <el-col :span="10" class="recordDetail_item">申请人</el-col>
            <el-col :span="14" class="recordDetail_item">{{applicationData.applicantName||'--'}}</el-col>
          </el-row>
          <el-row type="flex" align="middle" class="recordDetail_items">
            <el-col :span="10" class="recordDetail_item">代课节次</el-col>
            <el-col :span="14" class="recordDetail_item">{{applicationData.jie}}</el-col>
          </el-row>
          <el-row type="flex" align="middle" class="recordDetail_items">
            <el-col :span="10" class="recordDetail_item">代课教师</el-col>
            <el-col :span="14" class="recordDetail_item">{{applicationData.applicantName||'--'}}</el-col>
          </el-row>
          <el-row type="flex" align="middle" class="recordDetail_items">
            <el-col :span="10" class="recordDetail_item">代课有效期</el-col>
            <el-col :span="14" class="recordDetail_item">{{applicationData.haveTime}}</el-col>
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
              <el-form-item label="审批结果：">
                <el-switch
                  v-model="recordMsg.result"
                  active-color="#09baa7"
                  inactive-color="#ff4949"
                  active-text="同意"
                  inactive-text="不同意">
                </el-switch>
              </el-form-item>
              <el-form-item label="审批意见：">
                <el-row class="approvalOpinion">
                  <el-input type="textarea" resize="none" max="100" placeholder="请输入审批意见"
                            v-model="recordMsg.advice"></el-input>
                  <p class="limitNum"><span>{{recordMsg.advice.length}}</span><span>/100</span></span></p>
                </el-row>
                <el-select v-model="recordMsg.use" placeholder="常用审批意见" style="width:100%;" @change="setAdvice">
                  <el-option label="同意" value="同意"></el-option>
                  <el-option label="不同意" value="不同意"></el-option>
                </el-select>
              </el-form-item>
            </el-form>
          </el-col>
        </el-row>
      </el-row>
      <span slot="footer" class="dialog-footer">
    <el-button type="primary" @click="save">提交</el-button>
  </span>
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
      setAdvice(){   //选择常用意见
        this.recordMsg.advice = this.recordMsg.use;
      },
      save(){
        var self = this, data = {
          advice: self.recordMsg.advice,
          result: self.recordMsg.result ? 1 : 0,
          tkId: self.recordMsg.tkId
        };
        req.ajaxSend('/school/classreplacement/dKsP?type=approval', 'get', data, function (res) {
          if (res.statu == 1) {
            self.vmMsgSuccess('审批成功！');
            self.dialogVisible = false;
            self.loadData(self.selectParam);
          } else {
            self.vmMsgError(res.message);
          }
        })
      },
      loadData(data){
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/classreplacement/dKsP?type=getNosp', 'get', data, function (res) {
          self.tableData = res.data;
          self.loading = false;
        })
      }
    }
  }
</script>
<style>
  .substitutePendingApproved {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .substitutePendingApproved h3 {
    font-size: 1.25rem;
    display: inline-block;
  }

  .substitutePendingApproved .l_gap {
    margin-left: 1rem;
  }

  .substitutePendingApproved .substitutePendingApproved_bread {
    padding: 0 1.25rem;
    font-size: 1.125rem;
    cursor: pointer;
  }

  .substitutePendingApproved .substitutePendingApproved_bread + .substitutePendingApproved_bread {
    border-left: 2px solid #d2d2d2;
  }

  .substitutePendingApproved .substitutePendingApproved_bread.active {
    color: #4da1ff;
  }

  .substitutePendingApproved .substitutePendingApproved_row {
    margin: 2rem 0 1.25rem;
  }

  .substitutePendingApproved .substitutePendingApproved_secRow {
    margin: 1.25rem 0;
  }

  .substitutePendingApproved .searchBtn {
    border-radius: 20px;
    padding: 10px 25px;
  }

  .substitutePendingApproved .el-form--inline .el-form-item {
    margin-right: 2rem;
    margin-bottom:0;
  }

  .substitutePendingApproved .line {
    text-align: center;
  }

  .substitutePendingApproved .el-table th, .substitutePendingApproved .el-table td {
    text-align: center;
  }

  .substitutePendingApproved .leaveRecordDetail {
    cursor: pointer;
    color: #4da1ff;
  }

  .substitutePendingApproved .el-dialog--small {
    width: 600px;
  }

  .substitutePendingApproved .recordDetail {
    height: 400px;
    overflow: auto;
  }

  .substitutePendingApproved .recordDetail h4 {
    font-size: 16px;
    text-align: center;
  }

  .substitutePendingApproved .recordDetail .recordDetail_items {
    border-top: 1px solid #d2d2d2;
  }

  .substitutePendingApproved .recordDetail .recordDetail_items:last-child {
    border-bottom: 1px solid #d2d2d2;
  }

  .substitutePendingApproved .recordDetail .recordDetail_item {
    text-align: center;
    padding: 12px 0;
  }

  .substitutePendingApproved .recordDetail .recordDetail_item + .recordDetail_item {
    border-left: 1px solid #d2d2d2;
  }

  .substitutePendingApproved .recordDetail .recordDetail_row {
    margin: 16px 0;
  }

  .substitutePendingApproved .recordDetail .annex {
    display: inline-block;
    padding: 8px 16px;
    background-color: #4ba8ff;
    color: #fff;
    border-radius: 0 18px 18px 0;
    -webkit-box-shadow: 0 5px 5px 1px #d2d2d2;
    -moz-box-shadow: 0 5px 5px 1px #d2d2d2;
    box-shadow: 0 5px 5px 1px #d2d2d2;
  }

  .substitutePendingApproved .recordDetail .el-form-item {
    margin-bottom: 12px;
  }

  .substitutePendingApproved .approvalOpinion {
    border: 1px solid #d2d2d2;
    border-radius: 4px;
    padding: 6px 6px 0 6px;
    margin-bottom: 10px;
  }

  .substitutePendingApproved .limitNum {
    font-size: 12px;
    text-align: right;
  }

  .substitutePendingApproved .limitNum > span:first-child {
    color: #ffb400;
  }

  .substitutePendingApproved .el-textarea__inner {
    height: 3.75rem;
    border: none;
    font-family: inherit;
  }

  .substitutePendingApproved .el-dialog__footer {
    -webkit-box-shadow: 0 -10px 20px -10px #d2d2d2;
    -moz-box-shadow: 0 -10px 20px -10px #d2d2d2;
    box-shadow: 0 -10px 20px -10px #d2d2d2;
  }

  .substitutePendingApproved .dTime .el-form-item {
    margin-right: 0;
  }
</style>
