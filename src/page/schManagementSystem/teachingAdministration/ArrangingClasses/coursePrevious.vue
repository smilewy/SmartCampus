<template>
  <div class="coursePrevious">
    <header class="g-timeHeader">
      <el-button class="g-gobackChart RedButton" @click="goBackChart">
        <img src="../../../../assets/img/schManagementSystem/teachingAdministration/arrangeClasses/icon_return.png" />
        返回流程图
      </el-button>
      <el-button class="blueButton" @click="saveSetting">保存</el-button>
    </header>
    <section class="g-classesTimeSetSection">
      <div class="g-sectionL">
        <header class="gL-header">
          <h2>待选科目</h2>
        </header>
        <section class="gL-section">
          <ul>
            <li @click="chooseCourse" :class="[index==0?'activeLi':'']" :data-id="content.subjectid" v-for="(content,index) in courseData" v-text="content.subjectname"></li>
          </ul>
        </section>
      </div>
      <div class="g-sectionC">
        <header class="gL-header">
          <h2>待选班级</h2>
        </header>
        <section class="gL-section g-tree">
          <!-- node-key="id" :default-expanded-keys="[1]"-->
          <el-tree
              v-loading="loadingTree"
              element-loading-text="拼命加载中"
              element-loading-spinner="el-icon-loading"
              :highlight-current="true"
              :data="treeData"
              :props="defaultProps"
              ref="allMsg"
              @node-click="handleNodeClick"></el-tree>
        </section>
      </div>
      <div class="g-sectionR alertsList">
        <header>
          <h2 v-if="headerText" v-text="headerText+'班课表'"></h2>
          <h2 v-else>(某)班课表</h2>
        </header>
        <el-table
              v-loading="loadingTable"
              element-loading-text="拼命加载中"
              element-loading-spinner="el-icon-loading"
              :data="classesTimeSetTable"
              @cell-click="tableCellClick"
              border
              class="g-timeSettingTable">
          <el-table-column label="节/周">
            <template slot-scope="props">
              <span v-text="'第'+(props.$index+1)+'节'"></span>
            </template>
          </el-table-column>
          <el-table-column v-for="n in 7" :key="n" :label="weekData[n-1]">
            <template slot-scope="props">
              <span v-if="props.row[n-1].statu==0" class="NotCourse">不上课</span>
              <span v-if="props.row[n-1].statu==2" class="NotCourse">班级不排课</span>
              <span v-if="props.row[n-1].statu==3" class="NotCourse">{{curriculumName}}不排课</span>
              <span v-if="props.row[n-1].statu==4" class="NotCourse">教师不排课</span>
              <span v-if="props.row[n-1].statu==5 && props.row[n-1].subject!==curriculumName" class="NotCourse" v-text="props.row[n-1].subject"></span>
              <span v-if="props.row[n-1].statu==5 && props.row[n-1].subject==curriculumName" class="SetNotCourse" v-text="curriculumName"></span>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </section>
  </div>
