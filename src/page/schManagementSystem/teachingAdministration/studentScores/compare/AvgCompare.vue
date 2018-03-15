<template>
  <div class="g-statisticalAnalysis g-container avgCompare">
    <header>
      <h2>均分对比</h2>
      <div class="g-textHeader g-flexStartRow">
        <el-form :model="repairForm" :inline="true">
          <el-form-item label="年级：">
            <el-select @change="gradeChange" v-model="repairForm.gradeid">
              <el-option v-for="(content,index) in gradeAjaxData" :key="index" :value="content.gradeid"
                         :label="content.name"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="本次考试：">
            <el-select v-model="repairForm.examinationid1" @change="getClassAjax">
              <el-option v-for="(content,index) in currentExamData" :key="index" :value="content.examinationid"
                         :label="content.examination"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="对比考试：">
            <el-select v-model="repairForm.examinationid2" @change="getClassAjax">
              <el-option v-for="(content,index) in compareExamData" :key="index" :value="content.examinationid"
                         :label="content.examination"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="科类：">
            <el-select v-model="repairForm.branchid">
              <el-option v-for="(content,index) in subjectAjaxData" :key="index" :value="content.branchid"
                         :label="content.branch"></el-option>
            </el-select>
          </el-form-item>
        </el-form>
        <el-button class="radiusButton selfCenter" @click="searchClick" type="primary" icon="el-icon-search">查询</el-button>
      </div>
    </header>
    <section class="centerTable alertsList">
      <div class="g-liOneRow g-sa_header_search">
        <div class="gs-button alertsBtn">
          <el-button-group>
            <el-button @click="exportClick" data-msg="export" class="filt buttonChild" title="导出">
              <img class="filt_unactive"
                   src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png"/>
              <img class="filt_active"
                   src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png"/>
            </el-button>
          </el-button-group>
          <el-button-group class="elGroupButton_two">
            <el-button data-msg="copy" class="filt buttonChild" title="复制" @click="operationData('copy')">
              <img class="filt_unactive"
                   src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png"/>
              <img class="filt_active"
                   src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png"/>
            </el-button>
            <el-button data-msg="print" class="filt buttonChild" title="打印" @click="operationData('print')">
              <img class="filt_unactive"
                   src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png"/>
              <img class="filt_active"
                   src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png"/>
            </el-button>
          </el-button-group>
        </div>
        <div class="gs-refresh g-fuzzyInput">
          <el-input type="text" v-model="fuzzyInput" suffix-icon="el-icon-search" placeholder="请输入"
                    @change="getTableLoad"></el-input>
        </div>
      </div>
      <el-table class="g-NotHover" border :data="classesTimeSetTable" @sort-change="sortChange"
                @selection-change="handleSelectionChange"
                v-loading="loading"
                element-loading-text="拼命加载中">
        <el-table-column label="科目" min-width="120" prop="subjectname" sortable="custom"></el-table-column>
        <el-table-column label="班级" min-width="120" prop="className" sortable="custom"></el-table-column>
        <el-table-column label="参考人数" min-width="150" prop="join" sortable="custom"></el-table-column>
        <el-table-column label="本次平均分" min-width="150" prop="result" sortable="custom"></el-table-column>
        <el-table-column label="本次名次" min-width="150" prop="rank" sortable="custom"></el-table-column>
        <el-table-column label="对比平均分" min-width="150" prop="result1" sortable="custom"></el-table-column>
        <el-table-column label="目标系数" min-width="150" prop="coefficient" sortable="custom"></el-table-column>
        <el-table-column label="目标均分" min-width="150" prop="targetresult" sortable="custom"></el-table-column>
        <el-table-column label="均分进退" min-width="150" prop="retreat" sortable="custom"></el-table-column>
        <el-table-column label="对比名次" min-width="150" prop="targetrank1" sortable="custom"></el-table-column>
        <el-table-column label="名次进退" min-width="150" prop="retreatrank" sortable="custom"></el-table-column>
      </el-table>
    </section>
    <footer class="g-footer">
      <el-row class="pageAlerts">
        <el-pagination
          @current-change="handleCurrentChange"
          :current-page.sync="currentPage"
          layout="prev, pager, next, jumper"
          :page-count="pageAll">
        </el-pagination>
      </el-row>
    </footer>
  </div>
