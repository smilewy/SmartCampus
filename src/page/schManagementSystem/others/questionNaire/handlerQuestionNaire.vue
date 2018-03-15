<template>
  <div class="g-container">
    <header class="g-textHeader g-flexStartRow">
      <el-button @click="goBackParent" class="g-gobackChart g-imgContainer RedButton">
        <img src="../../../../assets/img/commonImg/icon_return.png" />
        返回
      </el-button>
      <span class="selfCenter">填写问卷</span>
    </header>
    <section class="g-questionNaire">
      <header class="g-textHeader">
        <h2 v-text="questionName"></h2>
        <div v-text="explain" class="g-prompt"></div>
      </header>
      <section>
        <div class="g-QNSetContainer" v-for="(content,index) in QuestionNaireForm.QNArray">
          <div v-if="content.type==0" class="g-singleTopic">
            <!--单选题-->
            <div class="g-QNSetTopicContainer g-liOneRow">
              <h2 v-text="'Q'+content.QNum+':'"></h2>
              <div class="g-QNSetContent selfSpace">
                <div class="g-flexStartRow"><h2 v-text="content.title"></h2><span class="g-prompt">{{ content.isMust=='true' ? '（必填）' : '（选填）'}}</span></div>
                <div class="g-addQuestion">
                  <div class="g-addQuestionRow g-liOneRow" v-for="(question,qIndex) in content.QNSetting">
                    <el-radio v-model="content.index" :label="qIndex" class="selfCenter g-chooseBox">{{question.value}}</el-radio>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div v-if="content.type==1" class="g-multipleChoose">
            <!--多选题-->
            <div class="g-QNSetTopicContainer g-liOneRow">
              <h2 v-text="'Q'+content.QNum+':'"></h2>
              <div class="g-QNSetContent selfSpace">
                <div class="g-flexStartRow"><h2 v-text="content.title"></h2><span class="g-prompt">{{ content.isMust=='true' ? '（必填）' : '（选填）'}} {{ '（最多可选' + content.optionNum + '项）' }}</span></div>
                <div class="g-addQuestion g-checkboxMore">
                  <el-checkbox-group @change="chooseReaminChange" @input="chooseReaminInput(index)" v-model="content.checkChoose" class="g-addQuestionRow g-flexStartWrapRow">
                    <el-checkbox v-for="(question,qIndex) in content.QNSetting" :key="qIndex" :disabled="checkboxMaxChooseObj[index].disabledCom[qIndex]" :label="qIndex" class="selfCenter g-chooseBox g-checkbox">{{question.value}}</el-checkbox>
					<!--<div>
                       @change="chooseReaminChange(index,qIndex)"
                      &lt;!&ndash;值未选中&ndash;&gt;
                      <el-checkbox :label="qIndex" v-else-if="checkboxMaxChooseObj[qIndex] && !Number(question.isChoose)" :disabled="checkboxMaxChooseObj[qIndex].disabledCom" class="selfCenter g-chooseBox g-checkbox">{{question.value}}</el-checkbox>
                      &lt;!&ndash;默认&ndash;&gt;
                      <el-checkbox v-else :label="qIndex" class="selfCenter g-chooseBox g-checkbox">{{question.value}}</el-checkbox>
                    </div>-->
                  </el-checkbox-group>
                </div>
              </div>
            </div>
          </div>
          <div v-if="content.type==2" class="g-fillInBlank">
            <!--问答题-->
            <div class="g-QNSetTopicContainer g-liOneRow">
              <h2 v-text="'Q'+content.QNum+':'"></h2>
              <div class="g-QNSetContent selfSpace">
                <div class="g-flexStartRow"><h2 v-text="content.title"></h2>{{ content.isMust=='true' ? '（必填）' : '（选填）'}}</div>
                <div class="g-addQuestion">
                  <div class="g-addQuestionRow g-liOneRow">
                    <span>答:</span>
                    <el-input v-model="content.QNSetting.value" placeholder=""></el-input>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div v-if="content.type==3" class="g-scoresTopic">
            <!--分数题-->
            <div class="g-QNSetTopicContainer g-liOneRow">
              <h2 v-text="'Q'+content.QNum+':'"></h2>
              <div class="g-QNSetContent selfSpace">
                <div class="g-flexStartRow"><h2 v-text="content.title"></h2>{{ content.isMust=='true' ? '（必填）' : '（选填）'}} {{ '（可打分分值范围在 0 - '+ content.maxScore +'之间）' }}</div>
                <div class="g-addQuestion">
                  <div class="g-addQuestionRow g-liOneRow">
                    <el-input-number class="g-courseNum" v-model="content.QNSetting.value" :min="0" :max="Number(content.maxScore)"></el-input-number>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div v-if="content.type==4" class="g-paragraphDescript">
            <!--段落说明-->
            <div class="g-QNSetTopicContainer g-liOneRow">
              <div class="g-QNParagraph selfSpace">
                <div class="g-addQuestion">
                  <div class="g-addQuestionRow g-liOneRow">
                    <p class='g-paragraphContent' v-text="content.description"></p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      <div class="g-button">
        <el-button @click="saveAjax" type="primary">提交</el-button>
        <!--<el-button>重置</el-button>-->
      </div>
    </section>
  </div>
