<template>
  <div class="classCount">
    <h3>课时统计</h3>
    <el-row class="classCount_row">
      <el-form ref="form" :inline="true" :model="form">
        <el-form-item label="统计类型：" required>
          <el-select v-model="form.typeType" placeholder="请选择" class="countType">
            <el-option label="工作量统计" :value="1"></el-option>
            <el-option label="调代课统计" :value="2"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="时间段：" class="dTime">
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
    <el-row type="flex" align="middle" class="alertsBtn">
      <el-col :span="18">
        <el-button class="delete" title="导出" @click="operationData('out')">
          <img class="delete_unactive"
               src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png" alt="">
          <img class="delete_active"
               src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png"
               alt="">
        </el-button>
        <el-button-group class="secBtn-group">
          <el-button class="filt" title="复制" @click="operationData('copy')">
            <img class="filt_unactive"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png"
                 alt="">
            <img class="filt_active"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png"
                 alt="">
          </el-button>
          <el-button class="delete" title="打印" @click="operationData('print')">
            <img class="delete_unactive"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png"
                 alt="">
            <img class="delete_active"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png"
                 alt="">
          </el-button>
        </el-button-group>
      </el-col>
      <el-col :span="6">
        <div class="g-fuzzyInput">
          <el-input
            placeholder="请输入关键字"
            suffix-icon="el-icon-search"
            v-model="selectParam.valueData"
            @change="goSearch">
          </el-input>
        </div>
      </el-col>
    </el-row>
    <el-row class="alertsList"
            v-loading="loading"
            element-loading-text="拼命加载中">
      <el-table
        v-show="selectParam.typeType==1"
        :data="tableData"
        style="width: 100%"
        @sort-change="sort"
      >
        </el-table-column>
        <el-table-column
          prop="subjectName"
          label="科目" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="gradeName"
          label="年级" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="teacherName"
          label="教师" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="count"
          label="课时" sortable="custom">
        </el-table-column>
      </el-table>
      <el-table
        v-show="selectParam.typeType==2"
        :data="tableData"
        style="width: 100%"
        @sort-change="sort"
      >
        </el-table-column>
        <el-table-column
          prop="subjectName"
          label="科目" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="gradeName"
          label="年级" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="teacherName"
          label="教师" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="dk"
          label="代课节数" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="tk"
          label="调课节数" sortable="custom">
        </el-table-column>
      </el-table>
    </el-row>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  import moment from 'moment'
  export default{
    data(){
      return {
        tableData: [],
        selectParam: {
          sort: '',
          sortData: '',
          startTime: '',
          endTime: '',
          valueData: '',
          typeType: 1
        },
        form: {
          sTime: '',
          eTime: '',
          typeType: 1
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
    },
    methods: {
      search(){
        this.selectParam.startTime = this.form.sTime ? moment(this.form.sTime).format('YYYY-MM-DD') : '';
        this.selectParam.endTime = this.form.eTime ? moment(this.form.eTime).format('YYYY-MM-DD') : '';
        this.selectParam.typeType = this.form.typeType;
        this.selectParam.valueData = '';
        this.selectParam.sortData = '';
        this.selectParam.sort = '';
        this.loadData(this.selectParam);
      },
      goSearch(){
        this.selectParam.startTime = this.form.sTime ? moment(this.form.sTime).format('YYYY-MM-DD') : '';
        this.selectParam.endTime = this.form.eTime ? moment(this.form.eTime).format('YYYY-MM-DD') : '';
        this.selectParam.typeType = this.form.typeType;
        this.selectParam.sortData = '';
        this.selectParam.sort = '';
        this.loadData(this.selectParam);
      },
      sort(column){
        this.selectParam.sortData = column.order || '';
        this.selectParam.sort = column.prop || '';
        this.loadData(this.selectParam);
      },
      operationData(type){
        var url, date1 = this.form.sTime ? moment(this.form.sTime).format('YYYY-MM-DD') : '',
          date2 = this.form.eTime ? moment(this.form.eTime).format('YYYY-MM-DD') : '';
        let sAy = [], hdData;
        if (this.selectParam.typeType == 1) {
          hdData = {
            subjectName: '科目',
            gradeName: '年级',
            teacherName: '教师',
            count: '课时'
          }
        } else {
          hdData = {
            subjectName: '科目',
            gradeName: '年级',
            teacherName: '教师',
            dk: '代课节数',
            tk: '调课节数'
          }
        }
        sAy.push(hdData);
        for (let obj of this.tableData) {
          let d = {};
          for (let name in hdData) {
            d[name] = obj[name] || '';
          }
          sAy.push(d)
        }
        if (type == 'out') {
          url = '/school/classreplacement/statistics?type=export&sort=' + this.selectParam.sort + '&sortData=' + this.selectParam.sortData + '&startTime=' + date1 + '&endTime=' + date2 + '&valueData=' + this.selectParam.valueData + '&typeType=' + this.selectParam.typeType;
          this.$refs['form'].validate((valid) => {
            if (valid) {
              req.downloadFile('.classCount', url, 'post');
            } else {
              return false;
            }
          });
        } else if (type == 'copy') {
          req.copyTableData('.classCount', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      loadData(data){
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/classreplacement/statistics?type=getList', 'get', data, function (res) {
          self.tableData = res.data;
          self.loading = false;
        })
      }
    }
  }
</script>
<style>
  .classCount {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .classCount h3 {
    font-size: 1.25rem;
    display: inline-block;
  }

  .classCount .classCount_row {
    margin: 2rem 0 1.25rem;
  }

  .classCount .searchBtn {
    border-radius: 20px;
    padding: 10px 25px;
  }

  .classCount .countType {
    width: 8.75rem;
  }

  .classCount .alertsBtn {
    margin: 1.25rem 0;
  }

  .classCount .el-form--inline .el-form-item {
    margin-right: 2rem;
    margin-bottom:0;
  }

  .classCount .line {
    text-align: center;
  }

  .classCount .el-table th, .classCount .el-table td {
    text-align: center;
  }

  .classCount .g-fuzzyInput {
    float: right;
  }

  .classCount .dTime .el-form-item {
    margin-right: 0;
  }
</style>
