<template>
  <div class="g-statisticalAnalysis g-container">
    <header class="g-textHeader">
      <h2>学生素养评分</h2>
    </header>
    <section class="centerTable alertsList">
      <el-table
        v-loading.body="isLoading"
        element-loading-text="拼命加载中..."
        class="g-NotHover" :data="classesTimeSetTable">
        <el-table-column label="方案名称" prop="programmeName"></el-table-column>
        <el-table-column label="考核方向" prop="directionName"></el-table-column>
        <el-table-column label="考评进度">
          <template slot-scope="props">
            <div class="g-dialogDiv" style="cursor: pointer;" @click="ShowprocessDatails(props.row)">
              <span class="g-processPrompt" v-text="props.row.schedule"></span>
              <el-progress :text-inside="true" :show-text="false" :stroke-width="20" :percentage="props.row.percentage"></el-progress>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="创建时间" prop="createTime"></el-table-column>
        <el-table-column label="操作" fixd="right">
          <template slot-scope="props">
            <el-button :disabled="true" v-if="props.row.state==1" type="text">已发布</el-button>
            <el-button @click="publishScore(props.row.programmeId,props.row.schedule)" v-if="props.row.state==0" type="text">发布</el-button>
            <el-button @click="assetsScoreClick(props.row.programmeId)" v-if="props.row.state==0 || props.row.state==2" type="text">评分</el-button>
          </template>
        </el-table-column>
      </el-table>
    </section>
    <el-dialog title="学生名单" v-if="showprocess" :modal="false" :visible.sync="showprocess">
      <el-row class="alertsList" style="overflow-y: auto">
        <el-table
          v-loading.body="isLoading"
          element-loading-text="拼命加载中..."
          :data="detailDate"
          style="width: 100%;"
          border>
          <el-table-column
            type="index"
            label="序号"
            align="center"
            width="100">
          </el-table-column>
          <el-table-column
            prop="gradename"
            label="年级"
            align="center">
          </el-table-column>
          <el-table-column
            prop="classname"
            label="班级"
            align="center">
          </el-table-column>
          <el-table-column
            label="学生姓名"
            align="center"
            prop="name">
          </el-table-column>
          <el-table-column
            label="是否评分"
            align="center"
            prop="score">
            <template slot-scope="scope">
              <span v-if="scope.row.score==true">是</span>
              <span v-if="scope.row.score==false">否</span>
            </template>
          </el-table-column>
        </el-table>
      </el-row>
      <el-row class="pageAlerts">
        <el-pagination
          @current-change="handleCurrentChange"
          :current-page.sync="currentPage"
          :page-size="10"
          layout="prev, pager, next, jumper"
          :total="total">
        </el-pagination>
      </el-row>
    </el-dialog>
  </div>
</template>
<script>
  import {
    studentAssessScoreLoad,//加载
    studentAssessScorePublish,//发布
  } from '@/api/http'
  import req from './../../../../assets/js/common'
  export default{
    data(){
      return{
        isLoading:false,
        /*模糊查询*/
        fuzzyInput:'',
        evaluationName:'',
        /*table*/
        classesTimeSetTable:[],
        detailDate:[],
        detailDateTotal:[],
        showprocess:false,
        detailId:'',
        currentPage: 1,
        pageCount: 10,
        total:0,
      }
    },
    methods:{
      ShowprocessDatails(row){
        this.detailId=row.programmeId;
        this.currentPage =1;
        this.getTableData(1);
//        this.detailDateTotal=[];
        this.showprocess=true;
      },
      getTableData(page){
        if(page!==this.currentPage){
          this.currentPage=page;
        }
        this.isLoading=true;
        let param={
          programmeId:this.detailId,
        };
        this.isLoading=true;
        req.ajaxSend('/school/Accomplishment/pingfen/type/pingfenxinxi','post',param,(res)=>{
          if (res.length ) {
            this.detailDateTotal=res;
            this.total = res.length;
          }
          this.changePageMsg();
          this.isLoading=false;
        });
      },
      handleCurrentChange(val) {
        this.currentPage = val;
        this.changePageMsg()
      },
      changePageMsg(){
        this.detailDate=[];
        let count=(this.currentPage-1)*(this.pageCount);
        if(this.currentPage*this.pageCount>this.detailDateTotal.length){
          /*最后一页*/
          for(let i=count;i<this.detailDateTotal.length; i++){
            this.detailDate.push(this.detailDateTotal[i]);
          }
        }
        else{
          for(let i=count;i<this.currentPage*this.pageCount; i++){
            this.detailDate.push(this.detailDateTotal[i]);
          }
        }
      },
      /*模糊查询*/
      fuzzyClick(){
        /*模糊查询执行回调*/
      },
      /*评分*/
      assetsScoreClick(programmeId){
        this.$router.push({name:'ChildAssessScore',params:{id:programmeId}});
      },
      /*send ajax*/
      getLoadAjax(){
        this.isLoading=true;
        studentAssessScoreLoad().then(data=>{
          this.classesTimeSetTable=data;
          this.isLoading=false;
        });
      },
      /*发布成绩和取消发布*/
      publishScore(programmeId,schedule){
        if(schedule.split('/')[0]!==schedule.split('/')[1]){
          this.vmMsgWarning( '评分未完成，不能发布' ); return;
        }
        studentAssessScorePublish({programmeId:programmeId}).then(data=>{
          this.simplePrompt(data,'发布成功！','发布失败，请重试！');
        });
      },
      simplePrompt(data,suceessmsg,errMsg){
        if(data.return){
          this.vmMsgSuccess( suceessmsg );
          this.getLoadAjax();
        }
        else{
          this.vmMsgError( errMsg );
        }
      },
    },
    created(){
      this.getLoadAjax();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/style';
  @import '../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.css';
  @import '../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.less';
  .g-sa_header_search{.marginTop(32);.marginBottom(20);}
  /*进度条上的文字*/
  .g-processPrompt{position:absolute;z-index:10;}
</style>


