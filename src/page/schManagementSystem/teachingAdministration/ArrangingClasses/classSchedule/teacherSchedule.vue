<template>
  <section class="g-classesTimeSetSection">
    <div class="g-sectionL">
      <header class="gL-header">
        <h2>待选教师</h2>
        <el-input @input="fuzzyClick" v-model="fuzzyInput" class="fuzzyInput" placeholder="请输入教师姓名" suffix-icon="el-icon-search"></el-input>
      </header>
      <section class="gL-section">
        <el-tree
            v-loading="loadingTree"
            element-loading-text="拼命加载中"
            element-loading-spinner="el-icon-loading"
            :highlight-current="true"
            :data="treeData"
            :filter-node-method="filterNode"
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
            <span class="NotHeight" v-if="n==1 || n==2" v-text="prop.row[n-1].subjectName"></span>
            <div v-else>
              <h2 v-text="prop.row[n-1].subjectName"></h2>
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
    teacherScheduleGetTeacher,//得到待选教师
    teacherScheduleGetCourse,/*table默认数据*/
  } from '@/api/http'
  import req from '@/assets/js/common'
  export default{
    data(){
      return{
        pkListId: '',
        /*教师模糊查询*/
        fuzzyInput:'',
        /*tree*/
        treeData:[],
        defaultProps: {
          children: 'data',
          label:'techerName',
        },
        /*table数据请求所需参数*/
        techerId:'',
        techerName:'',
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
      /*教师信息模糊查询*/
      fuzzyClick(){
        /*input框输入值改变触发的函数*/
        this.$refs['allMsg'].filter(this.fuzzyInput);
      },
      filterNode(value, data) {
        /*筛选节点*/
        if (!value) return true;
        return data.techerName.indexOf(value) !== -1;
      },
      /*tree点击事件*/
      handleNodeClick(data,node){
        /*点击节点回调*/
        /*点击名字发送请求，返回数据绑定在右边部分*/
        /*给每一级赋值一个唯一的id即可辨认点击的是几级*/
        if('data' in data){
          return false;
        }else{
          this.techerId=data.techerId;
          this.techerName=data.techerName;
          this.getTableData();
        }
      },
      /*table框点击单元格事件*/
      tableCellClick(row,column,cell,event){
        const columnNum=this.weekData.indexOf(column.label);
        if(columnNum>=0){
          if(row[columnNum]==0){
            this.vmMsgWarning( '已设置不上课，不能限制!' );
          } else if (row[columnNum]==2 || row[columnNum]==3){
            this.vmMsgWarning( '已设置不排课，不能限制！' );
          } else {
            if(row[columnNum]==4){
              this.$set(row,columnNum,1);
            }else{
              this.$set(row,columnNum,4);
            }
          }
        }
      },
      /*send ajax*/
      /*导出课表*/
      exportClick(){
        req.downloadFile('.g-classSchedule','/school/curriculum/table?type=getTeacherTableExport&pkListId='+this.pkListId+'&techerId='+this.techerId,'post');
      },
      /*得到教师ajax*/
      getTeacherAjax(){
        this.loadingTree = true;
        teacherScheduleGetTeacher({pkListId:this.pkListId}).then(data=>{
          this.loadingTree = false;
          if(data.statu){
            this.treeData=data.data;
          } else{
            this.vmMsgError( '得到待选教师出错，请重试！' );
          }
        })
      },
      /*得到表格默认信息*/
      getTableData(){
        this.loadingTable = true;
        teacherScheduleGetCourse({pkListId:this.pkListId,techerId:this.techerId}).then(data=>{
          this.loadingTable = false;
          if(data.statu){
            this.classesTimeSetTable=data.data;
          } else{
            this.vmMsgError( '数据加载失败，请重试！' );
          }
        });
      }
    },
    created(){
      this.pkListId = sessionStorage.pkListId;
      this.getTeacherAjax();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../../style/arrangeClasses/classSchedule';
  @import '../../../../../style/arrangeClasses/classSchedule/teacherSchedule';
  @import '../../../../../style/arrangeClasses/arrangeClasses.css';
</style>




