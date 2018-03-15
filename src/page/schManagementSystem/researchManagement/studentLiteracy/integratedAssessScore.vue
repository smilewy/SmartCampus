<template>
  <div class="g-container">
    <header class="g-textHeader">
      <h2>综合素养成绩</h2>
      <div class="g-flexStartRow">
        <span class="selfCenter" style="margin-right:1.25rem;">方案名称:</span>
        <el-select v-model="repairForm.programmeId">
          <el-option v-for="(content,index) in repairOptionData" :key="index" :value="content.programmeId" :label="content.programmeName"></el-option>
        </el-select>
      </div>
    </header>
    <section class="">
      <header class="g-textHeader g-contentHeader">
        <h2 class="g-centerH" v-text="headerData.programmeName"></h2>
        <p class="g-prompt" v-text="headerData.directionName"></p>
        <ul class="g-flexCenterRow">
          <li>
            <span>姓名:</span>
            <span v-text="headerData.name"></span>
          </li>
          <li>
            <span>满分:</span>
            <span v-text="headerData.scoreAll"></span>
          </li>
          <li>
            <span>得分:</span>
            <span v-text="headerData.score"></span>
          </li>
          <li>
            <span>考核人:</span>
            <span v-text="headerData.appraiser"></span>
          </li>
        </ul>
      </header>
      <!--el-table-->
      <treeTable1 :expand="true" :columns="columns" :dataSource="assetTypeTable"></treeTable1>
    </section>
  </div>
</template>
<script>
  import {
    integratedAssessScoreName,//方案名称
    integratedAssessScoreLoad,//加载
  } from '@/api/http'
  import treeTable1 from '../../../../components/treeTable/treeTable1.vue'
  export default{
    data(){
      let _self=this;
      return{
        /*模糊查询*/
        fuzzyInput:'',
        evaluationName:'',
        /*form表单*/
        repairForm:{
          programmeId:'',
        },
        repairOptionData:[],
        /*headerMsg*/
        headerData:{
          programmeName:'',
          directionName:'',
          name:'',
          scoreAll:'',
          score:'',
          appraiser:'',
        },
        /*table组件*/
        columns:[
          /*props为列绑定数据*/
          {name:'考核项目',props:'projectNmae'},
          {name:'具体条例',props:'projectNmaeRules'},
          {name:'分值（分）',props:'scoreAll'},
          {name:'合计',props:'all'},
        ],
        /*table数据*/
        assetTypeTable:[],
      }
    },
    components:{treeTable1},
    methods:{
      /*点击返回流程图按钮*/
      goBackChart(){
        this.$router.push({name:'evaluationManagement'});
      },
      /*table*/
      handleSelectionChange(choose){
      },
      sortChange(value){
      },
      /*模糊查询*/
      fuzzyClick(){
        /*模糊查询执行回调*/
      },
      /*弹框——维修详情*/
      handlerDetailClose(done){
        done();
      },
      /*send ajax*/
      /*方案名称*/
      getProjectNameAjax(){
        integratedAssessScoreName().then(data=>{
          this.repairOptionData=data;
          if(data.length>0){
            this.repairForm.programmeId=this.repairOptionData[0].programmeId;
          }
        })
      },
      getLoadAjax(){
        integratedAssessScoreLoad({...this.repairForm}).then(data=>{
          /*table上方数据*/
          Object.keys(this.headerData).forEach((key)=>{
            this.headerData[key]=data[key];
          });
          /*table数据*/
          this.assetTypeTable=data.list;
          this.columns=[{name:'考核项目',props:'projectNmae'},{name:'具体条例',props:'projectNmaeRules'},
            {name:'分值（分）',props:'scoreAll'},...data.title,{name:'合计',props:'all'}];
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
    watch:{
      'repairForm.programmeId':function(val){
        this.getLoadAjax(val)
      }
    },
    created(){
      this.getProjectNameAjax();
    },
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/test';
  @import '../../../../style/style';
  .g-textHeader>div{.marginTop(20);}
  .g-contentHeader{
    width:100%;text-align:center;
    .g-prompt{.fontSize(14);margin:10/16rem 0 30/16rem;}
    ul{.widthRem(580);margin:0 auto;text-align:center;
      li{.fontSize(14);color:@normalColor;}
      li:not(:first-of-type){margin-left:20/16rem;}
    }
  }
</style>


