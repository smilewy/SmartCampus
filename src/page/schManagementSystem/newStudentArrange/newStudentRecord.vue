<template>
  <div class="g-container g-studentRecord">
    <header class="g-textHeader g-importCourseHeader">
      <div class="g-flexStartRow">
        <el-button style="background: #ff5b5b;border-color: #ff5b5b;color: #fff;border-radius: 1rem;padding: 0.5rem 1.225rem;" @click="goBackChart">
          <img src="../../../assets/img/schManagementSystem/teachingAdministration/arrangeClasses/icon_return.png" />
          返回流程图
        </el-button>
        <h2 class="selfCenter">学生补录</h2>
      </div>
    </header>
    <section class="g-section">
      <el-form ref="studentForm" class="scheduleDialog g-form" :rules="rules" :model="studentMsgForm" label-position="right" label-width="100px">
        <el-form-item label="年级:">
          <el-input disabled v-model="gradeName" placeholder="请选择年级"></el-input>
        </el-form-item>
        <el-form-item label="姓名:" class="g-floatLeft" prop="name">
          <el-input v-model="studentMsgForm.name"></el-input>
        </el-form-item>
        <el-form-item label="性别:" class="g-floatLeft" prop="sex">
          <el-radio-group v-model="studentMsgForm.sex">
            <el-radio label="女" value="女"></el-radio>
            <el-radio label="男" value="男"></el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="是否借读:" class="g-floatLeft" prop="IsTempStudy">
          <el-switch active-text="是" inactive-text="否" v-model="studentMsgForm.IsTempStudy"></el-switch>
        </el-form-item>
        <el-form-item label="指定到班:" prop="classId">
          <el-select v-model="studentMsgForm.classId" placeholder="请选择班级">
            <el-option v-for="(content,n) in chooseClassData" :key="n" :label="content.className" :value="content.classId"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="手机号码:" class="g-floatLeft" prop="phone">
          <el-input v-model="studentMsgForm.phone"></el-input>
        </el-form-item>
      </el-form>
      <div class="g-footer">
        <el-button class="largeButton" @click="saveClick" type="primary">提交</el-button>
      </div>
    </section>
  </div>
</template>
<script>
  import {fileTypeCheck} from '@/assets/js/common'
  import {
    newStudentGetGrade,//得到指定班级
    newStudentRecordSet,//操作
  } from '@/api/http'
  export default{
    data(){
      return {
        /*form表单的双向绑定数据*/
        gradeName:'一年级',
        studentMsgForm:{
          classId:'',
          name:'',
          phone:'',
          sex:'女',
          IsTempStudy:'',//借读,值为true/false
        },
        chooseClassData:[],
        /*send ajax param*/
        sendAjaxParam:{
          gradeId:'',
          classId:'',
          className:'',
          name:'',
          phone:'',
          sex:'',
          IsTempStudy:'',//借读,值为0/1
        },
        rules:{
          classId:[{required:true,message:'请选择指定到班'}],
          name:[{required:true,message:'请输入姓名'}],
          phone:[{required:true,message:'请输入手机号码'}],
        },
      }
    },
    methods:{
      /*点击返回流程图按钮*/
      goBackChart(){
        this.$router.push({name:'newStudentClass'});
      },
      /*点击保存*/
      saveClick(){
        if(!/^(13\d|14[579]|15[^4\D]|17[^49\D]|18\d)\d{8}$/.test(this.studentMsgForm.phone)){
          this.vmMsgWarning('请输入正确格式的手机号码');
          return;
        }
        this.$refs.studentForm.validate((valid)=>{
          if(valid){
            /*赋值className*/
            for(let i=0;i<this.chooseClassData.length;i++){
              if(this.chooseClassData[i].classId==this.studentMsgForm.classId){
                this.sendAjaxParam.className=this.chooseClassData[i].className;
                break
              }
            }
            /*赋值*/
            Object.keys(this.sendAjaxParam).forEach((value)=>{
              if(value in this.studentMsgForm){
                if(value=='IsTempStudy'){
                  this.sendAjaxParam[value]=Number(this.studentMsgForm[value]);
                }
                else{
                  this.sendAjaxParam[value]=this.studentMsgForm[value];
                }
              }
            });
            this.saveAjax();
          }
        });
      },
      /*send ajax*/
      getLoadAjax(){
        newStudentGetGrade({func:'gradeClass',param:{gradeId:this.sendAjaxParam.gradeId}}).then(data=>{
          this.gradeName=data.grade;
          if(data.status){
            this.chooseClassData=data.data;
          }
          else{
            this.vmMsgError('数据加载失败');
            this.chooseClassData=[];
          }
        });
      },
      saveAjax(){
        newStudentRecordSet(this.sendAjaxParam).then(data=>{
          if(data.status){
            this.vmMsgSuccess('保存成功！');
            this.$refs.studentForm.resetFields();
          }
          else{
            this.vmMsgError('保存失败，请重试！');
          }
        });
      }
    },
    created(){
      this.sendAjaxParam.gradeId=this.$route.params.gradeId;
      this.getLoadAjax();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../style/style';
  .g-textHeader{
    h2{.marginLeft(40,1582);}
  }
  .g-section{padding-top:50/16rem;
    .g-form{/*852*/
      .width(852,1582);margin:0 365/1582*100%;
    }
    .g-footer{
      .width(852,1582);margin:0 365/1582*100% 95/16rem;
    }
  }
</style>


