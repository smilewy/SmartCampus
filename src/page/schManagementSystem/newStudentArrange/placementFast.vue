<template>
  <div class="g-container placementFast">
    <header class="g-importCourseHeader">
      <div class="g-liOneRow">
        <div class="g-textHeader g-flexStartRow">
          <el-button class="g-gobackChart g-imgContainer RedButton" @click="goBackChart">
            <img src="../../../assets/img/schManagementSystem/teachingAdministration/arrangeClasses/icon_return.png" />
            返回流程图
          </el-button>
          <h2 class="selfCenter" style="width:9rem;">快速分班</h2>
        </div>
        <el-button class="radiusButton" @click="saveClick" type="primary">保存</el-button>
      </div>
      <div class="g-flexStartRow g-selectPadding">
        <el-form class="moreFormItem" :model="createPlacementForm" label-width="82px" label-position="left">
          <el-form-item label="分班方式:" required>
            <el-select @change="classWayChange" v-model="createPlacementForm.way">
              <el-option label="按成绩" value="score"></el-option>
              <el-option label="随机" value="random"></el-option>
              <el-option label="蛇形" value="snake"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="班级序号:" required>
            <el-select v-model="createPlacementForm.number">
              <el-option label="按成绩" value="score"></el-option>
              <el-option label="随机" value="random"></el-option>
              <el-option label="按名字拼音" value="name"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="性别:">
            <el-select v-model="createPlacementForm.sex" :disabled="sexSelectDis">
              <el-option label="平均分配" value="ave"></el-option>
              <el-option label="随机" value="random"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="成绩相同:" required>
            <el-select v-model="createPlacementForm.equal">
              <el-option label="增加班级人数" value="score"></el-option>
              <el-option label="随机" value="random"></el-option>
            </el-select>
          </el-form-item>
        </el-form>
        <el-button type="primary" @click="createClass" class="radiusButton selfTop">生成分班</el-button>
      </div>
      <div class="g-flexStartRow">
        <div class="selfCenter">成绩优先级(数字越小,优先级越高):</div>
        <div v-for="(content,index) in createPlacementForm.priority">
          <span v-text="content.level+'：'"></span>
          <el-input-number class="g-courseNum" :disabled="priorityDis" v-model="content.right" :min="0" :max="100"></el-input-number>
        </div>
      </div>
    </header>
    <div class="g-container g-containerNoPadding">
      <section class="g-section">
        <div class="gs-header g-liOneRow">
          <div class="gs-button alertsBtn">
            <el-button-group>
              <el-button @click="exportAjax" data-msg="export" class="filt buttonChild" title="导出">
                <img class="filt_unactive"  src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png" />
                <img class="filt_active" src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png" />
              </el-button>
            </el-button-group>
            <el-button-group class="elGroupButton_two">
              <el-button data-msg="copy" class="filt buttonChild" title="复制" @click="operationData('copy')">
                <img class="filt_unactive"  src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png" />
                <img class="filt_active" src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png" />
              </el-button>
              <el-button data-msg="print" class="filt buttonChild" title="打印" @click="operationData('print')">
                <img class="filt_unactive"  src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png" />
                <img class="filt_active" src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png" />
              </el-button>
            </el-button-group>
          </div>
          <!--          <div class="gs-refresh g-fuzzyInput">
                      <el-input type="text" v-model="fuzzyInput" suffix-icon="el-icon-search" @change="fuzzyClick"></el-input>
                    </div>-->
        </div>
        <div class="gs-table centerTable alertsList">
          <el-table
            v-loading.body="isLoading"
            element-loading-text="拼命加载中..."
            ref="studentMsgTable" :data="headerButtonData.studentBasicMsg" style="width:100%" @sort-change="sortChange" @selection-change="handleStudentTable">
            <!--show-overflow-tooltip 超出部分省略号显示-->
            <el-table-column label="序号" type="index" width="80px"></el-table-column>
            <el-table-column label="姓名" prop="name"></el-table-column>
            <el-table-column label="性别" prop="sex"></el-table-column>
            <el-table-column label="分班班级" prop="className"></el-table-column>
            <el-table-column label="分班序号" prop="serialNumber"></el-table-column>
          </el-table>
        </div>
      </section>
      <footer class="g-footer">
        <el-row class="pageAlerts">
          <el-pagination
            @current-change="handleCurrentChange"
            :current-page.sync="currentPage"
            :page-size="pageCount"
            layout="prev, pager, next, jumper"
            :total="pageAll">
          </el-pagination>
        </el-row>
      </footer>
    </div>
  </div>
