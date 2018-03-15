<template>
  <div class="g-classSchedule">
    <header class="g-timeHeader g-flexStartRow">
      <div class="g-headerButtonGroup">
        <el-button class="g-gobackChart RedButton" @click="goBackChart">
          <img src="../../../../assets/img/schManagementSystem/teachingAdministration/arrangeClasses/icon_return.png" />
          返回流程图
        </el-button>
      </div>
      <ul class="changeRouter selfCenter clear_fix">
        <li class="activeCss" data-msg="classesSchedule" @click="changeRouterClick">班级课表</li>
        <li class="normalCss" data-msg="teacherSchedule" @click="changeRouterClick">教师课表</li>
        <li class="normalCss" data-msg="totalSchedule" @click="changeRouterClick">总课表</li>
      </ul>
    </header>
    <el-card class="box-card" style="margin-top: 10px">
        <el-row :gutter="20">
            <el-col :span="8"><div class=" text item">方案名称：{{ kcbInfo.pkPlanName }}</div></el-col>
            <el-col :span="8"><div class=" text item">学年学期：{{ kcbInfo.yeerName }}</div></el-col>
            <el-col :span="8"><div class=" text item">排课范围：{{ kcbInfo.pkRange }}</div></el-col>
        </el-row>
        <el-row :gutter="20">
            <el-col :span="8"><div class=" text item">单双周情况：{{ kcbInfo.week }}</div></el-col>
            <el-col :span="8"><div class=" text item">开始时间：{{ kcbInfo.startTime }}</div></el-col>
            <el-col :span="8"><div class=" text item">结束时间：{{ kcbInfo.endTime }}</div></el-col>
        </el-row>
    </el-card>
    <router-view></router-view>
  </div>
</template>
<script>
  import {mapState} from 'vuex'
  import {classesTimeSettingGrade,//得到班级年级
    classesTimeSettingTable,/*table默认数据*/
    classesTimeSettingSaved,//保存时间限制
    kcbInfogGget // 获取课程表概要信息
  } from '@/api/http'
  export default{
    data(){
      return{
          pkListId: '',
          kcbInfo: {
              pkPlanName: '',
              pkRange: '',
              yeerName: '',
              week:'',
              startTime:'',
              endTime:''
          }
      }
    },
    methods:{
      /*点击返回流程图按钮*/
      goBackChart(){
        this.$router.push({name:'examinationChart'});
      },
      /*顶部li标签点击事件*/
      changeRouterClick(event){
        const e=$(event.currentTarget);
        e.addClass('activeCss');
        e.removeClass('normalCss');
        e.siblings().addClass('normalCss');
        e.siblings().removeClass('activeCss');
        this.$router.push({name:e.data('msg')});
      },
      getKcbInfo(){
          kcbInfogGget({ pkListId: this.pkListId }).then( (res) => {
              if( res.statu == 1 ){
                this.kcbInfo = res.data;
              } else {
                this.vmMsgError( '获取数据出错！' );
              }
          });
      }
    },
    created(){
        this.pkListId = sessionStorage.pkListId;
        this.getKcbInfo();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/style';
  @import '../../../../style/arrangeClasses/classSchedule';
  @import '../../../../style/arrangeClasses/arrangeClasses.css';
</style>