</template>
<script>
  import {mapState} from 'vuex'
  import {courseTimeSettingCourseLoad,//得到课程
    classesTimeSettingGrade,//得到班级年级
    coursePreviousTableLoad,/*table默认数据*/
    coursePreviousSaved,//保存时间限制
  } from '@/api/http'
  export default{
    data(){
      return{
        pkListId: '',
        /*表格header文本*/
        headerText:'',
        /*tree*/
        treeData:[],
        defaultProps: {
          children: 'data',
          label:'name',
        },
        /*课程数据*/
        curriculumId:'',
        curriculumName:'',
        courseData:[],
        /*table数据请求所需参数*/
        gradeId:'',
        gradeName:'',
        classId:'',
        className:'',
        teacherId:'',
        teacherName:'',
        /*table框绑定数据*/
        classesTimeSetTable:[],
        /*年级显示转换*/
        gradeData:['一年级','二年级','三年级','四年级','五年级','六年级','初一','初二',
          '初三','高一','高二','高三'
        ],
        /*星期转换*/
        weekData:['星期一','星期二','星期三','星期四','星期五','星期六','星期日'],
        /*最大课程*/
        allSubjectCount:0,
        loadingTree: false,
        loadingTable: false
      }
    },
    methods:{
      /*点击返回流程图按钮*/
      goBackChart(){
        this.$router.push({name:'examinationChart'});
      },
      /*保存*/
      saveSetting(){
        coursePreviousSaved({pkListId:this.pkListId,gradeId:this.gradeId,classId:this.classId,className:this.className,gradeName:this.gradeName,data:this.classesTimeSetTable}).then(data=>{
          if(data.statu==1){
            this.vmMsgSuccess( '保存成功！' );
            this.getTableData();
          }else{
            this.vmMsgError( '保存失败！' );
          }
        })
      },
      /*选择课程*/
      chooseCourse(event){
        const e=$(event.currentTarget);
        e.addClass('activeLi');
        e.siblings().removeClass('activeLi');
        this.curriculumId=e.data('id');
        this.curriculumName=e.text();
        this.getTableData();
      },
      /*tree点击事件*/
      handleNodeClick(data,node){
        /*点击节点回调*/
        /*点击名字发送请求，返回数据绑定在右边部分*/
        /*给每一级赋值一个唯一的id即可辨认点击的是几级*/
        if('data' in data){
          this.gradeName=data.name;
          return;
        }else{
          this.classId=data.classid;
          this.gradeId=data.gradeId;
          this.getTableData();
          this.headerText=node.parent.data.name+data.name;
          this.className=data.name;
        }
      },
      /*table框点击单元格事件*/
      tableCellClick(row,column,cell,event){
        const columnNum=this.weekData.indexOf(column.label);
        if(columnNum>=0){
          if(row[columnNum].statu==0){
            this.vmMsgWarning( '已设置不上课，不能设置！' );
          }
          else if(row[columnNum].statu==2 || row[columnNum].statu==3 || row[columnNum].statu==4){
            this.vmMsgWarning( '已设置不排课，不能设置！' );
          }
          else{
            if(row[columnNum].statu==5){
              if(row[columnNum].subject==this.curriculumName){
                row[columnNum].statu=1;
                row[columnNum].subject='';
                row[columnNum].subjectId='';
              }
              else{
                this.vmMsgWarning( '已预先安排'+row[columnNum].subject+'，不能设置！' );
              }
            }
            else{
              if(this.checkRemainCount()<this.allSubjectCount){
                row[columnNum].statu=5;
                row[columnNum].subject=this.curriculumName;
                row[columnNum].subjectId=this.curriculumId;
                row[columnNum].teacherId=this.teacherId;
                row[columnNum].teacherName=this.teacherName;
              }
              else{
                this.vmMsgWarning( '科目预排超额！' );
              }
            }
          }
        }
      },
      /*计算预先安排总节数*/
      checkRemainCount(){
        let count=0;
        this.classesTimeSetTable.forEach((row,rowI)=>{
          row.forEach((column,columnI)=>{
            if(column.statu==5 && column.subject==this.curriculumName){
              count++;
            }
          });
        });
        return count;
      },
      /*send ajax*/
      /*得到课程*/
      getCourse(){
        courseTimeSettingCourseLoad().then(data => {
          if (data.statu) {
            this.courseData = data.data;
          } else {
            this.vmMsgError('加载失败,请重新加载页面!');
          }
          if(this.courseData.length>0){
            this.curriculumId=this.courseData[0].subjectid;
            this.curriculumName=this.courseData[0].subjectname;
            this.getGradeAjax();
          }
        })
      },
      /*得到年级和班级ajax*/
      getGradeAjax(){
        this.loadingTree = true;
        classesTimeSettingGrade({pkListId:this.pkListId}).then(data=>{
          this.loadingTree = false;
          if (data.statu) {
            this.treeData = data.data;
          } else {
            this.vmMsgError('加载失败,请重新加载页面!');
          }
          /*headerText*/
          if(this.treeData.length>0){
            this.headerText='';
            this.gradeId=this.treeData[0].gradeId;
            this.classId=this.treeData[0].data[0].classid;
            this.gradeName=this.treeData[0].name;
            this.className=this.treeData[0].data[0].name;
          }
        })
      },
      /*得到表格默认信息*/
      getTableData(){
        this.loadingTable = true;
        coursePreviousTableLoad({pkListId:this.pkListId,gradeId:this.gradeId,classId:this.classId,subjectId:this.curriculumId}).then(data=>{
          this.loadingTable = false;
          if (data.statu) {
            this.classesTimeSetTable = data.data.classSet;
            this.teacherName = data.data.teacherName;
            this.teacherId = data.data.techerId;
            this.allSubjectCount=data.allSubjectCount;
          } else {
            this.vmMsgError('加载失败,请重新加载页面!');
          }
        });
      },
    },
    created(){
      this.pkListId = sessionStorage.pkListId;
      this.getCourse();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/arrangeClasses/coursePrevious';
  @import '../../../../style/arrangeClasses/arrangeClasses.css';
</style>




