<template>
  <div class="publishSubClassResult">
    <el-row type="flex" align="middle" class="subClassDivision_title">
      <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
        src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
        alt=""><span class="returnTxt">返回流程图</span></el-button>
      <h3>发布分班结果</h3>
    </el-row>
    <el-row class="publishSubClassResult_row">
      <el-form ref="form" :model="form" :rules="rules" label-width="180px" class="subMsg">
        <el-form-item label="允许学生查看分班结果：">
          <el-switch active-text="是" inactive-text="否" active-color="#09baa7"
                     inactive-color="#ff8686" v-model="form.score"></el-switch>
        </el-form-item>
        <el-form-item label="分班结果展示起止时间：" required>
          <el-col :span="11">
            <el-form-item prop="startTime">
              <el-date-picker type="date"
                              :picker-options="pickerBeginDateBefore"
                              :editable="false" placeholder="选择时间" v-model="form.startTime" style="width: 100%;"></el-date-picker>
            </el-form-item>
          </el-col>
          <el-col class="line" :span="2">-</el-col>
          <el-col :span="11">
            <el-form-item prop="endTime">
              <el-date-picker type="date"
                              :picker-options="pickerBeginDateAfter"
                              :editable="false" placeholder="选择时间" v-model="form.endTime" style="width: 100%;"></el-date-picker>
            </el-form-item>
          </el-col>
        </el-form-item>
      </el-form>
      <el-row class="submitBtn">
        <el-button type="primary" @click="publish">发布</el-button>
      </el-row>
    </el-row>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  import moment from 'moment'
  export default{
    data(){
      return {
        form: {
          name: '',
          score: true,
          startTime:'',
          endTime:''
        },
        pickerBeginDateBefore: {
          disabledDate: (time) => {
            let beginDateVal = this.form.endTime;
            if (beginDateVal) {
              return time.getTime() > beginDateVal;
            }
          }
        },
        pickerBeginDateAfter: {
          disabledDate: (time) => {
            let beginDateVal = this.form.startTime;
            if (beginDateVal) {
              return time.getTime() < beginDateVal;
            }
          }
        },
        rules:{
          startTime: [
            { type: 'date', required: true, message: '请选择日期', trigger: 'change' }
          ],
          endTime: [
            { type: 'date', required: true, message: '请选择日期', trigger: 'change' }
          ]
        }
      }
    },
    methods: {
      returnFlowchart(){
        this.$router.push('/subClassDivisionHome');
      },
      publish(){
          var self=this,data={
            planId:self.$route.params.planId,
            stuLook:self.form.score?1:0,
            sTime:moment(self.form.starttime).format('YYYY-MM-DD'),
            eTime:moment(self.form.endTime).format('YYYY-MM-DD')
          };
        this.$refs['form'].validate((valid) => {
          if (valid) {
              req.ajaxSend('/school/DivideBranch/publish','post',data,function (res) {
                if(res.status==1){
                  self.vmMsgSuccess('发布成功！');
                }else{
                  self.vmMsgError(res.msg);
                }
              })
          } else {
            return false;
          }
        });
      }
    }
  }
</script>
<style>
  .publishSubClassResult .publishSubClassResult_row{
    width:60%;
    margin:7.5rem auto 20rem;
  }
  .publishSubClassResult .line{
    text-align: center;
  }
  .publishSubClassResult .submitBtn{
    margin-top:6.25rem;
  }
  .publishSubClassResult .submitBtn .el-button{
  width:100%;
  }
</style>
