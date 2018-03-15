<template>
  <div class="g-PublishCourse">
    <header class="g-timeHeader">
      <el-button class="g-gobackChart RedButton" @click="goBackChart">
        <img src="../../../../assets/img/schManagementSystem/teachingAdministration/arrangeClasses/icon_return.png"/>
        返回流程图
      </el-button>
      <el-button class="blueButton" @click="saveSetting">发布</el-button>
    </header>
    <section class="g-classesTimeSetSection">
      <el-form class="g-form" :model="publishCourseForm" label-position="right" label-width="75px">
        <el-form-item label="发布类型:">
          <el-radio-group v-model="publishCourseForm.type">
            <el-radio label="不分单双周" value="0"></el-radio>
            <el-radio label="单周" value="1"></el-radio>
            <el-radio label="双周" value="2"></el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="学年学期:">
          <el-select v-model="publishCourseForm.range" placeholder="请选择学年学期">
            <el-option v-for="(content,index) in semesterArr" :key="index" :value="content.yearid"
                       :label="content.yearname+' '+content.term"></el-option>
          </el-select>
        </el-form-item>
      </el-form>
    </section>
  </div>
</template>
<script>
  import {mapState} from 'vuex'
  import {
    PublishCourseGetLoad,//得到学年
    PublishCourseSave,//发布课程
  } from '@/api/http'
  export default{
    data(){
      return {
        pkListId: '',
        /*form表单的双向绑定数据*/
        publishCourseForm: {
          type: '',
          range: ''
        },
        /*学年*/
        semesterArr: [],
        /*星期转换*/
        weekData: ['星期一', '星期二', '星期三', '星期四', '星期五', '星期六', '星期日'],
      }
    },
    methods: {
      /*点击返回流程图按钮*/
      goBackChart(){
        this.$router.push({name: 'examinationChart'});
      },
      /*保存*/
      saveSetting(){
        if (!this.publishCourseForm.type) {
          this.vmMsgWarning('请选择发布类型!'); return false;
        }
        if (!this.publishCourseForm.range) {
          this.vmMsgWarning('请选择学年学期!'); return false;
        }
        PublishCourseSave({
          pkListId: this.pkListId,
          yearId: this.publishCourseForm.range,
          ifWeek: this.publishCourseForm.type
        }).then(data => {
          if (data.statu == 1) {
            this.vmMsgSuccess('发布成功!');
          } else if (data.statu == 2) {
            this.vmMsgError('该时段年级课表已存在!');
          } else {
            this.vmMsgError('发布失败!');
          }
        });
      },
      /*send ajax*/
      /*学年学期*/
      getLoadData(){
        PublishCourseGetLoad().then(data => {
          if (data.statu) {
            this.semesterArr = data.data;
          }
        });
      },
    },
    created(){
      this.pkListId = sessionStorage.pkListId;
      this.getLoadData();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/arrangeClasses/PublishCourse';
  @import '../../../../style/arrangeClasses/arrangeClasses.css';
</style>




