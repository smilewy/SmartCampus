<template>
  <div class="g-container">
    <header class="g-header">
      <div class="g-textHeader g-flexStartRow">
        <el-button @click="goBackParent" class="g-gobackChart g-imgContainer RedButton">
          <img src="../../../assets/img/commonImg/icon_return.png" />
          返回
        </el-button>
        <h2 class="selfCenter">新生分班名单</h2>
      </div>
      <div class="g-prompt">新生人数：  <span v-text="newStudentNum"></span>    参与分班人数：  <span v-text="attend"></span>        提示：本表自动同步新生名单，如需要修改，请到新生管理中修改！</div>
    </header>
    <section class="g-section">
      <div class="gs-table alertsList">
        <el-table
          v-loading.body="isLoading"
          element-loading-text="拼命加载中..."
          ref="studentMsgTable" :data="headerButtonData.studentBasicMsg" style="width:100%" @sort-change="sortChange" @selection-change="handleStudentTable">
          <!--show-overflow-tooltip 超出部分省略号显示-->
          <el-table-column label="序号" type="index"></el-table-column>
          <el-table-column label="姓名" prop="name"></el-table-column>
          <el-table-column label="性别" prop="sex"></el-table-column>
          <el-table-column label="出生日期" prop="birthday"></el-table-column>
          <el-table-column label="志愿填报地区" prop="voluntPath"></el-table-column>
          <el-table-column label="户口所在地" prop="perAddress"></el-table-column>
          <el-table-column label="民族" prop="nation"></el-table-column>
          <el-table-column label="政治面貌" prop="politics"></el-table-column>
          <el-table-column label="考生类型" prop="exaCategory"></el-table-column>
          <el-table-column label="中学" prop="secSchool"></el-table-column>
        </el-table>
      </div>
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
  import {fileTypeCheck} from '@/assets/js/common'
  import {
    newStudentClassNameLoad,//操作
  } from '@/api/http'
  import {mapState} from 'vuex'
  export default{
    data(){
      return{
        isLoading:false,
        /*ajax data*/
        headerButtonData:{
          gradeloadData:[],
          classesLoadData:[],
          msgTypeLoadData:[],
          studentBasicMsg:[],
        },
        /*新生总人数*/
        newStudentNum:0,
        attend:0,//参与分班人数
        /*footer*/
        pageAll:1,
        currentPage:1,
        pageCount:10,
        /*表单验证规则*/
        importFormRules:{
          fileName:[
            {required:true,message:'请选择文件!'}
          ],
        }
      }
    },
    computed: {
    },
    methods:{
      /*返回*/
      goBackParent(){
        this.$router.push('/newStudentClass');
      },
      /*table*/
      handleStudentTable(section){
        /*section为选择项行信息组成的数组*/
      },
      sortChange(column){
        /*table排序回调*/
      },
      /*footer*/
      handleCurrentChange(val) {
        this.currentPage = val;
        this.getLoadAjax();
      },
      /*send ajax*/
      getLoadAjax(){
        this.isLoading=true;
        newStudentClassNameLoad({gradeId:this.gradeId,count:this.pageCount,page:this.currentPage}).then(data=>{
          this.newStudentNum=data.total;
          this.attend=data.attend;
          if(data.status){
            this.headerButtonData.studentBasicMsg=data.data;
            this.pageAll=data.maxPage;
          }
          else{
            this.headerButtonData.studentBasicMsg=[];
            this.pageAll=1;
          }
          this.isLoading=false;
        });
      }
    },
    created(){
      this.gradeId=this.$route.params.gradeId;
      this.getLoadAjax();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../style/style';
  .g-container{
    .g-textHeader{
      h2{.marginLeft(40,1582);}
    }
    .g-prompt{text-align:left;padding-top:20/16rem;}
  }
</style>


