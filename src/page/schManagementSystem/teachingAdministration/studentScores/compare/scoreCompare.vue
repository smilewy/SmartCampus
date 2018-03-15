<template>
  <div class="g-statisticalAnalysis g-container scoreCompare">
    <header>
      <h2>成绩对比</h2>
      <div class="g-textHeader g-flexStartRow">
        <el-form :model="repairForm" :inline="true">
          <el-form-item label="年级：">
            <el-select @change="gradeChange" v-model="repairForm.gradeid">
              <el-option v-for="(content,index) in gradeAjaxData" :key="index" :value="content.gradeid" :label="content.name"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="科类：">
            <el-select v-model="repairForm.branchid" @change="subjectChange">
              <el-option v-for="(content,index) in subjectAjaxData" :key="index" :value="content.branchid" :label="content.branch"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="班级：">
            <el-select multiple v-model="repairForm.class" @change="classChange">
              <el-option v-for="(content,index) in classAjaxData" :key="index" :value="content.clsssid" :label="content.clsssname"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="本次考试：">
            <el-select v-model="repairForm.examinationid1">
              <el-option v-for="(content,index) in currentExamData" :key="index" :value="content.examinationid" :label="content.examination"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="对比考试：">
            <el-select v-model="repairForm.examinationid2">
              <el-option v-for="(content,index) in compareExamData" :key="index" :value="content.examinationid" :label="content.examination"></el-option>
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
              <img class="filt_unactive"  src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png" />
              <img class="filt_active" src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png" />
            </el-button>
          </el-button-group>
          <el-button-group class="elGroupButton_two">
            <el-button data-msg="copy" class="filt buttonChild" title="复制" @click="operationData('copy')">
              <img class="filt_unactive"  src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png" />
              <img class="filt_active" src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png" />
            </el-button>
            <el-button  data-msg="print" class="filt buttonChild" title="打印" @click="operationData('print')">
              <img class="filt_unactive"  src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png" />
              <img class="filt_active" src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png" />
            </el-button>
          </el-button-group>
        </div>
        <div class="gs-refresh g-fuzzyInput">
          <el-input type="text" v-model="fuzzyInput" suffix-icon="el-icon-search" placeholder="班级/座号/姓名" @change="getTableLoad"></el-input>
        </div>
      </div>
      <el-table class="g-NotHover" border :data="classesTimeSetTable" @sort-change="sortChange"
                @selection-change="handleSelectionChange"
                v-loading="loading"
                element-loading-text="拼命加载中">
        <el-table-column label="班级" prop="className" sortable="custom"></el-table-column>
        <el-table-column label="座号" prop="serialNumber" sortable="custom"></el-table-column>
        <el-table-column label="姓名" prop="name" sortable="custom"></el-table-column>
        <el-table-column label="总分">
          <el-table-column label="本次成绩">
            <template slot-scope="prop">
              <span v-text="prop.row.exa.zf.results"></span>
            </template>
          </el-table-column>
          <el-table-column label="名次">
            <template slot-scope="prop">
              <span v-text="prop.row.exa.zf.ranking"></span>
            </template>
          </el-table-column>
          <el-table-column label="上次成绩">
            <template slot-scope="prop">
              <span v-text="prop.row.exa.zf.resultsContrast"></span>
            </template>
          </el-table-column>
          <el-table-column label="名次">
            <template slot-scope="prop">
              <span v-text="prop.row.exa.zf.rankingContrast"></span>
            </template>
          </el-table-column>
          <el-table-column label="进步">
            <template slot-scope="prop">
              <span v-text="prop.row.exa.zf.progress"></span>
            </template>
          </el-table-column>
        </el-table-column>
        <el-table-column v-for="(column,columnI) in columnData" :key="columnI" :label="column.subjectname">
          <el-table-column label="本次成绩">
            <template slot-scope="prop">
              <span v-text="prop.row.exa[column.subjectid].results"></span>
            </template>
          </el-table-column>
          <el-table-column label="名次">
            <template slot-scope="prop">
              <span v-text="prop.row.exa[column.subjectid].ranking"></span>
            </template>
          </el-table-column>
          <el-table-column label="上次成绩">
            <template slot-scope="prop">
              <span v-text="prop.row.exa[column.subjectid].resultsContrast"></span>
            </template>
          </el-table-column>
          <el-table-column label="名次">
            <template slot-scope="prop">
              <span v-text="prop.row.exa[column.subjectid].rankingContrast"></span>
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
    compareComGetClass,//得到年级
    compareExamComGetExam,//得到考试信息（对比考试与本次考试）
    compareScoreLoad,//页面查询信息
  } from '@/api/http'
  import req from '@/assets/js/common'
  export default{
    data(){
      let _self=this;
      return{
        /*模糊查询*/
        fuzzyInput:'',
        /*form表单*/
        repairForm:{
          class:[],//class
          examinationid1:'',//本次考试
          examinationid2:'',//对比考试
          gradeid:'',
          branchid:'',
        },
        gradeAjaxData:[],
        classAjaxData:[],
        subjectAjaxData:[],//科类加班级
        currentExamData:[],
        compareExamData:[],
        /*footer*/
        pageAll:1,
        currentPage:1,
        pageCount:10,
        /*table*/
        classesTimeSetTable:[],
        columnData:[],
        /*send ajax param*/
        orderBy:{
          order:'',
          field:'',//排序字段
        },
        loading:false
      }
    },
    methods:{
      /*点击返回流程图按钮*/
      goBackChart(){
        this.$router.push({name:'evaluationManagement'});
      },
      /*footer*/
      handleCurrentChange(val) {
        this.currentPage = val;
        this.getTableLoad();
      },
      /*科类change事件*/
      subjectChange(){
        this.repairForm.examinationid1='';
        this.repairForm.examinationid2='';
        this.repairForm.class=[];
        this.currentExamData=[];
        this.compareExamData=[];
        this.subjectAjaxData.forEach((row)=>{
          if(row.branchid==this.repairForm.branchid){
            this.classAjaxData=row.classlist;
          }
        });
      },
      /*班级change事件*/
      classChange(){
        this.repairForm.examinationid1='';
        this.repairForm.examinationid2='';
        this.currentExamData=[];
        this.compareExamData=[];
        this.getExamMsg();
      },
      /*年级变化*/
      gradeChange(){
        this.repairForm.examinationid1='';
        this.repairForm.examinationid2='';
        this.repairForm.class=[];
        this.repairForm.branchid='';
        this.subjectAjaxData=[];
        this.classAjaxData=[];
        this.currentExamData=[];
        this.compareExamData=[];
        this.getClassAjax();
      },
      /*查询*/
      searchClick(){
        if(this.repairForm.class.length>0 && this.repairForm.branchid && this.repairForm.examinationid1 && this.repairForm.examinationid2){
          if(this.repairForm.examinationid1==this.repairForm.examinationid2){
            this.vmMsgWarning('选择的两次考试一样！');
          }
          else{
            this.getTableLoad();
          }
        }
        else{
          this.vmMsgWarning('请选择所有相关信息！');
        }
      },
      /*table*/
      handleSelectionChange(choose){
      },
      sortChange(value){
        this.orderBy.order=value.order;
        this.orderBy.field=value.prop;
        this.getTableLoad();
      },
      handlerTaskClick(){
        this.$router.push('/handlerTask');
      },
      /*模糊查询*/
      fuzzyClick(){
        /*模糊查询执行回调*/
      },
      /*send ajax*/
      getGradeAjax(){
        compareComGetGrade().then((data)=>{
          this.gradeAjaxData=data;
          if(this.gradeAjaxData.length>0){
            this.repairForm.gradeid=this.gradeAjaxData[0].gradeid;
            this.getClassAjax();
          }
        });
      },
      getClassAjax(){
        compareComGetClass({gradeid:this.repairForm.gradeid}).then(data=>{
          this.subjectAjaxData=data;
          if(this.subjectAjaxData.length>0){
            this.repairForm.branchid=this.subjectAjaxData[0].branchid;
            this.subjectChange();
          }
        });
      },
      getExamMsg(){
        compareExamComGetExam({gradeid:this.repairForm.gradeid,classid:this.repairForm.class}).then(data=>{
          this.currentExamData=data;
          this.compareExamData=data;
        });
      },
      /*得到表格信息*/
      getTableLoad(){
        this.loading=true;
        compareScoreLoad({page:this.currentPage,limit:this.pageCount,find:this.fuzzyInput,...this.orderBy,...this.repairForm}).then(data=>{
          this.loading=false;
          if(!data.return){
            this.vmMsgWarning('不是同一群体或没有相同的考试科目！');
          }else{
            this.classesTimeSetTable=data.data;
            this.columnData=data.subjectlist;
            this.pageAll=data.pageclass.pageAll;
          }
        });
      },
      operationData(type){
        var tablestr = '<table class="table table=bordered" style="margin-bottom:0"><thead>';
        let tableThTr = '<tr><th rowspan="2">班级</th><th rowspan="2">座号</th><th rowspan="2">姓名</th>', tableThTr1 = '<tr>', tableTd = '';
        tableThTr += '<th colspan="5">总分</th>';
        tableThTr1 += '<th>本次成绩</th><th>名次</th><th>上次成绩</th><th>名次</th><th>进步</th>';
        for (let obj of this.columnData) {
          tableThTr += '<th colspan="4">'+obj.subjectname+'</th>';
          tableThTr1 += '<th>本次成绩</th><th>名次</th><th>上次成绩</th><th>名次</th>';
        }
        for (let obj of this.classesTimeSetTable) {
          let tTr = '<tr>';
          tTr += '<td>' + (obj.className||'') + '</td><td>' + (obj.serialNumber||'') + '</td><td>' + obj.name + '</td><td>' + obj.exa.zf.results + '</td><td>' + obj.exa.zf.ranking + '</td><td>' + obj.exa.zf.resultsContrast + '</td><td>' + obj.exa.zf.rankingContrast + '</td><td>' + obj.exa.zf.progress + '</td>';
          for (let mbj of this.columnData) {
            tTr += '<td>' + obj.exa[mbj.subjectid].results + '</td><td>' + obj.exa[mbj.subjectid].ranking + '</td><td>' + obj.exa[mbj.subjectid].resultsContrast + '</td><td>' + obj.exa[mbj.subjectid].rankingContrast + '</td>';
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
            $('.scoreCompare').append(element);
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
      exportClick(){
        if(this.repairForm.class.length>0 && this.repairForm.branchid && this.repairForm.examinationid1 && this.repairForm.examinationid2){
          let exportParam='?examinationid1='+this.repairForm.examinationid1+'&examinationid2='+this.repairForm.examinationid2+'&class='+this.repairForm.class+'&branchid='+this.repairForm.branchid;
          req.downloadFile('.g-container','/school/Achievement/contrast/type/resultexport'+exportParam,'post');
        }
        else{
          this.vmMsgWarning('请选择所有相关信息！');
        }
      }
    },
    created(){
      this.getGradeAjax();
    }
  }
</script>
<style lang="less" scoped>
  /*@import '../../../../../style/test';*/
  @import '../../../../../style/style';
  @import '../../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.css';
  @import '../../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.less';
  section.centerTable{.marginTop(20);}
  .g-sa_header_search{.marginTop(32);.marginBottom(20);}
  .g-classSchedule .g-container{padding:0;}
  .g-statisticalAnalysis header h2{.marginBottom(20);}
  .g-textHeader .el-form{}
  .g-textHeader .el-form-item .el-select{width:140/16rem;}
  .g-textHeader .el-form-item{margin-right:30/16rem;float:left;margin-bottom:0;}
  .g-textHeader .el-form-item:not(:first-of-type){}
  .g-liOneRow.g-sa_header_search{margin-top:0;}
</style>


