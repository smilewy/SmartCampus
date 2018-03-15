<template>
  <div class="g-container basesetting">
    <header>
      <div class="g-textHeader g-liOneRow">
        <h2>基础设置</h2>
      </div>
<!--      <div class="g-selection">
        <div class="g-switch">
          <span>是否允许非登录状态扫码报修:</span>
          <el-switch v-model="switchValue" active-text="是" inactive-text="否" active-color="" inactive-color=""></el-switch>
        </div>
      </div>-->
    </header>
    <section class="g-section">
      <div class="gs-header g-liOneRow">
        <div class="gs-button alertsBtn">
          <el-button-group>
            <el-button @click="addClick" data-msg="add" class="filt" title="添加">
              <img class="filt_unactive" src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_add.png" />
              <img class="filt_active" src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_add_highlight.png" />
            </el-button>
            <el-button @click="deleteClick" data-msg="delete" class="filt" title="删除">
              <img class="filt_unactive" src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_delete.png" />
              <img class="filt_active" src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_delete_highlight.png" />
            </el-button>
          </el-button-group>
          <el-button-group class="elGroupButton_two">
            <el-button @click="operationData('copy')" data-msg="copy" class="filt" title="复制">
              <img class="filt_unactive" src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png" />
              <img class="filt_active" src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png" />
            </el-button>
            <el-button @click="operationData('print')" data-msg="print" class="filt" title="打印">
              <img class="filt_unactive" src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png" />
              <img class="filt_active" src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png" />
            </el-button>
          </el-button-group>
        </div>
        <div class="g-fuzzyInput selfCenter">
          <el-input type="text" v-model="fuzzyInput" suffix-icon="el-icon-search" placeholder="请输入" @change="getLoadAjax"></el-input>
        </div>
      </div>
      <div class="centerTable alertsList">
        <el-table class="g-NotHover"
                  v-loading="loadingCategoryList"
                  element-loading-text="拼命加载中"
                  element-loading-spinner="el-icon-loading"
                  ref="studentMsgTable"
                  :data="headerButtonData.studentBasicMsg"
                  style="width:100%"
                  @sort-change="sortChange"
                  @selection-change="handleStudentTable">
          <!--show-overflow-tooltip 超出部分省略号显示-->
          <el-table-column type="selection" width="55"></el-table-column>
          <el-table-column label="序号" type="index" width="110"></el-table-column>
          <el-table-column label="报修类别" prop="repairType" sortable="custom"></el-table-column>
          <el-table-column label="维修人员" prop="user" sortable="custom"></el-table-column>
          <el-table-column label="操作" width="100" fixed="right">
            <template slot-scope="prop">
              <el-button type="text" @click="changeMsg(prop.$index)">编辑</el-button>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </section>
    <el-dialog class="headerNotBackground" title="新增报修类别" :modal="false" :visible.sync="isDialog" :before-close="handlerClose">
      <el-form :model="gradeDialogForm" label-width="75px" label-position="right">
        <el-form-item label="报修类别:">
          <el-input v-model="gradeDialogForm.type" placeholder="请输入报修类别"></el-input>
        </el-form-item>
      </el-form>
      <div class="g-button">
        <el-button type="primary" @click="confirmAddClick">确定</el-button>
        <el-button @click="isDialog=false">取消</el-button>
      </div>
    </el-dialog>
    <el-dialog class="headerNotBackground" title="分配人员" :modal="false" :visible.sync="isChangeDialog" :before-close="handlerClose">
      <section class="g-liOneRow">
        <div class="centerTable">
          <div class="g-defineInput">
            <span>报修类别:</span>
            <el-input v-model="handlerData.repairType"></el-input>
          </div>
          <el-table class="g-NotHover" :data="handlerData.user" :max-height="500">
            <el-table-column label="序号" type="index" width="100"></el-table-column>
            <el-table-column label="维修人员" prop="name"></el-table-column>
            <el-table-column label="操作">
              <template slot-scope="prop">
                <el-button type="text" @click="deletePersonClick(prop.$index)" class="deleteColor">删除</el-button>
              </template>
            </el-table-column>
          </el-table>
        </div>
        <div class="alertsList centerTable">
          <div class="g-fuzzyInput selfCenter">
            <el-input type="text" v-model="fuzzyDialogInput" suffix-icon="el-icon-search" @change="fuzzyDialogClick"></el-input>
          </div>
          <el-table class="g-NotHover g-trHover"
                    v-loading="loadingCandidate"
                    element-loading-text="拼命加载中"
                    element-loading-spinner="el-icon-loading"
                    @row-click="dialogRowClick"
                    :data="repairTeacher"
                    :max-height="500">
            <el-table-column label="序号" type="index" width="100"></el-table-column>
            <el-table-column label="维修人员" prop="name"></el-table-column>
            <el-table-column label="教职工类别" prop="post"></el-table-column>
          </el-table>
        </div>
      </section>
      <div class="g-button">
        <el-button type="primary" @click="saveSetPerson">保存</el-button>
      </div>
    </el-dialog>
  </div>
