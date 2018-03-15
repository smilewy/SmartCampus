<template>
  <div class="abnormalMotionCount">
    <h3>异动统计</h3>
    <el-row class="abnormalMotionCount_row">
      <el-form ref="form" :inline="true" :model="form" label-width="90px"
               class="abnormalMotionCount_form">
        <el-form-item label="更新时间：" class="timeChoose">
          <el-col :span="11">
            <el-form-item>
              <el-date-picker type="date" :editable="false" placeholder="选择日期" v-model="form.starttime"
                              :picker-options="pickerBeginDateBefore"
                              style="width: 100%;"></el-date-picker>
            </el-form-item>
          </el-col>
          <el-col class="line" :span="2">-</el-col>
          <el-col :span="11">
            <el-form-item>
              <el-date-picker type="date" :editable="false" placeholder="选择时间" v-model="form.endtime"
                              :picker-options="pickerBeginDateAfter"
                              style="width: 100%;"></el-date-picker>
            </el-form-item>
          </el-col>
        </el-form-item>
        <el-form-item>
          <el-button icon="el-icon-search" type="primary" @click="onSearch">查询</el-button>
        </el-form-item>
      </el-form>
    </el-row>
    <el-row class="d_line"></el-row>
    <el-row type="flex" justify="end" class="alertsBtn">
      <div class="g-fuzzyInput">
        <el-input
          placeholder="请输入关键字"
          suffix-icon="el-icon-search"
          v-model="selectParam.find"
          @change="goSearch">
        </el-input>
      </div>
    </el-row>
    <el-row class="alertsList">
      <el-table
        :data="tableData"
        style="width: 100%"
        @cell-click="viewList"
        v-loading="loading"
        element-loading-text="拼命加载中"
      >
        <el-table-column
          prop="name"
          label="年级/异动类型">
          <template slot-scope="scope">
            <span v-if="scope.row.name!='all'" class="cuAct">{{scope.row.name}}</span>
            <span v-if="scope.row.name=='all'" class="cuAct">合计</span>
          </template>
        </el-table-column>
        <el-table-column
          prop="zhuanban"
          label="转班">
          <template slot-scope="scope">
            <span :class="{'active':Number.parseInt(scope.row.zhuanban)!=0}">{{scope.row.zhuanban}}</span>
          </template>
        </el-table-column>
        <el-table-column
          prop="zhuanru"
          label="转入">
          <template slot-scope="scope">
            <span :class="{'active':Number.parseInt(scope.row.zhuanru)!=0}">{{scope.row.zhuanru}}</span>
          </template>
        </el-table-column>
        <el-table-column
          prop="zhuanchu"
          label="转出">
          <template slot-scope="scope">
            <span :class="{'active':Number.parseInt(scope.row.zhuanchu)!=0}">{{scope.row.zhuanchu}}</span>
          </template>
        </el-table-column>
        <el-table-column
          prop="xiuxue"
          label="休学">
          <template slot-scope="scope">
            <span :class="{'active':Number.parseInt(scope.row.xiuxue)!=0}">{{scope.row.xiuxue}}</span>
          </template>
        </el-table-column>
        <el-table-column
          prop="fuxue"
          label="复学">
          <template slot-scope="scope">
            <span :class="{'active':Number.parseInt(scope.row.fuxue)!=0}">{{scope.row.fuxue}}</span>
          </template>
        </el-table-column>
        <el-table-column
          prop="jiedu"
          label="借读">
          <template slot-scope="scope">
            <span :class="{'active':Number.parseInt(scope.row.jiedu)!=0}">{{scope.row.jiedu}}</span>
          </template>
        </el-table-column>
        <el-table-column
          prop="guadu"
          label="挂读">
          <template slot-scope="scope">
            <span :class="{'active':Number.parseInt(scope.row.guadu)!=0}">{{scope.row.guadu}}</span>
          </template>
        </el-table-column>
        <el-table-column
          prop="tuixue"
          label="退学">
          <template slot-scope="scope">
            <span :class="{'active':Number.parseInt(scope.row.tuixue)!=0}">{{scope.row.tuixue}}</span>
          </template>
        </el-table-column>
      </el-table>
    </el-row>
    <el-dialog
      title="异动学生名单"
      :visible.sync="dialogVisible"
      :modal="false"
      :before-close="handleClose">
      <el-row class="viewList">
        <el-table
          :data="gradeData"
          style="width: 100%"
          max-height="500"
          border
          v-loading="loading1"
          element-loading-text="拼命加载中"
        >
          <el-table-column
            prop="name"
            label="姓名">
          </el-table-column>
          <el-table-column
            prop="grade"
            label="年级">
          </el-table-column>
          <el-table-column
            prop="className"
            label="班级">
          </el-table-column>
          <el-table-column
            prop="typename"
            label="异动类型">
          </el-table-column>
          <el-table-column
            prop="lastRecordTime"
            label="更新日期">
          </el-table-column>
        </el-table>
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
        gradeData: [],
        selectParam: {
          starttime: '',
          endtime: '',
          find: ''
        },
        form: {
          starttime: '',
          endtime: ''
        },
        dialogVisible: false,
        pickerBeginDateBefore: {
          disabledDate: (time) => {
            let beginDateVal = this.form.endtime;
            if (beginDateVal) {
              return time.getTime() > beginDateVal;
            }
          }
        },
        pickerBeginDateAfter: {
          disabledDate: (time) => {
            let beginDateVal = this.form.starttime;
            if (beginDateVal) {
              return time.getTime() < beginDateVal;
            }
          }
        },
        loading: false,
        loading1: false
      }
    },
    created: function () {
      this.onSearch();
    },
    methods: {
      onSearch() {
        this.selectParam.starttime = this.form.starttime ? moment(this.form.starttime).format('YYYY-MM-DD') : '';
        this.selectParam.endtime = this.form.endtime ? moment(this.form.endtime).format('YYYY-MM-DD') : '';
        this.selectParam.find = '';
        this.loadData(this.selectParam);
      },
      goSearch() {  //查询
        this.selectParam.starttime = this.form.starttime ? moment(this.form.starttime).format('YYYY-MM-DD') : '';
        this.selectParam.endtime = this.form.endtime ? moment(this.form.endtime).format('YYYY-MM-DD') : '';
        this.loadData(this.selectParam);
      },
      viewList(row, column){
        var self = this, isGrade = '', data = {
          starttime: self.selectParam.starttime,
          endtime: self.selectParam.endtime,
          gradeid: row.gradeid,
          typename: ''
        };
        isGrade = column.property == 'name' ? 'all' : column.property;
        data.typename = isGrade;
        self.dialogVisible = true;
        self.loading1 = true;
        req.ajaxSend('/school/Transaction/statistics/type/mingdan', 'post', data, function (res) {
          self.gradeData = res;
          self.loading1 = false;
        })
      },
      handleClose(done) {
        done();
      },
      loadData(data){
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/Transaction/statistics/type/all/', 'post', data, function (res) {
          self.tableData = res;
          self.loading = false;
        })
      }
    }
  }
