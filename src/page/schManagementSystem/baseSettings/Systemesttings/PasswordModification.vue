<template>
  <div class="PasswordModification">
    <h3>密码修改</h3>
    <el-col :span="10" :offset="6" class="password-box">
      <el-col :span="24">
        <el-col :span="4" class="password-box-text">当前密码：</el-col>
        <el-col :span="20">
          <el-input type="password" v-model="param.nowPassword"></el-input>
        </el-col>
      </el-col>
      <el-col :span="24" style="padding-top: 2rem">
        <el-col :span="4" class="password-box-text">新密码：</el-col>
        <el-col :span="20">
          <el-input type="password" v-model="param.passwordNew"></el-input>
        </el-col>
      </el-col>
      <el-col :span="24" style="padding-top: 2rem">
        <el-col :span="4" class="password-box-text">确认密码：</el-col>
        <el-col :span="20">
          <el-input type="password" v-model="param.passwordNewOne"></el-input>
        </el-col>
      </el-col>
      <el-col :span="20" :offset="4" style="padding-top: 4rem;padding-bottom:22rem">
        <el-button type="primary" style="width: 100%;" @click="submit()">提交</el-button>
      </el-col>
    </el-col>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
    export default{
        data(){
            return{
              param:{
                nowPassword:'',
                passwordNew:'',
                passwordNewOne:''
              }
            }
        },
      methods:{
        submit(){
          if(!this.param.nowPassword){
            this.vmMsgWarning('请输入当前密码');
              return;
          }
          if(!(this.param.passwordNew)){
            this.vmMsgWarning('请输入新密码');
            return;
          }
          if(!(this.param.passwordNewOne)){
            this.vmMsgWarning('请输入确认密码');
            return;
          }
          if(!(this.param.passwordNew).trim()){

          }
          if(this.param.passwordNew!==this.param.passwordNewOne){
            this.vmMsgWarning('两次新密码输入不一致');
            return;
          }
          this.$confirm('是否确定修改密码?', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            req.ajaxSend('/school/Systemup/passWordUpdate?type=updataPassword','post',this.param,(res)=>{
              if(res.statu===0){
                this.vmMsgError('当前密码输入错误');
                this.param.nowPassword='';
              }else{
                if(res.statu===1){
                  this.vmMsgSuccess('修改成功');
                }
                if(res.statu===2){
                  this.vmMsgError('两次密码不一致');
                }
                if(res.statu===3){
                  this.vmMsgError('修改失败');
                }
                this.param.nowPassword='';
                this.param.passwordNew='';
                this.param.passwordNewOne='';
              }
            });
          }).catch(() => {});
        }
      }
    }
</script>
<style lang="less" scoped>
  .PasswordModification{
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }
  .password-box{
    margin-top: 120/16rem;
  }
  .password-box-text{
    padding-top: .5rem;
  }
</style>
