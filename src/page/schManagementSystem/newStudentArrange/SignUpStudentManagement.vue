<template>
  <div class="g-container">
    <header>
      <div class="g-textHeader g-liOneRow">
        <h2>签约生管理</h2>
      </div>
      <div class="g-selection">
        <el-form ref="studentMessge" lable-position="left" :model="dataHeader" label-width="60px">
          <el-form-item label="年级：">
            <el-select @change="getTableData" v-model="dataHeader.gradeId" placeholder="请选择年级">
              <el-option v-for="(content,index) in gradeAjaxData" :key="index" :label="content.znName" :value="content.id"></el-option>
            </el-select>
          </el-form-item>
        </el-form>
      </div>
    </header>
    <section class="g-section">
      <div class="gs-header g-liOneRow">
        <div class="gs-button alertsBtn">
          <el-button-group>
            <el-button @click="addClick" data-msg="add" class="filt" title="添加">
              <img class="filt_unactive" src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_add.png" />
              <img class="filt_active" src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_add_highlight.png" />
            </el-button>
            <el-button @click="deleteClick" data-msg="delete" class="filt" title="删除">
              <img class="filt_unactive" src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_delete.png" />
              <img class="filt_active" src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_delete_highlight.png" />
            </el-button>
            <el-button @click="importNewStudent" data-msg="delete" class="filt" title="批量导入">
              <img class="filt_unactive" src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_leading-in_n.png" />
              <img class="filt_active" src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_leading-in_highlight.png" />
            </el-button>
          </el-button-group>
        </div>
        <div class="g-fuzzyInput selfCenter">
          <el-input placeholder="准考证号/姓名" v-model="fuzzyInput" suffix-icon="el-icon-search" @change="getTableData"></el-input>
        </div>
      </div>
      <div class="centerTable alertsList">
        <el-table
          v-loading.body="isLoading"
          element-loading-text="拼命加载中..."
          class="g-NotHover" ref="studentMsgTable" :data="headerButtonData.studentBasicMsg" style="width:100%" @sort-change="sortChange" @selection-change="handleStudentTable">
          <!--show-overflow-tooltip 超出部分省略号显示-->
          <el-table-column type="selection" width="55"></el-table-column>
          <el-table-column label="序号" type="index" width="110"></el-table-column>
          <el-table-column label="姓名" prop="name"></el-table-column>
          <el-table-column label="准考证号" prop="regNumber"></el-table-column>
          <el-table-column label="性别" prop="sex"></el-table-column>
          <el-table-column label="出生日期" prop="birthday"></el-table-column>
          <el-table-column label="中学学校" prop="secSchool"></el-table-column>
          <el-table-column label="联系方式" prop="phone"></el-table-column>
          <el-table-column label="签约承诺" prop="promise"></el-table-column>
          <el-table-column label="家庭住址" prop="homePath"></el-table-column>
          <el-table-column label="户口所在地" prop="perAddress"></el-table-column>
          <el-table-column label="邮政编码" prop="nowHomePostcode"></el-table-column>
          <el-table-column label="现住地址" prop="nowHomePath"></el-table-column>
          <el-table-column label="操作" fixed="right">
            <template slot-scope="props">
              <el-button type="text" @click="changeMsg(props.$index)">编辑</el-button>
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
          layout="prev, pager, next, jumper"
          :page-count="pageAll">
        </el-pagination>
      </el-row>
    </footer>
    <el-dialog class="headerNotBackground" title="新增年级" :modal="false" :visible.sync="isDialog" :before-close="handlerClose">
      <el-form :model="gradeDialogForm" label-width="140px" label-position="right">
        <el-form-item label="年级代码:">
          <el-input v-model="gradeDialogForm.gradeCode" placeholder="请输入年级代码,如:C2016"></el-input>
        </el-form-item>
        <el-form-item label="年级名称:">
          <el-input v-model="gradeDialogForm.gradeName" placeholder="请输入年级名称,如:高一"></el-input>
        </el-form-item>
        <el-form-item label="所有年级自动升级:">
          <el-switch v-model="gradeDialogForm.update" active-text="是" inactive-text="否" active-color="#13b5b1"></el-switch>
        </el-form-item>
        <el-form-item label="毕业最高年级">
          <el-switch v-model="gradeDialogForm.maxGrade" active-text="是" inactive-text="否" active-color="#13b5b1"></el-switch>
        </el-form-item>
      </el-form>
      <div class="g-prompt">
        提示:年级代码高中以<span>G</span>开头,初中以<span>C</span>开头,小学以<span>X</span>开头。
      </div>
      <div class="g-button">
        <el-button type="primary">保存</el-button>
        <el-button @click="cancelCreateGrade">取消</el-button>
      </div>
    </el-dialog>
  </div>
