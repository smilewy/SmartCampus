<template>
  <div class="scoreQuery rankingCount">
    <h3>名次统计</h3>
    <el-row class="scoreRow scoreRowOne">
      <el-form :inline="true" class="formInline">
        <el-form-item label="年级：">
          <el-select v-model="gradeid" placeholder="请选择" class="grade" @change="selectExam">
            <el-option
              v-for="item in gradeList"
              :key="item.gradeid"
              :label="item.name"
              :value="item.gradeid">
            </el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="考试：">
          <el-select v-model="selectParam.examinationid" placeholder="请选择" class="test" @change="selectBranch">
            <el-option
              v-for="item in examList"
              :key="item.examinationid"
              :label="item.examination"
              :value="item.examinationid">
            </el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="科类：">
          <el-select v-model="selectParam.branchid" placeholder="请选择" class="subject">
            <el-option
              v-for="item in branchList"
              :key="item.branchid"
              :label="item.branch"
              :value="item.branchid">
            </el-option>
          </el-select>
        </el-form-item>
      </el-form>
    </el-row>
    <el-row class="scoreRow">
      <span>排名分段：</span>
      <span>前 <el-input class="rankData" v-model="selectParam.rank1"></el-input> 名</span>
      <span class="fillLeft">前 <el-input class="rankData" v-model="selectParam.rank2"></el-input> 名</span>
      <span class="fillLeft">前 <el-input class="rankData" v-model="selectParam.rank3"></el-input> 名</span>
      <span class="fillLeft">前 <el-input class="rankData" v-model="selectParam.rank4"></el-input> 名</span>
      <span class="fillLeft">前 <el-input class="rankData" v-model="selectParam.rank5"></el-input> 名</span>
      <span class="fillLeft">前 <el-input class="rankData" v-model="selectParam.rank6"></el-input> 名</span>
    </el-row>
    <el-row class="scoreRow scoreQuery_btn">
      <el-button type="primary" @click="search">
        <img src="../../../../../assets/img/schManagementSystem/teachingAdministration/studentScores/icon_search.png"
             alt="">
        <span>查询</span>
      </el-button>
    </el-row>
    <el-row class="d_line"></el-row>
    <el-row type="flex" align="middle" class="alertsBtn">
      <el-col :span="18">
        <el-button class="delete" title="导出" @click="operationData('out')">
          <img class="delete_unactive"
               src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png"
               alt="">
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
            v-model="selectParam.find"
            @change="goSearch">
          </el-input>
        </div>
      </el-col>
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
          prop="subjectname"
          min-width="120"
          label="科目" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="className"
          min-width="120"
          label="班级" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="join"
          min-width="120"
          label="参考人数" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="avg"
          min-width="120"
          label="平均名次" sortable="custom">
        </el-table-column>
        <el-table-column
          :prop="headData.prop"
          min-width="120"
          :label="'前'+headData.name+'名'" sortable="custom" v-for="(headData,idx) in tableHeadData" :key="idx"
          v-if="headData.name">
        </el-table-column>
        <el-table-column
          prop="teacherName"
          min-width="120"
          label="教师" sortable="custom">
        </el-table-column>
      </el-table>
    </el-row>
    <el-row class="pageAlerts" v-if="tableData&&tableData.length!=0">
      <el-pagination
        @current-change="handleCurrentChange"
        :current-page.sync="selectParam.page"
        :page-size="selectParam.limit"
        layout="prev, pager, next, jumper"
        :total="totalNum">
      </el-pagination>
    </el-row>
  </div>
