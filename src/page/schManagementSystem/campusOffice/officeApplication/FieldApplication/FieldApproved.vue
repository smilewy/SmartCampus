<template>
  <div>
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
     <el-col :span="17" class="alertsBtn"></el-col>
     <el-col :span="5" :offset="2" class="Infor-input-inner" style="padding:1.8rem 0 1rem 0;">
       <el-input style="border-radius:1rem" placeholder="请输入搜索内容" suffix-icon="el-icon-search" v-model="param.key" @change="handleIconClick"></el-input>
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
            width="200">
          </el-table-column>
          <el-table-column
            prop="principal"
            label="场地负责人"
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
            prop="createTime"
            label="操作"
            align="center">
            <template slot-scope="scope">
              <!--<span style="color:#ff6a6a;cursor: pointer;" @click="Delectlist(scope.row)">删除</span>-->
              <span style="color:#4da1ff;cursor: pointer;" @click="ShowDatils(scope.row)">详情</span>
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
  import formatdata from './../../../../../assets/js/date'
  export default{
    data(){
      return{
        changecolor:0,
        isLoading:false,
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
        startvalue:'',
        endvalue:'',
        needTime:false,
        tableData: [],
        checkedAll:false,
        currentPage: 1,
        pageALl: 10,
        total:0,
        id:'',
        param:{
          key:''
        },

        dialogData:{},
        AppData:[],
        AppDataChild:[],
        approvalIndex:0,
        link:'',
        person:'',
      }
    },
    created(){
      this.querryData(1);
    },
    methods:{
      handleIconClick(){
        this.querryData(1);
      },
      Delectlist(row){
        let that=this,
          idsarray=[],
          param={
            type:'del',
            whether:2,
            ids:idsarray,
          };
        idsarray.push(row.id);
        that.$confirm('此操作将永久删除该文件, 是否继续?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/WorkDemand/approvePlace','post',param,function (res){
            if(res.status===1){
              that.vmMsgSuccess(res.msg);
            }else {
              that.vmMsgError(res.msg);
            }
            that.querryData(1);
          })
        }).catch(() => {

        });
      },
      querryData(page){
        if(!(this.startvalue&&this.endvalue)){
          var param = {
            page: this.currentPage,
            whether: 2,
            count: 10,
            startTime:'',
            endTime:'',
            key:this.param.key
          };
        }else if(this.startvalue&&this.endvalue) {
          if (this.startvalue > this.endvalue) {
            this.vmMsgWarning('结束时间必须大于开始时间');
            return;
          }
          let startvalue = formatdata.format(this.startvalue, 'yyyy-MM-dd');
          let endvalue = formatdata.format(this.endvalue, 'yyyy-MM-dd') + ' 23:59:59';
          var param = {
            page: this.currentPage,
            whether: 2,
            count: 10,
            startTime: startvalue,
            endTime: endvalue,
            key: this.param.key
          };
        }
        if(page!==this.currentPage){
          this.currentPage=page;
        }
        this.isLoading=true;
        req.ajaxSend('/school/WorkDemand/approvePlace','post',param,(res)=>{
//            console.log(res);
          if(res.data===''){
            this.tableData=[];
            this.isLoading=false;
            return;
          }
          res.data.forEach(val=>{
            val.occupyTime=val.occupyTime.map(subval=>subval).join('、')
          });
          this.tableData =res.data;
          this.total = res.total;
          this.isLoading=false;
        });
      },
      ShowDatils(row){
        this.link='';
        this.person='';
        let param={
          type:'detail',
          id:row.id,
          whether: 2,
        };
        req.ajaxSend('/school/WorkDemand/approvePlace','post',param,(res)=>{
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
      handleCurrentChange(val) {
        this.currentPage = val;
        this.querryData(val);
      },
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
