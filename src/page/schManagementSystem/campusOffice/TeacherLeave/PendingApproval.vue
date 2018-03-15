<template>
  <div class="PendingApproval">
    <el-row class="LeaveRecord-title" type="flex" align="middle">
      <el-col :span="20">
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
        </el-form>
      </el-col>
      <el-col :span="4">
        <el-row type="flex" justify="end">
          <el-button type="primary" icon="el-icon-search" class="LeaveRecord-search" @click="queryData(1)">查询</el-button>
        </el-row>
      </el-col>
    </el-row>
    <el-row>
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
    </el-row>
    <el-row>
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
              align="center">
            </el-table-column>
            <el-table-column
              prop="name"
              label="类型" align="center" sortable="custom">
            </el-table-column>
            <el-table-column
              prop="startTime"
              label="开始时间"
              width="180" align="center" sortable="custom">
            </el-table-column>
            <el-table-column
              prop="endTime"
              label="结束时间"
              width="180" align="center" sortable="custom">
            </el-table-column>
            <el-table-column
              prop="duration"
              label="请假时长（小时）" align="center" sortable="custom">
            </el-table-column>
            <el-table-column
              prop="reason"
              label="请假原因" align="center" sortable="custom">
              <template slot-scope="scope">
                <span class="moretext">{{scope.row.reason}}</span>
              </template>
            </el-table-column>
            <el-table-column
              prop="proposer"
              label="申请人" align="center" sortable="custom">
            </el-table-column>
            <el-table-column
              prop="createTime"
              label="创建日期"
              width="180" align="center" sortable="custom">
            </el-table-column>
            <el-table-column
              label="操作"
              align="center">
              <template slot-scope="scope">
                <span style="color:#89bcf5;cursor: pointer;" v-if="scope.row.status=='0'||scope.row.status=='9'" @click="showDialogTable(scope.row)">审批</span>
                <span style="color:#FF6A6A;" v-if="scope.row.status=='2'">审批过期</span>
              </template>
            </el-table-column>
          </el-table>
        </el-row>
        <el-dialog title="待审批详情" :modal="false" :visible.sync="dialogTableVisible">
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
                      <div class="LeaveRecord-table-div-1">请假类型</div>
                    </el-col>
                    <el-col :span="17">
                      <div>{{dialogData.name}}</div>
                    </el-col>
                  </div>
                  <div class="LeaveRecord-table-div">
                    <el-col :span="7">
                      <div class="LeaveRecord-table-div-1">开始时间</div>
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
                      <div class="LeaveRecord-table-div-1">审批过期时间</div>
                    </el-col>
                    <el-col :span="17">
                      <div>{{dialogData.exceed}}</div>
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
                  <textarea v-model="dialogData.passReasonText" style="resize:none;border-radius: .5rem;width: 100%"
                            rows="6"></textarea>
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
                    <el-button type="primary" @click="confirm()" class="LeaveRecord-search">确认</el-button>
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
    </el-row>
  </div>
