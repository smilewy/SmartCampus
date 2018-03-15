<template>
  <div class="g-newEvaluation g-container">
    <header class="g-textHeader g-liOneRow">
      <div class="g-flexStartRow">
        <el-button class="g-gobackChart g-imgContainer RedButton" @click="goChartBack">
          <img src="../../../../assets/img/schManagementSystem/teachingAdministration/arrangeClasses/icon_return.png" />
          返回流程图
        </el-button>
        <h2 class="selfCenter g-headerH">修改考评方案</h2>
      </div>
    </header>
    <section class="g-content">
      <el-form ref="newEvaluationForm" :model="newEvaluationForm" :rules="formRule" label-position="left" label-width="85px">
        <el-form-item label="考评名称:" prop="name">
          <el-input v-model="newEvaluationForm.name" placeholder="请输入考评名称"></el-input>
        </el-form-item>
        <el-form-item label="考评时间:" required>
          <el-col :span="11">
            <el-form-item prop="startTime">
              <el-date-picker type="datetime" :picker-options="startPickerOption" placeholder="选择开始日期" v-model="newEvaluationForm.startTime" style="width: 100%;"></el-date-picker>
            </el-form-item>
          </el-col>
          <el-col class="el-icon-minus" :span="2"></el-col>
          <el-col :span="11">
            <el-form-item prop="endTime">
              <el-date-picker type="datetime" :picker-options="endPickerOption" placeholder="选择结束时间" v-model="newEvaluationForm.endTime" style="width: 100%;"></el-date-picker>
            </el-form-item>
          </el-col>
        </el-form-item>
      </el-form>
      <div class="g-footer">
        <el-button @click="createAjax" class="largeButton" type="primary">保存</el-button>
      </div>
    </section>
  </div>
</template>
<script>
  import {changeEvaluationProgramLoad} from '@/api/http'
  import moment from 'moment'
  export default{
    data(){
      let _self=this;
      return{
        newEvaluationForm:{
          name:'',
          startTime:'',
          endTime:''
        },
        startPickerOption:{
          disabledDate(time){
            if(_self.newEvaluationForm.endTime){
              return time.getTime()<Date.now()-8.64e7 || time.getTime()>Date.parse(_self.newEvaluationForm.endTime)-8.64e7;
            }else{
              return time.getTime()<Date.now()-8.64e7;
            }
          }
        },
        endPickerOption:{
          disabledDate(time){
            if(_self.newEvaluationForm.startTime){
              return time.getTime()<Date.parse(_self.newEvaluationForm.startTime);
            }else{
              return time.getTime()<Date.now()-8.64e7;
            }
          }
        },
        formRule:{
          name:[{required:true,message:'请输入考评名称'}],
          startTime:[{required:true,message:'请选择开始时间'}],
          endTime:[{required:true,message:'请选择结束时间'}],
        },
        /*send ajax param*/
        _id:'',
      }
    },
    methods:{
      /*返回流程图*/
      goChartBack(){
        this.$router.push('/evaluationManagement')
      },
      /*ajax*/
      getLoadAjax(){
        changeEvaluationProgramLoad({id:this._id}).then(data=>{
          if(data.status){
            this.newEvaluationForm=data.data;
          }
          else{
            this.vmMsgError( '初始数据加载失败，请重试！' );
          }
        });
      },
      createAjax(){
        this.$refs['newEvaluationForm'].validate((valid)=>{
          if(valid){
            changeEvaluationProgramLoad({type:'save',id:this._id,name:this.newEvaluationForm.name,startTime:moment(this.newEvaluationForm.startTime).format('YYYY-MM-DD HH:mm:ss'),endTime:moment(this.newEvaluationForm.endTime).format('YYYY-MM-DD HH:mm:ss')}).then(data=>{
              if(data.status){
                this.vmMsgSuccess( '修改成功！' );
              }else{
                this.vmMsgError( '修改失败！' );
              }
            });
          }
        });
      },
      /*数据处理成功回调*/
      handlerCallBack(){
        /*需根据页面不同进行修改*/
        this.$refs['newEvaluationForm'].resetFields();
      },
    },
    created(){
      this._id=this.$route.params.id;
      this.getLoadAjax();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/style';
  @import '../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.css';
  @import '../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.less';
  .g-content{/*848*/
    .width(848,1582);margin:170/16rem auto;
    /*时间选择中间横线*/
    .el-icon-minus{color:@elementBorder;text-align:center;.height(36);}
    button{.marginTop(100);}
  }
</style>






