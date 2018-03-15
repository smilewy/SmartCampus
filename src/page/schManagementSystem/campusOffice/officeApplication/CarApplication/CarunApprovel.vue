<template>
  <div class="CarunApprovel">
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
       <el-button class="delete" title="导出" @click="download">
         <img class="delete_unactive"
              src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png"
              alt="">
         <img class="delete_active"
              src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png"
              alt="">
       </el-button>
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
          @sort-change="sort"
          v-loading.body="isLoading"
          element-loading-text="拼命加载中...">
          <el-table-column
            prop="title"
            label="标题"
            sortable="custom"
            align="center">
          </el-table-column>
          <el-table-column
            prop="name"
            label="类型"
            sortable="custom"
            align="center">
          </el-table-column>
          <el-table-column
            prop="carType"
            label="车辆类型"
            sortable="custom"
            align="center">
          </el-table-column>
          <el-table-column
            prop="users"
            label="用车人数"
            sortable="custom"
            align="center">
          </el-table-column>
          <el-table-column
            prop="startTime"
            label="开始日期"
            sortable="custom"
            align="center" width="180">
            <template slot-scope="scope">
              <span>{{scope.row.startTime}}</span>
            </template>
          </el-table-column>
          <el-table-column
            prop="endTime"
            label="结束日期"
            sortable="custom"
            align="center" width="180">
            <template slot-scope="scope">
              <span>{{scope.row.endTime}}</span>
            </template>
          </el-table-column>
          <el-table-column
            prop="destination"
            label="目的地"
            sortable="custom"
            align="center">
            <template slot-scope="scope">
              <span class="moretext">{{scope.row.destination}}</span>
            </template>
          </el-table-column>
          <el-table-column
            prop="contactMan"
            label="用车联系人"
            sortable="custom"
            align="center">
          </el-table-column>
          <el-table-column
            prop="telephone"
            label="用车联系电话"
            sortable="custom"
            align="center" width="180">
          </el-table-column>
          <el-table-column
            prop="createTime"
            label="创建日期"
            align="center"
            sortable="custom" width="180">
            <template slot-scope="scope">
              <span>{{scope.row.createTime}}</span>
            </template>
          </el-table-column>
          <el-table-column
            prop="status"
            label="审批状态"
            sortable="custom"
            align="center">
            <template  slot-scope="scope">
              <span v-if="scope.row.status==-1" style="color: #ff5b5b">未通过</span>
              <span v-if="scope.row.status==9">待审批</span>
              <span v-if="scope.row.status==0">正在审批</span>
              <span v-if="scope.row.status==1">通过</span>
              <span v-if="scope.row.status==2" style="color: #ff5b5b">审批过期</span>
              <span v-if="scope.row.status==3">撤销</span>
              <span v-if="scope.row.status==4">转发</span>
            </template>
          </el-table-column>
          <el-table-column
            label="操作"
            align="center"
          width="180">
            <template  slot-scope="scope">
              <span style="color:#89bcf5;cursor: pointer;border-right:1px solid #d2d2d2;padding-right: .6rem" v-if="scope.row.status=='0'||scope.row.status=='9'"  @click="showDialogTable(scope.row)">审批</span>
              <!--<span style="color: #ff5b5b;padding-right: .6rem" v-if="scope.row.status=='2'">审批过期</span>-->
              <span style="color:#89bcf5;cursor: pointer;padding:0 .6rem"  @click="Details(scope.row)">详情</span>
            </template>
          </el-table-column>
        </el-table>
      </el-row>
      <el-dialog title="用车申请审批" :modal="false" :visible.sync="dialogTableVisible">
        <el-row>
          <el-col :span="24" style="text-align: center;height:36rem;overflow-y:auto;">
            <div class="LeaveRecord-table">
              <div class="LeaveRecord-dialog-title">#{{dialogData.title}}#</div>
              <el-col :span="24">
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">用车类型</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.carType}}</div>
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
                    <div class="LeaveRecord-table-div-1">用车人数</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.users}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">目的地</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.destination}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">用车联系人</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.contactMan}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">用车联系电话</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.telephone}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">开始日期</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.startTime}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">结束日期</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.endTime}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">审批过期时间</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.exceed}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">用车说明</div>
                  </el-col>
                  <el-col :span="17">
                    <div v-html="dialogData.reason"></div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div LeaveRecord-table-div-final">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">创建日期</div>
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
                <el-col :span="24" style="padding-bottom: 1.25rem;">
                  <el-col :span="5">
                    <span>审批结果：</span>
                  </el-col>
                  <el-col :span="12" style="text-align: left;font-size: 0;">
                  <span v-for="(list,index) in textLists"
                        :class="{ active:changecolor == index}" @click="toggleClass(index,list)"
                        class="LeaveRecord-agreed">{{list.text}}</span>
                  </el-col>
                </el-col>
                <el-col :span="24" style="padding-bottom: 1.25rem;">
                  <el-col :span="5">
                    <span>审批意见：</span>
                  </el-col>
                  <el-col :span="12" style="text-align: left">
                    <textarea v-model="dialogData.passReasonText" style="resize:none;border-radius: .5rem;width: 100%" rows="6"></textarea>
                  </el-col>
                </el-col>
                <el-col :span="12" :offset="5" style="padding-bottom: 1.25rem;">
                  <el-select v-model="dialogData.passReason" placeholder="常用审批意见" style="width: 100%">
                    <el-option
                      v-for="item in options"
                      :key="item.value"
                      :value="item.value">
                    </el-option>
                  </el-select>
                </el-col>
                <el-col :span="12" :offset="5" style="padding-bottom: 1.25rem;">
                  <el-button type="primary" style="  border-radius:2rem;" @click="confirm()" class="LeaveRecord-search-1">保存</el-button>
                </el-col>
              </el-col>
            </div>
          </el-col>
        </el-row>
      </el-dialog>
      <el-dialog title="用车申请审批详情" :modal="false" :visible.sync="detail">
        <el-row>
          <el-col :span="24" style="text-align: center;height:40rem;overflow-y: auto;">
            <div class="LeaveRecord-table">
              <div class="LeaveRecord-dialog-title">#{{detailData.title}}#</div>
              <el-col :span="24">
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">用车类型</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{detailData.carType}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">申请人</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{detailData.proposer}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">用车人数</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{detailData.users}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">目的地</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{detailData.destination}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">用车联系人</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{detailData.contactMan}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">用车联系电话</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{detailData.telephone}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">开始日期</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{detailData.startTime}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">结束日期</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{detailData.endTime}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">用车说明</div>
                  </el-col>
                  <el-col :span="17">
                    <div v-html="dialogData.reason"></div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div LeaveRecord-table-div-final">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">创建日期</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{detailData.createTime}}</div>
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
        changecolor:0,
        textLists:[{
          text:'同意'
        },{
          text:'不同意'
        }],
        options: [{
          value: '情况属实，申请通过'
        }, {
          value: '情况存在异议，申请不予通过'
        }, {
          value: '申请内容不符，申请不予通过'
        }],
        isLoading:false,
        dialogTableVisible: false,
        detail: false,
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
        tableData: [],
        currentPage: 1,
        pageALl: 10,
        total:0,
        dialogData:{},
        detailData:{},
        AppData:[],
        AppDataChild:[],
        approvalIndex:0,
        link:'',
        person:'',
        type:'',
        opinion:'',
        id:'',
        key:'',
        order:''
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
          carType: '车辆类型',
          users: '用车人数',
          startTime: '开始日期',
          endTime: '结束日期',
          destination: '目的地',
          contactMan: '用车联系人',
          telephone: '用车联系电话',
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
          req.copyTableData('.CarunApprovel', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      download(){
        req.downloadFile('.CarunApprovel','/school/WorkDemand/approveCar?export=ensure&whether=1','post');
      },
      showDialogTable(row){
//        row.pass = true;
        this.changecolor=0;
//        row.passReason = '';
        this.$set(row,'passReason','');
        this.$set(row,'passReasonText','');
        this.dialogData=row;
        this.dialogTableVisible = true;
      },
      toggleClass:function(index,list){
        this.changecolor = index;
//        this.dialogData.pass = list.text;
      },
      handleIconClick(){
        this.querryData(1);
      },
      querryData(page){
        if(!(this.startvalue&&this.endvalue)){
          var param = {
            page: this.currentPage,
            whether:1,
            count: 10,
            startTime:'',
            endTime:'',
            key:this.key,
            order:this.order
          };
        }else if(this.startvalue&&this.endvalue){
          if (this.startvalue>this.endvalue) {
            this.vmMsgWarning('结束时间必须大于开始时间');
            return;
          }
          let startvalue=formatdata.format(this.startvalue,'yyyy-MM-dd');
          let endvalue=formatdata.format(this.endvalue,'yyyy-MM-dd')+' 23:59:59';
          var param = {
            page: this.currentPage,
            whether:1,
            count: 10,
            startTime:startvalue,
            endTime: endvalue,
            key:this.key,
            order:this.order
          };
        }
        if(page!==this.currentPage){
          this.currentPage=page;
        }
        this.isLoading=true;
        req.ajaxSend('/school/WorkDemand/approveCar','post',param,(res)=>{
//            console.log(res);
          if(res.data===''){
            this.tableData=[];
            this.isLoading=false;
            return;
          }
          this.tableData =res.data;
          this.total = res.total;
          this.isLoading=false;
        });
      },
      Details(row){
        this.link='';
        this.person='';
        let param={
          type:'detail',
          id:row.id,
          whether:1
        };
        req.ajaxSend('/school/WorkDemand/approveCar','post',param,(res)=>{
          res.data.forEach(val=>{
            this.detailData=val;
            this.AppData=val.process;
          });
        });
        this.detail = true;
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
      sort(column){
        this.order = column.order?`${column.prop} ${column.order==='ascending'?'asc':'desc'}`:'';
        this.querryData();
      },
      handleCurrentChange(val) {
        this.currentPage = val;
        this.querryData(val);
      },
      confirm(){
        if(this.dialogData.passReasonText==='' && this.dialogData.passReason===''){
          this.vmMsgWarning('请先填写审批意见');
          return;
        }
        this.id=this.dialogData.id;
        this.type = this.changecolor===0? 'pass':'reject';
        this.opinion = this.dialogData.passReasonText===''? this.dialogData.passReason:this.dialogData.passReasonText;
        let param = {
          whether:1,
          type:this.type,
          opinion:this.opinion,
          carId:this.id
        };
        this.$confirm('是否保留该审批结果?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/WorkDemand/approveCar','post',param,(res)=>{
//              console.log(res);
            if(res.status===1){
              this.vmMsgSuccess(res.msg);
              this.querryData(this.currentPage);
              this.dialogTableVisible = false;
            }else {
              this.vmMsgError(res.msg);
            }
          });
        }).catch(() => {

        });
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
<style scoped>
  .LeaveRecord-title{
    margin-top:3.6rem;
    border-bottom: 1px solid #d2d2d2;
  }
  .LeaveRecord-search{
    background: #4da1ff;
    width: 7rem;
    margin-left: 1.6rem;
  }
  .LeaveRecord-search-1{
    background: #4da1ff;
    width: 7rem;
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
  .Infor-input-inner .el-input__inner{
    border-radius: 1.2rem;
  }
</style>
