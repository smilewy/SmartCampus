<template>
  <div class="subjectCount">
    <h3>学科统计</h3>
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
          <el-select v-model="selectParam.examinationid" placeholder="请选择" class="test">
            <el-option
              v-for="item in examList"
              :key="item.examinationid"
              :label="item.examination"
              :value="item.examinationid">
            </el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="按科类分开统计：">
          <el-switch
            v-model="showSingleRank"
            active-text="是"
            inactive-text="否"
            active-color="#09baa7"
            inactive-color="#ff4949">
          </el-switch>
        </el-form-item>
        <el-form-item class="scoreQuery_btn">
          <el-button type="primary" @click="search">
            <img
              src="../../../../../assets/img/schManagementSystem/teachingAdministration/studentScores/icon_search.png"
              alt="">
            <span>查询</span>
          </el-button>
        </el-form-item>
      </el-form>
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
          prop="branch"
          min-width="120"
          label="科类" sortable="custom">
        </el-table-column>
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
          label="均分" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="ranking"
          min-width="120"
          label="排名" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="max"
          min-width="120"
          label="最高分" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="min"
          min-width="120"
          label="最低分" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="excellent"
          min-width="120"
          label="优秀数" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="excellentPercent"
          min-width="120"
          label="优秀率%" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="pass"
          min-width="120"
          label="及格数" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="passPercent"
          min-width="120"
          label="及格率%" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="lowscore"
          min-width="120"
          label="低分数" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="lowscorePercent"
          min-width="120"
          label="低分率%" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="teacher"
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
  export default{
    data(){
      return {
        gradeid: '',
        gradeList: [],
        examList: [],
        showSingleRank: true,
        selectParam: {
          page: 1,
          limit: 50,
          field: '',
          find: '',
          order: '',
          examinationid: '',
          statistics: ''
        },
        tableData: [],
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
      selectExam(){
        var self = this, data = {
          gradeid: self.gradeid
        };
        self.selectParam.examinationid = '';
        req.ajaxSend('/school/Achievement/achievementFind/type/findexam', 'post', data, function (res) {
          self.examList = res;
        })
      },
      search(){
        if (!this.selectParam.examinationid) {
          this.vmMsgWarning('请选择考试!');
          return false;
        }
        this.selectParam.statistics = this.showSingleRank ? 1 : 0;
        this.loadData(this.selectParam);
      },
      goSearch() {  //查询
        this.selectParam.page = 1;
        this.selectParam.field = '';
        this.selectParam.order = '';
        this.loadData(this.selectParam);
      },
      sort(column){
        this.selectParam.field = column.prop || '';
        this.selectParam.order = column.order || '';
        this.loadData(this.selectParam);
      },
      handleCurrentChange(val) {
        this.selectParam.page = val;
        this.loadData(this.selectParam);
      },
      operationData(type){
        if (!this.selectParam.examinationid) {
          this.vmMsgWarning('请选择考试!');
          return false;
        }
        let sAy = [], hdData = {
          branch: '科类',
          subjectname: '科目',
          className: '班级',
          join: '参考人数',
          avg: '均分',
          ranking: '排名',
          max: '最高分',
          min: '最低分',
          excellent: '优秀数',
          excellentPercent: '优秀率%',
          pass: '及格数',
          passPercent: '及格率%',
          lowscore: '低分数',
          lowscorePercent: '低分率%',
          teacher: '教师',
        };
        sAy.push(hdData);
        for (let obj of this.tableData) {
          let d = {};
          for (let name in hdData) {
            d[name] = obj[name] || '';
          }
          sAy.push(d)
        }
        if (type == 'out') {
          this.selectParam.statistics = this.showSingleRank ? 1 : 0;
          req.downloadFile('.subjectCount', '/school/Achievement/statistics/type/subjectexport?examinationid=' + this.selectParam.examinationid + '&statistics=' + this.selectParam.statistics, 'post');
        } else if (type == 'copy') {
          req.copyTableData('.subjectCount', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      loadData(data){
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/Achievement/statistics/type/subject', 'post', data, function (res) {
          self.tableData = res.data;
          self.totalNum = res.pageclass.count;
          self.loading = false;
        })
      }
    }
  }
</script>
<style>
  .subjectCount {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
    font-size: 14px;
  }

  .subjectCount h3 {
    font-size: 1.25rem;
    color: #4e4e4e;
  }

  .subjectCount .scoreRow {
    margin: 1.125rem 0;
  }

  .subjectCount .scoreRowOne {
    margin: 2rem 0 1.125rem;
  }

  .subjectCount .el-table td, .subjectCount .el-table th {
    text-align: center;
  }

  .subjectCount .g-fuzzyInput {
    float: right;
  }

  .subjectCount .d_line {
    margin-top: 1.125rem;
  }

  .subjectCount .alertsBtn {
    margin: 1.125rem 0;
  }

  .subjectCount .scoreQuery_btn .el-button {
    padding: 0;
    height: 30px;
    border-radius: 15px;
    width: 100px;
    font-size: .875rem;
  }

  .subjectCount .grade {
    width: 8.75rem;
  }

  .subjectCount .l_class {
    width: 9.375rem;
  }

  .subjectCount .test {
    width: 15.625rem;
  }

  .subjectCount .formInline .el-form-item {
    margin-right: 2.5rem;
    margin-bottom: 0;
  }
</style>
