<template>
  <div class="g-container">
    <header class="g-textHeader">
      <h2>考核明细</h2>
      <div class="g-flexStartRow">
        <span class="selfCenter" style="margin-right:1.25rem;">方案名称:</span>
        <el-select  v-model="repairForm.programmeId">
          <el-option v-for="(content,index) in repairOptionData" :key="index" :value="content.programmeId" :label="content.programmeName"></el-option>
        </el-select>
      </div>
    </header>
    <section class="">
      <section class="g-tree_content">
        <div class="g-sectionL">
          <header class="gL-header">
            <h2>待选学生</h2>
            <el-input @input="fuzzyClick" v-model="fuzzyInput" class="fuzzyInput" placeholder="请输入" suffix-icon="el-icon-search"></el-input>
          </header>
          <section class="gL-section">
            <el-tree
              v-loading.body="isLoading"
              element-loading-text="拼命加载中..."
              :highlight-current="true" :data="treeData" :props="defaultProps" ref="allMsg" :filter-node-method="filterNode" @node-click="handleNodeClick"></el-tree>
          </section>
        </div>
        <div class="g-textHeader g-sectionR g-contentHeader">
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
          <!--el-table-->
          <treeTable2 :columns="columns" :expand="true" :dataSource="assetTypeTable"></treeTable2>
        </div>
      </section>
    </section>
  </div>
</template>
<script>
  import {
    AssessDetailName,//方案名称
    AssessDetailStudent,//待选学生
    AssessDetailLoad,//加载信息
  } from '@/api/http'
  import treeTable2 from '../../../../components/treeTable/treeTable2.vue'
  export default{
    data(){
      let _self=this;
      return{
        isLoading:false,
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
        /*tree*/
        treeData:[],
        defaultProps: {
          children: 'childs',
          label:'name',
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
        /*send ajax params*/
        userId:'',
      }
    },
    components:{treeTable2},
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
      /*tree点击事件*/
      handleNodeClick(data,node){
        /*点击节点回调*/
        /*点击名字发送请求，返回数据绑定在右边部分*/
        /*给每一级赋值一个唯一的id即可辨认点击的是几级*/
        if('childs' in data){
          return false;
        }else{
          this.userId=data.userId;
          this.getLoadAjax();
        }
      },
      /*教师信息模糊查询*/
      fuzzyClick(){
        /*input框输入值改变触发的函数*/
        this.$refs['allMsg'].filter(this.fuzzyInput);
      },
      filterNode(value, data) {
        /*筛选节点*/
        if (!value) return true;
        return data.name.indexOf(value) !== -1;
      },
      /*send ajax*/
      /*方案名称*/
      getProjectNameAjax(){
        AssessDetailName().then(data=>{
          this.repairOptionData=data;
          if(data.length>0){
            this.repairForm.programmeId=this.repairOptionData[0].programmeId;
          }
        })
      },
      /*得到学生*/
      getStudentAjax(){
        this.isLoading=true;
        AssessDetailStudent(this.repairForm).then(data=>{
          this.treeData=data;
          this.isLoading=false;
        })
      },
      getLoadAjax(){
        AssessDetailLoad({...this.repairForm,userId:this.userId}).then(data=>{
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
    },
    created(){
      this.getProjectNameAjax();
    },
    watch:{
      'repairForm.programmeId':function(val){
        this.getStudentAjax(val)
      }
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/test';
  @import '../../../../style/style';
  .g-textHeader>div{.marginTop(20);}
  .g-contentHeader{
    width:100%;
    h2{text-align:center;}
    .g-prompt{.fontSize(14);margin:10/16rem 0 30/16rem;}
    ul{.widthRem(580);margin:0 auto;text-align:center;
      li{.fontSize(14);color:@normalColor;}
      li:not(:first-of-type){margin-left:20/16rem;}
    }
  }
</style>


