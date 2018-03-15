<template>
  <div class="g-statisticalAnalysis">
    <header>
      <!--      <div class="defineSelect g-sa_header_left">
              <span>问卷名称:</span>
              <el-select v-model="evaluationName">
                <el-option label="123" value="1"></el-option>
              </el-select>
            </div>-->
      <div class="g-fuzzyContainer g-flexEndRow">
        <div class="gs-refresh g-fuzzyInput">
          <el-input type="text" v-model="fuzzyInput" suffix-icon="el-icon-search" placeholder="请输入" @change="getTableLoad"></el-input>
        </div>
      </div>
    </header>
    <section class="alertsList centerTable">
      <el-table v-loading="loading"
                element-loading-text="拼命加载中"
                element-loading-spinner="el-icon-loading"
                :data="classesTimeSetTable"
                @sort-change="sortChange"
                @selection-change="handleSelectionChange">
        <el-table-column label="序号" width="100px" type="index"></el-table-column>
        <el-table-column label="姓名" prop="name" sortable="custom"></el-table-column>
        <el-table-column label="年级班级" prop="name" sortable="custom">
          <template slot-scope="prop">
            <span v-if="prop.row.grade && prop.row.className" v-text="gradeData[prop.row.grade-1]+prop.row.className+'班'"></span>
            <span v-if="prop.row.grade && !prop.row.className" v-text="gradeData[prop.row.grade-1]"></span>
            <span v-if="!prop.row.grade && prop.row.className" v-text="prop.row.className+'班'"></span>
            <span v-if="!prop.row.grade && !prop.row.className"></span>
          </template>
        </el-table-column>
        <el-table-column label="是否填写" prop="name" sortable="custom">
          <template slot-scope="prop">
            <span v-if="Number(prop.row.ifFill)">已填写</span>
            <span v-else>未填写</span>
          </template>
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
    questionNaireRecordProgress,//页面查询信息
  } from '@/api/http'
  export default{
    data(){
      return{
        /*模糊查询*/
        fuzzyInput:'',
        evaluationName:'',
        /*table*/
        classesTimeSetTable:[],
        /*footer*/
        pageAll:1,
        currentPage:1,
        pageCount:10,
        gradeData:['一年级','二年级','三年级','四年级','五年级','六年级','初一','初二',
          '初三','高一','高二','高三'
        ],
        /*send ajax param*/
        orderBy:{
          sortData:'',
          sort:'',//排序字段
        },
        questionId:'',
        loading: false
      }
    },
    methods:{
      /*模糊查询*/
      fuzzyClick(){
        /*模糊查询执行回调*/
      },
      /*footer*/
      handleCurrentChange(val) {
        this.currentPage = val;
        this.getTableLoad();
      },
      /*table*/
      handleSelectionChange(choose){
      },
      sortChange(value){
        this.orderBy.sortData=value.order;
        this.orderBy.sort=value.prop;
        this.getTableLoad();
      },
      /*send ajax*/
      /*得到表格信息*/
      getTableLoad(){
        if(this.questionId){
          this.loading = true;
          questionNaireRecordProgress({roleNameId:4,questionId:this.questionId,page:this.currentPage,pageSize:this.pageCount,valueData:this.fuzzyInput,...this.orderBy}).then(data=>{
            this.loading = false;
            if(data.statu){
              this.classesTimeSetTable=data.data;
            }
            else{
              this.vmMsgError( '数据加载失败，请重试！' );
              this.classesTimeSetTable=[];
            }
          });
        }
        else{

        }
      }
    },
    created(){
      this.questionId=this.$route.params.id;
      this.getTableLoad();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../../style/style';
  @import '../../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.css';
  @import '../../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.less';
  .g-sa_header_search{.marginTop(32);.marginBottom(20);}
  .g-fuzzyContainer{width:100%;.marginBottom(20);}
  .g-sa_header_left{margin:20/16rem 0;}
</style>