</script>
<style>
  .abnormalMotionCount {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .abnormalMotionCount .abnormalMotionCount_row {
    margin-top: 2rem;
  }

  .abnormalMotionCount .alertsBtn {
    margin: 1.25rem 0;
  }

  .abnormalMotionCount .alertsList .el-table th, .abnormalMotionCount .alertsList .el-table td {
    text-align: center;
  }

  .abnormalMotionCount h3 {
    font-size: 1.25rem;
    color: #4e4e4e;
    display: inline-block;
  }

  .abnormalMotionCount .el-form--inline .el-form-item {
    margin-right: 2rem;
  }

  .abnormalMotionCount .timeChoose .el-form-item {
    margin-right: 0;
  }

  .abnormalMotionCount .line {
    text-align: center;
  }

  .abnormalMotionCount .abnormalMotionCount_form .el-button {
    border-radius: 20px;
    padding: 10px 25px;
  }

  .abnormalMotionCount .viewList .el-table th {
    background-color: #deeefe;
  }

  .abnormalMotionCount .viewList .el-table__footer-wrapper thead div, .abnormalMotionCount .viewList .el-table__header-wrapper thead div {
    background-color: #deeefe;
  }

  .abnormalMotionCount .viewList .el-table th, .abnormalMotionCount .viewList .el-table td {
    text-align: center;
  }

  .abnormalMotionCount .alertsList .active {
    color: #4da1ff;
  }

  .abnormalMotionCount .cuAct {
    cursor: pointer;
  }
</style>
