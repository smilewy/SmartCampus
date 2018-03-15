<template>
  <div class="g-container">
    <header class="g-textHeader">
      <h2>考评管理</h2>
      <div class="g-liOneRow g-headerBottom--chart">
        <el-form class="leftForm" :model="evaluationForm" label-position="left" label-width="80px">
          <el-form-item label="考评名称:">
            <el-select @change="getLoadAjax" v-model="evaluationForm.id" placeholder="请选择考评名称">
              <el-option v-for="(content,index) in evaluationNData" :key="index" :label="content.name" :value="content.id"></el-option>
            </el-select>
          </el-form-item>
        </el-form>
        <ul class="g-liOneRow g-headerBottomR">
          <li class="step un_able">不可编辑</li>
          <li class="step completed">已完成</li>
          <li class="step main_process">主要流程</li>
          <li class="step second_process">次要流程</li>
          <li class="step inactived">未激活</li>
        </ul>
      </div>
    </header>
    <section class="g-content--chart" v-if="statusData.length>0">
      <div class="g-row_content_1">
        <div class="g-column_1 g-col_content_1x">
          <div :class="['item_a',{'inactived':statusData[0].status==-1},{'un_able':statusData[0].status==0}]" v-if="statusData[0].status==-1 || statusData[0].status==0">修改考评方案</div>
          <router-link :class="['item_a',{'completed':statusData[0].status == 1},{'main_process':statusData[0].status == 2},{'second_process':statusData[0].status == 3}]" v-else :to="{name:'changeEvaluationProgram',params:{id:this.evaluationForm.id}}" tag="div">修改考评方案</router-link>
        </div>
      </div>
      <div class="g-row_arrow_1">
        <div class="g-column_1 g-col_line_down">
          <div class="g-line_container">
            <span :class="['line',{'inactived':statusData[0].status==-1},{'un_able':statusData[0].status==0},{'completed':statusData[0].status == 1},{'main_process':statusData[0].status == 2},{'second_process':statusData[0].status == 3}]"></span>
          </div>
          <div class="g-arrow_container">
            <span :class="['arrow el-icon-arrow-down',{'inactived':statusData[0].status==-1},{'un_able':statusData[0].status==0},{'completed':statusData[0].status == 1},{'main_process':statusData[0].status == 2},{'second_process':statusData[0].status == 3}]"></span>
          </div>
        </div>
      </div>
      <div class="g-row_content_2">
        <div class="g-column_1 g-col_content_1x">
          <div :class="['item_a',{'inactived':statusData[1].status==-1},{'un_able':statusData[1].status==0}]" v-if="statusData[1].status==-1 || statusData[1].status==0">评委分组</div>
          <router-link :class="['item_a',{'completed':statusData[1].status == 1},{'main_process':statusData[1].status == 2},{'second_process':statusData[1].status == 3}]" v-else :to="{name:'judgesGroup',params:{id:this.evaluationForm.id}}" tag="div">评委分组</router-link>
        </div>
      </div>
      <div class="g-row_arrow_2">
        <div class="g-column_1 g-col_line_down">
          <div class="g-line_container">
            <span :class="['line',{'inactived':statusData[1].status==-1},{'un_able':statusData[1].status==0},{'completed':statusData[1].status == 1},{'main_process':statusData[1].status == 2},{'second_process':statusData[1].status == 3}]"></span>
          </div>
          <div class="g-arrow_container">
            <span :class="['arrow el-icon-arrow-down',{'inactived':statusData[1].status==-1},{'un_able':statusData[1].status==0},{'completed':statusData[1].status == 1},{'main_process':statusData[1].status == 2},{'second_process':statusData[1].status == 3}]"></span>
          </div>
        </div>
      </div>
      <div class="g-row_content_3">
        <div class="g-column_1 g-col_content_1x">
          <div :class="['item_a',{'inactived':statusData[2].status==-1},{'un_able':statusData[2].status==0}]" v-if="statusData[2].status==-1 || statusData[2].status==0">设置被考评分组</div>
          <router-link :class="['item_a',{'completed':statusData[2].status == 1},{'main_process':statusData[2].status == 2},{'second_process':statusData[2].status == 3}]" v-else :to="{name:'evaluationGroup',params:{id:this.evaluationForm.id}}" tag="div">设置被考评分组</router-link>
        </div>
