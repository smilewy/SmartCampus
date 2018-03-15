<template>
  <div class="FilerecordPending">
    <el-col :span="24" class="FilerecordPending-border"></el-col>
    <el-col :span="17" class="alertsBtn" style="margin-top: 0">
      <el-button-group style="margin-top:1.8rem">
        <el-button class="delete" title="新增" @click="addFilerecord()">
          <img class="delete_unactive" src="./../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_add.png" alt="">
          <img class="delete_active" src="./../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_add_highlight.png" alt="">
        </el-button>
        <el-button class="delete" title="删除" @click="deleteRecord">
          <img class="delete_unactive" src="../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_delete.png" alt="">
          <img class="delete_active" src="../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_delete_highlight.png" alt="">
        </el-button>
      </el-button-group>
    </el-col>
    <el-col :span="5" :offset="2" class="Infor-input-inner" style="margin-top:1.8rem;">
      <el-input style="border-radius:1rem" placeholder="请输入搜索内容" suffix-icon="el-icon-search" v-model="key" @change="handleIconClick"></el-input>
    </el-col>
    <el-col :span="24">
      <el-row class="alertsList">
        <el-table
          :data="tableData"
          style="width: 100%"
          v-loading.body="isLoading"
          @selection-change="handleSelectionChange"
          element-loading-text="拼命加载中...">
          <el-table-column
            type="selection"
            width="50"
            @change="chooseAll">
          </el-table-column>
          <el-table-column
            prop="name"
            label="档案名称"
            align="center">
            <template slot-scope="scope">
              <span style="color:#4da1ff;cursor: pointer;" @click="ShowDatils(scope.row)">{{scope.row.name}}</span>
            </template>
          </el-table-column>
          <el-table-column
            prop="identity"
            label="档案编号"
            align="center">
          </el-table-column>
          <el-table-column
            prop="tag"
            label="标签"
            align="center">
          </el-table-column>
          <el-table-column
            prop="owner"
            label="档案所有者"
            align="center">
            <template slot-scope="scope">
              <span>{{scope.row.owner }}</span>
            </template>
          </el-table-column>
          <el-table-column
            prop="time"
            label="档案时间"
            align="center">
            <template slot-scope="scope">
              <span>{{scope.row.time}}</span>
            </template>
          </el-table-column>
          <el-table-column
            prop="remark"
            label="备注"
            align="center">
          </el-table-column>
          <el-table-column
            prop="status"
            label="处理情况"
            align="center">
            <template slot-scope="scope">
              <span v-if="scope.row.status==0">待审批</span>
              <span v-if="scope.row.status==1">通过</span>
              <span v-if="scope.row.status==2" style="color: #ff5b5b">不通过</span>
            </template>
          </el-table-column>
          <el-table-column
            label="操作"
            align="center">
            <template slot-scope="scope">
              <span style="color:#4da1ff;cursor: pointer;" @click="Showaccessory(scope.row)">附件</span>
            </template>
          </el-table-column>
        </el-table>
      </el-row>
      <el-dialog title="档案记录详情" v-if="dialogTableVisible" :modal="false" :visible.sync="dialogTableVisible">
        <el-row>
          <el-col :span="24" style="text-align: center;height: 38rem;overflow-y: auto;">
            <div class="LeaveRecord-table">
              <div class="LeaveRecord-dialog-title">#{{dialogData.name}}#</div>
              <el-col :span="24">
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">档案标签</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.tag}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">档案所有者</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.owner}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">档案时间</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.time}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">提交时间</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.createTime | formatDate}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">提交人</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.creator}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div LeaveRecord-table-div-final">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">备注</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.remark}}</div>
                  </el-col>
                </div>
              </el-col>
              <el-col :span="24">
                <div class="LeaveRecord-state-btn">审批状态</div>
              </el-col>
              <el-col :span="18" :offset="2" style="margin-top: 1.8rem">
                <el-col :span="24" style="padding-bottom: 1.25rem">
                  <el-col :span="5">
                    <span>审批人：</span>
                  </el-col>
                  <el-col :span="12" style="text-align: left">
                    <el-select v-model="person" @change="chooseApproval(person)">
                      <el-option
                        v-for="item in approvalData"
                        :key="item.name"
                        :value="item.name">
                      </el-option>
                    </el-select>
                  </el-col>
                </el-col>
                <el-col :span="24" style="padding-bottom: 1.25rem;">
                  <el-col :span="5">
                    <span>审批结果：</span>
                  </el-col>
                  <el-col :span="12" style="text-align: left">
                    <span>{{approvalData.length?approvalData[approvalIndex].result||'无' : '无'}}</span>
                  </el-col>
                </el-col>
                <el-col :span="24" style="padding-bottom: 1.25rem;">
                  <el-col :span="5">
                    <span>审批意见：</span>
                  </el-col>
                  <el-col :span="12" style="text-align: left">
                    <span>{{approvalData.length?approvalData[approvalIndex].opinion||'无' : '无'}}</span>
                  </el-col>
                </el-col>
              </el-col>
            </div>
          </el-col>
        </el-row>
      </el-dialog>
      <el-dialog title="附件" v-if="accessory" :modal="false" :visible.sync="accessory">
        <el-row class="alertsList">
          <el-table
            :data=" accessoryData"
            style="width: 100%"
            border>
            <el-table-column
              type="index"
              label="序号"
              align="center"
              width="100">
            </el-table-column>
            <el-table-column
              prop="accessoryName"
              label="附件名"
              align="center">
            </el-table-column>
            <el-table-column
              label="操作"
              align="center">
              <template slot-scope="scope">
                <span style="color:#4da1ff;cursor: pointer;" @click="download(scope.row)">下载</span>
              </template>
            </el-table-column>
          </el-table>
        </el-row>
      </el-dialog>
      <el-dialog title="新增档案记录" v-if="addFile" :modal="false" :visible.sync="addFile">
        <el-row>
          <el-col :span="22" :offset="1" class="FilerecordPending-add">
            <el-col :span="24">
              <el-col :span="3" class="FilerecordPending-add-title">档案名称：</el-col>
              <el-col :span="8">
                <el-input v-model="addFileData.name"></el-input>
              </el-col>
              <el-col :span="3" :offset="2" class="FilerecordPending-add-title">档案所有者：</el-col>
              <el-col :span="8">
                <el-select style="width: 100%;" v-model="addFileData.ownerId" @change="chooseOwner" filterable multiple placeholder="请选择">
                  <el-option
                    v-for="item in ownerArr"
                    :key="item.id"
                    :label="item.value"
                    :value="item.id">
                  </el-option>
                </el-select>
              </el-col>
            </el-col>
            <el-col :span="24" class="FilerecordPending-add-top">
              <el-col :span="3" class="FilerecordPending-add-title">时间：</el-col>
              <el-col :span="8">
                <el-date-picker
                  style="width: 100%"
                  v-model="addFileData.time"
                  type="date"
                  :editable="false"
                  placeholder="选择日期"
                  :picker-options="pickerOptions0">
                </el-date-picker>
              </el-col>
              <el-col :span="3" :offset="2" class="FilerecordPending-add-title">备注：</el-col>
              <el-col :span="8">
                <el-input v-model="addFileData.remark"></el-input>
              </el-col>
            </el-col>
            <el-col :span="24" class="FilerecordPending-add-top">
              <el-col :span="3" class="FilerecordPending-add-title">选择标签：</el-col>
              <el-col :span="21">
                是否选中上次所选标签：
                <el-switch
                  v-model="changeTagState"
                  @change="isRememberLast"
                  active-text="是"
                  inactive-text="否">
                </el-switch>
              </el-col>
            </el-col>
            <el-col :span="24" class="FilerecordPending-add-top">
              <el-col :span="21" :offset="3" class="FilerecordPending-add-Remarks">注：每种标签类型都要选择</el-col>
            </el-col>
            <el-col :span="24" class="FilerecordPending-add-top">
              <el-col :span="21" :offset="3" :key="row.id" v-for="row in Alltags">
                <el-checkbox :indeterminate="row.hasChecked" style="margin-top:.8rem;" v-model="row.allChecked" @change="selectAll(row)">{{row.name}}</el-checkbox>
                <div style="margin: 15px 0;"></div>
                <div>
                  <el-checkbox v-for="tag in row.tags"  @change="checkTag(row)" :label="tag.name" :key="tag.id" v-model="tag.checked">{{tag.name}}</el-checkbox>
                </div>
              </el-col>
            </el-col>
            <el-col :span="24" class="FilerecordPending-add-top">
              <el-col :span="3" class="FilerecordPending-add-title" style="margin-top: 1rem">上传附件：</el-col>
              <el-col :span="21">
                <div class="uploadFile">
                  <el-button>选择附件</el-button>
                  <input type="file" accept=".xlsx,.xlsm,.xls.doc,.docx,.ppt,.png,.jpg,.jpeg,.txt" class="file_input" @change="sendFile">
                </div>
              </el-col>
            </el-col>
            <el-col :span="24" class="FilerecordPending-add-top">
              <el-col :offset="3" :span="21" class="FilerecordPending-add-title">
                <div v-for="(file,index) in fileList">
                  <span :title="file.name">{{file.name}}</span>
                  <i class="el-icon-close" @click="deleteFile(index)"></i>
                </div>
              </el-col>
            </el-col>
            <el-col :span="24" style="margin-top: 2rem;">
              <el-col :span="2" :offset="9">
                <el-button type="primary" class="Field-save" :loading="save===true" @click="addnewField()">保存</el-button>
              </el-col>
              <el-col :span="2" :offset="3">
                <el-button class="Field-save" @click="Offdialog()">取消</el-button>
              </el-col>
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
  import req from './../../../../assets/js/common'
  import formatdata from './../../../../assets/js/date'
  export default{
    data(){
      return{
        key:'',
        save:false,
        dialogTableVisible:false,
        accessory:false,
        addFile:false,
        isLoading:false,
        tableData:[],
        accessoryData:[],
        fileList: [],
        approvalData:[],
        approvalIndex:0,
        dialogData:{},
        person:'',
        addFileData:{
          type:'addFile',
          name:'',
          ownerId:[],
          owner:[],
          tag:[],
          time:'',
          remark:'',
        },
        ownerArr: [],
        pickerOptions0: {
          disabledDate(time) {
            return time.getTime() < Date.now() - 8.64e7;
          }
        },
        changeTagState:false,
        Alltags:[],
        lastTagChecked:[],
        multipleSelection: [],
        currentPage: 1,
        pageALl: 10,
        total:0,
        addorNot:true

      }
    },
    created(){
      req.ajaxSend('/school/FileManage/common','post',{func:'fort'},(res)=>{
        this.addorNot=res.status;
      });
      this.GetRecordList();
    },
    methods:{
      isRememberLast(status){
        if(status){
          this.Alltags.forEach(val=>{
            val.tags.forEach(subVal=>{
              subVal.checked = this.lastTagChecked.some(sVal=>sVal===subVal.id);
            });
            val.tagsChecked = [];
            val.tags.forEach(function(subVal){
              if(subVal.checked)val.tagsChecked.push(subVal);
            });
            let tagsChecked = val.tagsChecked.length;
            val.allChecked = tagsChecked === val.tags.length;
            val.hasChecked = tagsChecked > 0 && tagsChecked < val.tags.length;
          });
        }else{
          this.Alltags.forEach(val=>{
            val.hasChecked = false;
            val.allChecked = false;
            val.tagsChecked=[];
            val.tags.forEach(subVal=>{
              subVal.checked = false;
            })
          });
        }
      },
      chooseApproval(name){
        this.approvalData.forEach((val,idx)=>{
          if(val.name===name){
            this.approvalIndex=idx;
          }
        })
      },
      chooseOwner(ids){
        this.addFileData.owner=[];
        ids.forEach(rootVal=>{
          this.addFileData.owner.push(this.ownerArr.filter(val=>val.id===rootVal)[0].value);
        });
      },
      GetRecordList(page){
        if(page!==this.currentPage){
          this.currentPage=page;
        }
        this.isLoading=true;
        let param={
          option:1,
          page:this.currentPage,
          count:10,
          key:this.key
        };
        req.ajaxSend('/school/FileManage/fileRecord','post',param,(res)=>{
          if (res.status === -1) {
            this.tableData = [];
            this.isLoading = false;
            return;
          }
          if(res.data){
            this.tableData =res.data.map(val=>{
              val.tag=val.tag.join('、');
              val.owner=val.owner.join('、');
              return val;
            });
          }
          this.total = res.total;
          this.isLoading=false;
        });
      },
      Offaccessory(){
        this.accessory=false;
      },
      Offdialog(){
        this.addFile=false;
      },
      //获得所有标签
      getallTag(){
        req.ajaxSend('/school/FileManage/common','post',{func:'getTag'},(res)=>{
          res.data.forEach(val=>{
            val.hasChecked = false;
            val.allChecked = false;
            val.tagsChecked=[];
            val.tags.forEach(subVal=>{
              subVal.checked = false;
            })
          });
          this.Alltags=res.data;
          this.isRememberLast(true);
        })
      },
      //获得上一次所选标签
      getlastTag(){
        req.ajaxSend('/school/FileManage/common','post',{func:'lastTag'},(res)=>{
          this.lastTagChecked = res.data;
        })
      },
      //获得档案所有人列表
      getFileownList(){
        req.ajaxSend('/school/FileManage/common','post',{func:'getOwner'},(res)=>{
          this.ownerArr=[];
          res.data.map(val=>{
            let owner = {
              id:val.id,
              value:val.name
            };
            this.ownerArr.push(owner);
          });
        })
      },
      sendFile(){
        var fileType, suffix, file = $('.file_input').prop('files')[0];
        if (!file) {
          return false;
        }
        $('.file_input').val('');
        if (this.fileList.length >= 3) {
          this.vmMsgWarning( '最多只能上传三个附件！' ); return false;
        }
        var sAry = file.name.split('.'), sL = sAry.length - 1;
        suffix = sAry[sL];
        for (let obj of this.fileList) {
          if (file.name == obj.name) {
            this.vmMsgWarning( '你已添加过该文件！' ); return false;
          }
        }
        this.fileList.push({'fileType': fileType, 'name': file.name, 'file': file});
      },
      deleteFile(idx){   //删除文件
        this.fileList.splice(idx,1);
      },
      //查看档案详情
      ShowDatils(row){
        this.approvalData=[];
        this.person='';
        row.situation=row.situation.filter(val=>val);
        if(row&&row.situation&&row.situation.length){
          for(let val in row.situation){
            this.approvalData.push(row.situation[val]);
          }
          this.person =  this.approvalData[0].name;
          this.approvalIndex = 0;
        }
        this.dialogData=row;
        this.dialogTableVisible=true;
      },
      //新增档案记录
      addFilerecord(){
        if(!this.addorNot){
          this.vmMsgWarning( '请先进行审批设置' ); return;
        }
        this.getFileownList();
        this.getlastTag();
        this.getallTag();
        this.addFileData = {
          type:'addFile',
          name:'',
          ownerId:[],
          owner:[],
          tag:[],
          time:'',
          remark:'',
        };
        this.fileList=[];
        this.changeTagState=true;
        this.addFile=true;
      },
      addnewField(){
        let sendFormData = new FormData();
        this.addFileData.tag = [];
        this.Alltags.forEach(rootVal => {
          rootVal.tags.forEach(val => {
            if (val.checked) {
              this.addFileData.tag.push(val.id)
            }
          })
        });
        for (let i of this.fileList) {
          //在此限制文件的大小
          if(i.file){
            if(i.file.size>3*1024*1024){
              this.vmMsgWarning( '上传文件大小不能超过3M' ); return;
            }
          }
          sendFormData.append('accessory[]', i.file);
        }
        if (this.addFileData.name === '') {
          this.vmMsgWarning( '请输入档案名称！' ); return;
        }
        if (this.addFileData.owner.length === 0) {
          this.vmMsgWarning( '请选择档案所有者！' ); return;
        }
        if (!this.addFileData.time) {
          this.vmMsgWarning( '请选择档案时间！' ); return;
        }
        if (this.Alltags.some((val) => {
            return !val.allChecked && !val.hasChecked;
          })) {
            this.vmMsgWarning( '每种标签类型都要选择' ); return;
        }
        this.$confirm('是否确定新增该档案?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          this.save=true;
          this.addFileData.time = formatdata.format(this.addFileData.time,'yyyy-MM-dd');
          sendFormData.append('type', this.addFileData.type);
          sendFormData.append('name', this.addFileData.name);
          sendFormData.append('ownerId', this.addFileData.ownerId);
          sendFormData.append('owner', this.addFileData.owner);
          sendFormData.append('tag', this.addFileData.tag);
          sendFormData.append('time', this.addFileData.time);
          sendFormData.append('remark', this.addFileData.remark);
          req.ajaxFile('/school/FileManage/fileRecord', 'post', sendFormData, (res) => {
            this.save=false;
            if(res.status===1){
              this.vmMsgSuccess( res.msg );
            }else {
              this.vmMsgError( res.msg );
            }
            this.GetRecordList();
            this.addFile = false;
          });
        }).catch(() => {
        });
      },
      //查看附件
      Showaccessory(row){
        let param={
          func:'getAccessory',
          param:{
            fileId:row.fileId
          }
        };
        req.ajaxSend('/school/FileManage/common','post',param,(res)=>{
          this.accessoryData=res
        });
        this.accessory=true;
      },
      //下载附件
      download(row){
        let id=row.accId;
        req.downloadFile('.FilerecordPending','/school/FileManage/fileRecord?type=downloadAcc&accId='+id,'post');
      },
      //删除档案记录
      deleteRecord(){
        let that=this;
        let idsarray=[];
        let param={
          type:'delFile',
          fileIds:idsarray
        };
        for(let i=0;i<that.multipleSelection.length;i++){
          idsarray.push(parseInt(that.multipleSelection[i].fileId));
        }
        if (!that.multipleSelection.length) {
          this.vmMsgWarning( '请选择记录！' ); return false;
        }
        that.$confirm('是否确定删除该档案记录?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/FileManage/fileRecord','post',param,function (res){
            that.GetRecordList();
            if(res.status===1){
              this.vmMsgSuccess( res.msg );
            }else{
              this.vmMsgError( res.msg );
            }
          })
        }).catch(() => {

        });
      },
      selectAll(row) {
        row.allChecked = row.tagsChecked.length === row.tags.length;
        row.tagsChecked = [];
        row.tags.forEach(function(val){
          val.checked = !row.allChecked;
          if(val.checked)row.tagsChecked.push(val);
        });
        let tagsChecked = row.tagsChecked.length;
        row.allChecked = tagsChecked === row.tags.length;
        row.hasChecked = tagsChecked > 0 && tagsChecked < row.tags.length;
      },
      checkTag(row) {
        row.tagsChecked = [];
        row.tags.forEach(function(val){
          if(val.checked)row.tagsChecked.push(val);
        });
        let tagsChecked = row.tagsChecked.length;
        row.allChecked = tagsChecked === row.tags.length;
        row.hasChecked = tagsChecked > 0 && tagsChecked < row.tags.length;
      },
      handleIconClick(){
        this.GetRecordList();
      },
      chooseAll(){
        if (this.checkedAll) {
          for (let obj of this.tableData) {
            obj.checked = true;
          }
          $.extend(this.multipleSelection, this.tableData);
        } else {
          for (let obj of this.tableData) {
            obj.checked = false;
          }
          this.multipleSelection = [];
        }
      },
      handleSelectionChange(val) {
        this.multipleSelection = val.map(val=>{
          val.checked = true;
          return val;
        });
      },
      handleCurrentChange(val) {
        this.currentPage = val;
        this.GetRecordList(val);
      },
    },
  }
