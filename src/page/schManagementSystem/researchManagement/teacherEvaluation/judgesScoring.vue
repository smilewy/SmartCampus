<template>
  <div class="g-statisticalAnalysis g-container">
    <header class="g-estatisticalAnalysisHeader">
      <!--      <el-button class="g-gobackChart g-imgContainer RedButton" @click="goBackChart">
              <img src="../../../../assets/img/commonImg/icon_return.png" />
              返回流程图
            </el-button>-->
      <div class="g-liOneRow">
        <h2>评委打分</h2>
        <div class="flex_right gs-refresh g-fuzzyInput">
          <el-button type="danger" @click="clearScoreClick"  :disabled="Boolean(Number(isSubmit))" class="defineHeight">清空</el-button>
          <el-button type="primary" class="defineHeight" :disabled="Boolean(Number(isSubmit))" @click="submitClick">提交</el-button>
        </div>
      </div>
      <div class="g-flexStartRow g-sa_header_search">
        <div class="defineSelect g-js_selectPart">
          <span>考评方案：</span>
          <el-select v-model="evaluationId">
            <el-option v-for="(content,index) in evaluationNData" :key="index" :label="content.name" :value="content.id"></el-option>
          </el-select>
        </div>
        <div class="defineSelect g-js_selectPart">
          <span>被考评分组：</span>
          <el-select v-model="IsEvaluationId">
            <el-option v-for="(content,index) in IsEvaluationOption" :key="index" :label="content.name" :value="content.id"></el-option>
          </el-select>
        </div>
        <div class="defineSelect js_datePrompt">
          <span>考评时间：</span>
          <span v-text="evaluationTime"></span>
        </div>
      </div>
    </header>
    <section class="alertsList centerTable g-statisticalAnalysisSection">
      <el-table
        v-loading.body="isLoading"
        element-loading-text="拼命加载中..."
        :data="classesTimeSetTable" class="g-NotHover">
        <el-table-column label="序号" width="100" type="index"></el-table-column>
        <el-table-column label="姓名" prop="name"></el-table-column>
        <el-table-column label="德（25分）" align="center">
          <template slot-scope="props">
            <span v-if="isSubmit" v-text="props.row.score[0]"></span>
            <input v-else type="text" @input="totalScoreInput(props.$index,0)" v-model="props.row.score[0]" class="tableInput" />
          </template>
        </el-table-column>
        <el-table-column label="能（25分）" align="center">
          <template slot-scope="props">
            <span v-if="isSubmit" v-text="props.row.score[1]"></span>
            <input v-else  type="text" @input="totalScoreInput(props.$index,1)" v-model="props.row.score[1]" class="tableInput" />
          </template>
        </el-table-column>
        <el-table-column label="勤（25分）" align="center">
          <template slot-scope="props">
            <span v-if="isSubmit" v-text="props.row.score[2]"></span>
            <input v-else  type="text" @input="totalScoreInput(props.$index,2)" v-model="props.row.score[2]" class="tableInput" />
          </template>
        </el-table-column>
        <el-table-column label="绩（25分）" align="center">
          <template slot-scope="props">
            <span v-if="isSubmit" v-text="props.row.score[3]"></span>
            <input v-else  type="text" @input="totalScoreInput(props.$index,3)" v-model="props.row.score[3]" class="tableInput" />
          </template>
        </el-table-column>
        <el-table-column label="总分">
          <template slot-scope="props">
            <span v-text="props.row.score[4]"></span>
          </template>
        </el-table-column>
      </el-table>
    </section>
  </div>
