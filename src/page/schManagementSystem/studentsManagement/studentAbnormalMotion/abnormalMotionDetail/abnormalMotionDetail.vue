<template>
  <div class="abnormalMotionDetail">
    <h3>异动明细</h3>
    <el-row class="abnormalMotionDetail_row">
      <el-form :inline="true" :model="form" label-width="90px"
               class="abnormalMotionDetail_form">
        <el-form-item label="更新时间：" class="timeChoose">
          <el-col :span="11">
            <el-form-item prop="starttime">
              <el-date-picker type="date" :editable="false" placeholder="选择日期" v-model="form.starttime"
                              :picker-options="pickerBeginDateBefore"
                              style="width: 100%;"></el-date-picker>
            </el-form-item>
          </el-col>
          <el-col class="line" :span="2">-</el-col>
          <el-col :span="11">
            <el-form-item prop="endtime">
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
    <el-row class="d_line abnormalMotionDetail_row"></el-row>
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
        v-loading="loading"
        element-loading-text="拼命加载中"
      >
        <el-table-column
          prop="grade"
          label="年级">
        </el-table-column>
        <el-table-column
          prop="className"
          label="班级">
        </el-table-column>
        <el-table-column
          prop="name"
          label="姓名">
        </el-table-column>
        <el-table-column
          prop="sex"
          label="性别">
        </el-table-column>
        <el-table-column
          prop="typename"
          label="异动类型">
        </el-table-column>
        <el-table-column
          prop="lastRecordTime"
          label="更新时间">
        </el-table-column>
        <el-table-column
          label="操作">
          <template slot-scope="scope">
            <span class="edit" @click="showDetail(scope.$index)">异动详情</span>
          </template>
        </el-table-column>
      </el-table>
    </el-row>
    <el-dialog
      title="异动详情"
      :visible.sync="dialogVisible"
      :modal="false"
      :before-close="handleClose">
      <el-row class="detail">
        <el-row>
          <el-row class="into_row">
            <span class="into_subTitle">学生信息</span>
          </el-row>
          <el-row>
            <el-col :span="12">
              <el-form label-width="100px">
                <el-form-item label="姓名：">
                  <span>{{dataDetail.stInformation[0].name}}</span>
                </el-form-item>
                <el-form-item label="性别：">
                  <span>{{dataDetail.stInformation[0].sex}}</span>
                </el-form-item>
                <el-form-item label="学籍号：">
                  <span>{{dataDetail.stInformation[0].studentCode}}</span>
                </el-form-item>
              </el-form>
            </el-col>
            <el-col :span="12">
              <el-form label-width="100px">
                <el-form-item label="身份证类型：">
                  <span>{{dataDetail.stInformation[0].certificate}}</span>
                </el-form-item>
                <el-form-item label="身份证号：">
                  <span>{{dataDetail.stInformation[0].idCard}}</span>
                </el-form-item>
                <el-form-item label="户籍所在地：">
                  <span>{{dataDetail.stInformation[0].hkAddress}}</span>
                </el-form-item>
              </el-form>
            </el-col>
          </el-row>
        </el-row>
        <el-row>
          <el-row class="into_row">
            <span class="into_subTitle">账号信息</span>
          </el-row>
          <el-row>
            <el-col :span="12">
              <el-form label-width="100px">
                <el-form-item label="学生账号：">
                  <span>{{dataDetail.account.student.number}}</span>
                </el-form-item>
                <el-form-item label="家长账号：">
                  <span>{{dataDetail.account.parent.password}}</span>
                </el-form-item>
              </el-form>
            </el-col>
            <el-col :span="12">
              <el-form label-width="100px">
                <el-form-item label="密码：">
                  <span>{{dataDetail.account.student.password}}</span>
                </el-form-item>
                <el-form-item label="密码：">
                  <span>{{dataDetail.account.parent.password}}</span>
                </el-form-item>
              </el-form>
            </el-col>
          </el-row>
        </el-row>
        <el-row>
          <el-row class="into_row">
            <span class="into_subTitle">{{dataDetail.transaction.typename}}</span>
          </el-row>
          <el-row>
            <el-form label-width="120px">
              <el-form-item :label="data.name+'：'" :key="data.name" v-for="data in dataDetail.transaction.list">
                <span>{{data.data}}</span>
              </el-form-item>
            </el-form>
          </el-row>
        </el-row>
      </el-row>
      <span slot="footer" class="dialog-footer">
    <el-button type="primary" @click="printList">打印异动确认单</el-button>
  </span>
    </el-dialog>
    <el-row class="familyReportPrint" v-show="familyReportPrint">
      <el-row type="flex" align="middle" class="familyReportPrint_head">
        <el-col :span="12" class="familyReportPrint_headL">学生异动确认单</el-col>
        <el-col :span="12" class="familyReportPrint_headR">
          <el-button @click="familyReportOperation('print')">打印</el-button>
          <el-button @click="familyReportOperation('out')">导出word</el-button>
          <span class="familyReportPrint_headClose"><i class="el-icon-circle-close"
                                                       @click="familyReportPrint=false"></i></span>
        </el-col>
      </el-row>
      <el-row class="familyReportPrint_body">
        <el-row class="familyReportPrint_content">
          <div v-html="familyReports.data" id="pagecontent" class="familyData"></div>
        </el-row>
      </el-row>
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
        dataDetail: {
          stInformation: [{}],
          account: {
            student: {},
            parent: {}
          },
          transaction: {
            typename: '',
            list: []
          }
        },
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
        familyReportPrint: false,
        familyReports: {},
        printData: {
          typename: '',
          id: ''
        },
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
        loading: false
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
      handleClose(done) {
        done();
      },
      loadData(data) {
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/Transaction/mingxi/type/all/', 'post', data, function (res) {
          self.tableData = res;
          self.loading = false;
        })
      },
      showDetail(idx) {
        var self = this, data = {
          userid: self.tableData[idx].userid,
          id: self.tableData[idx].id,
          typename: self.tableData[idx].typename
        };
        self.printData.typename = self.tableData[idx].typename;
        self.printData.id = self.tableData[idx].id;
        self.dialogVisible = true;
        req.ajaxSend('/school/Transaction/mingxi/type/xiangqing', 'post', data, function (res) {
          self.dataDetail = res;
        })
      },
      printList() {
        var self = this, data = {
          typename: self.printData.typename,
          id: self.printData.id
        };
        self.dialogVisible = false;
        self.familyReportPrint = true;
        req.ajaxSend('/school/Transaction/operation/type/yidongqueren', 'post', data, function (res) {
          self.familyReports = res;
        })
      },
      familyReportOperation(type) {
        if (type == 'out') {
          req.downloadFile('.abnormalMotionDetail', '/school/Transaction/operation/type/yidongquerenExport?typename=' + this.printData.typename + '&id=' + this.printData.id, 'post');
        } else {

        }
      }
    }
  }