</script>
<style lang="less" scoped>
  .FilerecordPending .FilerecordPending-border{
    padding-top:1.6rem;
    border-bottom: 1px solid #d2d2d2;
  }
  .FilerecordPending .LeaveRecord-dialog-title{
    display: inline-block;
    margin: auto;
    font-weight: bold;
    font-size: 16px;
    padding-bottom: 1.2rem;
  }
  .FilerecordPending .LeaveRecord-table-div{
    width: 100%;
    height: 2.625rem;
    border-top: 1px solid #d2d2d2;
    text-align: center;
    line-height: 2.625rem;
    box-sizing:border-box;
  }
  .FilerecordPending .LeaveRecord-table-div-final{
    border-bottom: 1px solid #d2d2d2;
  }
  .FilerecordPending .LeaveRecord-table-div-1{
    border-right: 1px solid #d2d2d2;
  }
  .FilerecordPending .LeaveRecord-state-btn{
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
  .FilerecordPending .FilerecordPending-add{
    height: 38rem;
    overflow-y: auto;
  }
  .FilerecordPending-add-title{
    /*text-align: right;*/
  }
  .FilerecordPending-add-top{
    margin-top: 1.2rem;
  }
  .FilerecordPending-add-Remarks{
    color: #A6A6A6;
  }
  .FilerecordPending .uploadFile {
    display: inline-block;
    position: relative;
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
    .el-button{
      border-radius:1.6rem;
      padding:.6rem 1.6rem;
      margin-top:.7rem;
    }
  }
  .Sendfile-text>span{
    padding:0 1rem;
    display: inline-block;
    border-right: 1px solid #d2d2d2;
  }
  .FilerecordPending .Field-save{
    position: static;
    margin: 1.5rem 0 2.4rem 0;
    padding: .6rem 2.6rem;
    border-radius: 1.1rem;
  }


  .LeaveRecord-title{
    margin-top: 2rem;
    padding-bottom: 1.3rem;
    border-bottom: 1px solid #d2d2d2;
  }
  .LeaveRecord-search{
    background: #4da1ff;
    width: 7.6rem;
  }
  .alertsBtn{
    margin-top: 1.5rem;
  }
  .LeaveRecord-dialog-title{
    display: inline-block;
    margin: auto;
    font-weight: bold;
    font-size: 16px;
    padding-bottom: 1.2rem;
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
  .LeaveRecord-agreed{
    width: 5rem;
    height: 2rem;
    text-align: center;
    display: inline-block;
    line-height: 2rem;
    cursor: pointer;
    font-size: 14px;
    color: #888888;
    border: 1px solid #d2d2d2;
    border-top-left-radius: 1rem;
    border-bottom-left-radius: 1rem;
  }
  .LeaveRecord-agreed:last-of-type{
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    border-top-right-radius: 1rem;
    border-bottom-right-radius: 1rem;
  }
  .active{
    background: #09baa7;
    color: #fff;
    border: 1px solid #09baa7;
  }
  .agreed2{
    border-top-right-radius: 1rem;
    border-bottom-right-radius: 1rem;
  }
</style>
<style>
  .Infor-input-inner .el-input__inner{
    border-radius: 1.2rem;
  }
  .FilerecordPending .FilerecordPending-add .el-tag .el-tag--info .el-tag--small{
    background-color:#F08BC5;
    border-color: #f08bc5;
    color: #fff;
  }
  .el-tag .el-icon-close{
    color: #fff;
  }
  .FilerecordPending .FilerecordPending-add .el-icon-close:hover{
    background-color:#fff;
    color: #888888;
  }
</style>
