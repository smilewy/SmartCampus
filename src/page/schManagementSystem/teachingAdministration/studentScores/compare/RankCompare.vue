<template>
  <div class="g-statisticalAnalysis g-container RankCompare">
    <header>
      <h2>名次对比</h2>
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
      <el-row class="scoreRow rankCount">
        <span>排名分段：</span>
        <span>前 <el-input class="rankData" v-model="selectParam.rank1"></el-input> 名</span>
        <span class="fillLeft">前 <el-input class="rankData" v-model="selectParam.rank2"></el-input> 名</span>
        <span class="fillLeft">前 <el-input class="rankData" v-model="selectParam.rank3"></el-input> 名</span>
        <span class="fillLeft">前 <el-input class="rankData" v-model="selectParam.rank4"></el-input> 名</span>
        <span class="fillLeft">前 <el-input class="rankData" v-model="selectParam.rank5"></el-input> 名</span>
        <span class="fillLeft">前 <el-input class="rankData" v-model="selectParam.rank6"></el-input> 名</span>
      </el-row>
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
        <el-table-column label="科目" prop="subjectname" sortable="custom"></el-table-column>
        <el-table-column label="班级" prop="className" sortable="custom"></el-table-column>
        <el-table-column label="对比人数" prop="join" sortable="custom"></el-table-column>
        <el-table-column label="本次平均名次" prop="avg" sortable="custom"></el-table-column>
        <el-table-column label="对比平均名次" prop="avga" sortable="custom"></el-table-column>
        <el-table-column label="差值" prop="shortavg" sortable="custom"></el-table-column>
        <el-table-column v-for="(column,columnI) in columnData" :key="columnI" :label="'前'+column.name+'名'">
          <el-table-column :label="'本次前'+column.name+'名'">
            <template slot-scope="prop">
              <span v-text="prop.row[column.value].rank1"></span>
            </template>
          </el-table-column>
          <el-table-column :label="'对比前 '+column.name+' 名'">
            <template slot-scope="prop">
              <span v-text="prop.row[column.value].rank2"></span>
            </template>
          </el-table-column>
          <el-table-column label="差值" prop="shortavg">
            <template slot-scope="prop">
              <span v-text="prop.row[column.value].shortrank"></span>
            </template>
          </el-table-column>
        </el-table-column>
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
    compareRankLoad,//页面查询信息
  } from '@/api/http'
  import req from '@/assets/js/common'

  export default {
    data() {
      let _self = this;
      return {
        /*模糊查询*/
        fuzzyInput: '',
        selectParam: {
          rank1: '',
          rank2: '',
          rank3: '',
          rank4: '',
          rank5: '',
          rank6: ''
        },
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
        compareRankLoad({
          page: this.currentPage,
          limit: this.pageCount,
          find: this.fuzzyInput, ...this.selectParam, ...this.orderBy, ...this.repairForm
        }).then(data => {
          this.loading=false;
          if (!data.return) {
            this.vmMsgWarning('不是同一群体或没有相同的考试科目！');
          } else {
            this.classesTimeSetTable = data.data;
            this.columnData = data.title;
            this.pageAll = data.pageclass.pageAll;
          }
        });
      },
      operationData(type){
        var tablestr = '<table class="table table=bordered" style="margin-bottom:0"><thead>';
        let tableThTr = '<tr><th rowspan="2">科目</th><th rowspan="2">班级</th><th rowspan="2">对比人数</th><th rowspan="2">本次平均名次</th><th rowspan="2">对比平均名次</th><th rowspan="2">差值</th>', tableThTr1 = '<tr>', tableTd = '';
        for (let obj of this.columnData) {
          tableThTr += '<th colspan="3">前'+obj.name+'名</th>';
          tableThTr1 += '<th>本次前 '+obj.name+' 名</th><th>对比前 '+obj.name+' 名</th><th>差值</th>';
        }
        for (let obj of this.classesTimeSetTable) {
          let tTr = '<tr>';
          tTr += '<td>' + (obj.subjectname||'') + '</td><td>' + (obj.className||'') + '</td><td>' + obj.join + '</td><td>' + obj.avg + '</td><td>' + obj.avga + '</td><td>' + obj.shortavg + '</td>';
          for (let mbj of this.columnData) {
            tTr += '<td>' + obj[mbj.value].rank1 + '</td><td>' + obj[mbj.value].rank2 + '</td><td>' + obj[mbj.value].shortrank + '</td>';
          }
          tTr += '</tr>';
          tableTd += tTr;
        }
        tableThTr += '</tr>';
        tableThTr1 += '</tr>';
        tablestr += tableThTr + tableThTr1 + '</thead><tbody>';
        tablestr += tableTd;
        tablestr += '</tbody></table>';
        if (type == 'copy') {
          if ($("#bstableCopy").length < 1) {
            var element = "<textarea id='bstableCopy' cols='20' rows='10' style='opacity: 0;position: fixed;'>复制内容</textarea>";
            $('.RankCompare').append(element);
          }
          $("#bstableCopy").html(tablestr);
          $("#bstableCopy").select(); // 选择对象
          document.execCommand("Copy");
          alert('复制成功,请粘贴到Excel表格中！');
        } else {
          var printCssStr = ".con{text-align:center;color: #343434;}.rostrum{width: 150px;padding: 8px 0;border: 1px solid #343434;margin:20px 0}";
          var printStyle, printContent;
          printStyle = "<style>" + printcss() + printCssStr + "</style>";
          printContent = printStyle + "<body>" + tablestr + "</body>";

          var newWin = window.open("");//新打开一个空窗口
          newWin.document.write(printContent);//将表格添加进新的窗口
          newWin.document.close();//在IE浏览器中使用必须添加这一句
          newWin.focus();//在IE浏览器中使用必须添加这一句

          newWin.print();//打印
          newWin.close();//关闭窗口

          function printcss() {
            var css = ".table-bordered {border: 1px solid #000;}.table {width: 100%;max-width: 100%;margin-bottom: 40px;}.table {border-spacing: 0;border-collapse: collapse;}.table td{border: 1px solid #000;vertical-align: middle;text-align:center;font-size:15px;height:30px;}.table th{padding:6px; vertical-align: top; border: 1px solid #000;text-align:center;  font-weight:bold;  font-size:13px;}.table div{  text-align:center; } .table .w_35{width:35px; }.table .w_100{width:100px; }.editdiv{ width: 100%;  border: 0;  height: 30px;   font-size: 15px;  font-weight: normal;  text-align: center;  line-height:30px; }.ng-table-filters{display:none;};input{width: 100%;border: 0;height:30px;line-height:30px;font-size: 15px;font-weight: normal;text-align: center;}";
            return css;
          }
        }
      },
      exportClick() {
        if (this.repairForm.branchid && this.repairForm.examinationid1 && this.repairForm.examinationid2) {
          let exportParam = '?examinationid1=' + this.repairForm.examinationid1 + '&examinationid2=' +
            this.repairForm.examinationid2 + '&branchid=' + this.repairForm.branchid + '&rank1=' +
            this.selectParam.rank1 + '&rank2=' + this.selectParam.rank2 + '&rank3=' + this.selectParam.rank3 +
            '&rank4=' + this.selectParam.rank4 + '&rank5=' + this.selectParam.rank5 + '&rank6=' + this.selectParam.rank6;
          req.downloadFile('.g-container', '/school/Achievement/contrast/type/rankexport' + exportParam, 'post');
        }
        else {
          this.vmMsgWarning('请选择所有相关信息！');
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

  /*排名分段*/
  .scoreRow {
    .marginTop(20);
  }

  .rankCount .grade {
    width: 8.75rem;
  }

  .rankCount .l_class {
    width: 9.375rem;
  }

  .rankCount .subject {
    width: 6.25rem;
  }

  .rankCount .test {
    width: 15.625rem;
  }

  .rankCount .fillLeft {
    margin-left: 2.5rem;
  }

  .rankCount .rankData {
    width: 3.75rem;
  }

  .rankCount .rankData .el-input__inner {
    height: 30px;
  }

  .rankCount .formInline .el-form-item {
    margin-right: 2.5rem;
    margin-bottom: 0;
  }
</style>


