<template>
  <div class="g-container studentStatistics">
    <header class="g-header">
      <div class="gh-header">学生统计</div>
      <div class="gh-section g-formNoMarB g-flexStartRow">
        <el-form ref="studentMessge" label-position="left" :model="dataHeader" label-width="50px">
          <el-form-item label="年级:" prop="gradeIdValue">
            <el-select class="g-select" v-model="dataHeader.gradeIdValue" placeholder="请选择年级">
              <el-option v-for="(content,index) in headerButtonData.gradeloadData" :value="content.gradeid" :label="gradeData[content.name-1]" :key="index"></el-option>
            </el-select>
          </el-form-item>
        </el-form>
        <el-button type="primary" class="g-buttonSearch el-icon-search" @click="searchClick">查询</el-button>
      </div>
<!--      <div class="gh-buttonGroup clear_fix">
        <el-button icon="reset" @click="resetClick">重置</el-button>

      </div>-->
    </header>
    <section class="g-section">
      <div class="gs-header">
        <div class="gs-button alertsBtn">
          <el-button-group>
            <el-button @click="buttonClick" data-msg="export" class="filt" title="导出">
              <img class="filt_unactive" src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png" />
              <img class="filt_active" src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png" />
            </el-button>
          </el-button-group>
          <el-button-group class="elGroupButton_two">
            <el-button data-msg="copy" class="filt" title="复制" @click="operationData('copy')">
              <img class="filt_unactive" src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png" />
              <img class="filt_active" src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png" />
            </el-button>
            <el-button data-msg="print" class="filt" title="打印" @click="operationData('print')">
              <img class="filt_unactive" src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png" />
              <img class="filt_active" src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png" />
            </el-button>
          </el-button-group>
        </div>
        <div class="gs-refresh g-fuzzyInput">
          <el-input type="text" v-model="fuzzyInput" placeholder="请输入班级" suffix-icon="el-icon-search" @change="fuzzyClick"></el-input>
        </div>
      </div>
      <div class="gs-table alertsList">
        <el-table ref="studentMsgTable" :data="headerButtonData.studentBasicMsg" style="width:100%" @sort-change="sortChange"
                  @selection-change="handleStudentTable"
                  v-loading="loading"
                  element-loading-text="拼命加载中">
          <!--show-overflow-tooltip 超出部分省略号显示-->
          <el-table-column label="班级" prop="className" sortable="custom"></el-table-column>
          <el-table-column label="在班" prop="count" sortable="custom"></el-table-column>
          <el-table-column label="借读" prop="isTempStudy" sortable="custom"></el-table-column>
          <el-table-column label="休学" prop="isLeave" sortable="custom"></el-table-column>
          <el-table-column label="挂靠" prop="isSubor" sortable="custom"></el-table-column>
          <el-table-column label="男生在校" prop="man" sortable="custom"></el-table-column>
          <el-table-column label="女生在校" prop="woman" sortable="custom"></el-table-column>
        </el-table>
      </div>
    </section>
    <footer class="g-footer">
      <el-row class="pageAlerts">
        <el-pagination
          @current-change="handleCurrentChange"
          :current-page.sync="currentPage"
          layout="prev, pager, next, jumper"
          :page-count="pageALl"
          >
        </el-pagination>
      </el-row>
    </footer>
  </div>