<!--        <div class="g-column_2 moreColumCss backgroundCss g-col_content_1x">
          <router-link to="/statisticalAnalysis" tag="div">对比名单统计</router-link>
        </div>-->
      </div>
      <div class="g-row_arrow_3">
        <div class="g-column_1 g-col_line_down">
          <div class="g-line_container">
            <span :class="['line',{'inactived':statusData[2].status==-1},{'un_able':statusData[2].status==0},{'completed':statusData[2].status == 1},{'main_process':statusData[2].status == 2},{'second_process':statusData[2].status == 3}]"></span>
          </div>
          <div class="g-arrow_container">
            <span :class="['arrow el-icon-arrow-down',{'inactived':statusData[2].status==-1},{'un_able':statusData[2].status==0},{'completed':statusData[2].status == 1},{'main_process':statusData[2].status == 2},{'second_process':statusData[2].status == 3}]"></span>
          </div>
        </div>
      </div>
      <div class="g-row_content_4">
        <div class="g-column_1">
          <div class="g-col_content_1x">
            <div :class="['item_a',{'inactived':statusData[3].status==-1},{'un_able':statusData[3].status==0}]" v-if="statusData[3].status==-1 || statusData[3].status==0">分配考评人员</div>
            <router-link :class="['item_a',{'completed':statusData[3].status == 1},{'main_process':statusData[3].status == 2},{'second_process':statusData[3].status == 3}]" v-else :to="{name:'addEvaluationPerson',params:{id:this.evaluationForm.id}}" tag="div">分配考评人员</router-link>
          </div>
<!--          <div class="g-col_content_2x">
            <router-link to="/addJudges" class="item_a main_process" tag="div">添加评委</router-link>
          </div>-->
        </div>
        <div class="g-column_2 g-col_content_1x">
          <div :class="['item_a',{'inactived':statusData[7].status==-1},{'un_able':statusData[7].status==0}]" v-if="statusData[7].status==-1 || statusData[7].status==0">考评进度跟踪</div>
          <router-link :class="['item_a',{'completed':statusData[7].status == 1},{'main_process':statusData[7].status == 2},{'second_process':statusData[7].status == 3}]" v-else :to="{name:'evaluationProgress',params:{id:this.evaluationForm.id}}" tag="div">考评进度跟踪</router-link>
        </div>
      </div>
      <div class="g-row_arrow_4">
        <div class="g-column_1 g-col_line_down">
          <div class="g-line_container">
            <span :class="['line',{'inactived':statusData[3].status==-1},{'un_able':statusData[3].status==0},{'completed':statusData[3].status == 1},{'main_process':statusData[3].status == 2},{'second_process':statusData[3].status == 3}]"></span>
          </div>
          <div class="g-arrow_container">
            <span :class="['el-icon-arrow-down arrow',{'inactived':statusData[3].status==-1},{'un_able':statusData[3].status==0},{'completed':statusData[3].status == 1},{'main_process':statusData[3].status == 2},{'second_process':statusData[3].status == 3}]"></span>
          </div>
        </div>
      </div>
      <div class="g-row_content_5">
        <div class="g-column_1 g-col_content_1x">
          <div class="item_a un_able">评委打分</div>
        </div>
      </div>
      <div class="g-row_arrow_5">
        <div class="g-column_1 g-col_line_down">
          <div class="g-line_container">
            <span class="line un_able"></span>
          </div>
          <div class="g-arrow_container">
            <span class="el-icon-arrow-down arrow un_able"></span>
          </div>
        </div>
      </div>
      <div class="g-row_content_5">
        <div class="g-column_1 g-col_content_1x">
          <div :class="['item_a',{'inactived':statusData[5].status==-1},{'un_able':statusData[5].status==0}]" v-if="statusData[5].status==-1 || statusData[5].status==0">统计分析</div>
          <router-link :class="['item_a',{'completed':statusData[5].status == 1},{'main_process':statusData[5].status == 2},{'second_process':statusData[5].status == 3}]" v-else :to="{name:'statisticalAnalysis'}" tag="div">统计分析</router-link>
        </div>
      </div>
    </section>
  </div>