</template>
<script>
  import {
    basicSettingLoad,//table加载数据
    basicSettingCreate,//创建
    basicSettingDelete,//删除
    basicSettingUpload,//编辑
    basicSettingPerson,//得到待选职工，和管理的员工
    basicSettingPersonDelete,//删除待选职工
  } from '@/api/http'
  import req from '@/assets/js/common'
  export default{
    data(){
      return{
        /*ajax data*/
        headerButtonData:{
          gradeloadData:[],
          classesLoadData:[],
          msgTypeLoadData:[],
          studentBasicMsg:[],
        },
        /*switch值*/
        switchValue:'',
        /*form表单双向绑定数据*/
        dataHeader:{
          gradeId:'',
        },
        /*table*/
        isFilter: false,
        tableData: [
          {
            serialNum:'1',
            classes:'1',
            seatNum:'1',//座位号
            name: 'hhh',
            sex: '女',
            sexNum: 0,
            tel: '123565566',
            section:'文科'//科类
          }
        ],
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
        multipleData:[],
        /*fuzzyFilter*/
        fuzzyInput:'',
        /*footer*/
        pageALl:1,
        currentPage:1,
        /*弹框*/
        isDialog:false,
        gradeDialogForm:{
          type:''
        },
        /*编辑*/
        handlerData:{
          repairType:'',
          user:[],
          id:''
        },
        fuzzyDialogInput:'',//待选人员模糊查询
        repairTeacherTable:[],//待选人员table总数据
        repairTeacher:[],//待选人员table绑定在页面上的数据
        /*分配人员*/
        isChangeDialog:false,
        /*send ajax param*/
        orderParam:{
          sortData:'',
          sort:'',//排序字段
        },
        loadingCategoryList: false,
        loadingCandidate: false
      }
    },
    computed: {},
    methods:{
      operationData(type){
        if(!this.headerButtonData.studentBasicMsg || this.headerButtonData.studentBasicMsg.length <= 0) {
          this.vmMsgWarning( '暂无数据！' ); return;
        }
        let sAy = [], hdData = {
          repairType: '报修类别',
          user: '维修人员'
        };
        sAy.push(hdData);
        for (let obj of this.headerButtonData.studentBasicMsg) {
          let d = {};
          for (let name in hdData) {
              d[name] = obj[name] || '';
          }
          sAy.push(d)
        }
        if (type == 'copy') {
          req.copyTableData('.basesetting', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      /*footer*/
      handleCurrentChange(val) {
        this.currentPage = val;
      },
      /*弹框*/
      handlerClose(done){
        done();
      },
      /*选择维修人员，点击table*/
      dialogRowClick(row){
        if(this.handlerData.user.length>0){
          for(let i=0;i<this.handlerData.user.length;i++){
            if(this.handlerData.user[i].id==row.id){
              this.vmMsgWarning( '该人员已经在维修名单中！' );
              break
            }
            else{
              if(i==(this.handlerData.user.length-1)){
                /*最后一项*/
                this.handlerData.user.push({id:row.id,name:row.name});
                break
              }
            }
          }
        }
        else{
          this.handlerData.user.push({id:row.id,name:row.name});
        }
      },
      /*维修人员表中的删除*/
      deletePersonClick(index){
        this.$confirm("确认删除维修人员" + this.handlerData.user[index].name + "?", "提示", {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then( () => {
          this.handlerData.user.splice(index,1);
        }).catch( () => {});
      },
      /*table*/
      handleStudentTable(section){
        /*section为选择项行信息组成的数组*/
        this.multipleData=section;
      },
      sortChange(column){
        /*table排序回调*/
        this.orderParam.sort=column.prop;
        this.orderParam.sortData=column.order;
      },
      /*编辑*/
      changeMsg(index){
        this.isChangeDialog=true;
        this.handlerData.id=this.headerButtonData.studentBasicMsg[index].id;
        this.handlerData.repairType=this.headerButtonData.studentBasicMsg[index].repairType;
        this.getPersonAjax();
      },
      fuzzyDialogClick(){
        /*点击search按钮*/
        this.repairTeacher=[];
        if(this.fuzzyDialogInput){
          this.repairTeacherTable.forEach((row,rowI)=>{
            Object.keys(row).forEach((column,columnI)=>{
              if(column != 'id'){
                /*除了id的模糊查询*/
                if(row[column].search(this.fuzzyDialogInput)>=0){
                  /*查找到数据*/
                  this.repairTeacher.push(row);
                }
              }
            });
          });
        }
        else{
          this.repairTeacher=this.repairTeacherTable;
        }
      },
      /*添加报修类别*/
      addClick(){
        this.isDialog=true;
        this.gradeDialogForm.type='';
      },
      /*模糊查询*/
      fuzzyClick(){
        /*点击search按钮*/
      },
      /*send ajax*/
      getLoadAjax(){
        this.loadingCategoryList = true;
        basicSettingLoad({...this.orderParam,valueData:this.fuzzyInput}).then(data=>{
          this.loadingCategoryList = false;
          if(data.statu){
            this.headerButtonData.studentBasicMsg=data.data;
          }
          else{
            this.headerButtonData.studentBasicMsg=[];
            this.vmMsgError( '数据加载失败，请重试！' );
          }
        });
      },
      /*删除*/
      deleteClick(){
        if(this.multipleData.length>0){
          this.$confirm("确定要删除所选数据吗？", "提示", {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            let idArr=[];
            this.multipleData.forEach((value,index)=>{
              idArr.push(value.id);
            });
            /*请求*/
            basicSettingDelete({id:idArr}).then(data=>{
              this.vmMsgSuccess( '删除成功！' );
              this.getLoadAjax();
            })
          }).catch( () => {});
        }
        else{
          this.vmMsgWarning( '请选择需要删除的数据！' );
        }
      },
      /*得到维修人员待选信息*/
      getPersonAjax(){
        this.loadingCandidate = true;
        basicSettingPerson({id:this.handlerData.id}).then(data=>{
          this.loadingCandidate = false;
          if(data.statu){
            this.handlerData.user=data.data.user;
            this.repairTeacherTable=data.data.zgList;
          }
          else{
            this.vmMsgError( '维修人员表格与待选人员表格数据加载失败！' );
            this.handlerData.user=[];
            this.repairTeacherTable=[];
          }
          this.repairTeacher=this.repairTeacherTable;
        });
      },
      /*确定添加报修类别*/
      confirmAddClick(){
        if(this.gradeDialogForm.type){
          basicSettingCreate({repairType:this.gradeDialogForm.type}).then(data=>{
            this.isDialog=false;
            if(data.statu){
              this.vmMsgSuccess( '添加成功！' );
              this.getLoadAjax();
            }
            else{
              this.vmMsgError( data.message );
            }
          });
        }
        else{
          this.vmMsgWarning( '请输入报修类别！' );
        }
      },
      /*编辑保存*/
      saveSetPerson(){
          if(this.handlerData.user.length<=0){
            this.vmMsgWarning( '请选择维修人员！' );
            return false;
          }
        if(this.handlerData.repairType){
          this.isChangeDialog=false;
          basicSettingUpload(this.handlerData).then(data=>{
            this.vmMsgSuccess( '编辑成功！' );
            this.getLoadAjax();
          });
        }
        else{
          this.vmMsgWarning( '请输入报修类别！' );
        }
      },
    },
    created(){
      this.getLoadAjax();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/style';
  .dialogHeader{position:absolute;right:20px;top:0.625rem;
    button{.border-radius(1rem);}
  }
  .headerNotBackground{
    .centerTable{.widthRem(438);
      &>div{.marginBottom(20);}
      .g-defineInput .el-input{.widthRem(260);}
      .g-defineInput span{margin-right:20/16rem;}
    }
    .centerTable:not(:first-of-type){padding-left:30/16rem;}
  }
</style>