</template>
<script>
  import {
    compareComGetGrade,//得到年级
    compareComGetClass,//得到科类
    compareComGetExam,//得到考试信息（对比考试与本次考试）
    compareAvgLoad,//页面查询信息
  } from '@/api/http'
  import req from '@/assets/js/common'

  export default {
    data() {
      let _self = this;
      return {
        /*模糊查询*/
        fuzzyInput: '',
        /*form表单*/
        repairForm: {
          examinationid1: '',//本次考试
          examinationid2: '',//对比考试
          gradeid: '',
          branchid: '',
        },
        gradeAjaxData: [],
        subjectAjaxData: [],//科类加班级
        currentExamData: [],
        compareExamData: [],
        /*footer*/
        pageAll: 1,
        currentPage: 1,
        pageCount: 10,
        /*table*/
        classesTimeSetTable: [],
        columnData: [],
        /*send ajax param*/
        orderBy: {
          order: '',
          field: '',//排序字段
        },
        loading:false
      }
    },
    methods: {
      /*点击返回流程图按钮*/
      goBackChart() {
        this.$router.push({name: 'evaluationManagement'});
      },
      /*footer*/
      handleCurrentChange(val) {
        this.currentPage = val;
        this.getTableLoad();
      },
      /*查询*/
      searchClick() {
        if (this.repairForm.branchid && this.repairForm.examinationid1 && this.repairForm.examinationid2) {
          if (this.repairForm.examinationid1 == this.repairForm.examinationid2) {
            this.vmMsgWarning('选择的两次考试一样！');
          }
          else {
            this.getTableLoad();
          }
        }
        else {
          this.vmMsgWarning('请选择所有相关信息！');
        }
      },
      /*table*/
      handleSelectionChange(choose) {
      },
      sortChange(value) {
        this.orderBy.order = value.order;
        this.orderBy.field = value.prop;
        this.getTableLoad();
      },
      handlerTaskClick() {
        this.$router.push('/handlerTask');
      },
      /*模糊查询*/
      fuzzyClick() {
        /*模糊查询执行回调*/
      },
      /*年级变化*/
      gradeChange() {
        this.getExamMsg();
        this.repairForm.examinationid1 = '';
        this.repairForm.examinationid2 = '';
        this.repairForm.branchid = '';
        this.subjectAjaxData = [];
      },
      /*send ajax*/
      getGradeAjax() {
        compareComGetGrade().then((data) => {
          this.gradeAjaxData = data;
          if (this.gradeAjaxData.length > 0) {
            this.repairForm.gradeid = this.gradeAjaxData[0].gradeid;
          }
          this.gradeChange();
        });
      },
      getClassAjax() {
        this.repairForm.branchid = '';
        this.subjectAjaxData = [];
        if (!this.repairForm.examinationid1 || !this.repairForm.examinationid2) {
          return false;
        }
        compareComGetClass({
          examinationid1: this.repairForm.examinationid1,
          examinationid2: this.repairForm.examinationid2
        }).then(data => {
          this.subjectAjaxData = data;
        });
      },
      getExamMsg() {
        compareComGetExam({gradeid: this.repairForm.gradeid}).then(data => {
          this.currentExamData = data;
          this.compareExamData = data;
        });
      },
      /*得到表格信息*/
      getTableLoad() {
        this.loading=true;
        compareAvgLoad({
          page: this.currentPage,
          limit: this.pageCount,
          find: this.fuzzyInput, ...this.orderBy, ...this.repairForm
        }).then(data => {
          this.loading=false;
          if (!data.return) {
            this.vmMsgWarning('不是同一群体或没有相同的考试科目！');
          } else {
            this.classesTimeSetTable = data.data;
            this.columnData = data.subjectlist;
            this.pageAll = data.pageclass.pageAll;
          }
        });
      },
      exportClick() {
        if (this.repairForm.branchid && this.repairForm.examinationid1 && this.repairForm.examinationid2) {
          let exportParam = '?examinationid1=' + this.repairForm.examinationid1 + '&examinationid2=' + this.repairForm.examinationid2 + '&branchid=' + this.repairForm.branchid;
          req.downloadFile('.g-container', '/school/Achievement/contrast/type/avgexport' + exportParam, 'post');
        }
        else {
          this.vmMsgWarning('请选择所有相关信息！');
        }
      },
      operationData(type){
        let sAy = [], hdData = {
          subjectname: '科目',
          className: '班级',
          join: '参考人数',
          result: '本次平均分',
          rank: '本次名次',
          result1: '对比平均分',
          coefficient: '目标系数',
          targetresult: '目标均分',
          retreat: '均分进退',
          targetrank1: '对比名次',
          retreatrank: '名次进退',
        };
        sAy.push(hdData);
        for (let obj of this.classesTimeSetTable) {
          let d = {};
          for (let name in hdData) {
            d[name] = obj[name] || '';
          }
          sAy.push(d)
        }
        if (type == 'copy') {
          req.copyTableData('.avgCompare', sAy);
        } else {
          req.lodop(sAy);
        }
      }
    },
    created() {
      this.getGradeAjax();
    }
  }
</script>
<style lang="less" scoped>
  /*@import '../../../../../style/test';*/
  @import '../../../../../style/style';
  @import '../../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.css';
  @import '../../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.less';

  section.centerTable {
    .marginTop(20);
  }

  .g-sa_header_search {
    .marginTop(32);
    .marginBottom(20);
  }

  .g-classSchedule .g-container {
    padding: 0;
  }

  .g-statisticalAnalysis header h2 {
    .marginBottom(20);
  }

  .g-textHeader .el-form {
  }

  .g-textHeader .el-form-item .el-select {
    width: 200/16rem;
  }

  .g-textHeader .el-form-item {
    margin-right: 30/16rem;
    float: left;
    margin-bottom: 0;
  }

  .g-textHeader .el-form-item:not(:first-of-type) {
  }

  .g-liOneRow.g-sa_header_search {
    margin-top: 0;
  }
</style>


