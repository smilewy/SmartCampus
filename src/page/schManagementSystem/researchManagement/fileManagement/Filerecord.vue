<template>
  <div class="Filerecord">
    <el-col :span="24">
      <h3 class="DocAp-title">档案记录</h3>
      <div class="DocAp-title" style="margin-left: 1.8rem;">
        <span v-for="(list,index) in AppLists"
              :class="{Topactive:changeTopcolor == index}"
              @click="toggleTitleClass(index,list)">{{list.text}}
        </span>
      </div>
    </el-col>
    <router-view></router-view>
  </div>
</template>
<script>
  export default{
    data(){
      return{
        changeTopcolor:0,
        AppLists:[{
          text:'待处理'
        },{
          text:'已通过'
        }],
      }
    },
    created(){
      this.changeTopcolor=0;
      if(this.$route.path.indexOf('/Filerecord/FilerecordPassed')>-1){
        this.$router.push('/Filerecord')
      }
    },
    methods:{
      toggleTitleClass:function(index){
        this.changeTopcolor = index;
        if(index===0){
          this.$router.push('/Filerecord')
        }else{
          this.$router.push('/Filerecord/FilerecordPassed')
        }
      },

    }
  }
</script>
<style scoped>
  .Filerecord .DocAp-title{
    display: inline-block;
  }
  .Filerecord .DocAp-title>span{
    cursor: pointer;
    margin-left: .5rem;
  }
  .Filerecord .DocAp-title>span:first-child{
    padding-right: 1rem;
    border-right: 1px solid #d2d2d2;
  }
  .Filerecord .DocAp-title>span:last-child{
    padding-left: 1rem;
  }
  .Topactive{
    color: #4ba8ff;
  }
  .Filerecord{
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }
</style>
