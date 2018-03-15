<template>
  <div class="g-container scoreEntry">
    <header class="g-header">
      <div class="g-liOneRow">
        <div class="g-textHeader g-flexStartRow selfSpace">
          <el-button class="g-gobackChart g-imgContainer RedButton" @click="goBackChart">
            <img src="../../../assets/img/schManagementSystem/teachingAdministration/arrangeClasses/icon_return.png" />
            返回上一步
          </el-button>
          <h2 class="selfCenter">成绩录入</h2>
        </div>
        <el-button class="radiusButton" @click="saveClick" type="primary">保存</el-button>
      </div>
      <div class="g-upload-section g-flexStartRow">
        <!--<span style="margin-top:.26rem">-->
        <!--<span>设置最大分数值:</span>-->
        <!--<span>{{maxPoint}}</span>-->
        <!--</span>-->
        <el-form style="margin-left: 2rem;width: 100%" ref="studentImportForm" label-position="left" :rules="importFormRules" :model="dataHeader" label-width="120px">
          <el-form-item label="设置最大分数值:" prop="maxPoint">
            <div v-text="maxPoint"></div>
          </el-form-item>
          <el-form-item label="文件路径:" prop="fileName">
            <div class="fileName" v-text="dataHeader.fileName"></div>
          </el-form-item>
          <div class="button_row">
            <button class="fileButtonShow headerButton">
              <img src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_choice.png" />
              选择文件
            </button>
            <input type="file" @change="chooseFile" class="chooseFile" title="选择文件" />
          </div>
          <div class="button_row">
            <button type="button" @click="exportClick" class="headerButton">
              <img src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_download.png" />
              下载模版
            </button>
          </div>
          <div class="button_row">
            <button @click="uploadClick" class="headerButton">
              <img src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_upload.png" />
              上传
            </button>
          </div>
        </el-form>
      </div>
    </header>
    <section class="g-section">
      <div class="gs-header g-liOneRow">
        <div class="gs-button alertsBtn">
          <el-button-group>
            <el-button  @click="operationData('copy')" data-msg="copy" class="filt buttonChild" title="复制">
              <img class="filt_unactive"  src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png" />
              <img class="filt_active" src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png" />
            </el-button>
            <el-button  @click="operationData('print')" data-msg="print" class="filt buttonChild" title="打印">
              <img class="filt_unactive"  src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png" />
              <img class="filt_active" src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png" />
            </el-button>
          </el-button-group>
        </div>
        <!--        <div class="gs-refresh g-fuzzyInput">
                  <el-input type="text" v-model="fuzzyInput" suffix-icon="el-icon-search" @change="fuzzyClick"></el-input>
                </div>-->
      </div>
      <div class="gs-table centerTable alertsList">
        <el-table
          v-loading.body="isLoading"
          element-loading-text="拼命加载中..."
          class="g-NotHover" ref="studentMsgTable" :data="headerButtonData.studentBasicMsg" style="width:100%" @sort-change="sortChange" @selection-change="handleStudentTable">
          <!--show-overflow-tooltip 超出部分省略号显示-->
          <el-table-column label="序号" type="index" width="80"></el-table-column>
          <el-table-column label="年级" prop="grade"></el-table-column>
          <el-table-column label="姓名" prop="name"></el-table-column>
          <el-table-column label="考号" prop="regNumber"></el-table-column>
          <el-table-column label="全卷(分)">
            <template slot-scope="props">
              <input @change="changeScore(props.$index)" type="text" class="tableInput" v-model="props.row.subScore" />
            </template>
          </el-table-column>
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
</template>
<script>
  import {fileTypeCheck} from '@/assets/js/common'
  import req from '@/assets/js/common'
  import {
    organizeResultsImportScore,//操作
    organizeResultsImportUpload,//上传
  } from '@/api/http'
  export default{
    data(){
      return{
        isLoading:false,
        /*ajax data*/
        headerButtonData:{
          gradeloadData:[],
          classesLoadData:[],
          msgTypeLoadData:[],
          studentBasicMsg:[],
        },
        /*table*/
        /*form表单双向绑定数据*/
        dataHeader:{
          /*显示div中文件信息值*/
          fileName:'',
        },
        /*type='file'input框的值*/
        fileInputValue:'',
        /*fuzzyFilter*/
        fuzzyInput:'',
        /*footer*/
        pageAll:1,
        currentPage:1,
        pageCount:10,//每页数据条数
        /*表单验证规则*/
        importFormRules:{
          fileName:[
            {required:true,message:'请选择文件!'}
          ],
        },
        /*返回的所有数据*/
        tableImportData:[],
        /*send ajax params*/
        gradeId:'',
        examId:'',
        subId:'',
        maxPoint:'',
        tableScore:{},
        bodyloading:false
      }
    },
    computed: {},
    methods:{
      operationData(type){
        let sAy = [], hdData = {
          grade: '年级',
          name: '姓名',
          regNumber: '考号',
          subScore: '全卷（分）',
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
          req.copyTableData('.scoreEntry', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      /*返回*/
      goBackChart(){
        this.$router.push({name:'importExam',params:this.gradeId,examId:this.examId});
      },
      /*table*/
      handleStudentTable(section){
        /*section为选择项行信息组成的数组*/
      },
      sortChange(column){
        /*table排序回调*/
      },
      /*修改分数*/
      changeScore(index){
//        let newKey=this.headerButtonData.studentBasicMsg[index].userId;
//        let newValue=this.headerButtonData.studentBasicMsg[index].subScore;
//        let oldKeyArr=Object.keys(this.tableScore);
//        oldKeyArr.forEach(val=>{
//          this.tableScore[val]=newValue
//        })
//        if(oldKeyArr.length>0){

//          outer:
//            for(let i=0;i<oldKeyArr.length;i++){
//              if(oldKeyArr[i]==newKey){
//                this.tableScore[oldKeyArr[i]]=newValue;
//                break outer;
//              }
//              else{
//                if(i==(oldKeyArr.length-1)){
//                  /*最后一项*/
//                  this.tableScore[newKey]=newValue;
//                }
//                else{
//                  continue
//                }
//              }
//            }
//        }
//        else{
//          this.tableScore[newKey]=newValue;
//        }
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
      /*header的button群*/
      /*模糊查询*/
      fuzzyClick(){
        /*点击search按钮*/
      },
      /*选择文件change事件*/
      chooseFile(event){
        /*this.fileName=this.extractFilename($(event.currentTarget).val());*/
        if(!fileTypeCheck(this.extractFilename($(event.currentTarget).val()),['.xls','.xlsx'])){
          this.$alert('文件不符合,请选择excel文件(*.xls或*.xlsx)!','提示',{
            confirmButtonText:'确定',
            type:'error',
            callback: action => {
            }
          });
        }else{
          this.dataHeader.fileName=this.extractFilename($(event.currentTarget).val());
          this.fileInputValue=$(event.currentTarget).val();
        }
      },
      extractFilename(path){
        let x;
        x = path.lastIndexOf('\\');
        if (x >= 0) // 基于Windows的路径
          return path.substr(x+1);
        x = path.lastIndexOf('/');
        if (x >= 0) // 基于Unix的路径
          return path.substr(x+1);
        return path; // 仅包含文件名
      },
      /*send ajax*/
      getLoadAjax(){
        this.isLoading=true;
        organizeResultsImportScore({gradeId:this.gradeId,type:'record',subId:this.subId}).then(data=>{
          if(data.status){
            this.tableImportData=data.data;
            this.pageAll=data.data.length;
            this.currentPage=1;
            this.changePageMsg();
          }
          else{
            this.headerButtonData.studentBasicMsg=[];
            this.pageAll=1;
            this.currentPage=1;
          }
          this.isLoading=false;
        })
      },
      /*修改分数*/
      saveClick(){
        let rows = this.headerButtonData.studentBasicMsg;
        if(rows.some(val=>{
            return Number(val.subScore)>Number(this.maxPoint);
          })){
          this.vmMsgWarning('分数超出范围');
          return;
        }
        let subScore = {};
        rows.forEach(val=>{
          subScore[val.userId] = val.subScore;
        });
        organizeResultsImportScore({gradeId:this.gradeId,type:'save',subId:this.subId,subScore}).then(data=>{
          this.getLoadAjax();
          this.tableScore={};
          if(data.status){
            this.vmMsgSuccess('保存成功！');
          }
          else{
            this.vmMsgError('保存失败！');
          }
        });
      },
      /*下载模版*/
      exportClick(){
        req.downloadFile('.g-container','/school/StudentIni/scoreManage?type=download&gradeId='+this.gradeId,'post');
      },
      /*上传*/
      uploadClick(){
        this.$refs['studentImportForm'].validate(valid=>{
          if(valid){
            let formData=new FormData();
            formData.append('import',$('.chooseFile')[0].files[0]);
            formData.append('type','import');
            formData.append('subId',this.subId);
            formData.append('gradeId',this.gradeId);
            this.bodyloading=true;
            req.ajaxFile('school/StudentIni/scoreManage','post',formData,(data)=>{
              if(data.status){
                this.tableImportData=data.data;
                this.pageAll=data.data.length;
                this.currentPage=1;
                this.changePageMsg();
                this.vmMsgSuccess('导入成功！');
                this.$refs['studentImportForm'].resetFields();
              }
              else{
                this.vmMsgError('导入失败，请重试！');
                this.headerButtonData.studentBasicMsg=[];
                this.pageAll=1;
                this.currentPage=1;
              }
            })
          }
        })
//        this.$refs['studentImportForm'].validate(valid=>{
//          if(valid){
//            let formData=new FormData();
//            formData.append('import',$('.chooseFile')[0].files[0]);
//            formData.append('type','import');
//            formData.append('subId',this.subId);
//            formData.append('gradeId',this.gradeId);
//            organizeResultsImportUpload(formData).then(data=>{
//              if(data.status){
//                this.tableImportData=data.data;
//                this.pageAll=data.data.length;
//                this.currentPage=1;
//                this.changePageMsg();
//                this.$message({
//                  message:'导入成功！',
//                  type:'success'
//                });
//                this.$refs['studentImportForm'].resetFields();
//              }
//              else{
//                this.$message.error('导入失败，请重试！');
//                this.headerButtonData.studentBasicMsg=[];
//                this.pageAll=1;
//                this.currentPage=1;
//              }
//            });
//          }
//        });
      }
    },
    created(){
      this.gradeId=this.$route.params.gradeId;
      this.examId=this.$route.params.examId;
      this.subId=this.$route.params.subId;
      this.maxPoint=this.$route.params.maxPoint;
      this.getLoadAjax();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../style/style';
  .g-container{
    .g-textHeader{
      h2{.marginLeft(40,1582);}
    }
    .g-prompt{margin-left:20/16rem;padding-top:11/16rem;}
  }
</style>


