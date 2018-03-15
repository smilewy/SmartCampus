<template>
  <div class="g-container">
    <header class="g-textHeader">
      <h2>新生分班</h2>
      <div class="g-liOneRow g-headerBottom--chart">
        <el-form class="leftForm" :model="evaluationForm" label-position="left" label-width="80px">
          <el-form-item label="选择年级:">
            <el-select v-model="evaluationForm.evaluationName" placeholder="请选择考评名称">
              <el-option v-for="(content,index) in gradeAjaxData" :key="index" :label="content.znName" :value="content.id"></el-option>
            </el-select>
          </el-form-item>
        </el-form>
        <ul class="g-liOneRow g-headerBottomR">
          <li class="step completed">已完成</li>
          <li class="step main_process">主要流程</li>
          <li class="step second_process">次要流程</li>
          <li class="step inactived">未激活</li>
        </ul>
      </div>
    </header>
    <section class="g-content--chart">
      <div class="g-row_content_1">
        <div class="g-column_1">
          <div class="g-col-1x g-col_content"></div>
          <div class="g-space-1x"></div>
        </div>
        <div class="g-column_2">
          <div class="g-col-1x g-col_content">
            <!--completed,main_process,second_process,inactived-->
            <router-link v-if="statusData[3] != -1" :class="['item_a',{'completed':statusData[3] == 1},{'main_process':statusData[3] == 2},{'second_process':statusData[3] == 3}]" :to="{name:'newStudentSpecial',params:{gradeId:evaluationForm.evaluationName}}" tag="div">指定学生到班</router-link>
            <div class="item_a inactived" v-else>指定学生到班</div>
            <div class="line--width-RD_container">
              <div :class="['lineTwo',{'inactived':statusData[3] == -1},{'completed':statusData[3] == 1},{'main_process':statusData[3] == 2},{'second_process':statusData[3] == 3}]"></div>
              <div class="g-arrow_container">
                <span :class="['arrow','el-icon-arrow-down',{'inactived':statusData[3] == -1},{'completed':statusData[3] == 1},{'main_process':statusData[3] == 2},{'second_process':statusData[3] == 3}]"></span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="g-row_arrow_1">
        <div class="g-column_1">
          <div class="g-col-1x g-col_content"></div>
          <div class="g-space-1x"></div>
        </div>
        <div class="g-column_2">
          <div class="g-col-1x g-col_line_up">
            <div class="g-line_container">
              <div class="g-arrow_container">
                <span :class="['arrow','el-icon-arrow-up',{'inactived':statusData[2] == -1},{'completed':statusData[2] == 1},{'main_process':statusData[2] == 2},{'second_process':statusData[2] == 3}]"></span>
              </div>
              <span :class="['line',{'inactived':statusData[2] == -1},{'completed':statusData[2] == 1},{'main_process':statusData[2] == 2},{'second_process':statusData[2] == 3}]"></span>
            </div>
          </div>
        </div>
      </div>
      <div class="g-row_content_2">
        <div class="g-column_1">
          <div class="g-col-1x g-col_content">
            <router-link v-if="statusData[1] != -1" :class="['item_a',{'completed':statusData[1] == 1},{'main_process':statusData[1] == 2},{'second_process':statusData[1] == 3}]" :to="{name:'newStudentClassName',params:{gradeId:evaluationForm.evaluationName}}" tag="div">新生分班名单</router-link>
            <div class="item_a inactived" v-else>新生分班名单</div>
          </div>
          <div class="g-space-1x g-col_right_line">
            <div class="g-line_container">
              <span :class="['line',{'inactived':statusData[1] == -1},{'completed':statusData[1] == 1},{'main_process':statusData[1] == 2},{'second_process':statusData[1] == 3}]"></span>
              <span :class="['arrow','el-icon-arrow-right',{'inactived':statusData[1] == -1},{'completed':statusData[1] == 1},{'main_process':statusData[1] == 2},{'second_process':statusData[1] == 3}]"></span>
            </div>
          </div>
        </div>
        <div class="g-column_2">
          <div class="g-col-1x g-col_content">
            <router-link :to="{name:'createdClass',params:{gradeId:evaluationForm.evaluationName}}" v-if="statusData[2] != -1" :class="['item_a',{'completed':statusData[2] == 1},{'main_process':statusData[2] == 2},{'second_process':statusData[2] == 3}]" tag="div">创建班级</router-link>
            <div class="item_a inactived" v-else>创建班级</div>
          </div>
          <div class="g-space-1x g-col_right_line">
            <div class="g-line_container">
              <span :class="['line',{'inactived':statusData[2] == -1},{'completed':statusData[2] == 1},{'main_process':statusData[2] == 2},{'second_process':statusData[2] == 3}]"></span>
              <span :class="['arrow','el-icon-arrow-right',{'inactived':statusData[2] == -1},{'completed':statusData[2] == 1},{'main_process':statusData[2] == 2},{'second_process':statusData[2] == 3}]"></span>
            </div>
          </div>
        </div>
        <div class="g-column_3">
          <div class="g-col-1x g-col_content">
            <router-link :to="{name:'placementFast',params:{gradeId:evaluationForm.evaluationName}}"  v-if="statusData[6] != -1" :class="['item_a',{'completed':statusData[6] == 1},{'main_process':statusData[6] == 2},{'second_process':statusData[6] == 3}]" tag="div">快速分班</router-link>
            <div class="item_a inactived" v-else>快速分班</div>
          </div>
          <div class="g-space-1x g-col_right_line">
            <div class="g-line_container">
              <span :class="['line',{'inactived':statusData[6] == -1},{'completed':statusData[6] == 1},{'main_process':statusData[6] == 2},{'second_process':statusData[6] == 3}]"></span>
              <span :class="['arrow','el-icon-arrow-right',{'inactived':statusData[6] == -1},{'completed':statusData[6] == 1},{'main_process':statusData[6] == 2},{'second_process':statusData[6] == 3}]"></span>
            </div>
          </div>
        </div>
        <div class="g-column_4">
          <div class="g-col-1x g-col_content">
            <router-link :to="{name:'printMessage',params:{gradeId:evaluationForm.evaluationName}}" v-if="statusData[9] != -1" :class="['item_a',{'completed':statusData[9] == 1},{'main_process':statusData[9] == 2},{'second_process':statusData[9] == 3}]" tag="div">打印报表</router-link>
            <div class="item_a inactived" v-else>打印报表</div>
          </div>
          <div class="g-space-1x"></div>
        </div>
      </div>
      <div class="g-row_arrow_2">
        <div class="g-column_1">
          <div class="g-col-1x g-col_line_down"></div>
        </div>
      </div>
      <div class="g-row_content_3">
        <div class="g-column_1">
          <div class="g-col-1x g-col_content"></div>
          <div class="g-space-1x"></div>
        </div>
        <div class="g-column_2">
          <div class="g-col-1x g-col_content">
            <router-link :to="{name:'organizeResults',params:{gradeId:evaluationForm.evaluationName}}" v-if="statusData[4] != -1" :class="['item_a',{'completed':statusData[4] == 1},{'main_process':statusData[4] == 2},{'second_process':statusData[4] == 3}]" tag="div">分班成绩合成设置</router-link>
            <div class="item_a inactived" v-else>分班成绩合成设置</div>
            <div class="line--width_container">
              <div class="line--width-LB_container">
                <div :class="['lineTwo',{'inactived':statusData[4] == -1},{'completed':statusData[4] == 1},{'main_process':statusData[4] == 2},{'second_process':statusData[4] == 3}]"></div>
                <div class="g-arrow_container">
                  <span :class="['arrow','el-icon-arrow-right',{'inactived':statusData[4] == -1},{'completed':statusData[4] == 1},{'main_process':statusData[4] == 2},{'second_process':statusData[4] == 3}]"></span>
                </div>
              </div>
              <div class="line--width-RT_container">
                <div class="g-arrow_container">
                  <span :class="['arrow','el-icon-arrow-up',{'inactived':statusData[4] == -1},{'completed':statusData[4] == 1},{'main_process':statusData[4] == 2},{'second_process':statusData[4] == 3}]"></span>
                </div>
                <div :class="['lineTwo',{'inactived':statusData[4] == -1},{'completed':statusData[4] == 1},{'main_process':statusData[4] == 2},{'second_process':statusData[4] == 3}]"></div>
              </div>
            </div>
          </div>
          <div class="g-space-1x"></div>
        </div>
        <div class="g-column_3">
          <div class="g-col-7x g-col_content"></div>
          <div class="g-col-1x g-col_content">
            <router-link :to="{name:'changeBySelf',params:{gradeId:evaluationForm.evaluationName}}" v-if="statusData[7] != -1" :class="['item_a',{'completed':statusData[7] == 1},{'main_process':statusData[7] == 2},{'second_process':statusData[7] == 3}]" tag="div">手动调整</router-link>
            <div class="item_a inactived" v-else>手动调整</div>
            <div class="line--width-BL_container">
              <div :class="['lineTwo',{'inactived':statusData[7] == -1},{'completed':statusData[7] == 1},{'main_process':statusData[7] == 2},{'second_process':statusData[7] == 3}]"></div>
              <div class="g-arrow_container">
                <span :class="['arrow','el-icon-arrow-right',{'inactived':statusData[7] == -1},{'completed':statusData[7] == 1},{'main_process':statusData[7] == 2},{'second_process':statusData[7] == 3}]"></span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="g-row_arrow_3">
        <div class="g-column_1">
          <div class="g-col-1x g-col_content"></div>
          <div class="g-space-1x"></div>
        </div>
        <div class="g-column_2">
          <div class="g-col-1x g-col_line_down">
            <div class="g-line_container">
              <span :class="['line',{'inactived':statusData[4] == -1},{'completed':statusData[4] == 1},{'main_process':statusData[4] == 2},{'second_process':statusData[4] == 3}]"></span>
              <div class="g-arrow_container">
                <span :class="['arrow','el-icon-arrow-down',{'inactived':statusData[4] == -1},{'completed':statusData[4] == 1},{'main_process':statusData[4] == 2},{'second_process':statusData[4] == 3}]"></span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="g-row_content_4">
        <div class="g-column_1">
          <div class="g-col-1x g-col_content"></div>
          <div class="g-space-1x"></div>
        </div>
        <div class="g-column_2">
          <div class="g-col-1x g-col_content">
            <router-link :to="{name:'classResult',params:{gradeId:evaluationForm.evaluationName}}" v-if="statusData[5] != -1" :class="['item_a',{'completed':statusData[5] == 1},{'main_process':statusData[5] == 2},{'second_process':statusData[5] == 3}]" tag="div">分班合成成绩情况</router-link>
            <div class="item_a inactived" v-else>确认分班合成成绩</div>
          </div>
          <div class="g-space-1x"></div>
        </div>
        <div class="g-column_3">
          <div class="g-col-7x g-col_content"></div>
          <div class="g-col-1x g-col_content">
            <router-link :to="{name:'newStudentRecord',params:{gradeId:evaluationForm.evaluationName}}" v-if="statusData[8] != -1" :class="['item_a',{'completed':statusData[8] == 1},{'main_process':statusData[8] == 2},{'second_process':statusData[8] == 3}]" tag="div">学生补录</router-link>
            <div class="item_a inactived" v-else>学生补录</div>
            <div class="line--width-BL_container hasOwnHeight">
              <div :class="['lineTwo',{'inactived':statusData[8] == -1},{'completed':statusData[8] == 1},{'main_process':statusData[8] == 2},{'second_process':statusData[8] == 3}]"></div>
              <div class="g-arrow_container">
                <span :class="['arrow','el-icon-arrow-right',{'inactived':statusData[8] == -1},{'completed':statusData[8] == 1},{'main_process':statusData[8] == 2},{'second_process':statusData[8] == 3}]"></span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>