</template>
<script>
  import {
    newStudentGetGrade,//得到年级
    SignUpStudentManagementLoad,//操作
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
        /*年级*/
        gradeAjaxData:[],
        /*form表单双向绑定数据*/
        dataHeader:{
          gradeId:'',
        },
        /*删除所需参数*/
        deleteParams:[],
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
        /*fuzzyFilter*/
        fuzzyInput:'',
        /*footer*/
        pageAll:1,
        currentPage:1,
        pageCount:10,
        /*弹框*/
        isDialog:false,
        gradeDialogForm:{
          gradeCode:'',
          gradeName:'',
          update:'false',
          maxGrade:'false'
        },
      }
    },
    computed: {},
    methods:{
      /*footer*/
      handleCurrentChange(val) {
        this.currentPage = val;
        this.getTableData();
      },
      /*弹框*/
      handlerClose(done){
        done();
      },
      /*创建年级按钮*/
      createGrade(){
        this.isDialog=true;
      },
      cancelCreateGrade(){
        this.isDialog=false;
      },
      /*table*/
      handleStudentTable(section){
        /*section为选择项行信息组成的数组*/
        this.deleteParams=[];
        section.forEach((value)=>{
          this.deleteParams.push(value.userId);
        });
      },
      sortChange(column){
        /*table排序回调*/
      },
      /*编辑*/
      changeMsg(index){
        this.$router.push({name:'AddSignUpStudent',params:{id:1,gradeId:this.dataHeader.gradeId,userId:this.headerButtonData.studentBasicMsg[index]['userId']}});
      },
      /*添加*/
      addClick(){
        /*编辑1，添加0*/
        if(this.dataHeader.gradeId){
          this.$router.push({name:'AddSignUpStudent',params:{id:0,gradeId:this.dataHeader.gradeId,userId:0}});
        }
        else{
          this.vmMsgWarning('请选择需要添加的年级！');
        }
      },
      /*删除*/
      deleteClick(){
        if(this.deleteParams.length>0){
          this.sendDeleteAjax();
        }
        else{
          this.vmMsgWarning('请选择需要删除信息！');
        }
      },
      /*批量导入*/
      importNewStudent(){
        if(this.dataHeader.gradeId){
          this.$router.push({name:'SignUpStudentImport',params:{param:this.dataHeader.gradeId}});
        }
        else{
          this.vmMsgWarning('请选择需要导入数据年级！');
        }
      },
      /*模糊查询*/
      fuzzyClick(){
        /*点击search按钮*/
      },
      /*send ajax*/
      /*得到年级*/
      getLoadAjax(){
        newStudentGetGrade({func:'getGrade'}).then(data=>{
          this.gradeAjaxData=this.handlerAjaxData(data,'年级暂无数据！');
          if(sessionStorage.getItem('gradeId2')){
            this.dataHeader.gradeId = sessionStorage.getItem('gradeId2');
            sessionStorage.removeItem('gradeId2');
          }
        });
      },
      getTableData(){
        this.isLoading=true;
        sessionStorage.setItem('gradeId2',this.dataHeader.gradeId);
        SignUpStudentManagementLoad({gradeId:this.dataHeader.gradeId,page:this.currentPage,count:this.pageCount,key:this.fuzzyInput}).then(data=>{
          /*加载table框数据及模糊查询接口*/
          this.headerButtonData.studentBasicMsg=this.handlerAjaxData(data);
          if(data.status){
            this.pageAll=data.maxPage;
            this.headerButtonData.studentBasicMsg.forEach((value,i)=>{
              value.homePath=value.homePath1.join('')+value.homePath2;
              value.nowHomePath=value.nowHomePath1.join('')+value.nowHomePath2;
              value.perAddress=value.perAddress.join('');
            });
          }
          else{
            this.pageAll=1;
            this.headerButtonData.studentBasicMsg=[];
          }
          this.isLoading=false;
        });
      },
      /*删除数据*/
      sendDeleteAjax(){
        SignUpStudentManagementLoad({type:'del',gradeId:this.dataHeader.gradeId,ids:this.deleteParams}).then(data=>{
          if(data.status){
            this.vmMsgSuccess('删除成功！');
            this.getTableData();
          }
          else{
            this.vmMsgError('删除失败！');
          }
        });
      },
      /*处理数据*/
      handlerAjaxData(data,msg){
        if(data.status){
          return data.data;
        }
        else{
          (msg || data.message) && this.vmMsgError(msg);
        }
      },
    },
    created(){
      this.getLoadAjax();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../style/style';
</style>


