<template>
  <div class="leaveCount">
    <h3>请假统计</h3>
    <el-row class="leaveCount_row">
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
          prop="className"
          label="班级" sortable>
        </el-table-column>
        <el-table-column
          prop="allCount"
          label="总计次数" sortable>
        </el-table-column>
        <el-table-column
          prop="allTimes"
          label="总计天数" sortable>
        </el-table-column>
        <el-table-column
          prop="1"
          label="事假" sortable>
        </el-table-column>
        <el-table-column
          prop="2"
          label="病假" sortable>
        </el-table-column>
        <el-table-column
          prop="3"
          label="其他" sortable>
        </el-table-column>
        <el-table-column
          label="操作">
          <template slot-scope="scope">
            <span class="leaveRecordDetail" @click="showDetail(scope.$index)">查看详情</span>
          </template>
        </el-table-column>
      </el-table>
    </el-row>
    <el-dialog
      title="请假统计详情"
      :visible.sync="dialogVisible"
      :modal="false"
      :before-close="handleClose">
      <el-row class="dialogMsgHead">
        <span>年级：{{detailData.gradeName}}</span>
        <span class="className">班级：{{detailData.className}}</span>
      </el-row>
      <el-row class="leaveCount_row dialogMsgBody">
        <el-table
          :data="detailDataList"
          style="width: 100%"
          :max-height="500"
          border
        >
          <el-table-column
            prop="name"
            label="姓名">
          </el-table-column>
          <el-table-column
            prop="sj"
            label="事假">
          </el-table-column>
          <el-table-column
            prop="sjTime"
            label="事假时长（天）">
          </el-table-column>
          <el-table-column
            prop="bj"
            label="病假">
          </el-table-column>
          <el-table-column
            prop="bjTime"
            label="病假时长（天）">
          </el-table-column>
          <el-table-column
            prop="qt"
            label="其他">
          </el-table-column>
          <el-table-column
            prop="qtTime"
            label="其他时长（天）">
          </el-table-column>
        </el-table>
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
          gradeid: ''
        },
        form: {
          startTime: '',
          endTime: '',
          classids: [],
          gradeid: ''
        },
        dialogVisible: false,
        detailData: {},
        detailDataList: [],
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
        self.form.classids = [];
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
            req.ajaxSend('/school/Studentleave/leaveStatistics?type=approvalList', 'get', self.selectParam, function (res) {
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
              className: '班级',
              allCount: '总计次数',
              allTimes: '总计天数',
              1: '事假',
              2: '病假',
              3: '其他'
            };
            sAy.push(hdData);
            for (let obj of this.tableData) {
              let d = {};
              for (let name in hdData) {
                if (name == 'className') {
                  d[name] = obj[name] || '';
                } else {
                  d[name] = obj[name];
                }
              }
              sAy.push(d)
            }
            this.selectParam.startTime = moment(this.form.startTime).format('YYYY-MM-DD');
            this.selectParam.endTime = moment(this.form.endTime).format('YYYY-MM-DD');
            if (type == 'out') {
              req.downloadFile('.leaveCount', '/school/Studentleave/leaveStatistics?type=export&startTime=' + this.selectParam.startTime + '&endTime=' + this.selectParam.endTime + '&gradeid=' + this.selectParam.gradeid + '&classid=' + this.selectParam.classid, 'post')
            } else if (type == 'copy') {
              req.copyTableData('.leaveCount', sAy);
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
        var self = this, data = {
          startTime: this.selectParam.startTime,
          endTime: this.selectParam.endTime,
          classId: self.tableData[idx].classId,
          gradeId: self.tableData[idx].gradeId
        };
        self.dialogVisible = true;
        $.extend(self.detailData, self.tableData[idx]);
        req.ajaxSend('/school/Studentleave/leaveStatistics?type=getXq', 'get', data, function (res) {
          self.detailDataList = res.data;
        })
      }
    }
  }
</script>
<style>
  .leaveCount {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .leaveCount h3 {
    font-size: 1.25rem;
  }

  .leaveCount .leaveCount_row {
    margin: 2rem 0 0;
  }

  .leaveCount .searchBtn {
    border-radius: 20px;
    padding: 10px 25px;
  }

  .leaveCount .el-form--inline .el-form-item {
    margin-right: 2rem;
  }

  .leaveCount .createTime {
    width: 30rem;
  }

  .leaveCount .createTime .el-form-item {
    margin-right: 0;
  }

  .leaveCount .el-form--inline .grade .el-select {
    width: 8.75rem;
  }

  .leaveCount .line {
    text-align: center;
  }

  .leaveCount .alertsBtn {
    margin: 1.25rem 0;
  }

  .leaveCount .el-table th, .leaveCount .el-table td {
    text-align: center;
  }

  .leaveCount .leaveRecordDetail {
    color: #20a0ff;
    cursor: pointer;
  }

  .leaveCount .dialogMsgHead {
    font-size: 14px;
  }

  .leaveCount .dialogMsgHead .className {
    margin-left: 2rem;
  }

  .leaveCount .dialogMsgBody .el-table th {
    background-color: #deeefe;
  }

  .leaveCount .dialogMsgBody .el-table__footer-wrapper thead div, .leaveCount .dialogMsgBody .el-table__header-wrapper thead div, .leaveCount .dialogMsgBody .el-table__fixed-header-wrapper thead div {
    background-color: #deeefe;
  }
</style>