</template>
<script>
  import req from '../../../../assets/js/common'
  import formatdata from '../../../../assets/js/date'

  export default {
    data() {
      return {
        changecolor: 0,
        textLists: [{
          text: '同意'
        }, {
          text: '不同意'
        }],
        options: [{
          value: '情况属实，申请通过'
        }, {
          value: '情况存在异议，申请不予通过'
        }, {
          value: '申请内容不符，申请不予通过'
        }],
        isLoading: false,
        dialogTableVisible: false,
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
        startvalue: '',
        endvalue: '',
        tableData: [],
        currentPage: 1,
        pageALl: 10,
        total: 0,
        order: 'createTime desc',
        dialogData: {},
        type: '',
        opinion: '',
        id: ''
      }
    },
    created() {
      this.queryData(1);
    },
    methods: {
      operationData(type){
        let sAy = [], hdData = {
          title: '标题',
          name: '类型',
          startTime: '开始时间',
          endTime: '结束时间',
          duration: '请假时长（小时）',
          reason: '请假原因',
          proposer: '申请人',
          createTime: '创建日期',
        };
        sAy.push(hdData);
        for (let obj of this.tableData) {
          let d = {};
          for (let name in hdData) {
            d[name] = obj[name] || '';
          }
          sAy.push(d);
        }
        if (type === 'copy') {
          req.copyTableData('.PendingApproval', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      download() {
        req.downloadFile('.PendingApproval', '/school/TeacherLeave/approve?export=ensure&whether=1', 'post');
      },
      showDialogTable(row) {
//        row.pass = true;
//        row.passReason = '';
        this.changecolor=0;
        this.$set(row, 'passReason', '');
        this.$set(row, 'passReasonText', '');
        this.dialogData = row;
        this.dialogTableVisible = true;
      },
      toggleClass: function (index, list) {
        this.changecolor = index;
//        this.dialogData.pass = list.text;
      },
      queryData(page) {
          if(!(this.startvalue&&this.endvalue)){
            var param = {
              page: this.currentPage,
              whether: 1,
              count: 10,
              startTime:'',
              endTime:'',
              order: this.order,
            };
          }else if(this.startvalue&&this.endvalue){
            if (this.startvalue>this.endvalue) {
              this.vmMsgWarning('结束时间必须大于开始时间');
              return;
            }
            let startvalue = formatdata.format(this.startvalue, 'yyyy-MM-dd') + ' 00:00:00';
            let endvalue = formatdata.format(this.endvalue, 'yyyy-MM-dd') + ' 23:59:59';
            var param = {
              page: this.currentPage,
              whether: 1,
              count: 10,
              startTime: startvalue,
              endTime: endvalue,
              order: this.order,
            };
          }
        if (page !== this.currentPage) {
          this.currentPage = page;
        }
        this.isLoading = true;
        req.ajaxSend('/school/TeacherLeave/approve', 'post', param, (res) => {
          if (res.status === -1) {
            this.tableData = [];
            this.isLoading = false;
            return;
          }
          this.tableData = res.data;
          this.total = res.total;
          this.isLoading = false;
        });
      },
      sort(column) {
        this.order = column.order?column.order?`${column.prop} ${column.order==='ascending'?'asc':'desc'}`:'':'';
        this.queryData(1);
      },
      handleCurrentChange(val) {
        this.currentPage = val;
        this.queryData(val);
      },
      confirm() {
        if (this.dialogData.passReasonText === '' && this.dialogData.passReason === '') {
          this.vmMsgWarning('请先填写审批意见');
          return;
        }
        this.id = this.dialogData.id;
        this.type = this.changecolor===0 ? 'pass' : 'reject';
        this.opinion = this.dialogData.passReasonText === '' ? this.dialogData.passReason : this.dialogData.passReasonText;
        let param = {
          whether: 1,
          type: this.type,
          opinion: this.opinion,
          id: this.id
        };
        this.$confirm('是否确定保留该审批结果?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/TeacherLeave/approve', 'post', param, (res) => {
            if (res.status === 1) {
              this.vmMsgSuccess(res.msg);
              this.queryData(this.currentPage);
              this.dialogTableVisible = false;
            }else{
              this.vmMsgError(res.msg);
            }
          });
        }).catch(() => {

        });
      }
    }
  }
</script>
<style scoped>
  .LeaveRecord{
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }
  .LeaveRecord-title{
    margin-top:3.6rem;
    border-bottom: 1px solid #d2d2d2;
  }
  .LeaveRecord-search{
    background: #4da1ff;
    width: 7.6rem;
    border-radius: 1.1rem;
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
  .LeaveRecord-table-div:after{
    content: ' ';
    display: block;
    height: 0;
    clear: both;
    overflow: hidden;
  }
  .LeaveRecord-table-div>div:last-child>div{
    border-left: 1px solid #d2d2d2;
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
  .Infor-input-inner .el-input__inner{
    border-radius: 1.2rem;
  }
  .PendingApproval .active {
    background: #09baa7;
    color: #fff;
    border: 1px solid #09baa7;
  }
</style>
