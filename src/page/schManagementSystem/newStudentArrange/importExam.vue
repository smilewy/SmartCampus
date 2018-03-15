<template>
  <div class="g-container">
    <header class="g-textHeader">
      <div class="g-flexStartRow">
        <el-button class="g-gobackChart g-imgContainer RedButton" @click="goBackChart">
          <img src="../../../assets/img/schManagementSystem/teachingAdministration/arrangeClasses/icon_return.png" />
          返回上一步
        </el-button>
        <h2 class="selfCenter">导入成绩</h2>
      </div>
    </header>
    <section class="g-section">
      <div class="g-flexStartWrapRow">
        <div class="g-examEntry g-liOneSpaceRow" v-for="(row,rowI) in examData">
          <el-progress class="g-examChart" :width="100" type="circle" :percentage="row.totalNumber!=='0'?row.recordNumber*100/row.totalNumber:0" :stroke-width="10"></el-progress>
          <div class="g-examTextContainer">
            <h4 v-text="row.subject"></h4>
            <div class="g-examTextRow">
              <span>总数:</span><span v-text="row.totalNumber"></span>
            </div>
            <div class="g-examTextRow">
              <span>已录:</span><span v-text="row.recordNumber"></span>
            </div>
            <div class="g-examTextRow">
              <span>未录:</span><span v-text="row.totalNumber-row.recordNumber"></span>
            </div>
          </div>
          <el-button @click="examEntryClick(row)" type="primary" class="radiusButton examRadiusButton">成绩录入</el-button>
        </div>
      </div>
    </section>
  </div>
</template>
<script>
  import {
    organizeResultsImportScore,//操作
  } from '@/api/http'
  export default{
    data(){
      return{
        examData:[],
        /*send ajax params*/
        gradeId:'',
        examId:'',
      }
    },
    computed: {},
    methods:{
      /*点击返回流程图按钮*/
      goBackChart(){
        this.$router.push({name:'organizeResults',params:{gradeId:this.gradeId}});
      },
      /*成绩录入*/
      examEntryClick(row){
        this.$router.push({name:'scoreEntry',params:{maxPoint:row.maxPoint,gradeId:this.gradeId,examId:this.examId,subId:row.id}});
      },
      /*send ajax*/
      getLoadAjax(){
        organizeResultsImportScore({gradeId:this.gradeId,examId:this.examId}).then(data=>{
          if(data.status){
            this.examData=data.data;
          }
          else{
            this.alertPrompt('暂无数据','error');
            this.examData=[];
          }
        })
      },
    },
    created(){
      this.gradeId=this.$route.params.gradeId;
      this.examId=this.$route.params.examId;
      this.getLoadAjax();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../style/style';
  .g-textHeader{
    .marginBottom(30);
    h2{.marginLeft(40,1582);}
  }
  .g-examEntry{margin-right:20/16rem;margin-bottom:40/16rem;}
</style>








