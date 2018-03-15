<template>
  <div class="g-statisticalAnalysis g-container">
    <header class="g-estatisticalAnalysisHeader">
      <div class="g-flexStartRow">
        <!--<el-button class="g-gobackChart g-imgContainer RedButton" @click="goBackChart">-->
        <!--<img src="../../../../assets/img/commonImg/icon_return.png"/>-->
        <!--返回流程图-->
        <!--</el-button>-->
        <h2 class="selfCenter g-headerH">统计分析</h2>
      </div>
      <div class="g-liOneRow g-sa_header_search">
        <div class="g-flexStartRow">
          <div class="defineSelect g-sa_header_left">
            <span>考评名称:</span>
            <el-select v-model="evaluationId" placeholder="请选择考评名称">
              <el-option v-for="(content,index) in evaluationNData" :key="index" :label="content.name" :value="content.id"></el-option>
            </el-select>
          </div>
          <div class="defineSelect g-js_selectPart">
            <span>被考评分组:</span>
            <el-select v-model="IsEvaluationId">
              <el-option v-for="(content,index) in IsEvaluationOption" :key="index" :label="content.name" :value="content.id"></el-option>
            </el-select>
          </div>
        </div>
        <!--        <div class="gs-refresh g-fuzzyInput">
                  <el-input type="text" v-model="fuzzyInput" suffix-icon="el-icon-search" placeholder="班级/姓名" @change="fuzzyClick"></el-input>
                </div>-->
      </div>
    </header>
    <section class="alertsList g-statisticalAnalysisSection">
      <el-table
        v-loading.body="isLoading"
        element-loading-text="拼命加载中..."
        :data="classesTimeSetTable">
        <el-table-column label="序号" width="100px" type="index"></el-table-column>
        <el-table-column label="姓名" prop="name"></el-table-column>
        <el-table-column label="德（25分）">
          <template slot-scope="props">
            <span v-text="props.row.score[0]"></span>
          </template>
        </el-table-column>
        <el-table-column label="能（25分）">
          <template slot-scope="props">
            <span v-text="props.row.score[1]"></span>
          </template>
        </el-table-column>
        <el-table-column label="勤（25分）">
          <template slot-scope="props">
            <span v-text="props.row.score[2]"></span>
          </template>
        </el-table-column>
        <el-table-column label="绩（25分）">
          <template slot-scope="props">
            <span v-text="props.row.score[3]"></span>
          </template>
        </el-table-column>
        <el-table-column
          v-for="colume in columes"
          :key="colume.prop"
          :label="colume.label"
          align="center">
          <template slot-scope="props">
            <span>{{props.row.score[colume.prop]}}</span>
          </template>
        </el-table-column>
        <!--<el-table-column v-for="(col,colI) in columnData" :key="colI" :label="col.name" :prop="col.id"></el-table-column>-->
      </el-table>
    </section>
    <footer class="g-footer">
      <el-row class="pageAlerts">
        <el-pagination
          @current-change="handleCurrentChange"
          :current-page.sync="currentPage"
          layout="prev, pager, next, jumper"
          :page-count="pageAll"
        >
        </el-pagination>
      </el-row>
    </footer>
  </div>
</template>
<script>
  import {
    statisticalAnalysisParams,//考评名称
    statisticalAnalysisLoad,//流程状态
  } from '@/api/http'
  export default{
    data(){
      return{
        isLoading:false,
        /*模糊查询*/
        fuzzyInput:'',
        evaluationName:'',
        /*table*/
        classesTimeSetTable:[],
        columes:[],
        /*考评名称*/
        IsEvaluationOption:[],//被考评data
        evaluationId:'',//考评方案value绑定数据
        evaluationNData:[],
        IsEvaluationId:'',//被考评分组value绑定数据
        /*footer*/
        pageAll:1,
        currentSize:10,
        currentPage:1,
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
        this.getLoadAjax();
      },
      /*模糊查询*/
      fuzzyClick(){
        /*模糊查询执行回调*/
      },
      /*考评名称change事件——被考评分组*/
      getIsGroup(newVal){
        this.IsEvaluationId=null;
        let obj = this.evaluationNData.filter(val=>val.id===newVal)[0];
        if(obj){
          if('group' in obj){
            this.IsEvaluationOption=obj['group'];
          }
          else{
            this.IsEvaluationOption=[];
          }
          if(this.IsEvaluationOption.length>0){
            this.IsEvaluationId=this.IsEvaluationOption[0].id;
          }
        }
      },
      /*被考评分组*/
      isEvaluationChange(newVal){
        let obj = this.IsEvaluationOption.filter(val=>val.id===newVal)[0];
        if(obj){
          if('id' in obj){
            this.getLoadAjax();
          }
          else{
            this.vmMsgWarning( '请选择被考评分组！' );
          }
        }
      },
      /*send ajax*/
      /*考评名称*/
      getEvaluationName(){
        statisticalAnalysisParams({sort:2}).then(data=>{
          if(data.status){
            if(data.data.length>0){
              this.evaluationId=data.data[0].id;
            }
            this.evaluationNData=data.data;
          }
          else{
            this.vmMsgError('未在被考评分组内！');
            this.evaluationId=null;
            this.evaluationNData=[];
          }
        })
      },
      /*页面状态*/
      getLoadAjax(){
        this.isLoading=true;
        statisticalAnalysisLoad({id:this.evaluationId,page:this.currentPage,count:this.currentSize,groupId:this.IsEvaluationId}).then(data=>{
          if(data.status){
            this.classesTimeSetTable=data.data;
            this.columes = data.group.map(val=>{
              return {
                label:val.name,
                prop:val.id
              }
            });
            this.pageAll=Number(data.maxPage);
          }
          else{
            this.classesTimeSetTable=[];
            this.columnData=[];
            this.pageAll=1;
          }
          this.isLoading=false;
        });
      },
    },
    created(){
      this.getEvaluationName();
    },
    watch:{
      evaluationId(val){
        this.getIsGroup(val);
      },
      IsEvaluationId(val){
        this.isEvaluationChange(val)
      }
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/style';
  @import '../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.css';
  @import '../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.less';
  .g-sa_header_search{.marginTop(32);.marginBottom(20);}
  .g-js_selectPart{margin-left:30/16rem;}
</style>