</script>
<style>
  .abnormalMotionDetail {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .abnormalMotionDetail .abnormalMotionDetail_row {
    margin-top: 2rem;
  }

  .abnormalMotionDetail .alertsBtn {
    margin: 1.25rem 0;
  }

  .abnormalMotionDetail .alertsList .el-table th, .abnormalMotionDetail .alertsList .el-table td {
    text-align: center;
  }

  .abnormalMotionDetail h3 {
    font-size: 1.25rem;
    color: #4e4e4e;
    display: inline-block;
  }

  .abnormalMotionDetail .el-form--inline .el-form-item {
    margin-right: 2rem;
    margin-bottom: 0;
  }

  .abnormalMotionDetail .line {
    text-align: center;
  }

  .abnormalMotionDetail .d_line.abnormalMotionDetail_row {
    margin: 1.25rem 0;
  }

  .abnormalMotionDetail .abnormalMotionDetail_form .el-button {
    border-radius: 20px;
    padding: 10px 25px;
  }

  .abnormalMotionDetail .edit {
    color: #4da1ff;
    cursor: pointer;
  }

  .abnormalMotionDetail .into_row {
    margin: 1.625rem 0;
  }

  .abnormalMotionDetail .into_subTitle {
    display: inline-block;
    width: 7.5rem;
    height: 2rem;
    line-height: 2rem;
    padding: 0;
    border-radius: 0 15px 15px 0;
    -webkit-box-shadow: 0 5px 5px 0 #ddd;
    -moz-box-shadow: 0 5px 5px 0 #ddd;
    box-shadow: 0 5px 5px 0 #ddd;
    background-color: #89bcf5;
    border-color: #89bcf5;
    color: #fff;
    text-align: center;
  }

  .abnormalMotionDetail .detail {
    max-height: 400px;
    overflow: auto;
  }

  .abnormalMotionDetail .detail .el-form-item {
    margin-bottom: 10px;
  }

  .abnormalMotionDetail .el-dialog--small {
    width: 600px;
  }

  .abnormalMotionDetail .el-dialog__footer {
    -webkit-box-shadow: 0 -10px 30px -10px #d2d2d2;
    -moz-box-shadow: 0 -10px 30px -10px #d2d2d2;
    box-shadow: 0 -10px 30px -10px #d2d2d2;
  }

  .abnormalMotionDetail .timeChoose .el-form-item {
    margin-right: 0;
  }

  .abnormalMotionDetail .familyReportPrint {
    position: fixed;
    width: 100%;
    height: 100%;
    left: 0;
    top: 0;
    background-color: #f0f0f0;
    z-index: 66;
  }

  .abnormalMotionDetail .familyReportPrint .familyReportPrint_head {
    height: 3.75rem;
    background-color: #4da1ff;
    color: #fff;
    padding: 0 2rem;
  }

  .abnormalMotionDetail .familyReportPrint_headL {
    font-size: 20px;
  }

  .abnormalMotionDetail .familyReportPrint_headR {
    text-align: right;
  }

  .abnormalMotionDetail .familyReportPrint_headR .el-button {
    padding: 10px 0;
    width: 6.875rem;
  }

  .abnormalMotionDetail .familyReportPrint_headClose {
    font-size: 24px;
    margin-left: 4rem;
    cursor: pointer;
  }

  .abnormalMotionDetail .familyReportPrint_body {
    height: 100%;
    overflow: auto;
  }

  .abnormalMotionDetail .familyReportPrint_content {
    background-color: #fff;
    width: 610px;
    margin: auto;
    padding: 0 20px 100px 20px;
  }
</style>
