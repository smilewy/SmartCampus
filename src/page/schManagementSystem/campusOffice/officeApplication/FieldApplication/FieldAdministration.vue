<template>
  <div class="FieldAdministration" v-loading.body="isLoading"
       element-loading-text="拼命上传中...">
    <h3>场地管理</h3>
    <el-row>
      <el-form :inline="true" class="demo-form-inline LeaveRecord-title">
        <el-form-item label="文件路径:">
          <el-input v-model="Filename" readonly="readonly"></el-input>
        </el-form-item>
        <el-form-item>
          <div class="uploadFile">
            <el-button type="primary" style="border-radius:.3rem" class="FieldAdmin-titlebtn">
              <img src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_choice.png"
                   alt="">
              <span>选择文件</span>
            </el-button>
            <input type="file" accept=".xlsx,.xlsm,.xls.doc,.docx,.ppt" class="file_input2" @change="sendFile">
          </div>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" class="FieldAdmin-titlebtn" @click="download()">
            <img src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_download.png"
                 alt="">
            <span>下载模板</span>
          </el-button>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" class="FieldAdmin-titlebtn1"
                     @click="uploadFile">
            <i class="el-icon-upload el-icon--right"></i>  上传</el-button>
        </el-form-item>
      </el-form>
    </el-row>
    <el-row>
      <el-col :span="17" class="alertsBtn" style="margin-top:1.8rem;">
        <el-button type="primary" style="padding:0.5rem 0.9rem;" @click="addField()">
          <i class="el-icon-plus"></i>
          <span>添加场地</span>
        </el-button>
      </el-col>
      <el-col :span="5" :offset="2" class="Infor-input-inner" style="padding:1.8rem 0 1rem 0;">
        <el-input style="border-radius:1rem" placeholder="请输入搜索内容" suffix-icon="el-icon-search" v-model="param.key" @change="handleIconClick"></el-input>
      </el-col>
    </el-row>
    <el-col :span="24">
      <el-row class="alertsList">
        <el-table
          v-loading.body="isLoading"
          element-loading-text="拼命上传中..."
          :data="tableData"
          style="width: 100%">
          <el-table-column
            prop="buildingNumber"
            label="栋号"
            align="center">
          </el-table-column>
          <el-table-column
            prop="buildingName"
            label="栋名"
            align="center">
          </el-table-column>
          <el-table-column
            prop="floor"
            label="层"
            align="center">
          </el-table-column>
          <el-table-column
            prop="room"
            label="号"
            align="center">
          </el-table-column>
          <el-table-column
            prop="name"
            label="场地名称"
            align="center">
          </el-table-column>
          <el-table-column
            label="是否开放"
            align="center">
            <template slot-scope="scope">
              <el-switch
                @change="changeStatus(scope.row)"
                v-model="scope.row.status"
                active-text="是"
                inactive-text="否"
                inactive-color="#A0A0A0"
                active-value="1"
                inactive-value="0">
                <!--on-value="1"-->
                <!--off-value="0"-->
              </el-switch>
            </template>
          </el-table-column>
          <el-table-column
            label="操作"
            align="center">
            <template slot-scope="scope">
              <span style="color:#4da1ff;cursor: pointer;border-right:1px solid #d2d2d2;padding-right: .6rem" @click="EditField(scope.row)">编辑</span>
              <span style="color:#ff6a6a;cursor: pointer;padding-left: .6rem" @click="DelectField(scope.row)">删除</span>
            </template>
          </el-table-column>
        </el-table>
      </el-row>
      <el-dialog title="添加场地" v-if="dialogTableVisibleother" :modal="false" :visible.sync="dialogTableVisibleother">
        <el-row>
          <el-col style="min-height: 24rem;">
            <el-col :span="22" :offset="1">
              <el-col :span="2" style="padding-top: .4rem;">栋号：</el-col>
              <el-col :span="8">
                <el-input placeholder="请输入" v-model="params.buildingNumber"></el-input>
              </el-col>
              <el-col :span="2" style="padding-top: .4rem;" :offset="3">栋名：</el-col>
              <el-col :span="8">
                <el-input placeholder="请输入" v-model="params.buildingName"></el-input>
              </el-col>
            </el-col>
            <el-col :span="22" :offset="1" style="padding-top: 2rem;">
              <el-col :span="2" style="padding-top: .4rem;">层：</el-col>
              <el-col :span="8">
                <el-input placeholder="请输入" v-model="params.floor"></el-input>
              </el-col>
              <el-col :span="2" style="padding-top: .4rem;" :offset="3">号：</el-col>
              <el-col :span="8">
                <el-input placeholder="请输入" v-model="params.room"></el-input>
              </el-col>
            </el-col>
            <el-col :span="22" :offset="1" style="padding-top: 2rem;">
              <el-col :span="4"  style="padding-top: .4rem;"><span style="color: red">*</span> 场地名称：</el-col>
              <el-col :span="19">
                <el-input placeholder="请输入" v-model="params.name"></el-input>
              </el-col>
            </el-col>
            <!--<el-col :span="24" style="padding-top: 2rem;">-->
            <!--<el-col :span="5" style="padding-top: .4rem;">是否开放：</el-col>-->
            <!--<el-col :span="19">-->
            <!--<el-select style="width: 100%" v-model="params.status" placeholder="请选择">-->
            <!--<el-option-->
            <!--v-for="item in switchoptions"-->
            <!--:key="item.value"-->
            <!--:label="item.label"-->
            <!--:value="item.value">-->
            <!--</el-option>-->
            <!--</el-select>-->
            <!--</el-col>-->
            <!--</el-col>-->
            <el-col :span="2" :offset="11">
              <el-button type="primary" class="Field-save" @click="SaveField()">保存</el-button>
            </el-col>
          </el-col>
        </el-row>
      </el-dialog>
      <el-dialog title="编辑场地" v-if="dialogTableVisible" :modal="false" :visible.sync="dialogTableVisible">
        <el-row>
          <el-col style="min-height: 24rem;">
            <el-col :span="22" :offset="1">
              <el-col :span="2" style="padding-top: .4rem;">栋号：</el-col>
              <el-col :span="8">
                <el-input placeholder="请输入" v-model="dialogData.buildingNumber"></el-input>
              </el-col>
              <el-col :span="2" style="padding-top: .4rem;" :offset="3">栋名：</el-col>
              <el-col :span="8">
                <el-input placeholder="请输入" v-model="dialogData.buildingName"></el-input>
              </el-col>
            </el-col>
            <el-col :span="22" :offset="1" style="padding-top: 2rem;">
              <el-col :span="2" style="padding-top: .4rem;">层：</el-col>
              <el-col :span="8">
                <el-input placeholder="请输入" v-model="dialogData.floor"></el-input>
              </el-col>
              <el-col :span="2" style="padding-top: .4rem;" :offset="3">号：</el-col>
              <el-col :span="8">
                <el-input placeholder="请输入" v-model="dialogData.room"></el-input>
              </el-col>
            </el-col>
            <el-col :span="22" :offset="1" style="padding-top: 2rem;">
              <el-col :span="4"  style="padding-top: .4rem;"><span style="color: red">*</span> 场地名称：</el-col>
              <el-col :span="19">
                <el-input placeholder="请输入" v-model="dialogData.name"></el-input>
              </el-col>
            </el-col>
            <el-col :span="24" >
              <span v-show="false">{{dialogData.id}}</span>
            </el-col>
            <el-col :span="22" :offset="1" style="padding-top: 2rem;">
              <el-col :span="4" style="padding-top: .4rem;">是否开放：</el-col>
              <el-col :span="19">
                <el-select style="width: 100%" v-model="dialogData.status" placeholder="请选择">
                  <el-option
                    v-for="item in switchoptions"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value">
                  </el-option>
                </el-select>
              </el-col>
            </el-col>
            <el-col :span="2" :offset="11">
              <el-button type="primary" class="Field-save" @click="SaveEditField()">保存</el-button>
            </el-col>
          </el-col>
        </el-row>
      </el-dialog>
      <el-row class="pageAlerts">
        <el-pagination
          @current-change="handleCurrentChange"
          :current-page.sync="currentPage"
          :page-size="10"
          layout="prev, pager, next, jumper"
          :total="total">
        </el-pagination>
      </el-row>
    </el-col>
  </div>
