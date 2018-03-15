<template>
  <section class="g-classesTimeSetSection">
    <div class="g-sectionL">
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
            @node-click="handleNodeClick">
        </el-tree>
      </section>
    </div>
    <div class="g-sectionR alertsList">
      <div class="gs-header">
        <div class="gs-button alertsBtn">
          <el-button-group>
            <el-button @click="exportClick" class="filt" title="导出">
              <img class="filt_unactive" src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png" />
              <img class="filt_active" src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png" />
            </el-button>
          </el-button-group>
          <el-button-group class="elGroupButton_two">
            <el-button class="filt" title="复制">
              <img class="filt_unactive" src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png" />
              <img class="filt_active" src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png" />
            </el-button>
            <el-button class="filt" title="打印预览">
              <img class="filt_unactive" src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png" />
              <img class="filt_active" src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png" />
            </el-button>
          </el-button-group>
        </div>
      </div>
      <el-table
          v-loading="loadingTable"
          element-loading-text="拼命加载中"
          element-loading-spinner="el-icon-loading"
          :data="classesTimeSetTable"
          border
          class="g-timeSettingTable">
        <el-table-column v-for="n in 9" :key="n" :label="weekData[n-1]">
          <template slot-scope="prop">
            <!--第一列只有时间-->
            <!-- <span class="NotHeight" v-text="prop.row[n-1]"></span> -->
            <span class="NotHeight" v-if="n==1 || n==2" v-text="prop.row[n-1].subjectName"></span>
            <div v-else>
              <h2 v-text="prop.row[n-1].subjectName"></h2>
              <span class="NotHeight" v-show="prop.row[n-1].teacherName" v-text="'('+prop.row[n-1].teacherName+')'"></span>
            </div>
          </template>
        </el-table-column>
      </el-table>
    </div>
  </section>
</template>
<script>
  import {mapState} from 'vuex'
  import {
    classScheduleGetClass,//得到班级年级
    classScheduleGetCourse,/*table默认数据*/
  } from '@/api/http'
  import req from '@/assets/js/common'
  export default{
    data(){
      return{
        pkListId: '',
        /*tree*/
        treeData:[],
        defaultProps: {
          children:'data',
          label:'classname',
        },
        /*table数据请求所需参数*/
        gradeId:'',
        classId:'',
        /*table框绑定数据*/
        classesTimeSetTable:[],
        /*年级显示转换*/
        gradeData:['一年级','二年级','三年级','四年级','五年级','六年级','初一','初二',
          '初三','高一','高二','高三'
        ],
        /*星期转换*/
        weekData:['节/周','上课时间','星期一','星期二','星期三','星期四','星期五','星期六','星期日'],
        loadingTree: false,
        loadingTable: false
      }
    },
    methods:{
      /*header的button群*/
      /*tree点击事件*/
      handleNodeClick(data,node){
        /*点击节点回调*/
        /*点击名字发送请求，返回数据绑定在右边部分*/
        /*给每一级赋值一个唯一的id即可辨认点击的是几级*/
        if('data' in data){
          return;
        }else{
          this.classId=data.classid;
          this.gradeId=data.grade;
          this.getTableData();
        }
      },
      /*table框点击单元格事件*/
      tableCellClick(row,column,cell,event){
        const columnNum=this.weekData.indexOf(column.label);
        if(columnNum>=0){
          if(row[columnNum]==0){
            this.vmMsgWarning( '已设置不上课，不能限制！' );
          } else {
            if(row[columnNum]==2){
              this.$set(row,columnNum,1);
            } else {
              this.$set(row,columnNum,2);
            }
          }
        }
      },
      /*send ajax*/
      /*导出课表*/
      exportClick(){
        req.downloadFile('.g-classSchedule','/school/curriculum/table?type=getClassTableExport&pkListId='+this.pkListId+'&grade='+this.gradeId+'&classid='+this.classId,'post');
      },
      /*得到年级和班级ajax*/
      getGradeAjax(){
        this.loadingTree = true;
        classScheduleGetClass({pkListId:this.pkListId}).then(data=>{
          this.loadingTree = false;
          if(data.statu){
            this.treeData=data.data;
          } else{
            this.vmMsgError( '得到待选班级出错，请重试！' );
          }
        });
      },
      /*得到表格默认信息*/
      getTableData(){
        this.loadingTable = true;
        classScheduleGetCourse({pkListId:this.pkListId,grade:this.gradeId,classid:this.classId}).then(data=>{
          this.loadingTable = false;
          if(data.statu){
            this.classesTimeSetTable=data.data;
          } else{
            this.vmMsgError( '数据加载失败，请重试！' );
          }
        })
      },
    },
    created(){
      this.pkListId = sessionStorage.pkListId;
      this.getGradeAjax();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../../style/arrangeClasses/classSchedule';
  @import '../../../../../style/arrangeClasses/classSchedule/classesSchedule';
  @import '../../../../../style/arrangeClasses/arrangeClasses.css';
</style>