</template>
<script>
  import {
    fillInTaskFillLoad,//得到问卷
    fillInTaskFillSave,//保存
  } from '@/api/http'
  export default{
    data(){
      return{
        /*问卷题目设置*/
        questionName:'',
        explain:'',
        QuestionNaireForm:{
          /*单选多选题等数据总和*/
          QNArray:[],
        },
        /*复选框判断最多选项所需对象，index为键名*/
        checkboxMaxChooseObj:{},
        /*复选框最多选项判断需要参数*/
        checkRowI:'',
        /*send ajax params*/
        questionId:'',
      }
    },
    methods:{
      goBackParent(){
        this.$router.push('/fillInTask');
      },
      /*复选框change事件*/
      chooseReaminChange(newVal){
        if(this.checkboxMaxChooseObj[this.checkRowI].remainNum<=newVal.length){
          this.QuestionNaireForm.QNArray[this.checkRowI].QNSetting.forEach((val,idx)=>{
            this.checkboxMaxChooseObj[this.checkRowI].disabledCom.splice(idx,1,true);
            newVal.forEach((clmn,clmnI)=>{
              this.checkboxMaxChooseObj[this.checkRowI].disabledCom.splice(clmn,1,false);
            });
          });
        }
        else{
          this.QuestionNaireForm.QNArray[this.checkRowI].QNSetting.forEach((val,idx)=>{
            this.checkboxMaxChooseObj[this.checkRowI].disabledCom.splice(idx,1,false);
          });
        }
      },
      /*input事件*/
      chooseReaminInput(rowI){
        this.checkRowI=rowI;
      },
      initCheckObj(){
        this.QuestionNaireForm.QNArray.forEach((row,rowI)=>{
          if(row.type==1){
            /*复选框*/
            let trueNum=0,disabledArr=[];
            row.QNSetting.forEach((column,columI)=>{
              disabledArr.push(false);
              if(Number(column.isChoose)){
                trueNum++;
              }
            });
            this.$set(this.checkboxMaxChooseObj,rowI,{remainNum:Number(row.optionNum)-trueNum,disabledCom:disabledArr});
//            this.checkboxMaxChooseObj[rowI]={remainNum:Number(row.optionNum)-trueNum,disabledCom:disabledArr};
          }
        });
      },
      handlerParam(){
        let _isfoo=true;
        for(let i=0;i<this.QuestionNaireForm.QNArray.length;i++){
          /*判断必选项是否完成填写*/
          if(this.QuestionNaireForm.QNArray[i].isMust=='true'){
            /*必填项*/
            if(this.QuestionNaireForm.QNArray[i].type==0){
              if(this.QuestionNaireForm.QNArray[i].index===''||this.QuestionNaireForm.QNArray[i].index===undefined){
                this.vmMsgWarning( '请完成所有的必填项！' );
                _isfoo=false;
                break;
              }
            }
            else if(this.QuestionNaireForm.QNArray[i].type==1){
              if(this.QuestionNaireForm.QNArray[i].checkChoose.length<=0){
                this.vmMsgWarning( '请完成所有的必填项！' );
                _isfoo=false;
                break;
              }
            }
            else if(this.QuestionNaireForm.QNArray[i].type==2){
              if(!this.QuestionNaireForm.QNArray[i].QNSetting.value){
                this.vmMsgWarning( '请完成所有的必填项！' );
                _isfoo=false;
                break;
              }
            }
          }
        }
        if(!_isfoo){
          /*必填项未填完*/
          return false;
        }
        this.QuestionNaireForm.QNArray.forEach((row,rowI)=>{
          /*修改复选框的choose值*/
          if(row.type==1){
            row.QNSetting.forEach((clm,clmI)=>{
              clm.isChoose=0;
            });
            row.checkChoose.forEach((clm,clmI)=>{
              row.QNSetting[clm].isChoose=1;
            });
          }
        });
        return true;
      },
      /*send ajax*/
      getLoadMsg(){
        fillInTaskFillLoad({questionId:this.questionId}).then(data=>{
          if(data.statu){
            this.questionName=data.data.questionName;
            this.explain=data.data.explain;
            /*为复选框加一个组的双向绑定值，在发送请求时将选中的值具体到每一项上*/
            data.data.content.forEach((val)=>{
              if(val.type==1){
                /*多选题*/
                val.checkChoose=[];
              }
            });
            this.QuestionNaireForm.QNArray=data.data.content;
            this.initCheckObj();//初始化复选框选中的数量
          }
          else{
            this.vmMsgError( '数据加载失败，请重试！' );
            this.questionName='';
            this.explain='';
            this.QuestionNaireForm.QNArray=[];
          }
        });
      },
      saveAjax(){
        if(this.handlerParam()){
          fillInTaskFillSave({questionId:this.questionId,QNArray:this.QuestionNaireForm.QNArray}).then(data=>{
            if(data.statu){
              this.vmMsgSuccess( '提交成功！' );
              this.goBackParent();
            }
            else{
              this.vmMsgError( '提交失败，请重试！' );
            }
          });
        }

      },
    },
    created(){
      this.questionId=this.$route.params.id;
      this.getLoadMsg();
    },
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/test';
  @import '../../../../style/style';
  header.g-textHeader{border:none;padding-bottom:20/16rem;
    span{.fontSize(19);color:@HColor;.marginLeft(40,1582);}
  }

  .g-QNSetContainer{padding:20/16rem 32/16rem;}
  .g-QNSetContainer h2{margin-right:2%;}
  /*段落说明容器*/
  .g-QNParagraph{background:#fff;}
  .g-QNParagraph .g-addQuestion{padding-top:0;padding-bottom:0;}
  /*设置单选题内容容器*/
  .g-QNSetTopicContainer{padding-right:2rem;}
  .g-addQuestion{/*比例*/
    width:100%;padding:20/16rem 0;
    .g-addQuestionRow:not(:first-of-type){.marginTop(15);}
    .topicHandler{flex-basis:9.6rem;border-left:0;.border-top-left-radius(0);.border-bottom-left-radius(0)}
    &.g-checkboxMore{width:40%;}
  }
  .g-addQuestionRow{
    .g-chooseBox{margin-right:1rem;}
    .g-checkbox{margin-bottom:15/16rem;}
    span{margin-right:15/16rem;}
    p.g-paragraphContent{padding:15/16rem 20/16rem;width:100%;background:@backgroundBlueOpacity;}
  }

  .g-container .g-questionNaire{/*1000*/
    .width(1000,1582);margin:30/16rem auto;
    &>header.g-textHeader{.marginBottom(30);}
    &>header.g-textHeader h2{text-align:center;.fontSize(24);}
  }
</style>


