<template>
  <div class="g-statisticalAnalysis">
    <header>
      <div class="defineSelect g-sa_header_left">
        <span>问卷名称:</span>
        <el-select v-model="evaluationName">
          <el-option label="123" value="1"></el-option>
        </el-select>
      </div>
      <div class="g-fuzzyContainer g-flexEndRow">
        <div class="gs-refresh g-fuzzyInput">
          <el-input type="text" v-model="fuzzyInput" suffix-icon="el-icon-search" placeholder="班级/姓名" @change="fuzzyClick"></el-input>
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
        <el-table-column label="序号" width="100" type="index" :index="indexMethod"></el-table-column>
        <el-table-column label="姓名" prop="name"></el-table-column>
        <el-table-column label="性别" prop="sex"></el-table-column>
        <el-table-column label="年龄" prop="age"></el-table-column>
        <!-- <el-table-column label="浏览量" prop="name"></el-table-column> -->
      </el-table>
    </section>
  </div>
</template>
<script>
  import {} from '@/api/http'
  export default{
    data(){
      return{
        /*模糊查询*/
        fuzzyInput:'',
        evaluationName:'',
        /*table*/
        classesTimeSetTable:[{name:"张三" , sex: "男", age: 28}],
        loading: false
      }
    },
    methods:{
      /*模糊查询*/
      fuzzyClick(){
        /*模糊查询执行回调*/
      },
      /*table*/
      handleSelectionChange(choose){
      },
      sortChange(value){
      },

      indexMethod( index ){
        return index + 1;
      }
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


