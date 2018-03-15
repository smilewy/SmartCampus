<template>
  <div class="g-classSchedule">
    <header class="g-timeHeader g-flexStartRow">
      <div class="g-headerButtonGroup">
        <h2>资产审批</h2>
      </div>
      <ul class="changeRouter selfCenter clear_fix">
        <li class="normalCss" :class="{'activeCss':idx!=1}" @click="changeRouterClick(0)">待审批</li>
        <li class="normalCss" :class="{'activeCss':idx==1}" @click="changeRouterClick(1)">已审批</li>
      </ul>
    </header>
    <router-view></router-view>
  </div>
</template>
<script>
  import {mapState} from 'vuex'
  import {classesTimeSettingGrade,//得到班级年级
    classesTimeSettingTable,/*table默认数据*/
    classesTimeSettingSaved,//保存时间限制
  } from '@/api/http'
  export default{
    data(){
      return{
        idx:0
      }
    },
    computed:{
      ...mapState(['pkListId']),
    },
    methods:{
      /*顶部li标签点击事件*/
      changeRouterClick(idx){
        this.idx = idx;
        if (idx == 0) {
          this.$router.push({name: 'assetPendApproval'});
        } else{
          this.$router.push({name: 'assetAlreadyApproval', params: {idx: idx}});
        }
      }
    },
    created(){
      this.idx = this.$route.params.idx;
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/style';
  @import '../../../../style/arrangeClasses/classSchedule';
  @import '../../../../style/arrangeClasses/arrangeClasses.css';
</style>