</template>
<script>
  import {
    studentStatusticsGrade,//得到年级接口
    studentStatusticsMsg,//得到统计页面加载数据
    studentStatusticsFilter,//模糊查询
  } from '@/api/http'
  import req from '@/assets/js/common'
  export default{
    data(){
      return{
        /*ajax data*/
        headerButtonData:{
          gradeloadData:[],//得到年级返回数据
          classesLoadData:[],
          msgTypeLoadData:[],
          studentBasicMsg:[],//table数据返回
        },
        /*form表单双向绑定数据*/
        dataHeader:{
          /*select*/
          gradeIdValue:'',
        },
        /*table*/
        isFilter: false,
        sortList: {   //排序按钮
          serialNum:{
            filterText:''
          },
          classes:{
            filterText:''
          },
          seatNum:{
            filterText:''
          },
          name: {
            filterText: ''
          },
          sex: {
            filterText: ''
          },
          tel: {
            filterText: ''
          },
          section:{
            filterText:''
          }
        },
        /*发送请求排序参数*/
        orderBy:'',//排序的字段
        sort:'',//排序方式
        /*年级转换*/
        gradeData:['一年级','二年级','三年级','四年级','五年级','六年级','初一','初二',
          '初三','高一','高二','高三'
        ],
        /*fuzzyFilter*/
        fuzzyInput:'',
        /*footer*/
        pageALl:1,
        currentPage:1,
        loading:false
      }
    },
    computed: {},
    methods:{
      /*footer*/
      handleCurrentChange(val) {
        this.currentPage = val;
        this.sendLoadAjax();
      },
      /*table*/
      handleStudentTable(section){
        /*section为选择项行信息组成的数组*/
      },
      sortChange(column){
        /*table排序回调*/
        this.sort=column.order;
        this.orderBy=column.prop;
        this.sendLoadAjax();
      },
      /*mine*/
      buttonClick(event){
        const e=$(event.currentTarget),targetMsg=e.data('msg');
        if(targetMsg=='export'){
          this.exportAjax()
        }
      },
      operationData(type) {
        let sAy = [], hdData;
        hdData = {
          className: '班级',
          count: '在读',
          isTempStudy: '借读',
          isLeave: '休学',
          isSubor:'挂靠',
          man:'男生在校',
          woman:'女生在校'
        };
        sAy.push(hdData);
        for (let obj of this.headerButtonData.studentBasicMsg) {
          let d = {};
          for (let name in hdData) {
              if(name=='className'){
                d[name] = obj[name] || '';
              }else{
                d[name] = obj[name];
              }
          }
          sAy.push(d)
        }
        if (type == 'copy') {
          req.copyTableData('.studentStatistics', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      /*模糊查询*/
      fuzzyClick(){
        /*点击search按钮*/
        if(this.dataHeader.gradeIdValue){
          this.filterAjax();
        }else{
          this.vmMsgWarning('请选择年级!');
          return false;
        }
      },
      /*重置点击事件*/
      resetClick(){
        this.$refs['studentMessge'].resetFields();
        this.headerButtonData.studentBasicMsg=[];
      },
      /*查询点击事件*/
      searchClick(){
        if(this.dataHeader.gradeIdValue){
          this.sendLoadAjax();
        }else{
          this.vmMsgWarning('请选择年级!');
        }
      },
      /*send ajax------------*/
      /*得到年级*/
      getGradeAjax(){
        studentStatusticsGrade().then((data)=>{
          if(data.length>0){
            this.headerButtonData.gradeloadData=data;
          }
          else{
            this.vmMsgWarning('无数据!');
          }
        });
      },
      sendLoadAjax(){
        this.loading=true;
        studentStatusticsMsg({grade:this.dataHeader.gradeIdValue,orderBy:this.orderBy,sort:this.sort,page:this.currentPage}).then((data)=>{
          this.loading=false;
          this.currentPage=Number(data.page);
          this.pageALl=Number(data.maxpage);
          this.headerButtonData.studentBasicMsg=data.data;
        });
      },
      filterAjax(){
        this.loading=true;
        studentStatusticsFilter({grade:this.dataHeader.gradeIdValue,orderBy:this.orderBy,sort:this.sort,page:this.currentPage,value:this.fuzzyInput}).then((data)=>{
          this.loading=false;
          this.currentPage=Number(data.page);
          this.pageALl=Number(data.maxpage);
          this.headerButtonData.studentBasicMsg=data.data;
        });
      },
      exportAjax(){
        if(this.dataHeader.gradeIdValue){
          req.downloadFile('.g-container','/school/user/userGl?type=studentStatisticsExport&grade='+this.dataHeader.gradeIdValue+'&page='+this.currentPage,'post');
        }else{
          this.vmMsgWarning('请选择年级!');
          return false;
        }
      },
    },
    created(){
      this.getGradeAjax();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../../style/style';
  @import '../../../../../style/userManager/student/studentMessage.less';
  @import '../../../../../style/userManager/student/studentManager.css';
  .g-buttonSearch{margin-left:20/16rem;}
</style>


