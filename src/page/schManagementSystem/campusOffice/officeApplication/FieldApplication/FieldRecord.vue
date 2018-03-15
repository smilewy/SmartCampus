<template>
  <div class="DocumentRecord FieldRecord">
    <h3>场地申请记录</h3>
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
          <!--<el-button class="delete" title="删除" @click="DeleteField">-->
          <!--<img class="delete_unactive" src="../../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_delete.png" alt="">-->
          <!--<img class="delete_active" src="../../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_delete_highlight.png" alt="">-->
          <!--</el-button>-->
          <el-button class="delete" title="导出" @click="download">
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
        <el-input style="border-radius:1rem" placeholder="请输入搜索内容" suffix-icon="el-icon-search" v-model="param.key" @change="handleIconClick"></el-input>
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
          <!--<el-table-column-->
          <!--type="selection"-->
          <!--width="50"-->
          <!--@change="chooseAll">-->
          <!--</el-table-column>-->
          <el-table-column
            prop="title"
            label="标题"
            align="center">
          </el-table-column>
          <el-table-column
            prop="address"
            label="详细地址"
            align="center">
            <template slot-scope="scope">
              <span class="moretext">{{scope.row.address}}</span>
            </template>
          </el-table-column>
          <el-table-column
            prop="name"
            label="类型"
            align="center">
          </el-table-column>
          <el-table-column
            prop="occupyTime"
            label="使用时间"
            align="center"
            width="280">
            <template slot-scope="scope">
              <p>{{scope.row.occupyTime}}</p>
            </template>
          </el-table-column>
          <el-table-column
            prop="principal"
            label="活动负责人"
            align="center">
          </el-table-column>
          <el-table-column
            prop="telephone"
            label="联系电话"
            align="center">
          </el-table-column>
          <el-table-column
            prop="createTime"
            label="创建日期"
            align="center">
            <template slot-scope="scope">
              <span>{{scope.row.createTime | formatDate}}</span>
            </template>
          </el-table-column>
          <el-table-column
            prop="status"
            label="审批状态"
            align="center">
            <template slot-scope="scope">
              <span v-if="scope.row.status==-1" style="color: #ff5b5b">未通过</span>
              <span v-if="scope.row.status==9">待审批</span>
              <span v-if="scope.row.status==0">正在审批</span>
              <span v-if="scope.row.status==1">通过</span>
              <span style="color: #ff5b5b" v-if="scope.row.status==2">审批过期</span>
              <span v-if="scope.row.status==3">撤销</span>
              <span v-if="scope.row.status==4">转发</span>
            </template>
          </el-table-column>
          <el-table-column
            label="操作"
            align="center"
            width="150">
            <template slot-scope="scope">
              <span style="color:#4da1ff;cursor: pointer;" @click="ShowDatils(scope.row)">详情</span>
              <span style="color:#FF6A6A;cursor: pointer;border-left:1px solid #d2d2d2;padding:0 .6rem" v-if="scope.row.status==9" @click="withdraw(scope.row)">撤回</span>
              <!--<span style="color:#FF6A6A;cursor: pointer;border-left:1px solid #d2d2d2;padding:0 .6rem" v-if="scope.row.status!=='0'" @click="DeleteField(scope.row)">删除</span>-->
            </template>
          </el-table-column>
        </el-table>
      </el-row>
      <el-dialog title="场地申请详情" v-if="dialogTableVisible" :modal="false" :visible.sync="dialogTableVisible">
        <el-row>
          <el-col :span="24" style="text-align: center;height: 35rem; overflow-y: auto;">
            <div class="LeaveRecord-table">
              <div class="LeaveRecord-dialog-title">#{{dialogData.title}}#</div>
              <el-col :span="24">
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">场地申请类型</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.name}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">活动负责人</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.principal}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">联系方式</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.telephone}}</div>
                  </el-col>
                </div>
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
                    <div class="LeaveRecord-table-div-1">使用场地</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.duration}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">详细地址</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.address}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">使用日期</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.occupyTime}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">配置选择</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.outfit}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div LeaveRecord-table-div-final">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">说明</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.explain}}</div>
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
  import Vue from 'vue'
  import formatdata from './../../../../../assets/js/date'
  export default{
    data(){
      return{
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
        dialogTableVisible: false,
        dialogTableVisibleother:false,
        tableData: [],
        dialogData:{},
        AppData:[],
        AppDataChild:[],
        approvalIndex:0,
        link:'',
        person:'',
        multipleSelection: [],
        options: [{
          value: '',
        }],
        currentPage: 1,
        pageALl: 10,
        total:0,
        checkedAll:false,
        startvalue:'',
        endvalue:'',
        param:{
          key:''
        },
        needTime:false,
      }
    },
    created(){
      this.getRecord(1)
    },
    methods:{
      operationData(type){
        let sAy = [], hdData = {
          title: '标题',
          address: '详细地址',
          name: '类型',
          occupyTime: '使用时间',
          principal: '活动负责人',
          telephone: '联系电话',
          createTime: '创建日期',
          Newstatus: '审批状态',
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
            }
            d[name] = obj[name] || '';
          }
          sAy.push(d);
        }
        if (type === 'copy') {
          req.copyTableData('.FieldRecord', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      withdraw(row){
        this.$confirm('是否确认撤回该记录?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          let param={
            type:'cancel',
            id:row.id
          };
          req.ajaxSend('/school/WorkDemand/logPlace','post',param,(res)=>{
            if(res.status===1){
              this.vmMsgSuccess(res.msg);
            }else {
              this.vmMsgError(res.msg);
            }
          });
          this.getRecord(1);
        }).catch(() => {

        });
      },
      download(){
        req.downloadFile('.FieldRecord','/school/WorkDemand/logPlace?export=ensure','post');
      },
      ShowDatils(row){
        this.link='';
        this.person='';
        let param={
          type:'detail',
          id:row.id
        };
        req.ajaxSend('/school/WorkDemand/logPlace','post',param,(res)=>{
          res.data.forEach(val=>{
            val.occupyTime = val.occupyTime.map(subVal=>subVal).join('、');
            this.dialogData=val;
            this.AppData=val.process;
          });
        });
        this.dialogTableVisible=true;
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
      handleIconClick(){
        this.getRecord(1)
      },
      getRecord(page){
        if(!(this.startvalue&&this.endvalue)){
          var param = {
            page: page,
            count:10,
            startTime:'',
            endTime:'',
            key:this.param.key
          };
        }else if(this.startvalue&&this.endvalue){
          if (this.startvalue>this.endvalue) {
            this.vmMsgWarning('结束时间必须大于开始时间');
            return;
          }
          var
            startvalue=formatdata.format(this.startvalue,'yyyy-MM-dd'),
            endvalue=formatdata.format(this.endvalue,'yyyy-MM-dd')+' 23:59:59',
            param={
              page: page,
              count:10,
              startTime:startvalue,
              endTime: endvalue,
              key:this.param.key
            };
        }
        if(page!==this.currentPage){
          this.currentPage=page;
        }
        this.isLoading=true;
        req.ajaxSend('/school/WorkDemand/logPlace','post',param,(res)=>{
          if (res.status === -1) {
            this.tableData = [];
            this.isLoading = false;
            return;
          }
          res.data.forEach(val=>{
            if(val.occupyTime){
              val.occupyTime = val.occupyTime.map(subVal=>subVal).join('、');
            }
          });
          this.tableData =res.data;
          this.total = res.total;
          this.isLoading=false;
        });
      },
      DeleteField(row){
        let that=this;
        let param={
          type:'del',
          id:row.id
        };
        that.$confirm('是否确定删除该记录?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/WorkDemand/logPlace','post',param,function (res){
            if(res.status===1){
              that.vmMsgSuccess(res.msg);
            }else {
              that.vmMsgError(res.msg);
            }
          });
          that.getRecord(1);
        }).catch(() => {

        });
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
        this.getRecord(val);
      }
    },
    filters:{
      getResultState(val){
        return val==='1'?'同意':
          val==='2'?'审批过期':
            val==='-1'?'不同意':
              val==='0'?'未审批':
                val==='5'?'未审批':
                  val==='4'?'转发':'无'
      }
    }
  }
</script>
<style lang="less" scoped>
  .DocumentRecord{
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
  .Infor-input-inner .el-input__inner{
    border-radius: 1.2rem;
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
  .LeaveRecord-dialog-title{
    display: inline-block;
    margin: auto;
    font-weight: bold;
    font-size: 16px;
    padding-bottom: 1.2rem;
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
</style>
