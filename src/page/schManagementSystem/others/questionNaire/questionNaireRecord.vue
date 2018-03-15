<template>
  <div class="g-statisticalAnalysis g-container">
    <header class="g-textHeader">
      <h2>问卷记录</h2>
      <!-- <div class="g-flexEndRow g-sa_header_search">
        <div class="gs-refresh g-fuzzyInput">
          <el-input type="text" v-model="fuzzyInput" suffix-icon="el-icon-search" placeholder="班级/姓名" @change="fuzzyClick"></el-input>
        </div>
      </div> -->
    </header>
    <section class="centerTable alertsList">
      <el-table class="g-NotHover"
                v-loading="loading" 
                element-loading-text="拼命加载中" 
                element-loading-spinner="el-icon-loading"  
                :data="classesTimeSetTable">
        <el-table-column label="问卷名" prop="questionName"></el-table-column>
        <el-table-column label="创建时间" prop="createTime"></el-table-column>
        <el-table-column label="问卷状态">
          <template slot-scope="prop">
            <span v-if="Number(prop.row.state)">收集中</span>
            <span v-else>未发布</span>
          </template>
        </el-table-column>
        <el-table-column label="填写人数">
          <template slot-scope="prop">
            <el-button @click="fillInProgressClick(prop.row.questionId)" type="text" v-text="prop.row.userNum"></el-button>
          </template>
        </el-table-column>
        <el-table-column label="问卷浏览量" prop="browser"></el-table-column>
        <el-table-column label="操作" width="500px">
          <template slot-scope="prop">
            <el-button @click="handlerClick(prop.row.questionId)" :disabled="Boolean(Number(prop.row.state))" v-if="true" type="text">编辑</el-button>
            <el-button v-if="Number(prop.row.state)" :disabled="prop.row.userNum > 0" @click="ReleaseAjax(prop.row.questionId,'撤回')" class="deleteColor" type="text">撤回</el-button>
            <el-button v-else @click="ReleaseClick(prop.row.questionId,'发布')" type="text">发布</el-button>
            <el-button :class="[Number(prop.row.state)?'greenButtonColor':'']" :disabled="!Boolean(Number(prop.row.state))" @click="shareQuestionNaire(prop.row.questionId)" type="text">分享</el-button>
            <el-button class="greenButtonColor" @click="scanQuestionNaire(prop.row.questionId)" type="text">预览</el-button>
            <el-button class="greenButtonColor" @click="copyNaireAjax(prop.row.questionId)" type="text">复制</el-button>
            <el-button class="greenButtonColor" @click="downLoadAjax(prop.row.questionId)" type="text">下载</el-button>
            <el-button class="deleteColor" @click="deleteClick(prop.row.questionId)" type="text">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
    </section>
  </div>
</template>
<script>
  import {
    questionNaireRecordLoad,//得到问卷记录表
    questionNaireRecordDelete,//删除
    questionNaireRecordRelease,//发布
    questionNaireRecordCopy,//复制
  } from '@/api/http'
  import req from '@/assets/js/common'
  export default{
    data(){
      return{
        /*模糊查询*/
        fuzzyInput:'',
        evaluationName:'',
        /*table*/
        classesTimeSetTable:[{name:1},{name:2}],
        loading: false
      }
    },
    methods:{
      /*点击返回流程图按钮*/
      goBackChart(){
        this.$router.push({name:'evaluationManagement'});
      },
      /*模糊查询*/
      fuzzyClick(){
        /*模糊查询执行回调*/
      },
      /*table*/
      /*填写人数点击*/
      fillInProgressClick(questionId){
        this.$router.push({name:'teacherFillIn',params:{id:questionId}});
      },
      /*编辑*/
      handlerClick(questionId){
        this.$router.push({name:'newQuestionNaire',params:{id:'handle-'+questionId}});
      },
      /*预览*/
      scanQuestionNaire(questionId){
        this.$router.push({name:'scanQuestionNaire',params:{id:'0-'+questionId}});
      },
      /*分享*/
      shareQuestionNaire(questionId){
        /*false为判断是否发布*/
        this.$router.push({name:'shareQuestionNaire',params:{id:questionId}});
      },
      /*发布*/
      ReleaseClick(questionId,msg){
        this.$confirm('是否发布（发布后问卷不能再次编辑）？','提示',{
          confirmButtonText:'确定',
          cancelButtonText:'取消',
          type:'warning'
        }).then(()=>{
          this.ReleaseAjax(questionId,msg);
        }).catch(()=>{});
      },
      /*send ajax*/
      getLoadAjax(){
        this.loading = true;
        questionNaireRecordLoad().then(data=>{
          this.loading = false;
          if(data.statu){
            this.classesTimeSetTable=data.data;
          }
          else{
            this.vmMsgError( '数据加载失败，请重试！' );
            this.classesTimeSetTable=[];
          }
        });
      },
      /*删除*/
      deleteClick(questionId){
        this.$confirm('此操作将永久删除该问卷，是否继续？','提示',{
          confirmButtonText:'确定',
          cancelButtonText:'取消',
          type:'warning'
        }).then(()=>{
          questionNaireRecordDelete({questionId:questionId}).then(data=>{
            if(data.statu){
              this.vmMsgSuccess( '删除成功！' );
              this.getLoadAjax();
            }
            else{
              this.vmMsgError('删除失败，请重试！');
            }
          });
        }).catch(()=>{});
      },
      /*发布与撤回*/
      ReleaseAjax(questionId,msg){
        questionNaireRecordRelease({questionId:questionId}).then(data=>{
          if(data.statu){
            this.vmMsgSuccess(msg+'成功！');
            this.getLoadAjax();
          }
          else{
            this.vmMsgError(msg+'失败，请重试！');
          }
        });
      },
      /*下载*/
      downLoadAjax(questionId){
        req.downloadFile('.g-container','/school/Questionnaire/questionnaireRecord?type=export&questionId='+questionId,'post');
      },
      /*复制*/
      copyNaireAjax(questionId){
        questionNaireRecordCopy({questionId:questionId}).then(data=>{
          if(data.statu){
            this.vmMsgSuccess('复制成功！');
            this.getLoadAjax();
          }
          else{
            this.vmMsgError('复制失败，请重试！');
          }
        });
      },
    },
    created(){
      this.getLoadAjax();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/style';
  .g-sa_header_search{.marginTop(20);.marginBottom(20);}
</style>