</template>
<script>
  import req from './../../../../../assets/js/common'
  import formatdata from './../../../../../assets/js/date'
  export default{
    data(){
      return{
        switchoptions: [{
          value: '0',
          label: '不开放'
        }, {
          value: '1',
          label: '开放'
        }],
        dialogTableVisible: false,
        dialogTableVisibleother:false,
        isLoading:false,
        tableData: [],
        Filename:'',
        fileList: [],
        dialogData:[],
        params:{
          type: 'create',
          buildingName: '',
          buildingNumber:'',
          floor: '',
          room: '',
          name: '',
          status: '1',
        },
        param:{
          key:''
        },
        currentPage: 1,
        pageALl: 10,
        total:0,
      }
    },
    created(){
      this.getRecord(1);
    },
    methods:{
      uploadFile(){
        if(!this.Filename){
          this.vmMsgWarning('请先选择文件');
          return;
        }
        let sendFormData=new FormData();
        for (let i of this.fileList){
          sendFormData.append('import',i.file);
        }
        sendFormData.append('type','import');
        this.$confirm('是否确定上传该模板?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          this.isLoading=true;
          req.ajaxFile('/school/WorkDemand/placeManage','post',sendFormData,(res)=>{
            if(res.status===-1){
              sendFormData.append('name',res.name);
              this.$confirm('场地名称重复，将用新记录覆盖旧记录', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
              }).then(() => {
                this.isLoading=true;
                req.ajaxFile('/school/WorkDemand/uploadCache','post',sendFormData,(res)=>{
                  if(res.status===1){
                    this.vmMsgSuccess(res.msg);
                  }else {
                    this.vmMsgError(res.msg);
                  }
                  this.getRecord(1);
                  this.isLoading=false;
                });
              }).catch(() => {

              });
            }
            this.getRecord(1);
            this.isLoading=false;
          });
        }).catch(() => {

        });
      },
      sendFile(){
        let suffix;
        for (let i = 0, file; file = $('.file_input2').prop('files')[i]; i++) {
          suffix = file.name.split('.')[1];
          switch (suffix) {
            case 'xlsx':
            case 'xls':
              break;
            default:
              this.vmMsgWarning('只能上传xlsx、xls格式文件！');
              return false;
          }
          this.fileList.push({ 'name': file.name, 'file': file});
          this.Filename=file.name
        }
      },
      download(){
        req.downloadFile('.FieldAdministration','/school/WorkDemand/placeManage?type=download','post');
      },
      EditField(row){
        this.dialogData=JSON.parse(JSON.stringify(row));
        this.dialogTableVisible=true;
      },
      addField(){
        this.dialogTableVisibleother=true;
      },
      changeStatus(row){
        this.$confirm('是否确定更改场地信息?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          let editData={
            type: 'edit',
            status:row.status,
            id:row.id,
            buildingName:row.buildingName,
            buildingNumber:row.buildingNumber,
            floor:row.floor,
            room:row.room,
            name:row.name,
          };
          req.ajaxSend('/school/WorkDemand/placeManage','post',editData,(res)=>{
            if(res.status===1){
              this.vmMsgSuccess(res.msg);
            }else {
              this.vmMsgError(res.msg);
            }
            this.getRecord(1);
          });
        }).catch(() => {
          this.getRecord(1);
        });
      },
      SaveEditField(){
        this.$confirm('是否确定修改此场地信息?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          let editData={
            type: 'edit',
            buildingName: this.dialogData.buildingName,
            buildingNumber:this.dialogData.buildingNumber,
            floor: this.dialogData.floor,
            room: this.dialogData.room,
            name:this.dialogData.name,
            status: this.dialogData.status,
            id:this.dialogData.id
          };
          req.ajaxSend('/school/WorkDemand/placeManage','post',editData,(res)=>{
            if(res.status===1){
              this.dialogTableVisible=false;
              this.vmMsgSuccess(res.msg);
              this.getRecord(1);
            }else {
              this.vmMsgError(res.msg);
            }
          });
        }).catch(() => {});
      },
      SaveField(){
        if(!this.params.name){
          this.vmMsgWarning('请输入场地名称');
          return;
        }
        if(this.tableData.some(val=>val.name===this.params.name)){
          this.vmMsgWarning('场地名称重复');
          return;
        }
        this.$confirm('是否确定新建该场地?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/WorkDemand/placeManage','post',this.params,(res)=>{
            if(res.status===1){
              this.dialogTableVisibleother=false;
              this.vmMsgSuccess(res.msg);
              this.getRecord(1);
              this.tableData.push(res.data)
            }else {
              this.vmMsgError(res.msg);
            }
          });
        }).catch(() => {

        });
      },
      DelectField(row){
        let that=this;
        let param={
          type:'del',
          id:row.id
        };
        that.$confirm('是否确定删除该场地?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/WorkDemand/placeManage','post',param,function (res){
            that.getRecord(1);
            if(res.status===1){
              that.vmMsgSuccess(res.msg);
            }else {
              that.vmMsgError(res.msg);
            }
          });
        }).catch(() => {

        });
      },
      handleIconClick(){
        req.ajaxSend('/school/WorkDemand/placeManage','post',this.param,(res)=>{
          if(res.status===-1){
            this.tableData=[];
            return;
          }
          this.tableData=res.data
        });
      },
      getRecord(page){
        if(page!==this.currentPage){
          this.currentPage=page;
        }
        this.isLoading=true;
        let param={
          page: page,
          count:10,
        };
        req.ajaxSend('/school/WorkDemand/placeManage','post',param,(res)=>{
//            console.log(res)
          if (res.status === -1) {
            this.tableData = [];
            this.isLoading = false;
            return;
          }
          this.tableData =res.data;
          this.total = res.total;
          this.isLoading=false;
        });
      },
      handleCurrentChange(val) {
        this.currentPage = val;
        this.getRecord(val);
      },
    }
  }