</template>
<script>
  import {
    placementFastSet,//操作
    newStudentGetGrade,//得到班级级别
  } from '@/api/http'
  import req from '@/assets/js/common'
  export default{
    data(){
      return{
        isLoading:false,
        /*ajax data*/
        headerButtonData:{
          studentBasicMsg:[],
        },
        tableImportData:[],
        /*footer*/
        pageAll:1,
        currentPage:1,
        pageCount:10,//每页数据条数
//        priority:['','',''],
        /*fuzzyFilter*/
        fuzzyInput:'',
        /*生成分班*/
        createPlacementForm:{
          way:'',
          number:'',
          sex:'',
          equal:'score',
          /*班级级别等级*/
          priority:[]//平行班
        },
        /*条件限制*/
        priorityDis:true,
        sexSelectDis:true,
        /*send ajax param*/
        gradeId:'',
        stuLists:[],
      }
    },
    computed: {},
    methods:{
      operationData(type){
        let sAy = [], hdData = {
          name: '姓名',
          sex: '性别',
          className: '分班班级',
          serialNumber: '分班序号',
        };
        sAy.push(hdData);
        for (let obj of this.headerButtonData.studentBasicMsg) {
          let d = {};
          for (let name in hdData) {
            d[name] = obj[name] || '';
          }
          sAy.push(d);
        }
        if (type === 'copy') {
          req.copyTableData('.placementFast', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      /*点击返回流程图按钮*/
      goBackChart(){
        this.$router.push({name:'newStudentClass'});
      },
      /*footer*/
      handleCurrentChange(val) {
        this.currentPage = val;
        this.changePageMsg();
      },
      /*更改页面展示信息*/
      changePageMsg(){
        this.headerButtonData.studentBasicMsg=[];
        if(this.currentPage*this.pageCount>this.tableImportData.length){
          /*最后一页*/
          for(let i=((this.currentPage-1)*this.pageCount);i<this.tableImportData.length;i++){
            this.headerButtonData.studentBasicMsg.push(this.tableImportData[i]);
          }
        }
        else{
          for(let i=((this.currentPage-1)*this.pageCount);i<this.currentPage*this.pageCount;i++){
            this.headerButtonData.studentBasicMsg.push(this.tableImportData[i]);
          }
        }
      },
      /*分班方式值变化*/
      classWayChange(){
        if(this.createPlacementForm.way=='score'){
          this.priorityDis=false;
          this.sexSelectDis=false;
        }
        else if(this.createPlacementForm.way=='snake'){
          this.priorityDis=true;
          this.createPlacementForm.sex='ave';
          this.sexSelectDis=true;
        }
        else{
          this.priorityDis=true;
          this.sexSelectDis=false;
        }
      },
      /*生成分班*/
      createClass(){
        if(this.createPlacementForm.way && this.createPlacementForm.number && this.createPlacementForm.equal){
          this.createClassAjax();
        }
        else{
          this.vmMsgWarning('请完善相关信息！');
        }
      },
      /*table*/
      handleStudentTable(section){
        /*section为选择项行信息组成的数组*/
//        console.log(section);
      },
      sortChange(column){
        /*table排序回调*/
      },
      /*编辑*/
      changeClick(index){
        this.isDialog=true;
        this.dialogTitle='编辑信息';
      },
      /*header的button群*/
      /*模糊查询*/
      fuzzyClick(){
        /*点击search按钮*/
//        console.log('模糊查询');
      },
      /*保存*/
      saveClick(){
        this.stuLists=[];
        this.tableImportData.forEach((row,n)=>{
          this.stuLists.push({id:row.id,classId:row.classId,className:row.className,userId:row.userId,stuId:row.stuId,serialNumber:row.serialNumber});
        });
        if(this.stuLists.length>0){
          this.saveAjax();
        }
        else{
          this.vmMsgWarning('没有需要保存的数据！');
        }
      },
      /*弹框*/
      /*关闭按钮点击*/
      handlerClose(done){
        done();
      },
      /*send ajax*/
      getLoadAjax(){
        /*得到班级*/
        newStudentGetGrade({func:'classLevel',param:{gradeId:this.gradeId}}).then(data=>{
          this.createPlacementForm.priority=[];
          if(data.status){
            data.data.forEach((row,n)=>{
              this.createPlacementForm.priority.push({level:row.level,levelId:row.levelId,right:0});
            });
          }
          else{
            this.vmMsgError('数据加载失败');
          }
        });
      },
      createClassAjax(){
        this.isLoading=true;
        placementFastSet({gradeId:this.gradeId,type:'divide',...this.createPlacementForm}).then(data=>{
          if(data.status){
            this.tableImportData=data.data;
            this.pageAll=data.data.length;
            this.currentPage=1;
            this.changePageMsg();
          }
          else{
            this.headerButtonData.studentBasicMsg=[];
            this.tableImportData=[];
            this.stuLists=[];
            this.pageAll=1;
            this.currentPage=1;
          }
          this.isLoading=false;
        });
      },
      exportAjax(){
        req.downloadFile('.g-container','/school/StudentIni/quickBranch?type=download&gradeId='+this.gradeId,'post');
      },
      saveAjax(){
        placementFastSet({gradeId:this.gradeId,type:'save',stuLists:this.stuLists}).then(data=>{
//            this.getLoadAjax();
          this.stuLists=[];
          if(data.status){
            this.vmMsgSuccess('保存成功！');
          }
          else{
            this.vmMsgError('保存失败！');
          }
        });
      }
    },
    created(){
      this.gradeId=this.$route.params.gradeId;
      this.getLoadAjax();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../style/style';
  .g-importCourse .g-container .g-section{margin:1.25rem 0;width:100%;}
  .g-container.g-containerNoPadding{width:100%;}
  /*弹框*/
  .headerNotBackground{
    .dialogHeader{position:absolute;right:20px;top:0.625rem;
      button{.border-radius(1rem);}
    }
    .dialogSection{
      .NotAllWidth{width:auto;}
      .defineInputWidth{.widthRem(60);}
    }
  }
  .g-textHeader{
    h2{.marginLeft(40,1582);}
  }
  .g-flexStartRow{.fontSize(14);
    span{padding-left:30/16rem;.fontSize(14);}
  }
  .g-flexStartRow.g-selectPadding{padding:20/16rem 0 0;}
</style>








