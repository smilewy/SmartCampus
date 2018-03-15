<template>
  <div class="g-newEvaluation g-container">
    <header class="g-textHeader">
      <h2>新建考评方案</h2>
    </header>
    <section class="g-content">
      <el-form ref="newEvaluationForm" :model="newEvaluationForm" :rules="formRule" label-position="left" label-width="85px">
        <el-form-item label="考评名称:" prop="name">
          <el-input v-model="newEvaluationForm.name" placeholder="请输入考评名称"></el-input>
        </el-form-item>
        <el-form-item label="考评时间:" required>
          <el-col :span="11">
            <el-form-item prop="startTime">
              <el-date-picker type="date" :picker-options="startPickerOption" placeholder="选择开始日期" v-model="newEvaluationForm.startTime" style="width: 100%;"></el-date-picker>
            </el-form-item>
          </el-col>
          <el-col class="el-icon-minus" :span="2"></el-col>
          <el-col :span="11">
            <el-form-item prop="endTime">
              <el-date-picker type="date" :picker-options="endPickerOption" placeholder="选择结束时间" v-model="newEvaluationForm.endTime" style="width: 100%;"></el-date-picker>
            </el-form-item>
          </el-col>
        </el-form-item>
      </el-form>
      <div class="g-footer">
        <el-button @click="createAjax" class="largeButton" type="primary">提交</el-button>
      </div>
    </section>
  </div>
</template>
<script>
  import {newEvaluationLoad} from '@/api/http'
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
              return time.getTime() > _self.newEvaluationForm.endTime;
            }
            else{
              return time.getTime()<Date.now()-8.64e7;
            }
          }
        },
        endPickerOption:{
          disabledDate(time){
            if(_self.newEvaluationForm.startTime){
              return time.getTime() < _self.newEvaluationForm.startTime;
            }
            else{
              return time.getTime()<Date.now()-8.64e7;
            }
          }
        },
        formRule:{
          name:[{required:true,message:'请输入考评名称'}],
          startTime:[{required:true,message:'请选择开始时间'}],
          endTime:[{required:true,message:'请选择结束时间'}],
        },
      }
    },
    methods:{
      createAjax(){
        if(moment(this.newEvaluationForm.startTime).format('YYYY-MM-DD') > moment(this.newEvaluationForm.endTime).format('YYYY-MM-DD')){
          this.vmMsgWarning( '结束时间必须大于开始时间' ); return;
        }
        this.$refs['newEvaluationForm'].validate((valid)=>{
          if(valid){
            newEvaluationLoad({type:'create',name:this.newEvaluationForm.name,startTime:moment(this.newEvaluationForm.startTime).format('YYYY-MM-DD'),endTime:moment(this.newEvaluationForm.endTime).format('YYYY-MM-DD')}).then(data=>{
              this.createHandler('保存',data);
            });
          }
          else{
            this.vmMsgWarning( '请填写完整相关信息！' );
          }
        });
      },
      /*创建等的数据处理*/
      createHandler(msg,data){
        if(data.status==1){
          this.vmMsgSuccess( msg+'成功！' );
        }else if(data.status==2){
          this.vmMsgWarning( '此方案已存在' );
        }
        else{
          this.vmMsgError( msg+'失败，请重试！' );
        }
      },
      /*数据处理成功回调*/
      handlerCallBack(){
        /*需根据页面不同进行修改*/
        this.$refs['newEvaluationForm'].resetFields();
      },
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