</template>
<script>
  import req from '@/assets/js/common'

  export default {
    data() {
      return {
        gradeid: '',
        gradeList: [],
        examList: [],
        branchList: [],
        selectParam: {
          page: 1,
          limit: 50,
          field: '',
          find: '',
          order: '',
          examinationid: '',
          branchid: '',
          rank1: '',
          rank2: '',
          rank3: '',
          rank4: '',
          rank5: '',
          rank6: ''
        },
        tableData: [],
        tableHeadData: [],
        totalNum: 0,
        loading: false
      }
    },
    created: function () {
      var self = this, data;
      req.ajaxSend('/school/Achievement/statistics/type/findgrade', 'post', '', function (res) {
        self.gradeList = res;
        self.gradeid = res[0].gradeid;
        data = {
          gradeid: self.gradeid
        };
        req.ajaxSend('/school/Achievement/achievementFind/type/findexam', 'post', data, function (res) {
          self.examList = res;
        })
      })
    },
    methods: {
      selectExam() {
        var self = this, data = {
          gradeid: self.gradeid
        };
        self.selectParam.examinationid = '';
        self.selectParam.branchid = '';
        self.branchList = [];
        req.ajaxSend('/school/Achievement/achievementFind/type/findexam', 'post', data, function (res) {
          self.examList = res;
        })
      },
      selectBranch() {
        var self = this, data = {
          examinationid: self.selectParam.examinationid
        };
        self.selectParam.branchid = '';
        req.ajaxSend('/school/Achievement/achievementFind/type/findclass', 'post', data, function (res) {
          self.branchList = res;
        })
      },
      search() {
        this.selectParam.page = 1;
        this.selectParam.find = '';
        this.selectParam.field = '';
        this.selectParam.order = '';
        if (!this.selectParam.branchid) {
          this.vmMsgWarning('请选择科类!');
          return false;
        }
        this.tableHeadData = [];
        for (var i = 1; i <= 6; i++) {
          let n = 'rank' + i, hData = {
            name: this.selectParam[n],
            prop: n
          };
          this.tableHeadData.push(hData);
        }
        this.loadData(this.selectParam);
      },
      goSearch() {  //查询
        this.selectParam.page = 1;
        this.selectParam.field = '';
        this.selectParam.order = '';
        this.loadData(this.selectParam);
      },
      sort(column) {
        this.selectParam.field = column.prop || '';
        this.selectParam.order = column.order || '';
        this.loadData(this.selectParam);
      },
      handleCurrentChange(val) {
        this.selectParam.page = val;
        this.loadData(this.selectParam);
      },
      operationData(type) {
        var param = '';
        if (!this.selectParam.branchid) {
          this.vmMsgWarning('请选择科类!');
          return false;
        }
        let sAy = [], hdData = {
          subjectname: '科目',
          className: '班级',
          join: '参考人数',
          avg: '平均名次'
        };
        for (let obj of this.tableHeadData) {
          hdData[obj.prop] = '前' + obj.name + '名';
        }
        hdData.teacherName = '教师';
        sAy.push(hdData);
        for (let obj of this.tableData) {
          let d = {};
          for (let name in hdData) {
            if (name == 'subjectname' || name == 'teacherName') {
              d[name] = obj[name] || '';
            } else {
              d[name] = obj[name];
            }
          }
          sAy.push(d)
        }
        if (type == 'out') {
          param = '?examinationid=' + this.selectParam.examinationid + '&branchid=' + this.selectParam.branchid + '&rank1=' + this.selectParam.rank1 + '&rank2=' + this.selectParam.rank2 + '&rank3=' + this.selectParam.rank3 + '&rank4=' + this.selectParam.rank4 + '&rank5=' + this.selectParam.rank5 + '&rank6=' + this.selectParam.rank6;
          req.downloadFile('.rankingCount', '/school/Achievement/statistics/type/rankingexport' + param, 'post');
        } else if (type == 'copy') {
          req.copyTableData('.rankingCount', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      loadData(data) {
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/Achievement/statistics/type/ranking', 'post', data, function (res) {
          self.tableData = res.data;
          self.totalNum = res.pageclass.count;
          self.loading = false;
        })
      }
    }
  }
</script>
<style>
  .rankingCount {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
    font-size: 14px;
  }

  .rankingCount h3 {
    font-size: 1.25rem;
    color: #4e4e4e;
  }

  .rankingCount .scoreRow {
    margin: 1.125rem 0;
  }

  .rankingCount .scoreRowOne {
    margin: 2rem 0 1.125rem;
  }

  .rankingCount .el-table td, .rankingCount .el-table th {
    text-align: center;
  }

  .rankingCount .g-fuzzyInput {
    float: right;
  }

  .rankingCount .d_line {
    margin-top: 1.125rem;
  }

  .rankingCount .alertsBtn {
    margin: 1.125rem 0;
  }

  .rankingCount .scoreQuery_btn {
    text-align: right;
    margin: 1.25rem 0;
  }

  .rankingCount .scoreQuery_btn .el-button {
    padding: 0;
    height: 30px;
    border-radius: 15px;
    width: 100px;
    font-size: .875rem;
  }

  .rankingCount .grade {
    width: 8.75rem;
  }

  .rankingCount .l_class {
    width: 9.375rem;
  }

  .rankingCount .subject {
    width: 10rem;
  }

  .rankingCount .test {
    width: 15.625rem;
  }

  .rankingCount .fillLeft {
    margin-left: 2.5rem;
  }

  .rankingCount .rankData {
    width: 3.75rem;
  }

  .rankingCount .rankData .el-input__inner {
    height: 30px;
  }

  .rankingCount .formInline .el-form-item {
    margin-right: 2.5rem;
    margin-bottom: 0;
  }
</style>
