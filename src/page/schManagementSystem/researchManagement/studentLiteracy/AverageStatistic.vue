<template>
  <div class="g-container AverageStatistic">
    <header class="g-textHeader g-flexStartRow">
      <div class="g-headerButtonGroup">
        <h2>均分统计</h2>
      </div>
    </header>
    <div class="g-statisticalAnalysis">
      <header class="g-textHeader g-flexStartRow">
        <span class="selfCenter" style="margin-right:1.25rem;">方案名称:</span>
        <el-select v-model="repairForm.programmeId">
          <el-option v-for="(content,index) in repairOptionData" :key="index" :value="content.programmeId" :label="content.programmeName"></el-option>
        </el-select>
      </header>
      <section class="centerTable alertsList">
        <div class="g-liOneRow g-sa_header_search">
          <div class="gs-button alertsBtn">
            <el-button-group>
              <el-button @click="exportClick" data-msg="export" class="filt buttonChild" title="导出">
                <img class="filt_unactive"  src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png" />
                <img class="filt_active" src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png" />
              </el-button>
            </el-button-group>
            <el-button-group class="elGroupButton_two">
              <el-button data-msg="copy" class="filt buttonChild" title="复制" @click="operationData('copy')">
                <img class="filt_unactive"  src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png" />
                <img class="filt_active" src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png" />
              </el-button>
              <el-button  data-msg="print" class="filt buttonChild" title="打印预览" @click="operationData('print')">
                <img class="filt_unactive"  src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png" />
                <img class="filt_active" src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png" />
              </el-button>
            </el-button-group>
          </div>
          <div class="gs-refresh g-fuzzyInput">
            <el-input type="text" v-model="fuzzyInput" suffix-icon="el-icon-search" placeholder="请输入" @change="getLoadAjax"></el-input>
          </div>
        </div>
        <el-table
          v-loading.body="isLoading"
          element-loading-text="拼命加载中..."
          class="" :data="classesTimeSetTable" @sort-change="sortChange" @selection-change="handleSelectionChange">
          <el-table-column label="年级" prop="gradeName"></el-table-column>
          <el-table-column label="班级" prop="className"></el-table-column>
          <el-table-column label="被评人数" prop="count"></el-table-column>
          <el-table-column label="平均分数" prop="score"></el-table-column>
        </el-table>
      </section>
    </div>
  </div>
</template>
<script>
  import {
    AverageStatisticName,//方案名称
    AverageStatisticLoad,//加载
  } from '@/api/http'
  import req from '@/assets/js/common'
  export default{
    data(){
      let _self=this;
      return{
        isLoading:false,
        /*模糊查询*/
        fuzzyInput:'',
        evaluationName:'',
        /*form表单*/
        repairForm:{
          programmeId:'',
        },
        repairOptionData:[],
        /*table*/
        classesTimeSetTable:[],
      }
    },
    methods:{
      operationData(type){
        let sAy = [], hdData = {
          gradeName: '年级',
          className: '班级',
          count: '被评人数',
          score: '平均分数',
        };
        sAy.push(hdData);
        for (let obj of this.classesTimeSetTable) {
          let d = {};
          for (let name in hdData) {
            d[name] = obj[name] || '';
          }
          sAy.push(d);
        }
        if (type === 'copy') {
          req.copyTableData('.AverageStatistic', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      /*点击返回流程图按钮*/
      goBackChart(){
        this.$router.push({name:'evaluationManagement'});
      },
      /*table*/
      handleSelectionChange(choose){
      },
      sortChange(value){
      },
      /*模糊查询*/
      fuzzyClick(){
        /*模糊查询执行回调*/
      },
      /*弹框——维修详情*/
      handlerDetailClose(done){
        done();
      },
      /*send ajax*/
      /*方案名称*/
      getProjectNameAjax(){
        AverageStatisticName().then(data=>{
          this.repairOptionData=data;
          if(data.length>0){
            this.repairForm.programmeId=this.repairOptionData[0].programmeId;
          }
        })
      },
      getLoadAjax(){
        this.isLoading=true;
        AverageStatisticLoad({...this.repairForm,find:this.fuzzyInput}).then(data=>{
          this.classesTimeSetTable=data;
          this.isLoading=false;
        });
      },
      /*导出*/
      exportClick(){
        let _paramUrl='?programmeId='+this.repairForm.programmeId;
        req.downloadFile('.g-container','/school/Accomplishment/junfen/type/export'+_paramUrl,'post');
      },
    },
    watch:{
        'repairForm.programmeId':function(val){
          this.getLoadAjax(val)
        }
    },
    created(){
      this.getProjectNameAjax();
    },
  }
</script>
<style lang="less" scoped>
  /*@import '../../../../../style/test';*/
  @import '../../../../style/style';
  @import '../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.css';
  @import '../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.less';
  .g-sa_header_search{.marginTop(32);.marginBottom(20);}
  .g-classSchedule .g-container{padding:0;}
  .g-statisticalAnalysis header.g-textHeader.g-flexStartRow{.marginTop(20);.marginBottom(20);border-bottom:1px solid @borderColor;}
  .g-textHeader .el-form{.width(825,1582);}
  .g-textHeader .el-form-item{float:left;margin-bottom:0;}
  .g-textHeader .el-form-item:nth-of-type(1){.width(222,825);}
  .g-textHeader .el-form-item:nth-of-type(2){.width(558,825);.marginLeft(40,825);}
  .g-liOneRow.g-sa_header_search{margin-top:0;}

  .defineSelect{margin-right:20/16rem;}
</style>


