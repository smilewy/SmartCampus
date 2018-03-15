<template>
  <div class="g-statisticalAnalysis g-container repairpending">
    <header class="g-textHeader g-flexStartRow">
      <el-form :model="repairForm" label-width="85px" label-position="left">
        <el-form-item label="报修类别:" required>
          <el-select v-model="repairForm.id">
            <el-option v-for="(content,index) in repairTypeData" :key="index" :value="content.id" :label="content.repairType"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="时间段:">
          <el-col :span="10">
            <el-date-picker type="date" :picker-options="pickerOptionStart" placeholder="选择日期" v-model="repairForm.startTime" style="width:100%"></el-date-picker>
          </el-col>
          <el-col :span="2" class="line" style="text-align:center;">--</el-col>
          <el-col :span="10">
            <el-date-picker type="date" :picker-options="pickerOptionEnd" placeholder="选择日期" v-model="repairForm.endTime" style="width:100%"></el-date-picker>
          </el-col>
        </el-form-item>
      </el-form>
      <el-button class="radiusButton selfCenter" @click="searchTableClick" type="primary" icon="el-icon-search">查询</el-button>
    </header>
    <section class="centerTable alertsList">
      <div class="g-liOneRow g-sa_header_search">
        <div class="gs-button alertsBtn">
          <el-button-group>
            <el-button data-msg="export" @click="exportClick" class="filt buttonChild" title="导出">
              <img class="filt_unactive"  src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png" />
              <img class="filt_active" src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png" />
            </el-button>
            <el-button @click="addClick" data-msg="add" class="filt buttonChild" title="新增">
              <img class="filt_unactive"  src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_add.png" />
              <img class="filt_active" src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_add_highlight.png" />
            </el-button>
            <el-button @click="deleteClick" data-msg="add" class="filt buttonChild" title="删除">
              <img class="filt_unactive"  src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_delete.png" />
              <img class="filt_active" src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_delete_highlight.png" />
            </el-button>
          </el-button-group>
          <el-button-group class="elGroupButton_two">
            <el-button data-msg="copy" class="filt buttonChild" title="复制" @click="operationData('copy')">
              <img class="filt_unactive"  src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png" />
              <img class="filt_active" src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png" />
            </el-button>
            <el-button  data-msg="print" class="filt buttonChild" title="打印预览" @click="operationData('print')">
              <img class="filt_unactive"  src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png" />
              <img class="filt_active" src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png" />
            </el-button>
          </el-button-group>
        </div>
        <div class="gs-refresh g-fuzzyInput">
          <el-input type="text" v-model="fuzzyInput" suffix-icon="el-icon-search" placeholder="请输入" @change="searchTableClick"></el-input>
        </div>
      </div>
      <el-table class="g-NotHover"
                v-loading="loadingPending"
                element-loading-text="拼命加载中"
                element-loading-spinner="el-icon-loading"
                :data="classesTimeSetTable"
                :max-height="550"
                @sort-change="sortChange"
                @selection-change="handleSelectionChange">
        <el-table-column type="selection"></el-table-column>
        <el-table-column label="序号" type="index" width="100"></el-table-column>
        <el-table-column label="报修单号" min-width="150" sortable="custom">
          <template slot-scope="props">
            <el-button @click="detailShowClick(props.$index)" type="text" v-text="props.row.repairNumber"></el-button>
          </template>
        </el-table-column>
        <el-table-column label="报修物品" min-width="150" prop="repairName" sortable="custom"></el-table-column>
        <el-table-column label="报修内容" min-width="150" prop="repairContent" sortable="custom"></el-table-column>
        <el-table-column label="联系方式" min-width="150" prop="phone" sortable="custom"></el-table-column>
        <el-table-column label="报修类别" min-width="150" prop="repairType" sortable="custom"></el-table-column>
        <el-table-column label="报修地址" min-width="150" prop="repairAddress" sortable="custom"></el-table-column>
        <el-table-column label="接单人" min-width="150" prop="repairUserName" sortable="custom"></el-table-column>
        <el-table-column label="申请时间" min-width="150" prop="applyTime" sortable="custom"></el-table-column>
        <el-table-column label="接单时间" min-width="150" prop="reciveTime" sortable="custom"></el-table-column>
        <el-table-column label="到场时间" min-width="150" prop="arriveTime" sortable="custom"></el-table-column>
        <el-table-column label="维修状态" min-width="150" prop="repairState" sortable="custom">
          <template slot-scope="prop">
            <span v-if="prop.row.repairState==0">待维修</span>
            <span v-else>已接单</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="120">
          <template slot-scope="prop">
            <el-button :disabled="Boolean(Number(prop.row.repairState))" type="text" @click="changeClick(prop.$index)">编辑</el-button>
            <el-button :disabled="Boolean(Number(prop.row.repairState))" @click="destoryClick(prop.row.id)" class="deleteColor" type="text">撤销</el-button>
          </template>
        </el-table-column>
      </el-table>
    </section>
    <el-dialog :modal="false" :title="dialogTitle" :visible.sync="isDialog" :before-close="handlerClose">
      <el-form :model="handlerForm" ref="dialogForm" :rules="rules" label-width="90px" label-position="right">
        <div class="g-liOneWrapRow">
          <el-form-item label="报修类别:" prop="repairTypeId">
            <el-select v-model="handlerForm.repairTypeId">
              <el-option v-for="(content,index) in repairDialogTypeData" :key="index" :value="content.id" :label="content.repairType"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="维修地点:" prop="repairAddress">
            <el-input v-model="handlerForm.repairAddress"></el-input>
          </el-form-item>
          <el-form-item label="报修物品:" prop="repairName">
            <el-input v-model="handlerForm.repairName"></el-input>
          </el-form-item>
          <el-form-item label="联系方式:" prop="phone">
            <el-input v-model="handlerForm.phone"></el-input>
          </el-form-item>
        </div>
        <el-form-item label="资产编号:" required prop="assetsNumber">
          <el-input v-model="handlerForm.assetsNumber" placeholder="与资产管理模块生成的编号一致"></el-input>
        </el-form-item>
        <el-form-item label="报修内容:" prop="repairContent">
          <el-input type="textarea" v-model="handlerForm.repairContent"></el-input>
        </el-form-item>
        <el-form-item label="上传图片:">
          <div class="g-detailRow g-repairPerson">
            <ul class="g-flexStartWrapRow">
              <li v-for="url in handlerForm.logo">
                <img :src="url" alt="" />
                <p class="del-font" @click="delPicture(url)">删除</p>
              </li>
            </ul>
            <div class="button_row">
              <button type="button" class="fileButtonShow headerButton">
                <!--<img src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_choice.png" />-->
                选择上传图片
              </button>
              <input type="file" @change="chooseFile" class="chooseFile" title="选择上传图片" />
            </div>
          </div>
        </el-form-item>
      </el-form>
      <div class="g-button">
        <el-button @click="submitAddDialog" type="primary">提交</el-button>
        <el-button @click="cancelDialogClick">取消</el-button>
      </div>
    </el-dialog>
    <el-dialog class="overflowDialog headerNotBackground" :modal="false" title="维修详情" :visible.sync="isDetailDialog" :before-close="handlerDetailClose">
      <div class="g-repairDetail">
        <div class="g-liOneWrapRow">
          <div class="g-detailRow">
            <span class="g-detailLable">报修单号:</span>
            <span v-text="detailForm.repairNumber"></span>
          </div>
          <div class="g-detailRow">
            <span class="g-detailLable">维修地点:</span>
            <span v-text="detailForm.repairAddress"></span>
          </div>
          <div class="g-detailRow">
            <span class="g-detailLable">报修物品:</span>
            <span v-text="detailForm.repairName"></span>
          </div>
          <div class="g-detailRow">
            <span class="g-detailLable">联系方式:</span>
            <span v-text="detailForm.phone"></span>
          </div>
          <div class="g-detailRow">
            <span class="g-detailLable">资产编号:</span>
            <span v-text="detailForm.assetsNumber"></span>
          </div>
          <div class="g-detailRow">
            <span class="g-detailLable">报修类别:</span>
            <span v-text="detailForm.repairType"></span>
          </div>
        </div>
        <div class="g-detailRow">
          <span class="g-detailLable">报修内容:</span>
          <span v-text="detailForm.repairContent"></span>
        </div>
        <div class="g-detailRow g-repairPerson g-liOneRow">
          <span class="g-detailLable">图片:</span>
          <ul class="g-flexStartWrapRow">
            <li v-for="url in detailForm.logo">
              <img :src="url" alt="" />
            </li>
          </ul>
        </div>
      </div>
      <div class="g-repairPerson g-liOneWrapRow">
        <div class="g-detailRow">
          <span class="g-detailLable">接单人:</span>
          <span v-text="detailForm.repairUserName"></span>
        </div>
        <div class="g-detailRow">
          <span class="g-detailLable">申请时间:</span>
          <span v-text="detailForm.applyTime"></span>
        </div>
        <div class="g-detailRow">
          <span class="g-detailLable">维修状态:</span>
          <span v-if="detailForm.repairState==0">未接单</span>
          <span v-if="detailForm.repairState==1">维修中</span>
          <span v-if="detailForm.repairState==2">已维修</span>
          <span v-if="detailForm.repairState==3">已验收</span>
        </div>
        <div class="g-detailRow">
          <span class="g-detailLable">接单时间:</span>
          <span v-text="detailForm.reciveTime"></span>
        </div>
        <div class="g-detailRow">
          <span class="g-detailLable">到场时间:</span>
          <span v-text="detailForm.arriveTime"></span>
        </div>
      </div>
    </el-dialog>
  </div>