</template>
<script>
  import {
    evaluationManagementName,//考评名称
    evaluationManagementLoad,//流程状态
  } from '@/api/http'
  export default{
    data(){
      return{
        /*考评名称*/
        evaluationForm:{
          id:'',
        },
        /*考评名称*/
        evaluationNData:[],
        datePickerOption:{
          disabledDate(time){
            return time.getTime()<Date.now()-8.64e7;
          }
        },
        /*状态*/
        statusData:[],
      }
    },
    methods:{
      /*考评名称change事件*/
      /*send ajax*/
      /*考评名称*/
      getEvaluationName(){
        evaluationManagementName().then(data=>{
          if(data.status){
            if(sessionStorage.getItem('teacherEval')){
              this.evaluationForm.id = sessionStorage.getItem('teacherEval');
              sessionStorage.removeItem('teacherEval');
            }else{
              this.evaluationForm.id=data.data[0].id;
            }
            this.getLoadAjax();
            this.evaluationNData=data.data;
          }
          else{
            this.evaluationNData=[];
          }
        })
      },
      /*页面状态*/
      getLoadAjax(){
        sessionStorage.setItem('teacherEval',this.evaluationForm.id);
        evaluationManagementLoad(this.evaluationForm).then(data=>{
          if(data.status){
            this.statusData=data.data;
          }
          else{
            this.statusData=[];
          }
        });
      },
    },
    created(){
      this.getEvaluationName();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/style';
  @import '../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.css';
  @import '../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.less';
  /*header下*/
  .g-headerBottom--chart{/*1582*/
    width:100%;.marginTop(32);.height(36);.marginBottom(25);
    .leftForm{}
    .g-headerBottomR{.width(530,1582);
      .before{content:'';.widthRem(10);color:@normalColor;.height(10);.box-sizing();margin-right:15/16rem;.border-radius(50%);display:inline-block;}
      li{.fontSize(14);color:#666;
        &:before{.before}
      }
    }
  }
  /*section*/
  .g-content--chart{/*645*/
    .widthRem(645);margin:50/16rem auto 0;
    /*图表走向*/
    /*列css，浮动，一行排多列，每列间距*/
    div[class*='g-row']{
      &:after{content:'';display:block;clear:both;}
    }
    div[class*='g-column']{float:left;position:relative;
      div[class*='g-col_content']:not(:first-of-type){.marginTop(20);}
    }
    div[class*='g-column']:not(:first-of-type){margin-left:160/16rem;}
    /*单元格*/
    /*含字*/
    div[class*='g-col_content']{.widthRem(240);.NotLineheight(40);.border-radius(20/16rem);.fontSize(14);.box-sizing();text-align:center;

    }
    /*路由元素css*/
    div.item_a{display:inline-block;width:100%;.height(40);.border-radius(20/16rem);
      &:hover{cursor:pointer;}
    }
    /*line单元格 向下箭头*/
    .g-col_line_down{.widthRem(240);.fontSize(14);.box-sizing();text-align:center;.marginTop(15);.marginBottom(17);
      .g-line_container{/*240*/
        width:100%;
        span{display:inline-block;border-left:2/16rem solid;.height(40);}
      }
      .g-arrow_container{
        width:100%;.marginTop(-4);
        span{.fontSize(16);font-weight:normal;}
      }
    }
  }
</style>








