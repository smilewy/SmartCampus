<template>
  <div class="g-classSchedule">
    <header class="g-timeHeader g-flexStartRow">
      <div class="g-headerButtonGroup">
        <h2>我的报修单</h2>
      </div>
      <ul class="changeRouter selfCenter clear_fix">
        <li class="normalCss" :class="{'activeCss':idx!=1&&idx!=2}" @click="changeRouterClick(0)">待处理</li>
        <li class="normalCss" :class="{'activeCss':idx==1}" @click="changeRouterClick(1)">已维修</li>
        <li class="normalCss" :class="{'activeCss':idx==2}" @click="changeRouterClick(2)">已验收</li>
      </ul>
    </header>
    <router-view></router-view>
  </div>
</template>
<script>
  import {mapState} from 'vuex'
  import {
    classesTimeSettingGrade,//得到班级年级
    classesTimeSettingTable, /*table默认数据*/
    classesTimeSettingSaved,//保存时间限制
  } from '@/api/http'

  export default {
    data() {
      return {
        idx: 0
      }
    },
    computed: {
      ...mapState(['pkListId']),
    },
    methods: {
      /*顶部li标签点击事件*/
      changeRouterClick(idx) {
        this.idx = idx;
        if (idx == 0) {
          this.$router.push({name: 'reportWait'});
        } else if (idx == 1) {
          this.$router.push({name: 'reportProcessed', params: {idx: idx}});
        } else {
          this.$router.push({name: 'reportAccepted', params: {idx: idx}});
        }
      }
    },
    created() {
      this.idx = this.$route.params.idx;
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/style';
  @import '../../../../style/arrangeClasses/classSchedule';
  @import '../../../../style/arrangeClasses/arrangeClasses.css';
</style>




