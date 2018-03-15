<template>
  <div class="DocApproved">
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
          <el-button type="primary" icon="el-icon-search" class="LeaveRecord-search" @click="querryData(1)">查询</el-button>
        </el-form-item>
      </el-form>
    </el-row>
    <el-row>
      <el-col :span="17" class="alertsBtn">
        <el-button-group>
          <el-button class="delete" title="导出" @click="download">
            <img class="delete_unactive"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png"
                 alt="">
            <img class="delete_active"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png"
                 alt="">
          </el-button>
          <el-button class="delete" title="删除"  @click="deleteDocApproved">
            <img class="delete_unactive" src="../../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_delete.png" alt="">
            <img class="delete_active" src="../../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_delete_highlight.png" alt="">
          </el-button>
        </el-button-group>
        <el-button-group style="margin-left: 2.1rem">
          <el-button class="filt" title="复制" @click="operationData('copy')">
            <img class="filt_unactive"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png"
                 alt="">
            <img class="filt_active"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png"
                 alt="">
          </el-button>
          <el-button class="delete" title="打印" @click="operationData('print')">
            <img class="delete_unactive"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png"
                 alt="">
            <img class="delete_active"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png"
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
          element-loading-text="拼命加载中..."
          @selection-change="handleSelectionChange">
          <el-table-column
            type="selection"
            width="40"
            @change="chooseAll">
          </el-table-column>
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
            prop="proposer"
            label="申请人"
            align="center">
          </el-table-column>
          <el-table-column
            prop="createTime"
            label="创建日期"
            align="center">
            <template  slot-scope="scope">
              <span>{{scope.row.createTime}}</span>
            </template>
          </el-table-column>
          <el-table-column
            prop="result"
            label="审批结果"
            align="center">
            <template slot-scope="scope">
              <p :style="scope.row.result=='2'||scope.row.result=='-1' ? 'color:#ff5b5b':''">{{scope.row.result | getResultState}}</p>
            </template>
          </el-table-column>
          <el-table-column
            prop="opinion"
            label="审批意见"
            align="center">
          </el-table-column>
          <el-table-column
            prop="approver"
            label="审批人"
            align="center">
          </el-table-column>
          <el-table-column
            label="操作"
            align="center">
            <template  slot-scope="scope">
              <span style="color:#4da1ff;cursor: pointer;"  @click="showDialogTable(scope.row)">详情</span>
            </template>
          </el-table-column>
        </el-table>
      </el-row>
      <el-dialog :title="dialogData.name+' 详情'" :modal="false" :visible.sync="dialogTableVisible">
        <el-row>
          <el-col :span="24" style="text-align: center;height: 40rem;overflow-y: auto">
            <div class="LeaveRecord-table">
              <div class="LeaveRecord-dialog-title">#{{dialogData.title}}#</div>
              <el-col :span="24">
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">申请人</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.proposer}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">类型</div>
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
                      <span style="color:#48b6c4;border-bottom: 1px solid #48b6c4;cursor: pointer;"  @click="downloadAcc(index)">{{name}} 、</span>
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
              </el-col>
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
  import formatdata from './../../../../../assets/js/date'
  export default{
    data(){
      return{
        key: '',
        isLoading:false,
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
        startvalue:'',
        endvalue:'',
        dialogData: {},
        accessory:[],
        accessoryName:[],
        AppData: [],
        AppDataChild: [],
        approveData:[],
        dialogTableVisible: false,
        approvalIndex: 0,
        link: '',
        person: '',
        tableData: [],
        multipleSelection: [],
        checkedAll:false,
        currentPage: 1,
        pageALl: 10,
        total:0,
        type:'',
        opinion:'',
        id:''
      }
    },
    created(){
      this.querryData(1);
    },
    methods:{
      operationData(type){
        let sAy = [], hdData = {
          title: '标题',
          name: '类型',
          proposer: '申请人',
          createTime: '创建日期',
          Newresult: '审批结果',
          opinion: '审批意见',
          approver: '审批人',
        };
        sAy.push(hdData);
        for (let obj of this.tableData) {
          this.tableData.forEach(val=>{
            return val.newTime=val.startTime+'至'+val.endTime
          });
          let d = {};
          for (let name in hdData) {
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
                obj[name]='未审批'
              }
            }
            d[name] = obj[name] || '';
          }
          sAy.push(d);
        }
        if (type === 'copy') {
          req.copyTableData('.DocApproved', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      download(){
        req.downloadFile('.DocApproved','/school/WorkDemand/approveDoc?export=ensure&whether=2','post');
      },
      downloadAcc(idx){
        req.downloadFile('.DocApproved','/school/WorkDemand/LogDoc?type=download&acc='
          +this.accessory[idx]+'&aNa='+this.accessoryName[idx],'post');
      },
      handleIconClick(){
        this.querryData(1);
      },
      querryData(page){
        if(!(this.startvalue&&this.endvalue)){
          var param = {
            whether:2,
            page: page,
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
              whether:2,
              page: page,
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
        req.ajaxSend('/school/WorkDemand/approveDoc','post',param,(res)=>{
          if (!res.data) {
            this.tableData = [];
            this.isLoading = false;
            return;
           }
          this.tableData =res.data;
          this.total = res.total;
          this.isLoading=false;
        });
      },
      showDialogTable(row){
        this.link = '';
        this.person = '';
        let param = {
          type: 'detail',
          id: row.id,
          whether:2,
        };
        req.ajaxSend('/school/WorkDemand/approveDoc', 'post', param, (res) => {
          res.data.forEach(val => {
            this.accessory=val.accessory;
            this.accessoryName=val.accessoryName;
            this.dialogData = val;
            this.AppData = val.process;
          });
        });
        this.dialogTableVisible = true;
      },
      chooseApproval(){
        this.AppDataChild.forEach((val, idx) => {
          if (val.approver === this.person) {
            this.approvalIndex = idx;
          }
        })
      },
      changeApp(){
        this.AppData.forEach(val => {
          if (val.name === this.link) {
            this.AppDataChild = val.child;
            this.person=val.child[0].approver;
          }
        })
      },
      handleCurrentChange(val) {
        this.currentPage = val;
        this.querryData(val);
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
      deleteDocApproved(){
        let that=this;
        let idsarray=[];
        let param={
          type:'del',
          ids:idsarray,

        };
        for(let i=0;i<that.multipleSelection.length;i++){
          idsarray.push(parseInt(that.multipleSelection[i].id));
        }
        if (!that.multipleSelection.length) {
          that.vmMsgWarning('请选择记录！');
          return false;
        }
        that.$confirm('是否确定删除该公文?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/WorkDemand/approveDoc','post',param,function (res){
            that.querryData();
            if(res.status===1){
              that.vmMsgSuccess(res.msg);
            }else{
              that.vmMsgSuccess(res.msg);
            }
          })
        }).catch(() => {

        });
      },
      handleSelectionChange(val) {
        this.multipleSelection = val.map(val=>{
          val.checked = true;
          return val;
        });
      },
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
<style lang="less" scoped>
  .LeaveRecord-title{
    margin-top:3.6rem;
    border-bottom: 1px solid #d2d2d2;
  }
  .LeaveRecord-search{
    background: #4da1ff;
    width: 7rem;
    margin-left: 1.6rem;
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
    border-top: 1px solid #d2d2d2;
    text-align: center;
    line-height: 2.625rem;
    box-sizing:border-box;
  }
  .LeaveRecord-table-div-final{
    border-bottom: 1px solid #d2d2d2;
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
  .DocApproved .Infor-input-inner .el-input__inner{
    border-radius: 1.2rem;
  }
</style>
