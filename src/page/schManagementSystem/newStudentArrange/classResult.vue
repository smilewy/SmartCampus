<template>
  <div class="g-container classResult">
    <header class="g-importCourseHeader">
      <div class="g-textHeader g-flexStartRow">
        <el-button class="g-gobackChart g-imgContainer RedButton" @click="goBackChart">
          <img src="../../../assets/img/schManagementSystem/teachingAdministration/arrangeClasses/icon_return.png" />
          返回流程图
        </el-button>
        <h2 class="selfCenter">分班合成成绩情况</h2>
      </div>
    </header>
    <div class="g-container g-containerNoPadding">
      <section class="g-section">
        <div class="gs-header g-liOneRow">
          <div class="gs-button alertsBtn">
            <el-button-group>
              <el-button @click="exportClick" data-msg="export" class="filt buttonChild" title="导出">
                <img class="filt_unactive"  src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png" />
                <img class="filt_active" src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png" />
              </el-button>
            </el-button-group>
            <el-button-group class="elGroupButton_two">
              <el-button  @click="operationData('copy')" data-msg="copy" class="filt buttonChild" title="复制">
                <img class="filt_unactive"  src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png" />
                <img class="filt_active" src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png" />
              </el-button>
            </el-button-group>
          </div>
          <div class="gs-refresh g-fuzzyInput">
            <el-input type="text" v-model="fuzzyInput" placeholder="姓名/性别" suffix-icon="el-icon-search" @change="getLoadAjax"></el-input>
          </div>
        </div>
        <div class="gs-table centerTable alertsList">
          <el-table
            v-loading.body="isLoading"
            element-loading-text="拼命加载中..."
            class="g-NotHover" border ref="studentMsgTable" :data="tableData.student" style="width:100%" @sort-change="sortChange" @selection-change="handleStudentTable">
            <!--show-overflow-tooltip 超出部分省略号显示-->
            <el-table-column label="姓名" sortable prop="name"></el-table-column>
            <el-table-column label="性别" sortable prop="sex"></el-table-column>
            <el-table-column label="总分" sortable prop="score"></el-table-column>
            <el-table-column :label="columnP.name" v-for="(columnP,columnPI) in tableData.exam" :key="columnPI">
              <el-table-column :label="columnC.name" v-for="(columnC,columnCI) in columnP.subs" :key="columnCI">
                <template slot-scope="prop">
                  <div v-if="prop.row[columnC.subId]" v-text="prop.row[columnC.subId].score"></div>
                </template>
              </el-table-column>
            </el-table-column>
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
  </div>
</template>
<script>
  import {
    classResultTScore,//操作
  } from '@/api/http'
  import req from '@/assets/js/common'
  export default{
    data(){
      return{
        isLoading:false,
        /*ajax data*/
        headerButtonData:{
          studentBasicMsg:[],
        },
        tableData:{
          student:[],
          exam:[]
        },
        /*fuzzyFilter*/
        fuzzyInput:'',
        /*footer*/
        pageAll:1,
        currentPage:1,
        pageCount:10,//每页数据条数
        /*send ajax*/
        order:'',//升降序
        orderValue:'',//排序字段
      }
    },
    computed: {},
    methods:{
      operationData(type){
        let sAy = [], hdData = {
          name: '姓名',
          sex: '性别',
          score: '总分',
        };
        sAy.push(hdData);
        for (let obj of this.tableData.student) {
          let d = {};
          for (let name in hdData) {
            d[name] = obj[name] || '';
          }
          sAy.push(d);
        }
        if (type === 'copy') {
          req.copyTableData('.classResult', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      /*点击返回流程图按钮*/
      goBackChart(){
        this.$router.push({name:'newStudentClass'});
      },
      /*footer*/
      handleCurrentChange(val) {
        this.currentPage = val;
        this.getLoadAjax();
      },
      /*table*/
      handleStudentTable(section){
        /*section为选择项行信息组成的数组*/
      },
      /*编辑*/
      changeClick(index){
        this.isDialog=true;
        this.dialogTitle='编辑信息';
      },
      sortChange(column){
        /*table排序回调*/
        this.orderValue=column.prop;
        this.order=column.order;
      },
      /*header的button群*/
      /*模糊查询*/
      fuzzyClick(){
        /*点击search按钮*/
//        console.log('模糊查询');
      },
      /*弹框*/
      /*关闭按钮点击*/
      handlerClose(done){
        done();
      },
      /*send ajax*/
      getLoadAjax(){
        /*分页和模糊查询*/
        this.isLoading=true;
        classResultTScore({gradeId:this.gradeId,examId:this.examId,page:this.currentPage,count:this.pageCount,key:this.fuzzyInput,order:this.order,according:this.orderValue}).then(data=>{
          if(data.status){
            this.tableData.student=data.data.student;
            this.tableData.exam=data.data.exam;
            this.pageAll=data.maxPage;
          }
          else{
            this.tableData.student=[];
            this.pageAll=1;
            this.vmMsgWarning('暂无数据！');
          }
          this.isLoading=false;
        });
      },
      exportClick(){
        req.downloadFile('.g-container','/school/StudentIni/scoreInfo?download=ensure&gradeId='+this.gradeId,'post');
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
  .g-importCourse .g-container .g-section{margin:1.25rem 0;width:100%;}
  .g-container.g-containerNoPadding{width:100%;}
  /*弹框*/
  .headerNotBackground{
    .dialogHeader{position:absolute;top:0.625rem;
      button{.border-radius(1rem);}
    }
    .dialogSection{
      .NotAllWidth{width:auto;}
      .defineInputWidth{.widthRem(60);}
    }
  }
  .g-textHeader{
    h2{.marginLeft(40,1582);}
  }
</style>