</template>
<script>
  import {
    repaymentLoad,//加载
    repaymentType,//报修类别
    repaymentDialogType,//弹框报修类别
    repaymentCreate,//添加与修改
    repaymentDelete,//删除
    repaymentUploadImg,//上传图片
    repaymentBack,//撤销
    repaymentPicDelete // 删除上传图片
  } from '@/api/http'
  import {fileTypeCheck} from '@/assets/js/common'
  import req from '@/assets/js/common'
  import moment from 'moment'
  export default{
    data(){
      var reg=/^[0-9-()（）]{7,18}$/;
      var checkPhone=(rule, value, callback) => {
        if (!reg.test(value)) {
          return callback(new Error('请输入正确的电话号码格式！'));
        } else {
          callback();
        }
      };
      let _self=this;
      return{
        /*模糊查询*/
        fuzzyInput:'',
        evaluationName:'',
        /*form表单*/
        repairForm:{
          id:'',
          startTime:'',
          endTime:''
        },
        repairTypeData:[],
        /*table*/
        classesTimeSetTable:[],
        pickerOptionStart:{
          disabledDate(time){
            if(_self.repairForm.endTime){
              return time.getTime()>Date.parse(_self.repairForm.endTime);
            }
          }
        },
        pickerOptionEnd:{
          disabledDate(time){
            if(_self.repairForm.startTime){
              return time.getTime()<Date.parse(_self.repairForm.startTime);
            }
          }
        },
        /*弹框——添加与编辑*/
        isDialog:false,
        dialogTitle:'新增报修单',
        handlerForm:{
          repairTypeId:'',
          repairAddress:'',
          repairName:'',
          phone:'',
          repairContent:'',
          logo:[],
          id:'',
          assetsNumber:'',
        },
        imgUpload:[],
        rules:{
          repairTypeId:[{required:true,message:'请选择报修类别'}],
          repairAddress:[{required:true,message:'请输入维修地点'}],
          repairName:[{required:true,message:'请输入报修物品'}],
          phone:[{validator: checkPhone,required:true}],
          repairContent:[{required:true,message:'请输入保修内容'}],
          assetsNumber:[{required:true,message:'请输入资产编号'}],
        },
        /*报修类别*/
        repairDialogTypeData:[],
        /*弹框——维修详情*/
        isDetailDialog:false,
        detailForm:{},//维修详情form
        /*send ajax param*/
        orderParam:{
          sortData:'',
          sort:'',//排序字段
        },
        multipleData:[],
        loadingPending: false
      }
    },
    methods:{
      operationData(type){
        if(!this.classesTimeSetTable || this.classesTimeSetTable.length <= 0) {
          this.vmMsgWarning( '暂无数据！' ); return;
        }
        let sAy = [], hdData = {
          repairNumber:'报修单号',
          repairName: '报修物品',
          repairContent: '报修内容',
          phone: '联系方式',
          repairType: '报修类别',
          repairAddress: '报修地址',
          repairUserName: '接单人',
          applyTime: '申请时间',
          reciveTime: '接单时间',
          arriveTime: '到场时间',
          repairState: '维修状态',
        };
        sAy.push(hdData);
        for (let obj of this.classesTimeSetTable) {
          let d = {};
          for (let name in hdData) {
            if( name == 'repairState' ){
              d[name] = obj[name] == 0 ? '待维修' : '已接单';
            } else {
              d[name] = obj[name] || '';
            }
          }
          sAy.push(d)
        }
        if (type == 'copy') {
          req.copyTableData('.repairpending', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      /*点击返回流程图按钮*/
      goBackChart(){
        this.$router.push({name:'evaluationManagement'});
      },
      /*查询*/
      searchTableClick(){
        if(this.repairForm.id){
          this.getLoadAjax();
        }
        else{
          this.vmMsgWarning( '请选择报修类别！' );
        }
      },
      /*table*/
      handleSelectionChange(choose){
        this.multipleData=choose;
      },
      sortChange(column){
        this.orderParam.sort=column.prop;
        this.orderParam.sortData=column.order;
      },
      changeClick(index){
        this.isDialog=true;
        Object.keys(this.handlerForm).forEach((row,rowI)=>{
          this.handlerForm[row]=this.classesTimeSetTable[index][row];
        });
        this.dialogTitle='编辑报修单';
        this.sendDialogType();
      },
      addClick(){
        this.isDialog=true;
        this.dialogTitle='新增报修单';
        /*清空*/
        this.handlerForm={
          repairTypeId:'',
          repairAddress:'',
          repairName:'',
          phone:'',
          repairContent:'',
          logo:[],
          id:'',
          assetsNumber:'',
        };
        this.sendDialogType();
      },
      /*模糊查询*/
      fuzzyClick(){
        /*模糊查询执行回调*/
      },
      /*弹框——增加与编辑*/
      handlerClose(done){
        this.$refs['dialogForm'].resetFields();
        done();
      },
      cancelDialogClick(){
        this.isDialog=false;
        this.$refs['dialogForm'].resetFields();
      },
      /*选择文件change事件*/
      chooseFile(event){
        /*this.fileName=this.extractFilename($(event.currentTarget).val());*/
        if(!fileTypeCheck(this.extractFilename($(event.currentTarget).val()),['.png','.jpeg','.jpg'])){
          this.$alert('上传图片只能是 JPG或PNG 格式!','提示',{
            confirmButtonText:'确定',
            type:'warning',
            callback: action => {}
          });
        }else{
          this.uploadImgAjax();//上传图片
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
      /*图片上传*/
      handleAvatarSuccess(res, file) {
        if(res.statu){
          this.vmMsgSuccess( '上传成功！' );
        }
        else{
          this.vmMsgError( res.message );
          return false;
        }
      },
      beforeAvatarUpload(file) {
        const isJPG = file.type === 'image/jpeg' || file.type ==='image/png';
        const isLt2M = file.size / 1024 / 1024 < 2;

        if (!isJPG) {
          this.vmMsgWarning( '上传图片只能是 JPG或PNG 格式!' );
        }
        if (!isLt2M) {
          this.vmMsgWarning( '上传头像图片大小不能超过 2MB!' );
        }
        return isJPG && isLt2M;
      },
      handlerAvatarRemove(file,fileList){
      },
      /*弹框——维修详情*/
      handlerDetailClose(done){
        done();
      },
      /*显示详情点击*/
      detailShowClick(index){
        this.isDetailDialog=true;
        this.detailForm=this.classesTimeSetTable[index];
      },
      /*send ajax*/
      /*得到报修类别*/
      getRepairTypeAjax(){
        repaymentType().then(data=>{
          if(data.statu){
            this.repairTypeData=data.data;
            /*默认第一条*/
            if(this.repairTypeData.length>0){
              this.repairForm.id = this.repairTypeData[0].id;
              this.getLoadAjax();
            }
          }
          else{
            this.vmMsgError( '报修类别数据加载失败，请重试！' );
            this.repairTypeData=[];
          }
        });
      },
      /*得到弹框报修类别*/
      sendDialogType(){
        repaymentDialogType().then(data=>{
          if(data.statu){
            this.repairDialogTypeData=data.data;
          }
          else{
            this.vmMsgError( '报修类别数据加载失败，请重试！' );
            this.repairDialogTypeData=[];
          }
        });
      },
      getLoadAjax(){
        this.loadingPending = true;
        if(this.repairForm.startTime){
          this.repairForm.startTime=moment(this.repairForm.startTime).format('YYYY-MM-DD');
        }
        if(this.repairForm.endTime){
          this.repairForm.endTime=moment(this.repairForm.endTime).format('YYYY-MM-DD');
        }
        repaymentLoad({repairType:'list',...this.repairForm,state:1,...this.orderParam,valueData:this.fuzzyInput}).then(data=>{
          this.loadingPending = false;
          if(data.statu){
            this.classesTimeSetTable=data.data;
          }
          else{
            this.classesTimeSetTable=[];
            this.vmMsgError( '数据加载失败，请重试！' );
          }
        });
      },
      exportClick(){
        if(this.repairForm.id){
          let urlPrompt='&repairType=export&state=1&id='+this.repairForm.id;
          req.downloadFile('.g-container','/school/Eqrepair/myRepair?type=getRepairList'+urlPrompt,'post');
        }
        else{
          this.vmMsgWarning( '请选择报修类别！' );
        }
      },
      /*删除*/
      deleteClick(){
        if(this.multipleData.length>0){
          this.$confirm('确定删除所选数据？','提示',{
            confirmButtonText:'确定',
            type:'warning'
          }).then(()=>{
            let idArr=[];
            this.multipleData.forEach((value,index)=>{
              idArr.push(value.id);
            });
            repaymentDelete({id:idArr}).then(data=>{
              this.vmMsgSuccess( '删除成功！' );
              idArr.forEach( str => {
                let temp = this.classesTimeSetTable.find( obj => obj.id == str );
                this.classesTimeSetTable.splice(this.classesTimeSetTable.indexOf(temp), 1);
              });
            });
          }).catch(()=>{});
        }
        else{
          this.vmMsgWarning( '请选择需要删除的信息！' );
        }
      },
      /*撤销*/
      destoryClick(id){
        this.$confirm("确认撤销该条数据吗？", "提示", {
          confirmButtonText:'确定',
          type:'warning'
        }).then( () => {
          repaymentBack({id:id}).then(data=>{
            this.vmMsgSuccess( '撤销成功！' );
          });
        }).catch( () => {});
      },
      /*上传图片*/
      uploadImgAjax(){
        let formData=new FormData();
        formData.append('userFile',$('.chooseFile')[0].files[0]);
        repaymentUploadImg(formData).then(data=>{
          if(data.statu){
            this.vmMsgSuccess( '上传成功！' );
            this.handlerForm.logo.push(data.url);
          }
          else{
            this.vmMsgError( data.message );
          }
        });
      },

      /**@author dubo
       * @description 新增报修单-删除已上传图片
       * @date 2018-01-12
       */
      delPicture( url ){
        this.$confirm("确认删除该图片吗？", "提示", {
          confirmButtonText:'确定',
          type:'warning'
        }).then( () => {
          let logo = this.handlerForm.logo;
          let index = logo.indexOf( url );

          repaymentPicDelete({url: url}).then( data => {
            if( !data.statu ){
              this.vmMsgError( data.message );
            }else{
              logo.splice( index, 1 );
            }
          });
        }).catch( () => {});
      },

      /*编辑和新增弹框确定*/
      submitAddDialog(){
        this.$refs['dialogForm'].validate((valid) => {
          if(valid){
            repaymentCreate(this.handlerForm).then(data => {
              if( data.statu == 1 ){
                this.isDialog = false;
                this.getLoadAjax();
                this.$refs['dialogForm'].resetFields();
                if(this.dialogTitle == '新增报修单' ){
                  this.vmMsgSuccess( '添加成功！' );
                } else {
                  this.vmMsgSuccess( '修改成功！' );
                }
              } else {
                this.vmMsgError( data.message );
              }
            });
          }
        });
      },
    },
    created(){
      this.getRepairTypeAjax();
    }
  }
</script>
<style lang="less" scoped>
  /*@import '../../../../../style/test';*/
  @import '../../../../../style/style';
  @import '../../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.css';
  @import '../../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.less';
  .g-sa_header_search{.marginTop(32);.marginBottom(20);}
  .g-classSchedule .g-container{padding:0;width:100%;}
  .g-statisticalAnalysis header.g-textHeader.g-flexStartRow{.marginTop(20);.marginBottom(20);border-bottom:1px solid @borderColor;}
  .g-textHeader .el-form{.width(825,1582);}
  .g-textHeader .el-form-item{float:left;margin-bottom:0;}
  .g-textHeader .el-form-item:nth-of-type(1){.width(222,825);}
  .g-textHeader .el-form-item:nth-of-type(2){.width(558,825);.marginLeft(40,825);}
  .g-liOneRow.g-sa_header_search{margin-top:0;}
  /*添加与编辑弹框*/
  .g-liOneWrapRow .el-form-item{.widthRem(385);}
  .g-liOneWrapRow .el-select{width:100%;}
  /*维修详情弹框*/
  .g-repairDetail{border-bottom:2/16rem solid @borderColor;.marginBottom(20);}
  .g-liOneWrapRow .g-detailRow{.widthRem(370);}
  .g-detailRow{width:100%;.marginBottom(36);
    .g-detailLable{display:inline-block;.widthRem(80);text-align:right;margin-right:10/16rem;}
    span{.fontSize(14);color:@normalColor;}
  }
  .g-repairPerson{
    ul{width:100%;}
    li{width:7.5rem;height:7.5rem;margin-right:10/16rem;.marginBottom(10);
      img{width:100%; height: 5rem;}
    }
  }

  /*header button*/
  .button_row{position:relative;}
  button.headerButton{.widthRem(120);height:36px;line-height:36px;border:none;.border-radius(4px);text-align:center;background:#13b5b1;color:#fff;
    &:focus{outline:none;}
    &:hover{cursor:pointer;}
  }
  .fileButtonShow{position:relative;z-index:1;}
  .chooseFile{z-index:10;opacity:0;position:absolute;left:0;top:0;.widthRem(120);height:36px;.box-sizing();
    &:hover{cursor:pointer;}
  }

  .del-font{
    font-size: 12px;
    text-align: center;
    color: #a29d9d;
    cursor: pointer;
  }
</style>