</template>
<script>
  import {
    judgesScoringParams,//考评名称和被考评分组
    judgesScoringLoad,//操作
  } from '@/api/http'
  export default{
    data(){
      return{
        isLoading:false,
        /*模糊查询*/
        fuzzyInput:'',
        evaluationName:'',
        /*被考评人分组*/
        IsEvaluationName:'',
        /*table*/
        classesTimeSetTable:[],
        /*考评方案*/
        evaluationNData:[],//考评方案分组
        evaluationId:'',//考评方案value绑定数据
        IsEvaluationOption:[],//被考评data
        IsEvaluationId:'',//被考评分组value绑定数据
        /*考评时间*/
        evaluationTime:'',
        /*判断是否提交*/
        isSubmit:0,
        /*send ajax param*/
        id:'',//考评方案
        groupId:'',//被考评分组
      }
    },
    methods:{
      /*点击返回流程图按钮*/
      goBackChart(){
        this.$router.push({name:'evaluationManagement'});
      },
      /*模糊查询*/
      fuzzyClick(){
        /*模糊查询执行回调*/
      },
      /*考评名称change事件——被考评分组*/
      getIsGroup(newVal){
        this.IsEvaluationId='';
        let obj = this.evaluationNData.filter(val=>val.id===newVal)[0];
        if(obj){
          if('group' in obj){
            this.IsEvaluationOption=obj['group'];
          }
          else{
            this.IsEvaluationOption=[];
          }
          if(this.IsEvaluationOption.length>0){
            this.IsEvaluationId=this.IsEvaluationOption[0].id;
          }
        }
      },
      /*被考评分组*/
      isEvaluationChange(newVal){
        let obj = this.IsEvaluationOption.filter(val=>val.id===newVal)[0];
        if(obj){
          if('id' in obj){
            this.evaluationTime=obj.startTime+'  -  '+obj.endTime;
            this.getLoadAjax();
          }
          else{
            this.vmMsgWarning( '请选择被考评分组！' );
          }
        }
      },
      /*table框类分数input*/
      totalScoreInput(idx,columnI){
        if(isNaN(this.classesTimeSetTable[idx].score[columnI])){
          this.vmMsgWarning('请输入合法字符！');
          this.$set(this.classesTimeSetTable[idx].score,4,0);
        }
        else if(Number(this.classesTimeSetTable[idx].score[columnI])>25){
          this.vmMsgWarning('分数超额！','warning');
          this.$set(this.classesTimeSetTable[idx].score,4,0);
        }
        else{
          let _total=0;
          for(let i=0;i<(this.classesTimeSetTable[idx].score.length-1);i++){
            _total+=Number(this.classesTimeSetTable[idx].score[i]);
          }
          this.$set(this.classesTimeSetTable[idx].score,4,_total);
        }
      },
      /*清空*/
      clearScoreClick(){
        for(let idx=0;idx<this.classesTimeSetTable.length;idx++){
          this.classesTimeSetTable[idx].score=['','','','',''];
        }
      },
      /*send ajax*/
      /*考评方案*/
      getEvaluationName(){
        judgesScoringParams().then(data=>{
          if(data.status){
            this.evaluationNData=data.data;
            if(this.evaluationNData.length>0){
              this.evaluationId=this.evaluationNData[0].id;
            }
            else{
              this.vmMsgWarning('您不是评委，无权参与打分');
            }
          }
          else{
            this.evaluationNData=[];
            this.evaluationId='';
          }
        })
      },
      getLoadAjax(){
        this.isLoading=true;
        judgesScoringLoad({id:this.evaluationId,groupId:this.IsEvaluationId}).then(data=>{
          if(data.status){
            this.classesTimeSetTable=data.data;
            this.isSubmit=Number(data.submit);
          }
          else{
            this.classesTimeSetTable=[];
            this.isSubmit=0;
          }
          this.isLoading=false;
        });
      },
      /*提交*/
      submitClick(){
        let _isFoo=true;
        /*判断分数是否为空*/
        for(let idx=0;idx<this.classesTimeSetTable.length;idx++){
          for(let i=0;i<(this.classesTimeSetTable[idx].score.length-1);i++){
            if(!this.classesTimeSetTable[idx].score[i]){
              _isFoo=false;
              this.vmMsgWarning('评分未完成，请继续打分！');
              break;
            }
          }
        }
        if(this.classesTimeSetTable.some(val=>val.score[4]>100)
          ||this.classesTimeSetTable.some(val=>(Number(val.score[0]) + Number(val.score[1]) + Number(val.score[2]) + Number(val.score[3]))>100)
          ||this.classesTimeSetTable.some(val=>(Number(val.score[0]))>25)
          ||this.classesTimeSetTable.some(val=>(Number(val.score[1]))>25)
          ||this.classesTimeSetTable.some(val=>(Number(val.score[2]))>25)
          ||this.classesTimeSetTable.some(val=>(Number(val.score[3]))>25)){
          this.vmMsgError('分数超额不能提交！');
          return;
        }
        if(_isFoo){
          this.$confirm('提交后无法修改，确定提交？','提示',{
            confirmButtonText:'确定',
            cancelButtonText:'取消',
            type:'warning'
          }).then(()=>{
            /*处理参数score*/
            let _score={};
            this.classesTimeSetTable.forEach((val,i)=>{
              _score[val.id]=val.score;
            });
            /*发送请求*/
            judgesScoringLoad({id:this.evaluationId,type:'submit',score:_score,groupId:this.IsEvaluationId}).then(data=>{
              if(data.status){
                this.vmMsgSuccess('提交成功！');
                this.getLoadAjax();
              }
              else{
                this.vmMsgError( data.msg );
              }
            });
          }).catch(()=>{});
        }
      },
    },
    created(){
      this.getEvaluationName();
    },
    watch:{
      evaluationId(val){
        this.getIsGroup(val);
      },
      IsEvaluationId(val){
        this.isEvaluationChange(val)
      }
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/style';
  @import '../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.css';
  @import '../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.less';
  .g-sa_header_search{.marginTop(32);.marginBottom(20);}
  .js_datePrompt{align-self:center;flex-grow:1;}
  .g-js_selectPart{margin-right:15/16rem;}
  .gs-refresh.g-fuzzyInput{width:auto;}
</style>


