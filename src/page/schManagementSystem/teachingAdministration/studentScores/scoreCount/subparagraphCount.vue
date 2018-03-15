<template>
  <div class="subparagraphCount">
    <h3>分段统计</h3>
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
      <span>分数分段：</span>
      <span><el-input class="rankData" v-model="selectParam.section[0].from"></el-input> 分 — <el-input class="rankData"
                                                                                                       v-model="selectParam.section[0].to"></el-input> 分</span>
      <span class="fillLeft"><el-input class="rankData" v-model="selectParam.section[1].from"></el-input> 分 — <el-input
        class="rankData" v-model="selectParam.section[1].to"></el-input> 分</span>
      <span class="fillLeft"><el-input class="rankData" v-model="selectParam.section[2].from"></el-input> 分 — <el-input
        class="rankData" v-model="selectParam.section[2].to"></el-input> 分</span>
      <span class="fillLeft"><el-input class="rankData" v-model="selectParam.section[3].from"></el-input> 分 — <el-input
        class="rankData" v-model="selectParam.section[3].to"></el-input> 分</span>
      <span class="fillLeft"><el-input class="rankData" v-model="selectParam.section[4].from"></el-input> 分 — <el-input
        class="rankData" v-model="selectParam.section[4].to"></el-input> 分</span>
      <span class="fillLeft"><el-input class="rankData" v-model="selectParam.section[5].from"></el-input> 分 — <el-input
        class="rankData" v-model="selectParam.section[5].to"></el-input> 分</span>
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
          prop="className"
          label="班级" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="join"
          label="参考人数" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="avg"
          label="平均分" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="ranking"
          label="名次" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="max"
          label="最高分" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="min"
          label="最低分" sortable="custom">
        </el-table-column>
        <el-table-column
          :prop="headData.name"
          :label="headData.name" v-for="(headData,idx) in tableHeadData" :key="idx" v-if="headData.name!='-'">
        </el-table-column>
        <el-table-column
          prop="teacher"
          label="班主任" sortable="custom">
        </el-table-column>
      </el-table>
    </el-row>
    <el-row class="pageAlerts" v-if="tableData&&tableData.length!=0">
      <el-pagination
        @current-change="handleCurrentChange"
        :page-size="selectParam.limit"
        :current-page.sync="selectParam.page"
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
        branchList: [],
        selectParam: {
          page: 1,
          limit: 50,
          field: '',
          find: '',
          order: '',
          examinationid: '',
          branchid: '',
          section: [{
            from: '',
            to: ''
          }, {
            from: '',
            to: ''
          }, {
            from: '',
            to: ''
          }, {
            from: '',
            to: ''
          }, {
            from: '',
            to: ''
          }, {
            from: '',
            to: ''
          }]
        },
        totalNum: 0,
        tableData: [],
        tableHeadData: [],
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
        self.selectParam.branchid = '';
        self.branchList = [];
        req.ajaxSend('/school/Achievement/achievementFind/type/findexam', 'post', data, function (res) {
          self.examList = res;
        })
      },
      selectBranch(){
        var self = this, data = {
          examinationid: self.selectParam.examinationid
        };
        self.selectParam.branchid = '';
        req.ajaxSend('/school/Achievement/achievementFind/type/findclass', 'post', data, function (res) {
          self.branchList = res;
        })
      },
      search(){
        this.selectParam.page = 1;
        this.selectParam.find = '';
        this.selectParam.field = '';
        this.selectParam.order = '';
        if (!this.selectParam.branchid) {
          this.vmMsgWarning('请选择科类!');
          return false;
        }
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
        var param = '';
        if (!this.selectParam.branchid) {
          this.vmMsgWarning('请选择科类!');
          return false;
        }
        let sAy = [], hdData = {
          className: '班级',
          join: '参考人数',
          avg: '平均分',
          ranking: '排名',
          max: '最高分',
          min: '最低分',
        };
        for (let obj of this.tableHeadData) {
          hdData[obj.name] = obj.name;
        }
        hdData.teacher = '班主任';
        sAy.push(hdData);
        for (let obj of this.tableData) {
          let d = {};
          for (let name in hdData) {
            d[name] = obj[name] || '';
          }
          sAy.push(d)
        }
        if (type == 'out') {
          param = '?examinationid=' + this.selectParam.examinationid + '&branchid=' + this.selectParam.branchid + '&section=' + this.selectParam.section;
          req.downloadFile('.subparagraphCount', '/school/Achievement/statistics/type/subsectionexport' + param, 'post');
        } else if (type == 'copy') {
          req.copyTableData('.subparagraphCount', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      loadData(data){
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/Achievement/statistics/type/subsection', 'post', data, function (res) {
          self.tableData = res.data;
          self.tableHeadData = res.title;
          self.totalNum = res.pageclass.count;
          self.loading = false;
        })
      }
    }
  }
</script>
<style>
  .subparagraphCount {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
    font-size: 14px;
  }

  .subparagraphCount h3 {
    font-size: 1.25rem;
    color: #4e4e4e;
  }

  .subparagraphCount .scoreRow {
    margin: 1.125rem 0;
  }

  .subparagraphCount .scoreRowOne {
    margin: 2rem 0 1.125rem;
  }

  .subparagraphCount .el-table td, .subparagraphCount .el-table th {
    text-align: center;
  }

  .subparagraphCount .g-fuzzyInput {
    float: right;
  }

  .subparagraphCount .d_line {
    margin-top: 1.125rem;
  }

  .subparagraphCount .alertsBtn {
    margin: 1.125rem 0;
  }

  .subparagraphCount .scoreQuery_btn {
    text-align: right;
    margin: 1.25rem 0;
  }

  .subparagraphCount .scoreQuery_btn .el-button {
    padding: 0;
    height: 30px;
    border-radius: 15px;
    width: 100px;
    font-size: .875rem;
  }

  .subparagraphCount .grade {
    width: 8.75rem;
  }

  .subparagraphCount .subject {
    width: 10rem;
  }

  .subparagraphCount .test {
    width: 15.625rem;
  }

  .subparagraphCount .fillLeft {
    margin-left: 2rem;
  }

  .subparagraphCount .rankData {
    width: 3rem;
  }

  .subparagraphCount .rankData .el-input__inner {
    height: 30px;
  }

  .subparagraphCount .formInline .el-form-item {
    margin-right: 2.5rem;
    margin-bottom: 0;
  }
</style>
