<template>
  <div class="g-classSchedule">
    <header class="g-timeHeader g-flexStartRow">
      <div class="g-headerButtonGroup">
        <h2>问卷统计</h2>
      </div>
      <ul class="changeRouter selfCenter clear_fix">
        <li class="activeCss" data-msg="echartsStatistic" @click="changeRouterClick">统计图表</li>
        <li class="normalCss" data-msg="replyDetail" @click="changeRouterClick">回答详情</li>
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
      }
    },
    computed:{
      ...mapState(['pkListId']),
    },
    methods:{
      /*顶部li标签点击事件*/
      changeRouterClick(event){
        const e=$(event.currentTarget);
        e.addClass('activeCss');
        e.removeClass('normalCss');
        e.siblings().addClass('normalCss');
        e.siblings().removeClass('activeCss');
        this.$router.push({name:e.data('msg')});
      },
    },
    created(){
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/style';
  @import '../../../../style/arrangeClasses/classSchedule';
  @import '../../../../style/arrangeClasses/arrangeClasses.css';
</style>




