<template>
  <div class="BindAccount">
    <h3>账号绑定</h3>
    <el-col :span="20" :offset="2">
      <div class="Setting">安全设置</div>
      <div>
        <div>
          <img src="" alt="">
        </div>
        <div>
          <div>手机绑定</div>
          <div>可用手机号加密码登录系统，可通过手机号找回密码</div>
        </div>
        <div>
          <el-button>立即绑定</el-button>
        </div>
      </div>
    </el-col>
  </div>
</template>
<script>
    export default{
      data(){
        return{

        }
      }
    }
</script>
<style lang="less" scoped>
  .BindAccount{
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
    .Setting{
      color: #2DB1D7;
      border-left: .3rem solid #2DB1D7;
      padding-left: .3rem;
      margin-top: 50/16rem;
      margin-bottom: 80/16rem;
      font-weight: bolder;
      font-size: 1.1rem;

    }
  }

</style>
