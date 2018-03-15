<template>
  <div class="g-container">
    <header class="g-textHeader g-liOneRow">
      <div class="g-flexStartRow">
        <el-button class="g-gobackChart g-imgContainer RedButton" @click="goBackChart">
          <img src="../../../../assets/img/schManagementSystem/teachingAdministration/arrangeClasses/icon_return.png" />
          返回
        </el-button>
        <h2 class="g-hasMargin selfCenter">评分</h2>
      </div>
      <el-button @click="saveClick" type="primary" :disabled="returnData===true">保存</el-button>
    </header>
    <div class="g-flexStartRow">
      <div>
        <span class="selfCenter" style="margin-right:1.25rem;">开始时间:</span>
        <span class="selfCenter" style="margin-right:1.25rem;" v-text="time.startTime"></span>
      </div>
      <div>
        <span class="selfCenter" style="margin-right:1.25rem;">结束时间:</span>
        <span class="selfCenter" style="margin-right:1.25rem;" v-text="time.endTime"></span>
      </div>
    </div>
    <section class="">
      <section class="g-tree_content">
        <div class="g-sectionL">
          <header class="gL-header">
            <h2>待选学生</h2>
            <el-input @input="fuzzyClick" v-model="fuzzyInput" class="fuzzyInput" placeholder="请输入" suffix-icon="el-icon-search"></el-input>
          </header>
          <section class="gL-section">
            <el-tree :highlight-current="true" :data="treeData" :props="defaultProps" ref="allMsg" :filter-node-method="filterNode" @node-click="handleNodeClick"></el-tree>
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
          <treeTable1 :expand="true" :ParamObj="ParamObj" :columns="columns" :dataSource="assetTypeTable"></treeTable1>
        </div>
      </section>
    </section>
  </div>
</template>
<script>
  import {
    ChildAssessScoreStudent,//待选学生
    ChildAssessScoreLoad,//加载信息
    ChildAssessScoreSave,//保存
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
/*        repairForm:{
          programmeId:'',
        },
        repairOptionData:[],*/
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
          {name:'评分',props:'score'},
        ],
        /*table数据*/
        assetTypeTable:[],
        time:{
          startTime:'',
          endTime:''
        },
        /*send ajax need params*/
        ParamObj:['score'],
        /*send ajax params*/
        userId:'',
        classId:'',
        programmeId:'',
        scoreParamArr:{},
        scoreId:'',
        isFoo:true,//是否发送保存请求
        returnData:false,
      }
    },
    components:{treeTable1},
    methods:{
      /*点击*/
      goBackChart(){
        this.$router.push({name:'studentAssessScore'});
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
        }
        else if('classId' in data){
          this.classId=classId;
        }
        else{
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
      /*得到学生*/
      getStudentAjax(){
        ChildAssessScoreStudent({programmeId:this.programmeId}).then(data=>{
          this.treeData=data.student;
          this.time=data.time;
        })
      },
      getLoadAjax(){
        ChildAssessScoreLoad({programmeId:this.programmeId,classId:this.classId,userId:this.userId}).then(data=>{
          /*table上方数据*/
          Object.keys(this.headerData).forEach((key)=>{
            this.headerData[key]=data[key];
          });
          /*table数据*/
          this.assetTypeTable=data.list;
          this.scoreId=data.scoreId;
          this.returnData=false;
        });
      },
      /*保存*/
      saveClick(){
        if(this.assetTypeTable.length>0){
          this.scoreParamArr={};
          this.isFoo=true;
          this.handlerParam(this.assetTypeTable);
          if(this.isFoo){
            ChildAssessScoreSave({score:this.scoreParamArr,scoreId:this.scoreId,programmeId:this.programmeId,classId:this.classId,userId:this.userId}).then(data=>{
              if(data.return){
                this.returnData=true;
                this.vmMsgSuccess( '保存成功！' );
                this.getLoadAjax();
              }
              else{
                this.vmMsgError( '保存失败，请重试！' );
              }
            });
          }
        }
        else{
          this.vmMsgWarning('没有保存信息！');
        }
      },
      handlerParam(data){
        for(let i=0;i<data.length;i++){
          if('score' in data[i]){
            if(data[i].score){
              this.scoreParamArr[data[i].projectId]=data[i].score;
            }
            else{
              this.vmMsgWarning('请填写完所有评分项！');
              this.isFoo=false;
              break;
            }
          }
          if(this.isFoo){
            if('childs' in data[i]){
              this.handlerParam(data[i].childs);
            }
          }
          else{
            return false;
          }
        }
      },
    },
    created(){
      this.programmeId=this.$route.params.id;
      this.getStudentAjax();
    },
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/test';
  @import '../../../../style/style';
  .g-textHeader>div{.marginTop(20);}
  .g-hasMargin{margin-left:20/16rem;}
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


