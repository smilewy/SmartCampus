<template>
  <div class="g-container">
    <header>
      <div class="g-textHeader g-liOneRow">
        <h2>填写任务</h2>
      </div>
    </header>
    <section class="g-section">
      <!--<div class="gs-header g-liOneRow">
        <div class="gs-button alertsBtn">
          <el-button-group>
            <el-button @click="" data-msg="export" class="filt" title="导出">
              <img class="filt_unactive" src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png" />
              <img class="filt_active" src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png" />
            </el-button>
          </el-button-group>
          <el-button-group class="elGroupButton_two">
            <el-button @click="" data-msg="copy" class="filt" title="复制">
              <img class="filt_unactive" src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png" />
              <img class="filt_active" src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png" />
            </el-button>
            <el-button @click="" data-msg="print" class="filt" title="打印">
              <img class="filt_unactive" src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png" />
              <img class="filt_active" src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png" />
            </el-button>
          </el-button-group>
        </div>
&lt;!&ndash;        <div class="g-fuzzyInput selfCenter">
          <el-input type="text" v-model="fuzzyInput" suffix-icon="el-icon-search" @change="fuzzyClick"></el-input>
        </div>&ndash;&gt;
      </div>-->
      <div class="centerTable alertsList">
        <el-table class="g-NotHover"
                  ref="studentMsgTable"
                  v-loading="loading"
                  element-loading-text="拼命加载中"
                  element-loading-spinner="el-icon-loading"
                  :data="tableData"
                  style="width:100%"
                  @sort-change="sortChange"
                  @selection-change="handleStudentTable">
          <el-table-column label="问卷名" prop="questionName"></el-table-column>
          <el-table-column label="发布者" prop="releaseName"></el-table-column>
          <el-table-column label="发布时间" prop="releaseTime"></el-table-column>
          <el-table-column label="是否匿名" prop="anonymous">
            <template slot-scope="prop">{{ prop.row.anonymous == 1 ? '是' : '否' }}</template>
          </el-table-column>
          <el-table-column label="是否提交" prop="ifFill"></el-table-column>
          <el-table-column label="操作" fixed="right">
            <template slot-scope="props">
              <el-button type="text" v-if="props.row.ifFill=='未提交'" @click="handlerQuestionNaire(props.row.questionId)">填写</el-button>
              <el-button type="text" v-else @click="scanQuestionNaire(props.row.questionId)">查看</el-button>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </section>
  </div>
</template>
<script>
  import {
    fillInTaskLoad,//table
  } from '@/api/http'
  export default{
    data(){
      return{
        /*ajax data*/
        /*form表单双向绑定数据*/
        dataHeader:{
          gradeId:'',
        },
        /*table*/
        isFilter: false,
        tableData: [],
        /*fuzzyFilter*/
        fuzzyInput:'',
        /*send ajax params*/
        questionId:'',
        loading: false
      }
    },
    computed: {},
    methods:{
      /*table*/
      handleStudentTable(section){
        /*section为选择项行信息组成的数组*/
      },
      sortChange(column){
        /*table排序回调*/
      },
      /*查看*/
      scanQuestionNaire(questionId){
        this.questionId=questionId;
        this.$router.push({name:'scanQuestionNaire',params:{id:'2-'+this.questionId}});
      },
      /*填写问卷*/
      handlerQuestionNaire(questionId){
        this.questionId=questionId;
        this.$router.push({name:'handlerQuestionNaire',params:{id:this.questionId}});
      },
      /*模糊查询*/
      fuzzyClick(){
        /*点击search按钮*/
      },
      /*send ajax*/
      getLoadTable(){
        this.loading = true;
        fillInTaskLoad().then(data=>{
          this.loading = false;
          if(data.statu){
            this.tableData=data.data;
          }
          else{
            this.vmMsgError( '数据加载失败，请重试！' );
            this.tableData=[];
          }
        });
      },
    },
    created(){
      this.getLoadTable();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/style';
</style>


