<template>
  <div class="g-container">
    <header class="g-textHeader g-flexStartRow">
      <el-button @click="goBackParent" class="g-gobackChart g-imgContainer RedButton">
        <img src="../../../../assets/img/commonImg/icon_return.png" />
        返回
      </el-button>
      <span class="selfCenter" v-text="headerText[_id]"></span>
    </header>
    <section>
      <header class="g-textHeader">
        <h2 v-text="questionName + anonymous"></h2>
        <div v-text="explain" class="g-prompt"></div>
      </header>
      <div class="g-QNSetContainer" v-for="(content,index) in QuestionNaireForm.QNArray">
        <div v-if="content.type==0" class="g-singleTopic">
          <!--单选题-->
          <div class="g-QNSetTopicContainer g-liOneRow">
            <h2 v-text="'Q'+content.QNum+':'"></h2>
            <div class="g-QNSetContent selfSpace">
                <div class="g-flexStartRow">
                     <h2 v-text="content.title"></h2><span class="g-prompt">{{ content.isMust=='true' ? '（必填）' : '（选填）'}}</span>
                </div>
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
              <div class="g-flexStartRow">
                     <h2 v-text="content.title"></h2><span class="g-prompt">{{ content.isMust=='true' ? '（必填）' : '（选填）'}} {{ '（最多可选' + content.optionNum + '项）' }}</span>
                </div>
              <div class="g-addQuestion g-checkboxMore">
                <div class="g-addQuestionRow g-flexStartWrapRow">
                  <el-checkbox-group @change="chooseReaminChange" @input="chooseReaminInput(index)" v-model="content.checkChoose" class="g-addQuestionRow g-flexStartWrapRow">
                    <el-checkbox v-for="(question,qIndex) in content.QNSetting" :key="qIndex" :disabled="checkboxMaxChooseObj[index].disabledCom[qIndex]" :label="qIndex" class="selfCenter g-chooseBox g-checkbox">{{question.value}}</el-checkbox>
                  </el-checkbox-group>
                  <!-- <el-checkbox v-for="(question,qIndex) in content.QNSetting" :key="qIndex" v-model="question.isChoose" class="selfCenter g-chooseBox g-checkbox">{{question.value}}</el-checkbox> -->
                </div>
              </div>
            </div>
          </div>
        </div>
        <div v-if="content.type==2" class="g-fillInBlank">
          <!--填空题-->
          <div class="g-QNSetTopicContainer g-liOneRow">
            <h2 v-text="'Q'+content.QNum+':'"></h2>
            <div class="g-QNSetContent selfSpace">
             <div class="g-flexStartRow">
                     <h2 v-text="content.title"></h2><span class="g-prompt">{{ content.isMust=='true' ? '（必填）' : '（选填）'}}</span>
                </div>
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
              <div class="g-flexStartRow">
                     <h2 v-text="content.title"></h2><span class="g-prompt">{{ content.isMust=='true' ? '（必填）' : '（选填）'}} {{ '（可打分分值范围在 0 - '+ content.maxScore +'之间）' }}</span>
                </div>
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
    <div class="g-button" v-if="_id==0">
        <el-button @click="simulationSubmit" type="primary">提交</el-button>
    </div>
    </section>
  </div>