<script>
  import {
    newStudentClassLoad,//操作
    newStudentGetGrade,//得到年级
  } from '@/api/http'
  import {mapState,mapActions} from 'vuex'
  export default{
    data(){
      return{
        /*考评名称*/
        evaluationForm:{
          evaluationName:0,
        },
        /*页面状态分析*/
        statusData:{1:-1,2:-1,3:-1,4:-1,5:-1,6:-1,7:-1,8:-1,9:-1},
        datePickerOption:{
          disabledDate(time){
            return time.getTime()<Date.now()-8.64e7;
          }
        },
        /*年级*/
        gradeAjaxData:[],
      }
    },
    computed:{
      ...mapState(['gradeId']),
    },
    methods:{
      getMsgAjax(){
        sessionStorage.setItem('gradeId3',this.evaluationForm.evaluationName);
        newStudentClassLoad({gradeId:this.evaluationForm.evaluationName}).then(data=>{
          if(data.status){
            this.statusData=data.data;
          }
          else{
//            this.$message.error('状态信息加载失败！');
            this.statusData={1:-1,2:-1,3:-1,4:-1,5:-1,6:-1,7:-1,8:-1,9:-1};
          }
        });
      },
      /*修改gradeId方法*/
      ...mapActions(['newStudentClassGrade']),
      /*send ajax*/
      /*得到年级*/
      getLoadAjax(){
        newStudentGetGrade({func:'getGrade'}).then(data=>{
          this.gradeAjaxData=this.handlerAjaxData(data,'暂无数据');
          if(sessionStorage.getItem('gradeId3')){
            this.evaluationForm.evaluationName = sessionStorage.getItem('gradeId3');
            sessionStorage.removeItem('gradeId3');
          } else {
            this.evaluationForm.evaluationName=this.gradeAjaxData[0].id;
            this.getMsgAjax();
          }
        });
      },
      /*处理数据*/
      handlerAjaxData(data,msg){
        if(data.status){
          return data.data;
        }
        else{
          this.vmMsgError(msg);
        }
      },
    },
    created(){
      this.getLoadAjax();
    },
    watch:{
      'evaluationForm.evaluationName':function(val){
        this.getMsgAjax(val)
      }
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../style/style';
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
  .g-content--chart{
    /*图表走向*/
    /*列css，浮动，一行排多列，每列间距*/
    div[class*='g-row']{
      &:after{content:'';display:block;clear:both;}
    }
    div[class*='g-column']{float:left;position:relative;
      &>div{float:left;}
      &:after{display:block;content:'';clear:both;}
    }
    /*单元格*/
    /*含字的div，原本的a标签*/
    /*字体css*/
    div[class*='g-col_content']{
      position:relative;.NotLineheight(40);.border-radius(20/16rem);.fontSize(14);.box-sizing();text-align:center;

    }
    /*路由元素css*/
    div.item_a{display:inline-block;width:100%;.height(40);.border-radius(20/16rem);
      &:hover{cursor:pointer;}
    }
    /*线加箭头----*/
    /*箭头行占据的高度*/
    div[class*='g-col_line']{
      .NotLineheight(56);.marginTop(12);.marginBottom(15);
    }
    /*右下箭头*/
    .line--width-RD_container{/*185*/
      position:absolute;.widthRem(185);left:260/16rem;top:20/16rem;
      .lineTwo{width:100%;.NotLineheight(75);border-right:2/16rem solid ;border-top:2/16rem solid;}
      .g-arrow_container{width:100%;.marginTop(-4);text-align:right;
        span{.fontSize(16);font-weight:normal;.transformTranslate(50%);}
      }
    }
    /*包含左和右的箭头容器*/
    .line--width_container{position:absolute;top:0;left:0;}
    /*左下箭头-长*/
    .line--width-LB_container{
      position:absolute;left:-200/16rem;top:-70/16rem;
      .lineTwo{.widthRem(180);.NotLineheight(90);border-left:2/16rem solid ;border-bottom:2/16rem solid;}
      .g-arrow_container{position:absolute;right:0;bottom:0;
        span{.fontSize(16);font-weight:normal;.transformTranslate(100%,50%);}
      }
    }
    /*左下箭头-短*/
    .line--width-BL_container{
      position:absolute;left:-51/16rem;top:-70/16rem;
      .lineTwo{.widthRem(21);.NotLineheight(90);border-left:2/16rem solid ;border-bottom:2/16rem solid;}
      .g-arrow_container{position:absolute;right:0;bottom:0;
        span{.fontSize(16);font-weight:normal;.transformTranslate(100%,50%);}
      }
    }
    /*左下箭头-短,含自己高*/
    .line--width-BL_container.hasOwnHeight{
      position:absolute;left:-51/16rem;top:-102/16rem;
      .lineTwo{.widthRem(21);.NotLineheight(122);border-left:2/16rem solid ;border-bottom:2/16rem solid;}
      .g-arrow_container{position:absolute;right:0;bottom:0;
        span{.fontSize(16);font-weight:normal;.transformTranslate(100%,50%);}
      }
    }
    /*右上箭头*/
    .line--width-RT_container{position:absolute;top:-94/16rem;left:250/15rem;
      .lineTwo{.widthRem(21);.NotLineheight(94);border-right:2/16rem solid ;border-bottom:2/16rem solid;}
      .g-arrow_container{.transformTranslate(10%,25%);
        span{.fontSize(16);font-weight:normal;.transformTranslate(50%);}
      }
    }
    /*line单元格 向下箭头*/
    .g-col_line_down{
      text-align:center;
      .g-line_container{/*240*/
        width:100%;
        .line{display:inline-block;border-left:2/16rem solid;.height(40);}
      }
      .g-arrow_container{
        width:100%;.marginTop(-4);
        span{.fontSize(16);font-weight:normal;}
      }
    }
    /*line单元格 向上箭头*/
    .g-col_line_up{
      text-align:center;
      .g-line_container{/*240*/
        width:100%;
        .line{display:inline-block;border-left:2/16rem solid;.height(40);}
      }
      .g-arrow_container{
        width:100%;position:relative;top:6/16rem;
        span{.fontSize(16);font-weight:normal;}
      }
    }
    /*line单元格 向右箭头*/
    .g-col_right_line{
      text-align:center;.height(40);
      .g-line_container{
        .line{display:inline-block;border-top:2/16rem solid;.widthRem(40);position:relative;top:-2/16rem;left:4/16rem;}
        span.arrow{.fontSize(16);font-weight:normal;}
      }

    }
  }
</style>








