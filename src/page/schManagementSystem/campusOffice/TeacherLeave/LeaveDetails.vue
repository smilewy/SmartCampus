<template>
  <div class="LeaveRecord LeaveDetails" id="LeaveRecord">
    <el-col :span="24" style="border-bottom: 1px solid #d2d2d2;padding-bottom: 2rem;">
      <h3>请假明细</h3>
    </el-col>
    <!--<el-col class="LeaveRecord-title">-->
    <!--&lt;!&ndash;时间段&ndash;&gt;-->
    <!--<el-col :span="24">-->
    <!--<el-col :span="2">时间段：</el-col>-->
    <!--<el-col :span="3" style="margin-left: -3rem">-->
    <!--<el-date-picker-->
    <!--v-model="startvalue" type="date" :picker-options="pickerOptions0" style="width: 100%">-->
    <!--</el-date-picker>-->
    <!--</el-col>-->
    <!--<el-col :span="1"  style="text-align: center">_</el-col>-->
    <!--<el-col :span="3">-->
    <!--<el-date-picker-->
    <!--v-model="endvalue" type="date" :picker-options="pickerOptions1" style="width: 100%">-->
    <!--</el-date-picker>-->
    <!--</el-col>-->
    <!--<el-col :span="1" :offset="12">-->
    <!--<el-button type="primary" icon="el-icon-search" class="LeaveRecord-search" @click="querryDetails()">查询</el-button>-->
    <!--</el-col>-->
    <!--</el-col>-->
    <!--</el-col>-->
    <el-col :span="18" class="alertsBtn">
      <el-button class="delete" title="导出" @click="download">
        <img class="delete_unactive"
             src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png"
             alt="">
        <img class="delete_active"
             src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png"
             alt="">
      </el-button>
      <el-button-group style="margin-left: 2.1rem">
        <el-button class="filt" title="复制" @click="operationData('copy')">
          <img class="filt_unactive"
               src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png"
               alt="">
          <img class="filt_active"
               src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png"
               alt="">
        </el-button>
        <el-button class="delete" title="打印" @click="operationData('print')">
          <img class="delete_unactive"
               src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png"
               alt="">
          <img class="delete_active"
               src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png"
               alt="">
        </el-button>
      </el-button-group>
    </el-col>
    <el-col :span="24">
      <el-row class="alertsList">
        <el-table
          :data="tableData"
          style="width: 100%"
          @sort-change="sort"
          align="center"
          v-loading.body="isLoading"
          element-loading-text="拼命加载中...">
          <el-table-column
            prop="proposer"
            label="申请人"
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
            prop="startTime"
            label="开始时间"
            width="200"
            sortable="custom"
            align="center">
          </el-table-column>
          <el-table-column
            prop="endTime"
            label="结束时间"
            width="200"
            sortable="custom"
            align="center">
          </el-table-column>
          <el-table-column
            prop="duration"
            label="请假时长（小时）"
            sortable="custom"
            align="center">
          </el-table-column>
          <el-table-column
            prop="reason"
            label="请假原因"
            sortable="custom"
            align="center">
            <template slot-scope="scope">
              <span class="moretext">{{scope.row.reason}}</span>
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
            align="center">
            <template slot-scope="scope">
              <span style="color:#89bcf5;cursor: pointer;" @click="Showdialog(scope.row)">详情</span>
            </template>
          </el-table-column>
        </el-table>
      </el-row>
      <el-dialog title="请假明细详情" v-if="dialogTableVisible" :modal="false" :visible.sync="dialogTableVisible">
        <el-row>
          <el-col :span="24" style="text-align: center;height:40rem;overflow-y: auto;">
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
                    <div class="LeaveRecord-table-div-1">请假类型</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.name}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">起始时间</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.startTime}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">结束时间</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.endTime}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">请假时长</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.duration}}</div>
                  </el-col>
                </div>
                <div class="LeaveRecord-table-div">
                  <el-col :span="7">
                    <div class="LeaveRecord-table-div-1">请假原因</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.reason}}</div>
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
                        :key="item.approve"
                        :value="item.approve">
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
          :page-size="pageALl"
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

  export default {
    data() {
      return {
        isLoading: false,
        dialogData: {},
        AppData:[],
        AppDataChild:[],
        approvalIndex:0,
        link: '',
        person: '',
        dialogTableVisible: false,
        pickerOptions0: {
          disabledDate(time) {
            return time.getTime() > Date.now();
          }
        },
        pickerOptions1: {
          disabledDate(time) {
            return time.getTime() > Date.now();
          }
        },
        startvalue: new Date(),
        endvalue: new Date(),
        tableData: [],
        currentPage: 1,
        pageALl: 10,
        total: 0,
        order: 'createTime desc'
      }
    },
    created() {
      this.querryDetails(1)
    },
    methods: {
      operationData(type){
        let sAy = [], hdData = {
          proposer: '申请人',
          name: '类型',
          startTime: '开始时间',
          endTime: '结束时间',
          duration: '请假时长（小时）',
          reason: '请假原因',
          Newstatus: '审批状态',
        };
        sAy.push(hdData);
        for (let obj of this.tableData) {
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
          req.copyTableData('.LeaveDetails', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      download() {
        req.downloadFile('.LeaveDetails', '/school/TeacherLeave/detail?export=ensure', 'post');
      },
      chooseApproval(){
        this.AppDataChild.forEach((val,idx)=>{
          if(val.approve===this.person){
            this.approvalIndex=idx;
          }
        })
      },
      changeApp(){
        this.AppData.forEach(val=>{
          if(val.name===this.link){
            this.AppDataChild=val.child;
            this.person=val.child[0].approve;
          }
        })
      },
      Showdialog(row) {
        this.link='';
        this.person='';
        let param={
          type:'detail',
          id:row.id
        };
        req.ajaxSend('/school/TeacherLeave/detail','post',param,(res)=>{
          res.data.forEach(val=>{
            this.dialogData=val;
            this.AppData=val.process;
          });
        });
        this.dialogTableVisible = true;
      },
      querryDetails(page) {
        if (page !== this.currentPage) {
          this.currentPage = page;
        }
        this.isLoading = true;
//        let startvalue=formatdata.format(this.startvalue,'yyyy-MM-dd');
//        let endvalue=formatdata.format(this.endvalue,'yyyy-MM-dd');
        let param = {
          page: this.currentPage,
          count: 10,
//          startTime:startvalue,
//          endTime: endvalue,
          order: this.order
        };
        req.ajaxSend('/school/TeacherLeave/detail', 'post', param, (res) => {
          if (res.status === -1) {
            this.tableData = [];
            this.isLoading = false;
            return;
          }
          res.data.forEach((val) => {
            val.linkOne = [];
            val.linkTwo = [];
            if (val.approver && val.approver[0]) {
              val.approver[0].forEach((subval, idx) => {
                if (subval) {
                  let result = this.getResultState(val.result[0][idx]);
                  val.linkOne.push(`<span>${subval}</span>  <span style="color:#48b6c4;">${result}</span>`)
                }
              })
            }
            if (val.approver && val.approver[1]) {
              val.approver[1].forEach((subval, idx) => {
                if (subval) {
                  let result = this.getResultState(val.result[1][idx]);
                  val.linkTwo.push(`<span>${subval}</span>  <span style="color:#48b6c4;">${result}</span>`)
                }
              })
            }
          });
          this.tableData = res.data;
          this.total = res.total;
          this.isLoading = false;
        })
      },
      sort(column) {
        this.order = column.order?column.order?`${column.prop} ${column.order==='ascending'?'asc':'desc'}`:'':'';
        this.querryDetails(1);
      },
      handleCurrentChange(val) {
        this.currentPage = val;
        this.querryDetails(val);
//        console.log(`当前页: ${this.currentPage}`);
      },
      getResultState(val){
        return val==='1'?'同意':
          val==='2'?'审批过期':
            val==='-1'?'不同意':
              val==='0'?'未审批':
                val==='5'?'未审批':
                  val==='4'?'转发':'无'
      }
    },
    filters: {
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
  .LeaveRecord-table-div:after{
    content: ' ';
    display: block;
    height: 0;
    clear: both;
    overflow: hidden;
  }
  .LeaveRecord {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .LeaveRecord-title {
    margin-top: 2rem;
    padding-bottom: 1.3rem;
    border-bottom: 1px solid #d2d2d2;
  }

  .LeaveRecord-search {
    background: #4da1ff;
    width: 7.6rem;
    border-radius: 1.1rem;
  }

  .alertsBtn {
    margin-top: 1.5rem;
  }

  .LeaveRecord-dialog-title {
    display: inline-block;
    margin: auto;
    font-weight: bold;
    font-size: 16px;
    padding-bottom: 1.2rem;
  }

  .LeaveRecord-table-div {
    width: 100%;
    border-top: 1px solid #d2d2d2;
    text-align: center;
    line-height: 2.625rem;
    box-sizing: border-box;
  }

  .LeaveRecord-table-div-final {
    border-bottom: 1px solid #d2d2d2;
  }
  .LeaveRecord-state-btn {
    width: 6.25rem;
    height: 1.875rem;
    background: #4ba8ff;
    color: #fff;
    text-align: center;
    line-height: 1.875rem;
    border-top-right-radius: 1.1rem;
    border-bottom-right-radius: 1.1rem;
    margin-top: 1.2rem;
  }

  .LeaveRecord-agreed {
    color: #4cb6c1;
  }
</style>
