<template>
  <div class="g-statisticalAnalysis g-container">
    <header class="g-textHeader">
      <h2>考评记录</h2>
      <!--<div class="g-liOneRow g-sa_header_search">
        <div class="gs-button alertsBtn">
          <el-button-group>
            <el-button data-msg="export" class="filt buttonChild" title="导出">
              <img class="filt_unactive"  src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png" />
              <img class="filt_active" src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png" />
            </el-button>
          </el-button-group>
          <el-button-group class="elGroupButton_two">
            <el-button data-msg="copy" class="filt buttonChild" title="复制">
              <img class="filt_unactive"  src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png" />
              <img class="filt_active" src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png" />
            </el-button>
            <el-button  data-msg="print" class="filt buttonChild" title="打印预览">
              <img class="filt_unactive"  src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png" />
              <img class="filt_active" src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png" />
            </el-button>
          </el-button-group>
        </div>
        <div class="gs-refresh g-fuzzyInput">
          <el-input type="text" v-model="fuzzyInput" suffix-icon="el-icon-search" placeholder="班级/姓名" @change="fuzzyClick"></el-input>
        </div>
      </div>-->
    </header>
    <section class="centerTable alertsList">
      <el-table
        v-loading.body="isLoading"
        element-loading-text="拼命加载中..."
        class="g-NotHover" :data="classesTimeSetTable">
        <el-table-column label="考评名称" prop="name"></el-table-column>
        <el-table-column label="创建时间" prop="createTime"></el-table-column>
        <el-table-column label="考评进度">
          <template slot-scope="props">
            <div @click="CheckDataTo(props.row)">
              <el-progress :text-inside="true" :stroke-width="20" :percentage="props.row.rate"></el-progress>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="开始时间" prop="startTime"></el-table-column>
        <el-table-column label="截止时间" prop="endTime"></el-table-column>
        <el-table-column label="操作" fixd="right">
          <template slot-scope="props">
            <el-button @click="publishScore(props.row,props.$index,'撤回')" class="destoryColor" v-if="Number(props.row.publish)" type="text">撤回</el-button>
            <el-button @click="publishScore(props.row,props.$index,'发布成绩')" v-else type="text">发布成绩</el-button>
            <el-button @click="deleteClick(props.row.id)" class="deleteColor" type="text">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
    </section>
    <el-dialog title="考评进度" :visible.sync="progressShow" :modal="false">
      <el-row>
        <el-col :span="24" style="height: 40rem;overflow-y: auto">
          <el-col :span="24">
            <el-radio-group v-model="radio" @change="changeData">
              <el-radio :label="1">已考评</el-radio>
              <el-radio :label="0">未考评</el-radio>
            </el-radio-group>
          </el-col>
          <el-col :span="24" style="margin-top: 2rem">
            <el-table
              v-loading.body="isLoading"
              element-loading-text="拼命加载中..."
              :data="dialogChangeData">
              <el-table-column
                type="index"
                label="序号"
                width="100"
                align="center">
              </el-table-column>
              <el-table-column
                prop="name"
                label="姓名"
                align="center">
              </el-table-column>
            </el-table>
          </el-col>
        </el-col>
      </el-row>
    </el-dialog>
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
    evaluationRecordLoad,//操作
  } from '@/api/http'
  import req from './../../../../assets/js/common'
  export default{
    data(){
      return{
        isLoading:false,
        dialogData:[],
        dialogChangeData:[],
        radio:1,
        /*模糊查询*/
        fuzzyInput:'',
        evaluationName:'',
        /*table*/
        classesTimeSetTable:[],
        /*footer*/
        pageAll:1,
        currentPage:1,
        pageCount:10,
        progressShow:false,
      }
    },
    methods:{
      CheckDataTo(row){
        this.dialogChangeData=[];
        this.radio=1;
        req.ajaxSend('/school/TeacherEvaluate/trackProgress','post',{id:row.id}, (res)=> {
          this.dialogData=res.data;
          if(res.data.length){
            this.dialogChangeData=res.data[0].lists;
          }
        });
        this.progressShow=true
      },
      changeData(val){
        if(this.dialogData.length){
          if(val===1){
            this.dialogChangeData=this.dialogData[0].lists;
          }else{
            this.dialogChangeData=this.dialogData[1].lists;
          }
        }
      },
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
      /*send ajax*/
      getLoadAjax(){
        this.isLoading=true;
        evaluationRecordLoad({page:this.currentPage,count:this.pageCount}).then(data=>{
          if(data.status){
            this.classesTimeSetTable=data.data;
            this.pageAll=data.maxPage;
          }
          else{
            this.classesTimeSetTable=[];
            this.pageAll=1;
          }
          this.isLoading=false;
        });
      },
      /*发布成绩和取消发布*/
      publishScore(row,idx,msg){
        if(Number(this.classesTimeSetTable[idx].publish)){
          /*已发布，需要取消发布*/
          evaluationRecordLoad({type:'publish',logId:this.classesTimeSetTable[idx].id,status:0}).then(data=>{
            this.simplePrompt(data,msg);
          });
        }
        else{
          if(row.rate!==100){
            this.vmMsgWarning( '考评未完成，不能发布成绩' ); return;
          }
          evaluationRecordLoad({type:'publish',logId:this.classesTimeSetTable[idx].id,status:1}).then(data=>{
            this.simplePrompt(data,msg);
          });
        }
      },
      /*删除*/
      deleteClick(logId){
        this.$confirm('是否删除数据？','提示',{
          confirmButtonText:'确定',
          cancelButtonText:'取消',
          type:'warning'
        }).then(()=>{
          evaluationRecordLoad({type:'del',logId:logId}).then(data=>{
            this.simplePrompt(data,'删除');
          });
        }).catch(()=>{});
      },
      simplePrompt(data,msg){
        if(data.status){
          this.vmMsgSuccess( msg+'成功！' );
          this.getLoadAjax();
        }
        else{
          this.vmMsgError( msg+'失败！' );
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
  .g-statisticalAnalysis .el-progress-bar__inner{
    text-align: center;
  }
</style>


