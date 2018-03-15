<template>
  <div class="g-ClassestimeSetting">
    <header class="g-timeHeader">
      <el-button class="g-gobackChart RedButton">
        <img src="../../../../assets/img/schManagementSystem/teachingAdministration/arrangeClasses/icon_return.png" />
        <router-link to="examinationChart">返回流程图</router-link>
      </el-button>
      <el-button class="blueButton" @click="saveSetting">保存</el-button>
    </header>
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
              node-key="name"
              :data="treeData"
              :props="defaultProps"
              ref="allMsg"
              @node-click="handleNodeClick">
          </el-tree>
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
              <span v-if="props.row[n-1].statu==2" class="SetNotCourse">不排课</span>
              <span v-if="props.row[n-1].statu==5" class="NotCourse" v-text="props.row[n-1].subject"></span>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </section>
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
        pkListId: '',
        /*表格header文本*/
        headerText:'',
        /*tree*/
        treeData:[],
        defaultProps: {
          children: 'data',
          label:'name',
        },
        /*table数据请求所需参数*/
        gradeId:'',
        gradeName:'',
        classId:'',
        className:'',
        /*table框绑定数据*/
        classesTimeSetTable:[],
        /*年级显示转换*/
        gradeData:['一年级','二年级','三年级','四年级','五年级','六年级','初一','初二',
          '初三','高一','高二','高三'
        ],
        /*星期转换*/
        weekData:['星期一','星期二','星期三','星期四','星期五','星期六','星期日'],
        /*必须剩余排课节数*/
        allClassCount:0,
        /*table中总可排节数*/
        allTableCount:0,
        /*剩余可排节数*/
        remainTableCount:0,
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
        classesTimeSettingSaved({pkListId:this.pkListId,gradeId:this.gradeId,classId:this.classId,className:this.className,gradeName:this.gradeName,data:this.classesTimeSetTable}).then(data=>{
          if(data.statu==1){
            this.vmMsgSuccess( '保存成功！' );
            this.getTableData();
          }else{
            this.vmMsgError( '保存失败！' );
          }
        })
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
            this.vmMsgWarning( '已设置不上课，不能设置!' );
          }
          else if(row[columnNum].statu==5){
            this.vmMsgWarning( '已预先安排'+row[columnNum].subject+'，不能设置!' );
          }
          else{
            if(row[columnNum].statu==2){
              row[columnNum].statu=1;
              this.remainTableCount=this.allTableCount+Number(this.checkRemainTable());
            }else{
              if(this.remainTableCount>this.allClassCount){
                row[columnNum].statu=2;
                this.remainTableCount=this.allTableCount-Number(this.checkRemainTable());
              } else {
                this.vmMsgWarning( '班级不排课超额！' );
              }
            }
          }
        }
      },
      /*判断页面中有多少个不排课*/
      checkRemainTable(){
        let count=0;
        this.classesTimeSetTable.forEach((row,rowI)=>{
          row.forEach((column,columnI)=>{
            if(column.statu==2){
              count++;
            }
          });
        });
        return count;
      },
      /*send ajax*/
      /*得到年级和班级ajax*/
      getGradeAjax(){
        this.loadingTree = true;
        classesTimeSettingGrade({pkListId:this.pkListId}).then(data=>{
          this.loadingTree = false;
          if ( data.statu ) {
            this.treeData = data.data;
          } else {
            this.vmMsgError( '加载失败,请重新加载页面!' );
          }
          /*headerText*/
          if(this.treeData.length>0){
            this.headerText='';
            this.gradeId=this.treeData[0].gradeId;
            this.classId= (this.treeData[0].data && this.treeData[0].data.length > 0) ? this.treeData[0].data[0].classid : '';
            this.gradeName=this.treeData[0].name;
            this.className= (this.treeData[0].data && this.treeData[0].data.length > 0) ? this.treeData[0].data[0].name: '';
          }
        })
      },
      /*得到表格默认信息*/
      getTableData(){
        this.loadingTable = true;
        classesTimeSettingTable({pkListId:this.pkListId,gradeId:this.gradeId,classId:this.classId}).then(data=>{
          this.loadingTable = false;
          if ( data.statu ) {
            this.classesTimeSetTable = data.data;
            this.allClassCount=data.allClassCount;
            this.allTableCount=data.allTableCount;
            this.remainTableCount=this.allTableCount-Number(this.checkRemainTable());
          } else {
            this.vmMsgError( '加载失败,请重新加载页面!' );
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
  @import '../../../../style/arrangeClasses/classesTimeSetting';
  @import '../../../../style/arrangeClasses/arrangeClasses.css';
</style>




