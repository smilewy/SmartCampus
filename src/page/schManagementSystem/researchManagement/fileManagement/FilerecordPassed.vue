<template>
  <div class="FilerecordPassed">
    <el-row class="LeaveRecord-title" type="flex" align="center">
      <el-form :inline="true" class="formInline">
        <el-form-item label="选择标签：">
          <el-input v-model="optionsvalue" @focus="Showcheckbox()"></el-input>
          <!--多选框-->
          <el-col :span="24" class="FilerecordPassed-checkbox" v-if="showcheckbox">
            <el-col :span="24" :key="row.id" v-for="row in Alltags">
              <el-checkbox :indeterminate="row.hasChecked" v-model="row.allChecked" @change="selectAll(row)">{{row.name}}</el-checkbox>
              <div>
                <el-checkbox v-for="tag in row.tags"  @change="checkTag(row)" :label="tag.name" :key="tag.id" v-model="tag.checked">{{tag.name}}</el-checkbox>
              </div>
            </el-col>
            <el-col :span="1" :offset="9">
              <el-button type="primary"  class="Hidecheckbox-btn" @click ="Hidecheckbox()">确定</el-button>
            </el-col>
          </el-col>
        </el-form-item>
        <el-form-item label="档案时间：">
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
          <el-button type="primary" icon="el-icon-search" class="LeaveRecord-search" @click="GetRecordList(1)">查询</el-button>
        </el-form-item>
      </el-form>
    </el-row>
   <el-row>
     <el-col :span="17" class="alertsBtn" style="margin-top: 0">
       <el-button-group style="margin-top:1.8rem">
         <el-button class="delete" title="删除" @click="deleteRecord">
           <img class="delete_unactive" src="../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_delete.png" alt="">
           <img class="delete_active" src="../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_delete_highlight.png" alt="">
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
          </el-table-column>
          <el-table-column
            prop="time"
            label="档案时间"
            align="center">
          </el-table-column>
          <el-table-column
            prop="remark"
            label="备注"
            align="center">
          </el-table-column>
          <el-table-column
            prop="result"
            label="处理情况"
            align="center">
            <template slot-scope="scope">
              <span v-if="scope.row.status==0">待审批</span>
              <span v-if="scope.row.status==1">通过</span>
              <span v-if="scope.row.status==2">拒绝</span>
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
          <el-col :span="24" style="text-align: center;min-height: 38rem;">
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
  import req from '@/assets/js/common'
  import formatdata from '@/assets/js/date'
  export default{
    data(){
      return{
        pickerOptions0: {
          disabledDate:(time)=> {
            if(this.endvalue){
              return time.getTime() > this.endvalue;
            }
          }
        },
        pickerOptions1: {
          disabledDate:(time)=> {
            if(this.startvalue){
              return time.getTime() < this.startvalue;
            }
          }
        },
        startvalue:'',
        endvalue:'',
        optionsvalue:'',
        inTags:[],
        checkAll: true,
        isIndeterminate: true,
        showcheckbox:false,
        dialogTableVisible:false,
        accessory:false,
        isLoading:false,
        tableData:[],
        Alltags:[],
        accessoryData:[],
        key:'',
        checkedAll:false,
        dialogData:{},
        approvalData:[],
        approvalIndex:0,
        person:'',
        value5: [],
        multipleSelection: [],
        needTime:false,
        currentPage: 1,
        pageALl: 10,
        total:0,
      }
    },
    created(){
      this.getallTag();
      this.GetRecordList(1);
    },
    methods:{
      chooseApproval(name){
        this.approvalData.forEach((val,idx)=>{
          if(val.name===name){
            this.approvalIndex=idx;
          }
        })
      },
      GetRecordList(page){
        if(!(this.startvalue&&this.endvalue)){
          var param = {
            option:2,
            page:this.currentPage,
            count:10,
            startTime:'',
            endTime:'',
            key:this.key,
            inTag:this.inTags
          };
        }else if(this.startvalue&&this.endvalue){
          if (this.startvalue>this.endvalue) {
            this.vmMsgWarning( '结束时间必须大于开始时间！' ); return;
          }
          var startvalue=formatdata.format(this.startvalue,'yyyy-MM-dd')+' 00:00:00',
            endvalue=formatdata.format(this.endvalue,'yyyy-MM-dd')+' 23:59:59',
            param={
              option:2,
              page:this.currentPage,
              count:10,
              startTime:startvalue,
              endTime:endvalue,
              key:this.key,
              inTag:this.inTags
            };
        }
        if(page!==this.currentPage){
          this.currentPage=page;
        }
        this.isLoading=true;
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
        })
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
      Showcheckbox(){
        this.showcheckbox=true;
      },
      Hidecheckbox(){
        this.optionsvalue = '';
        this.inTags = [];
        this.Alltags.forEach(rootVal=>{
          if(!rootVal.tagsChecked.length)
            return;
          this.optionsvalue += (rootVal.name+'，'+rootVal.tagsChecked.map(val=>val.name).join('、')+'，');
          this.inTags = this.inTags.concat(rootVal.tagsChecked.map(val=>val.id));
        });

        this.showcheckbox=false;
      },
      Offaccessory(){
        this.accessory=false;
      },
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
      handleIconClick(){
        this.GetRecordList();
      },
      ShowDatils(row){
        this.approvalData=[];
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
      addFilerecord(){
        this.addFile=true;
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
        req.downloadFile('.FilerecordPassed','/school/FileManage/fileRecord?type=downloadAcc&accId='+id,'post');
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
      handleCurrentChange(val) {
        this.currentPage = val;
        this.GetRecordList(val);
      },
      handleSelectionChange(val) {
        this.multipleSelection = val.map(val=>{
          val.checked = true;
          return val;
        });
      },
    },
  }
</script>
<style lang="less" scoped>
  .FilerecordPassed-checkbox{
    border: 1px solid #d1dbe5;
    border-radius: 3px;
    background-color: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,.12), 0 0 6px rgba(0,0,0,.04);
    box-sizing: border-box;
    margin:6px 0;
    z-index:1001;
    position: absolute;
    padding: 1rem;
  }
  .FilerecordPassed  .el-checkbox-group .el-checkbox{
    margin-left: 0;
    margin-top:0;
    margin-right: .6rem;
  }
  .FilerecordPassed .Field-save{
    position: absolute;
    bottom: 1.4rem;
    padding: .6rem 2.6rem;
    border-radius: 1.1rem;
  }
  .FilerecordPassed .Hidecheckbox-btn{
    margin-top: 1.6rem;
    padding: .35rem 1rem;
  }
  .LeaveRecord-title{
    margin-top:3.6rem;
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
</style>