</template>
<script>
  import {
    /*编辑*/
    handlerQuestionNaireScanLoad,//得到编辑问卷信息
    questionNaireRecordScan,//问卷记录预览
    fillInTaskFillScan,//填写任务预览
    replyDetailScan,//回答详情预览
  } from '@/api/http'
  import {mapState} from 'vuex'
  export default{
    data(){
      return{
        /*form表单*/
        dataForm:{},
        /*确定是添加还是修改操作*/
        headerText:['预览问卷','查看问卷','查看答案'],
        /*路由参数*/
        _id:'',
        questionId:'',
        /*编辑*/
        token:'',
        /*回答详情*/
        replyDetailParam:{
          ifOut:'',
          id:'',
        },
        /*问卷题目设置*/
        questionName:'',
        explain:'',
        QuestionNaireForm:{
          /*单选多选题等数据总和*/
          QNArray:[],
        },
        // 是否匿名
        anonymous: '',
        /*复选框判断最多选项所需对象，index为键名*/
        checkboxMaxChooseObj:{},
      }
    },
    computed:{
      ...mapState(['scanData','questionN','explainN']),
    },
    methods:{
      goBackParent(){
        if(this._id==0){
          /*问卷记录*/
          this.$router.push('/questionNaireRecord');
        }
        else if(this._id==1){
          /*问卷统计*/
          this.$router.push('/replyDetail');
        }
        else if(this._id==2){
          /*填写任务*/
          this.$router.push('/fillInTask');
        }
        else if(this._id==3){
          /*问卷编辑——id为获取编辑问卷需要的参数questionId*/
          this.$router.push({name:'newQuestionNaire',params:{id:'handle-'+this.questionId+'-'+this.token}});
        }
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
          }
        });
      },
       /*input事件*/
      chooseReaminInput(rowI){
        this.checkRowI=rowI;
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
      handlerParam(){
        let _isfoo=true;
        for(let i=0;i<this.QuestionNaireForm.QNArray.length;i++){
          /*判断必选项是否完成填写*/
          if(this.QuestionNaireForm.QNArray[i].isMust=='true'){
            /*必填项*/
            if(this.QuestionNaireForm.QNArray[i].type==0){
              if(this.QuestionNaireForm.QNArray[i].index===''||this.QuestionNaireForm.QNArray[i].index===undefined){
                this.alertPrompt('选择题：请选择选项！','error');
                _isfoo=false;
                break;
              }
            }
            else if(this.QuestionNaireForm.QNArray[i].type==1){
                if(this.QuestionNaireForm.QNArray[i].checkChoose.length<=0){
                    this.alertPrompt("选择题：请选择选项！",'warning');
                    _isfoo=false;
                    break;
                }
            }
            else if(this.QuestionNaireForm.QNArray[i].type==2 || this.QuestionNaireForm.QNArray[i].type == 3){
              if(!this.QuestionNaireForm.QNArray[i].QNSetting.value){
                  if( this.QuestionNaireForm.QNArray[i].type==2 ){
                      this.alertPrompt('问答题：请输入内容！','error');
                  }
                  if( this.QuestionNaireForm.QNArray[i].type == 3 ){
                      this.alertPrompt('分数题：请打分！','error');
                  }
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
        return true;
      },
      simulationSubmit(){
          if(this.handlerParam()){
            this.goBackParent();
            this.vmMsgSuccess( '提交成功！' );
          }
      },
      getLoadData(){
/*        if(this._id==3 || this._id==4){
          /!*新建问卷和问卷编辑*!/
          this.QuestionNaireForm=this.scanData;
          this.questionName=this.questionN;
          this.explain=this.explainN;
        }else{
          /!*发送ajax获取问卷信息*!/
        }*/
        if(this._id==0){
          /*问卷记录——预览*/
          this.getScanAjax();
        }
        else if(this._id==1){
          /*问卷统计——回答详情*/
          this.getReplyDetailScan();
        }
        else if(this._id==2){
          /*填写任务*/
          this.getTaskScan();
        }
        else if(this._id==3){
          /*问卷编辑**/
          this.getHandlerDataAjax();
        }
      },
      /*send ajax*/
      /*编辑问卷，得到问卷信息*/
      getHandlerDataAjax(){
        handlerQuestionNaireScanLoad({token:this.token}).then(data=>{
          if(data.statu){
            this.questionName=data.data.questionName;
            this.explain=data.data.explain;
             if(this._id == 0){
                this.anonymous = (data.data.anonymous == 0 ? '（匿名问卷）' : '（实名问卷）');
            }
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
            this.vmMsgError('数据加载失败，请重试！');
            this.questionName='';
            this.explain='';
            this.QuestionNaireForm.QNArray=[];
          }
        })
      },
      /*问卷记录*/
      getScanAjax(){
        questionNaireRecordScan({questionId:this.questionId}).then(data=>{
          if(data.statu){
            this.questionName=data.data.questionName;
            this.explain=data.data.explain;
            if(this._id == 0){
                this.anonymous = (data.data.anonymous == 0 ? '（匿名问卷）' : '（实名问卷）');
            }
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
            this.vmMsgError('数据加载失败，请重试！');
            this.questionName='';
            this.explain='';
            this.QuestionNaireForm.QNArray=[];
          }
        });
      },
      /*填写任务*/
      getTaskScan(){
        fillInTaskFillScan({questionId:this.questionId}).then(data=>{
          if(data.statu){
            this.questionName=data.data.questionName;
            this.explain=data.data.explain;
             if(this._id == 0){
                this.anonymous = (data.data.anonymous == 0 ? '（匿名问卷）' : '（实名问卷）');
            }
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
            this.vmMsgError('数据加载失败，请重试！');
            this.questionName='';
            this.explain='';
            this.QuestionNaireForm.QNArray=[];
          }
        });
      },
      /*回答详情*/
      getReplyDetailScan(){
        replyDetailScan({questionId:this.questionId,...this.replyDetailParam}).then(data=>{
          if(data.statu){
            this.questionName=data.data.questionName;
            this.explain=data.data.explain;
             if(this._id == 0){
                this.anonymous = (data.data.anonymous == 0 ? '（匿名问卷）' : '（实名问卷）');
            }
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
            this.vmMsgError('数据加载失败，请重试！');
            this.questionName='';
            this.explain='';
            this.QuestionNaireForm.QNArray=[];
          }
        });
      },
    },
    created(){
      this._id=this.$route.params.id.split('-')[0];//判断从哪个页面来
      this.questionId=this.$route.params.id.split('-')[1];//questionId
      if(this._id==3){
        /*编辑页面含有token*/
        this.token=this.$route.params.id.split('-')[2];
      }
      else if(this._id==1){
        /*回答详情*/
        this.replyDetailParam.ifOut=this.$route.params.id.split('-')[2];
        this.replyDetailParam.id=this.$route.params.id.split('-')[3];
      }
/*      if(this.$route.params.id.split('-').length>2){
      }*/
      this.getLoadData();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/style';
  .g-container{
    header.g-textHeader{border:none;padding-bottom:70/16rem;
      h2{text-align:center;}
      span{.fontSize(19);color:@HColor;.marginLeft(40,1582);text-align:center;}
    }
    section{/*1105*/
      .width(1105,1582);margin:0 auto;
    }
    .g-footer{width:100%;}
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
    .g-prompt{padding:10/16rem 0;}
  }
</style>