</script>
<style lang="less" scoped>
  .uploadFile {
    display: inline-block;
    position: relative;
    .file_input2 {
      width: 100%;
      height: 36px;
      position: absolute;
      right: 0;
      top: 0;
      z-index: 1;
      -moz-opacity: 0;
      -ms-opacity: 0;
      -webkit-opacity: 0;
      opacity: 0; /*css属性——opcity不透明度，取值0-1*/
      filter: alpha(opacity=0); /*兼容IE8及以下--filter属性是IE特有的，它还有很多其它滤镜效果，而filter: alpha(opacity=0); 兼容IE8及以下的IE浏览器(如果你的电脑IE是8以下的版本，使用某些效果是可能会有一个允许ActiveX的提示,注意点一下就ok啦)*/
      cursor: pointer;
    }
  }
  .FieldAdministration .el-button.FieldAdmin-titlebtn {
    background-color: #13b5b1;
    border-color: #13b5b1;
    height: 36px;
    padding: 0 .9rem;
  }
  .FieldAdministration .el-button.FieldAdmin-titlebtn1{
    height: 36px;
    padding: 0 2rem;
  }
  .Field-save{
    position: absolute;
    bottom: 2rem;
    padding: .5rem 2.1rem;
    border-radius: 1.1rem;
  }
  .FieldAdministration{
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }
  .Doc-dia-top{
    margin-top: 1.3rem;
  }
  .LeaveRecord-title{
    margin-top: 2rem;
    border-bottom: 1px solid #d2d2d2;
  }
  .LeaveRecord-search{
    background: #4da1ff;
    width: 7rem;
    margin-left: 1.6rem;
  }
  .LeaveRecord-table-div{
    width: 100%;
    height: 2.625rem;
    border-top: 1px solid #d2d2d2;
    text-align: center;
    line-height: 2.625rem;
    box-sizing:border-box;
  }
  .LeaveRecord-table-div-final{
    border-bottom: 1px solid #d2d2d2;
  }
  .LeaveRecord-table-div-1{
    border-right: 1px solid #d2d2d2;
  }
  .LeaveRecord-dialog-title{
    display: inline-block;
    margin: auto;
    font-weight: bold;
    font-size: 16px;
    padding-bottom: 1.2rem;
  }
</style>
<style>
  .FieldAdministration .Infor-input-inner .el-input__inner{
    border-radius: 1.2rem;
  }
</style>
