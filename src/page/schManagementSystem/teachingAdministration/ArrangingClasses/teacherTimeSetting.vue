<template>
  <div class="g-teachertimeSetting">
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
          <h2>待选老师</h2>
          <el-input @input="fuzzyClick" v-model="fuzzyInput" class="fuzzyInput" placeholder="请输入教师姓名" suffix-icon="el-icon-search"></el-input>
        </header>
        <section class="gL-section">
          <el-tree
              v-loading="loadingTree"
              element-loading-text="拼命加载中"
              element-loading-spinner="el-icon-loading"
              :highlight-current="true"
              :data="treeData"
              :props="defaultProps"
              ref="allMsg"
              :filter-node-method="filterNode"
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
              <span v-if="props.row[n-1]==0" class="NotCourse">不上课</span>
              <span v-if="props.row[n-1]==6" class="NotCourse">已预排</span>
              <span v-if="props.row[n-1]==4" class="SetNotCourse">不排课</span>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </section>
  </div>
</template>
<script>
  import {mapState} from 'vuex'
  import {teacherTimeSettingTeacherLoad,//得到教师
    teacherTimeSettingTabelLoad,/*table默认数据*/
    teacherTimeSettingSaved,//保存时间限制
  } from '@/api/http'
  import {fuzzyQuery} from '@/assets/js/fuzzyQuery'
  export default{
    data(){
      return{
        pkListId: '',
        /*表格header文本*/
        headerText:'',
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
        weekData:['星期一','星期二','星期三','星期四','星期五','星期六','星期日'],
        /*教师总节数*/
        teacherCount:0,
        /*总节数*/
        allCount:0,
        /*可排节数*/
        remainCount:0,
        loadingTable: false,
        loadingTree: false
      }
    },
    methods:{
      /*点击返回流程图按钮*/
      goBackChart(){
        this.$router.push({name:'examinationChart'});
      },
      /*保存*/
      saveSetting(){
        teacherTimeSettingSaved({pkListId:this.pkListId,teacherId:this.techerId,teacherName:this.techerName,data:this.classesTimeSetTable}).then(data=>{
          if(data.statu==1){
            this.vmMsgSuccess('保存成功!');
            this.getTableData();
          }else{
            this.vmMsgError('保存失败!');
          }
        })
      },
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
          return;
        }else{
          this.techerId=data.techerId;
          this.techerName=data.techerName;
          this.headerText=data.techerName+'('+node.parent.data.techerName+')';
          this.getTableData();
        }
      },
      /*table框点击单元格事件*/
      tableCellClick(row,column,cell,event){
        const columnNum=this.weekData.indexOf(column.label);
        if(columnNum>=0){
          if(row[columnNum]==0){
            this.vmMsgWarning('已设置不上课，不能设置！');
          } else if(row[columnNum]==6){
            this.vmMsgWarning('该位置预排了当前科目教师上课！');
          } else {
            if(row[columnNum]==4){
              this.$set(row,columnNum,1);
              this.remainCount++;
            }else{
              if(this.remainCount>this.teacherCount){
                this.$set(row,columnNum,4);
                this.remainCount--;
              } else {
                this.vmMsgWarning('教师不排课超额！');
              }
            }
          }
        }
      },
      /*检测剩余可排节数*/
      checkRemainCount(){
        let count=0;
        this.classesTimeSetTable.forEach((row,rowI)=>{
          row.forEach((column,columnI)=>{
            if(column==4){
              count++;
            }
          });
        });
        return count;
      },
      /*send ajax*/
      /*得到教师ajax*/
      getTeacherAjax(){
        this.loadingTree = true;
        teacherTimeSettingTeacherLoad({pkListId:this.pkListId}).then(data=>{
          this.loadingTree = false;
          if (data.statu) {
            this.treeData = data.data;
          } else {
            this.vmMsgError('加载失败,请重新加载页面!');
          }
          /*headerText*/
          if(this.treeData.length>0){
            this.headerText='';
            this.techerId=this.treeData[0].data[0].techerId;
            this.techerName=this.treeData[0].data[0].techerName;
          }
        })
      },
      /*得到表格默认信息*/
      getTableData(){
        this.loadingTable = true;
        teacherTimeSettingTabelLoad({pkListId:this.pkListId,teacherId:this.techerId}).then(data=>{
          this.loadingTable = false;
          if (data.statu) {
            this.classesTimeSetTable = data.data.classSet;
            this.teacherCount=data.teacherCount;
            this.allCount=data.allCount;
            this.remainCount=Number(this.allCount)-Number(this.checkRemainCount());
          } else {
            this.vmMsgError('加载失败,请重新加载页面!');
          }
        })
      },
    },
    created(){
      this.pkListId = sessionStorage.pkListId;
      this.getTeacherAjax();
      this.fuzzySearchArr=this.fuzzySearchData;
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/arrangeClasses/teacherTimeSetting';
  @import '../../../../style/arrangeClasses/arrangeClasses.css';
</style>




