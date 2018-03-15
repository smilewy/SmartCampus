<template>
  <div class="DocumentRecord">
    <h3>公文流转记录</h3>
    <el-row class="LeaveRecord-title" type="flex" align="center">
      <el-form :inline="true" class="formInline">
        <el-form-item label="创建时间：">
          <el-date-picker
            v-model="startvalue" type="date" :picker-options="pickerOptions0" style="width: 100%">
          </el-date-picker>
        </el-form-item>
        <span>_</span>
        <el-form-item>
          <el-date-picker
            v-model="endvalue" type="date" :picker-options="pickerOptions1" style="width: 100%">
          </el-date-picker>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" icon="el-icon-search" class="LeaveRecord-search" @click="getRecord(1)">查询</el-button>
        </el-form-item>
      </el-form>
    </el-row>
    <el-row>
      <el-col :span="17" class="alertsBtn" style="margin-top: 0">
        <el-button-group style="margin-top:1.8rem">
          <el-button class="delete" title="导出" @click="downloadedFile">
            <img class="delete_unactive"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png"
                 alt="">
            <img class="delete_active"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png"
                 alt="">
          </el-button>
          <el-button class="filt" title="复制" @click="operationData('copy')">
            <img class="filt_unactive"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png"
                 alt="">
            <img class="filt_active"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png"
                 alt="">
          </el-button>
        </el-button-group>
      </el-col>
      <el-col :span="5" :offset="2" class="Infor-input-inner" style="margin-top:1.8rem;">
        <el-input style="border-radius:1rem" placeholder="请输入搜索内容" suffix-icon="el-icon-search" v-model="key" @change="handleIconClick"></el-input>
      </el-col>
    </el-row>
    <el-col :span="24">
      <el-row class="alertsList">
        <el-table
          :data="tableData"
          style="width: 100%"
          v-loading.body="isLoading"
          element-loading-text="拼命加载中...">
          <el-table-column
            prop="title"
            label="标题"
            align="center">
          </el-table-column>
          <el-table-column
            prop="name"
            label="类型"
            align="center">
          </el-table-column>
          <el-table-column
            prop="content"
            label="内容"
            align="center">
            <template slot-scope="scope">
              <span v-html="scope.row.content" class="moretext"></span>
            </template>
          </el-table-column>
          <el-table-column
            prop="createTime"
            label="创建日期"
            align="center"
          >
            <template slot-scope="scope">
              <span>{{scope.row.createTime}}</span>
            </template>
          </el-table-column>
          <el-table-column
            prop="status"
            label="审批状态"
            align="center">
            <template slot-scope="scope">
              <span v-if="scope.row.status==-1" style="color: #ff5b5b">未通过</span>
              <span v-if="scope.row.status==0">正在审批</span>
              <span v-if="scope.row.status==1">通过</span>
              <span style="color: #ff5b5b" v-if="scope.row.status==2">审批过期</span>
              <span v-if="scope.row.status==3">撤销</span>
              <span v-if="scope.row.status==4">转发</span>
              <span v-if="scope.row.status==9">未审批</span>
            </template>
          </el-table-column>
          <el-table-column
            prop="result"
            label="审批结果"
            align="center">
            <template slot-scope="scope">
              <span>{{scope.row.result | getResultState}}</span>
            </template>
          </el-table-column>
          <el-table-column
            prop="approver"
            label="审批人"
            align="center">
          </el-table-column>
          <el-table-column
            label="操作"
            align="center"
            width="200">
            <template slot-scope="scope">
              <span style="color:#4da1ff;cursor: pointer;border-right:1px solid #d2d2d2;padding-right:.6rem" v-if="scope.row.status==9" @click="showDialog(scope.row)">编辑</span>
              <span style="color:#48b6c4;cursor: pointer;padding:0 .6rem" @click="showDialogother(scope.row)">详情</span>
              <span style="color:#ff6a6a;cursor: pointer;border-left:1px solid #d2d2d2;padding-left: .6rem" v-if="scope.row.result=='1'||scope.row.result=='-1' " @click="Delectlist(scope.row)">删除</span>
            </template>
          </el-table-column>
        </el-table>
      </el-row>
      <el-dialog title="编辑信息" :modal="false" :visible.sync="dialogTableVisible">
        <el-row>
          <el-col :span="24" style="height:40rem;overflow-y: auto;">
            <el-col :span="24" class="Doc-dia-top">
              <el-col :span="2">标题：</el-col>
              <el-col :span="22">
                <el-input v-model="form.title"></el-input>
              </el-col>
            </el-col>
            <el-col :span="24" class="Doc-dia-top">
              <el-col :span="2">类型：</el-col>
              <el-col :span="22">
                <el-select style="width:100%" v-model="form.name">
                  <el-option
                    v-for="item in options"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value">
                  </el-option>
                </el-select>
              </el-col>
            </el-col>
            <el-col :span="24" class="Doc-dia-top">
              <el-col :span="2">内容：</el-col>
              <el-col :span="22" style="height: 20rem">
                <quill-editor style="height:80%" v-model="form.content"></quill-editor>
              </el-col>
            </el-col>
            <el-col :span="24" class="Doc-dia-top">
              <el-row>
                <el-col :span="5">
                  <el-col :span="7" style="margin-top: .3rem">附件：</el-col>
                  <el-col :span="16" style="border-right: 1px solid #d2d2d2">
                    <div class="uploadFile">
                      <el-button>选择附件</el-button>
                      <input type="file" accept=".xlsx,.xlsm,.xls.doc,.docx,.ppt" class="file_input" @change="sendFile">
                    </div>
                  </el-col>
                </el-col>
                <el-col :span="19" class="fileLists">
                  <div v-for="(file,index) in fileLists">
                    <img v-if="file.fileType==1||file.fileType.indexOf('ppt')>-1"
                         src="../../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_ppt.png"
                         alt="">
                    <img v-if="file.fileType==2||file.fileType.indexOf('doc')>-1"
                         src="../../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_word.png"
                         alt="">
                    <img v-if="file.fileType==3||file.fileType.indexOf('xls')>-1"
                         src="../../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_excel.png"
                         alt="">
                    <span :title="file.name">{{file.name}}</span>
                    <i class="el-icon-close" @click="deleteFile(index)"></i>
                  </div>
                </el-col>
              </el-row>
            </el-col>
            <el-col :span="2" :offset="11">
              <el-button type="primary" style="width:7rem;margin: 2rem 0;border-radius: 1.1rem;" :loading="save===true" @click="editDoc()">保存</el-button>
            </el-col>
          </el-col>
        </el-row>
      </el-dialog>
      <el-dialog title="公文流转详情" :modal="false" :visible.sync="dialogTableVisibleother">
        <el-row>
          <el-col :span="24" style="text-align: center;height:40rem;overflow-y: auto;">
            <div class="LeaveRecord-table">
              <div class="LeaveRecord-dialog-title">#{{dialogData.title}}#</div>
              <el-col :span="24">
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1" >类型</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.name}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">附件</div>
                  </el-col>
                  <el-col :span="17">
                    <div v-for="(name,index) in dialogData.accessoryName">
                      <span style="cursor: pointer;color:#48b6c4;border-bottom: 1px solid #48b6c4;" @click="download(index)">{{name}} 、</span>
                    </div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">内容</div>
                  </el-col>
                  <el-col :span="17">
                    <div v-html="dialogData.content"></div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div LeaveRecord-table-div-final">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">创建时间</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.createTime}}</div>
                  </el-col>
                </div>
                <el-col :span="24">
                  <div class="LeaveRecord-state-btn">审批状态</div>
                </el-col>
                <el-col :span="18" :offset="2" style="margin-top: 1.8rem">
                  <el-col :span="24" style="padding-bottom: 1.25rem">
                    <el-col :span="5">
                      <span>审批环节：</span>
                    </el-col>
                    <el-col :span="12" style="text-align: left">
                      <el-select v-model="link" @change="changeApp()">
                        <el-option
                          v-for="item in AppData"
                          :key="item.name"
                          :value="item.name">
                        </el-option>
                      </el-select>
                    </el-col>
                  </el-col>
                  <el-col :span="24" style="padding-bottom: 1.25rem">
                    <el-col :span="5">
                      <span>审批人：</span>
                    </el-col>
                    <el-col :span="12" style="text-align: left">
                      <el-select v-model="person" @change="chooseApproval()">
                        <el-option
                          v-for="item in AppDataChild"
                          :key="item.approver"
                          :value="item.approver">
                        </el-option>
                      </el-select>
                    </el-col>
                  </el-col>
                  <el-col :span="24" style="padding-bottom: 1.25rem;">
                    <el-col :span="5">
                      <span>审批结果：</span>
                    </el-col>
                    <el-col :span="12" style="text-align: left">
                      <span v-if="person">{{AppDataChild[approvalIndex].result | getResultState}}</span>
                      <span v-else>无</span>
                    </el-col>
                  </el-col>
                  <el-col :span="24" style="padding-bottom: 1.25rem;">
                    <el-col :span="5">
                      <span>审批意见：</span>
                    </el-col>
                    <el-col :span="12" style="text-align: left">
                      <span v-if="person">{{AppDataChild[approvalIndex].opinion||'无'}}</span>
                      <span v-else>无</span>
                    </el-col>
                  </el-col>
                  <el-col :span="24" style="padding-bottom: 1.25rem;">
                    <el-col :span="5">
                      <span>审批时间：</span>
                    </el-col>
                    <el-col :span="12" style="text-align: left">
                      <span v-if="person">{{AppDataChild[approvalIndex].approveTime||'无'}}</span>
                      <span v-else>无</span>
                    </el-col>
                  </el-col>
                </el-col>
              </el-col>
            </div>
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
  import Vue from 'vue'
  import formatdata from './../../../../../assets/js/date'
  export default{
    data(){
      return{
        key: '',
        pickerOptions0: {
          disabledDate:(time)=> {
            if(this.endvalue){
              return time.getTime() > this.endvalue;
            }
            return time.getTime() > Date.now();
          }
        },
        pickerOptions1: {
          disabledDate:(time)=> {
            if(this.startvalue){
              return time.getTime() < this.startvalue;
            }
            return time.getTime() > Date.now();
          }
        },
        isLoading:false,
        save: false,
        dialogTableVisible: false,
        dialogTableVisibleother:false,
        tableData: [],
        fileLists: [],
        dialogData:{},
        AppData:[],
        AppDataChild:[],
        accessory:[],
        accessoryName:[],
        approvalIndex:0,
        link:'',
        person:'',
        options: [{
          value: '',
        }],
        currentPage: 1,
        pageALl: 10,
        total:0,
        startvalue:'',
        endvalue:'',
        form:{
          title: '',
          name:'',
          content: '',
          id:''
        }
      }
    },
    created(){
      let param={
        kind:4
      };
      req.ajaxSend('/school/WorkDemand/getName','post',param,(res)=>{
        this.options = res.map(val=>({value:val.name}))
      });
      this.getRecord(1);
    },
    methods:{
      operationData(type){
        let sAy = [], hdData = {
          title: '标题',
          name: '类型',
          content: '内容',
          createTime: '创建日期',
          Newstatus: '审批状态',
          Newresult: '审批结果',
          approver: '审批人',
        };
        sAy.push(hdData);
        for (let obj of this.tableData) {
          this.tableData.forEach(val=>{
            return val.newTime=val.startTime+'至'+val.endTime
          });
          let d = {};
          for (let name in hdData) {
            if(name==='Newstatus'){
              if(obj.status==-1){
                obj[name]='未通过'
              }else if(obj.status==0){
                obj[name]='待审批'
              }
              else if(obj.status==1){
                obj[name]='通过'
              }
              else if(obj.status==2){
                obj[name]='审批过期'
              }
              else if(obj.status==3){
                obj[name]='撤销'
              }
              else if(obj.status==4){
                obj[name]='转发'
              }
              else if(obj.status==9){
                obj[name]='未审批'
              }
            }
            if(name==='Newresult'){
              if(obj.result==-1){
                obj[name]='不同意'
              }else if(obj.result==0){
                obj[name]='未审批'
              }
              else if(obj.result==1){
                obj[name]='同意'
              }
              else if(obj.result==2){
                obj[name]='审批过期'
              }
              else if(obj.result==4){
                obj[name]='转发'
              }
              else if(obj.result==5){
                obj[name]='正在审批'
              }
            }
            d[name] = obj[name] || '';
          }
          sAy.push(d);
        }
        if (type === 'copy') {
          req.copyTableData('.DocumentRecord', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      showDialog(row){
        this.form=JSON.parse(JSON.stringify(row));
        this.fileLists=this.form.accessoryName?this.form.accessoryName.split(',').map((val,idx)=>({'fileType': val.split('.')[1], 'name': val, 'file': null,'path':this.form.accessory.split(',')[idx]})):[];
        this.dialogTableVisible=true;
        this.id=row.id;
      },
      sendFile(){   //上传文件
        if (this.fileLists.length >= 3) {
          this.vmMsgWarning('最多只能上传三个附件！');
          return false;
        }
//        console.log($('.file_input').prop('files'));
        // 实例化一个表单数据对象
        var fileType, suffix;
        // 遍历图片文件列表，插入到表单数据中
        for (var i = 0, file; file = $('.file_input').prop('files')[i]; i++) {
          suffix = file.name.split('.')[1];
          switch (suffix) {
            case 'ppt':
              fileType = 1;
              break;
            case 'docx':
            case 'doc':
              fileType = 2;
              break;
            case 'xlsx':
            case 'xlsm':
            case 'xls':
              fileType = 3;
              break;
            default:
              this.vmMsgWarning('只能上传word、xlsx、xlsm、xls、ppt格式文件！');
              return false;
          }
          for (let obj of this.fileLists) {
            if (file.name == obj.name) {
              this.vmMsgWarning('你已添加过该文件！');
              return false;
            }
          }
          this.fileLists.push({'fileType': suffix, 'name': file.name, 'file': file});
        }
      },
      deleteFile(idx){   //删除文件
        this.fileLists.splice(idx,1);
      },
      editDoc(){
        let sendFormData=new FormData(),
          aName = [],
          upload = [];
        for(let i = 0;i<this.fileLists.length;i++){
          let val = this.fileLists[i];
          if(val.path){
            upload.push(val.path);
          }else{
            sendFormData.append('accessory[]', val.file);
          }
          aName.push(val.name);
        }

        sendFormData.append('aName', aName);
        sendFormData.append('upload', upload);
        sendFormData.append('content', this.form.content);
        sendFormData.append('name',this.form.name);
        sendFormData.append('id',this.form.id);
        sendFormData.append('title',this.form.title);
        sendFormData.append('type','edit');
        if(this.form.title===''){
          this.vmMsgWarning('请输入公文标题');
          return;
        }
        else if(this.form.content===''){
          this.vmMsgWarning('请完善公文内容');
        }
        else{
          this.$confirm('是否确认修改该公文?', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            this.save=true;
            req.ajaxFile('/school/WorkDemand/logDoc','post',sendFormData,(res)=>{
              this.save=false;
              if(res.status===1){
                this.vmMsgSuccess(res.msg);
              }else {
                this.vmMsgError(res.msg);
              }
              this.dialogTableVisible=false;
              this.getRecord(1);
            });
          }).catch(() => {
          });
        }
      },
      showDialogother(row){
        this.link='';
        this.person='';
        let param={
          type:'detail',
          id:row.id
        };
        req.ajaxSend('/school/WorkDemand/logDoc','post',param,(res)=>{
          res.data.forEach(val=>{
            this.accessory=val.accessory;
            this.accessoryName=val.accessoryName;
            this.dialogData=val;
            this.AppData=val.process;
          });
        });
        this.dialogTableVisibleother=true;
      },
      chooseApproval(){
        this.AppDataChild.forEach((val,idx)=>{
          if(val.approver===this.person){
            this.approvalIndex=idx;
          }
        })
      },
      changeApp(){
        this.AppData.forEach(val=>{
          if(val.name===this.link){
            this.AppDataChild=val.child;
            this.person=val.child[0].approver;
          }
        })
      },
      downloadedFile(){
        req.downloadFile('.DocumentRecord','/school/WorkDemand/logDoc?export=ensure','post');
      },
      download(idx){
        req.downloadFile('.DocumentRecord','/school/WorkDemand/logDoc?type=download&acc='
          +this.accessory[idx]+'&aNa='+this.accessoryName[idx],'post');
      },
      handleIconClick(){
        this.getRecord(1)
      },
      getRecord(page){
        if(!(this.startvalue&&this.endvalue)){
          var param = {
            page: this.currentPage,
            count:10,
            startTime:'',
            endTime:'',
            key:this.key
          };
        }else if(this.startvalue&&this.endvalue){
          if (this.startvalue>this.endvalue) {
            this.vmMsgWarning('结束时间必须大于开始时间');
            return;
          }
          var
            startvalue=formatdata.format(this.startvalue,'yyyy-MM-dd')+ ' 00:00:00',
            endvalue=formatdata.format(this.endvalue,'yyyy-MM-dd')+ ' 23:59:59',
            param={
              page: this.currentPage,
              count:10,
              startTime:startvalue,
              endTime: endvalue,
              key:this.key
            };
        }
        if(page!==this.currentPage){
          this.currentPage=page;
        }
        this.isLoading=true;
        req.ajaxSend('/school/WorkDemand/logDoc','post',param,(res)=>{
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
      Delectlist(row){
        let that=this,
          idsarray=[],
          detailsarray=[],
          param={
            type:'del',
            id:idsarray,
            detailId:detailsarray,
          };
        idsarray.push(row.id);
        detailsarray.push(row.detailId);
        that.$confirm('确定删除数据?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/WorkDemand/logDoc','post',param,function (res){
            that.getRecord(1);
            if(res.status===1){
              that.vmMsgWarning(res.msg);
            }else{
              that.vmMsgError(res.msg);
            }
          })
        }).catch(() => {

        });
      },
      handleCurrentChange(val) {
        this.currentPage = val;
        this.getRecord(val);
      }
    },
    filters:{
      getResultState(val){
        return val==='1'?'同意':
          val==='2'?'审批过期':
            val==='-1'?'不同意':
              val==='0'?'未审批':
                val==='5'?'正在审批':
                  val==='4'?'转发':'无'
      }
    }
  }
</script>
<style>
  .DocumentRecord .Infor-input-inner .el-input__inner{
    border-radius: 1.2rem;
  }
</style>
<style lang="less" scoped>
  .DocumentRecord{
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
    .LeaveRecord-state-btn{
      width: 6.25rem;
      height: 1.875rem;
      background:#4ba8ff;
      color:#fff;
      text-align: center;
      line-height: 1.875rem;
      border-top-right-radius: 1.1rem;
      border-bottom-right-radius: 1.1rem;
      margin-top: 1.2rem;
    }
    .LeaveRecord-dialog-title{
      display: inline-block;
      margin: auto;
      font-weight: bold;
      font-size: 16px;
      padding-bottom: 1.2rem;
    }
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
  .fileLists > div {
    float: left;
    position: relative;
    font-size: 14/16rem;
    margin-left: 2rem;
    margin-top: 1rem;
    span {
      display: inline-block;
      max-width: 10rem;
      text-overflow: ellipsis;
      overflow: hidden;
      white-space: nowrap;
    }
    img {
      width: 22/16rem;
      margin-right: 14/16rem;
    }
    i {
      position: absolute;
      right: -10px;
      top: -5px;
      cursor: pointer;
      font-size: 12px;
    }
  }
  .uploadFile {
    display: inline-block;
    position: relative;
    .el-button{
      border-radius:1.1rem;
    };
    .file_input {
      width: 100%;
      height: 36px;
      border-radius: 18/16rem;
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

  .LeaveRecord-table-div{
    width: 100%;
    border-top: 1px solid #d2d2d2;
    text-align: center;
    line-height: 2.625rem;
    box-sizing:border-box;
  }
  .LeaveRecord-table-div-final{
    border-bottom: 1px solid #d2d2d2;
  }

</style>
