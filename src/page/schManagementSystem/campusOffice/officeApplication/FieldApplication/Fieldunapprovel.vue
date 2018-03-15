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
            prop="proposer"
            label="场地申请人"
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
            align="center">
            <template  slot-scope="scope">
              <span style="color:#89bcf5;cursor: pointer;" v-if="scope.row.status=='0'||scope.row.status=='9'" @click="showDialogTable(scope.row)">审批</span>
              <span style="color:#FF6A6A;" v-if="scope.row.status==='2'">审批过期</span>
            </template>
          </el-table-column>
        </el-table>
      </el-row>
      <el-dialog title="场地申请审批" :modal="false" :visible.sync="dialogTableVisible">
        <el-row>
          <el-col :span="24" style="text-align: center;height:36rem;overflow-y:auto;">
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
                    <div class="LeaveRecord-table-div-1">审批过期时间</div>
                  </el-col>
                  <el-col :span="17">
                    <div>{{dialogData.exceed}}</div>
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
                <div class="LeaveRecord-state-btn">我的审批</div>
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
        order:'createTime desc',
        dialogData:{},
        type:'',
        opinion:'',
        id:'',
        param:{
          key:''
        },
        needTime:false,
      }
    },
    created(){
      this.querryData(1);
    },
    methods:{
      showDialogTable(row){
//        row.pass = true;
        this.changecolor=0;
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
            whether: 1,
            count: 10,
            startTime:'',
            endTime:'',
            key:this.param.key
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
            whether: 1,
            count: 10,
            startTime:startvalue,
            endTime: endvalue,
            key:this.param.key
          };
        }
        if(page!==this.currentPage){
          this.currentPage=page;
        }
        this.isLoading=true;
        req.ajaxSend('/school/WorkDemand/approvePlace','post',param,(res)=>{
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
          whether: 1,
          type:this.type,
          opinion:this.opinion,
          placeId:this.id
        };
        this.$confirm('是否确定保留该审批结果?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/WorkDemand/approvePlace','post',param,(res)=>{
            if(res.status===1){
              this.vmMsgSuccess(res.msg);
            }else {
              this.vmMsgError(res.msg);
            }
            this.querryData(1);
            this.dialogTableVisible = false;
          });
        }).catch(() => {

        });
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
