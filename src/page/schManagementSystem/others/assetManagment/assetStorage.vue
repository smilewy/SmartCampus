<template>
  <div class="g-classSchedule">
    <header class="g-timeHeader g-flexStartRow">
      <div class="g-headerButtonGroup">
        <h2>资产入库</h2>
      </div>
      <ul class="changeRouter selfCenter clear_fix">
        <li class="normalCss" :class="{'activeCss':idx!=1}" @click="changeRouterClick(0)">新增入库</li>
        <li class="normalCss" :class="{'activeCss':idx==1}" @click="changeRouterClick(1)">入库记录</li>
      </ul>
    </header>
    <router-view></router-view>
  </div>
</template>
<script>
  import {mapState} from 'vuex'

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
          this.$router.push({name: 'newStorage'});
        } else {
          this.$router.push({name: 'storageRecord', params: {idx: idx}});
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




