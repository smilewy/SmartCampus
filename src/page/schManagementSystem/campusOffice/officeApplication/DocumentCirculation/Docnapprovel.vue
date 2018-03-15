<template>
  <div class="Docnapprovel">
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
            prop="proposer"
            label="申请人"
            align="center">
          </el-table-column>
          <el-table-column
            prop="approver"
            label="上一级审批人"
            align="center">
          </el-table-column>
          <el-table-column
            prop="result"
            label="审批结果"
            align="center">
            <template slot-scope="scope">
              <p>{{scope.row.result | getResultState}}</p>
            </template>
          </el-table-column>
          <el-table-column
            prop="opinion"
            label="审批意见"
            align="center">
          </el-table-column>
          <!--<el-table-column-->
          <!--prop="status"-->
          <!--label="审批状态"-->
          <!--align="center">-->
          <!--<template slot-scope="scope">-->
          <!--<span v-if="scope.row.status==-1">未通过</span>-->
          <!--<span v-if="scope.row.status==0">正在审批</span>-->
          <!--<span v-if="scope.row.status==1">通过</span>-->
          <!--<span style="color: #ff5b5b" v-if="scope.row.status==2">审批过期</span>-->
          <!--<span v-if="scope.row.status==3">撤销</span>-->
          <!--<span v-if="scope.row.status==4">转发</span>-->
          <!--<span v-if="scope.row.status==9">未审批</span>-->
          <!--</template>-->
          <!--</el-table-column>-->
          <el-table-column
            prop="createTime"
            label="创建日期"
            width="180"
            align="center">
            <template  slot-scope="scope">
              <span>{{scope.row.createTime}}</span>
            </template>
          </el-table-column>
          <el-table-column
            label="操作"
            align="center"
            width="260">
            <template  slot-scope="scope">
              <!--<span style="color:#89bcf5;cursor: pointer;"  @click="showDialogTable(scope.row)">审批</span>-->
              <span style="color:#4da1ff;cursor: pointer;border-right:1px solid #d2d2d2;padding-right:.6rem" @click="showDialogTable(scope.row)">详情</span>
              <span style="color: #ff5b5b;padding-left: .6rem" v-if="scope.row.status=='2'">审批过期</span>
              <span style="color:#48b6c4;cursor: pointer;border-right:1px solid #d2d2d2;padding:0 .6rem"  v-if="scope.row.status!='2'" @click="Agreepass(scope.row)">同意</span>
              <span style="color:#ff6a6a;cursor: pointer;padding:0 .6rem" v-if="scope.row.status!='2'" @click="Rejectopinion(scope.row)">不同意</span>
              <span style="color:#4da1ff;cursor: pointer;padding-left: .6rem;border-left:1px solid #d2d2d2;" @click="Forwardpass(scope.row)" v-if="scope.row.isCommon==='Y'&&scope.row.status!='2'" >转发</span>
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
                      <span style="color:#48b6c4;border-bottom: 1px solid #48b6c4;cursor: pointer;" @click="downloadAcc(index)">{{name}} 、</span>
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
                <!--<div class="LeaveRecord-table-div">-->
                <!--<el-col :span="7">-->
                <!--<div class="LeaveRecord-table-div-1">审批过期时间</div>-->
                <!--</el-col>-->
                <!--<el-col :span="17">-->
                <!--<div>{{dialogData.exceed}}</div>-->
                <!--</el-col>-->
                <!--</div>-->
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
      <el-dialog title="审批—同意" :modal="false" :visible.sync="Agree">
        <el-row>
          <el-col :span="24" v-if="listname==='通用公文流转'" style="text-align: center;height: 40rem;overflow-y: auto">
            <el-col :span="16">
              <span class="agree-title">提示：如不选择下一审批人，则表示审批流程结束</span>
            </el-col>
            <el-col :span="24">
              <el-col :span="13">
                <el-col :span="24" style="margin-top: 2rem;">
                  <el-col :span="7" style="margin-top: .6rem">下一审批人：</el-col>
                  <el-col :span="17">
                    <el-input  :disabled="true" v-model="pass.name" placeholder="请从右侧列表中选择"></el-input>
                  </el-col>
                </el-col>
                <el-col :span="24" style="margin-top: 2rem;">
                  <el-col :span="7" style="margin-top: .6rem">审批意见：</el-col>
                  <el-col :span="17">
                    <!--<el-input placeholder="请填写审批意见（选填）"-->
                    <!--type="textarea"-->
                    <!--:rows="12"-->
                    <!--v-model="pass.opinion"></el-input>-->
                    <textarea class="textarea" type="text" v-model="pass.opinion" placeholder="请填写审批意见（选填）"></textarea>
                  </el-col>
                </el-col>
              </el-col>
              <el-col :span="9" :offset="1" class="Appset-right">
                <div class="Appset-right-title">待选人员（单选）</div>
                <div class="Appset-right-border"></div>
                <el-col :span="24">
                  <el-tree
                    v-loading.body="isLoading"
                    element-loading-text="拼命加载中..."
                    class="filter-tree"
                    :data="approveData"
                    :props="defaultProps"
                    ref="tree2"
                    default-expand-all
                    @node-click="handleNodeClick">
                  </el-tree>
                </el-col>
              </el-col>
            </el-col>
            <el-col :span="24" style="margin: 3rem 0 2rem">
              <el-col :span="2" :offset="8">
                <el-button type="primary" @click="submitNext()" class="submit">提交</el-button>
              </el-col>
              <el-col :span="2" :offset="3" v-if="listname==='通用公文流转'">
                <el-button type="primary" class="submit" @click="submitAndover()">提交并结束</el-button>
              </el-col>
            </el-col>
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="24" v-if="listname!=='通用公文流转'" style="text-align: center;height: 40rem;overflow-y: auto">
            <el-col :span="22">
              <el-col :span="24" style="margin-top: 2rem;">
                <el-col :span="7" style="margin-top: .6rem">下一审批人：</el-col>
                <el-col :span="17">
                  <el-input v-model="nextApprove" :disabled="true"></el-input>
                </el-col>
              </el-col>
              <el-col :span="24" style="margin-top: 2rem;">
                <el-col :span="7" style="margin-top: .6rem">审批意见：</el-col>
                <el-col :span="17">
                  <el-input placeholder="请填写审批意见（选填）"
                            type="textarea"
                            :rows="17"
                            v-model="pass.opinion"></el-input>
                </el-col>
              </el-col>
            </el-col>
            <el-col :span="24" style="margin: 3rem 0 2rem">
              <el-col :span="2" :offset="11">
                <el-button type="primary" @click="submitNext()" class="submit">提交</el-button>
              </el-col>
              <el-col :span="2" :offset="3" v-if="listname==='通用公文流转'">
                <el-button type="primary" @click="submitAndover()" class="submit">提交并结束</el-button>
              </el-col>
            </el-col>
            <!--<el-col :span="24" style="margin: 3rem 0 2rem"  v-if="!nextApprove&&listname==='通用公文流转'" class="submit">-->
            <!--<el-col :span="2" :offset="11">-->
            <!--<el-button type="primary" @click="submitAndover()">提交并结束</el-button>-->
            <!--</el-col>-->
            <!--</el-col>-->
          </el-col>
        </el-row>
      </el-dialog>
      <el-dialog title="审批—不同意" :modal="false" :visible.sync="Reject">
        <el-row>
          <el-col :span="24" style="text-align: center;height: 40rem;overflow-y: auto">
            <el-col :span="22">
              <el-col :span="24" style="margin-top: 2rem;">
                <el-col :span="7" style="margin-top: .6rem">下一审批人：</el-col>
                <el-col :span="17">
                  <el-input v-model="nextApprove" :disabled="true"></el-input>
                </el-col>
              </el-col>
              <el-col :span="24" style="margin-top: 2rem;">
                <el-col :span="7" style="margin-top: .6rem">审批意见：</el-col>
                <el-col :span="17">
                  <el-input placeholder="请填写审批意见（选填）"
                            type="textarea"
                            :rows="17"
                            v-model="pass.opinion"></el-input>
                </el-col>
              </el-col>
            </el-col>
            <el-col :span="24" style="margin: 3rem 0 2rem">
              <el-col :span="2" :offset="11">
                <el-button type="primary" @click="submitReject()" class="submit">提交</el-button>
              </el-col>
            </el-col>
          </el-col>
        </el-row>
      </el-dialog>
      <el-dialog title="审批—转发" :modal="false" :visible.sync="Forward">
        <el-row>
          <el-col :span="24" style="margin-top:-1rem;text-align: center;height: 40rem;overflow-y: auto">
            <el-col :span="24">
              <el-col :span="13">
                <el-col :span="24" style="margin-top: 2rem;">
                  <el-col :span="7" style="margin-top: .6rem">下一审批人：</el-col>
                  <el-col :span="17">
                    <el-input  :disabled="true" v-model="pass.name" placeholder="请从右侧列表中选择"></el-input>
                  </el-col>
                </el-col>
                <el-col :span="24" style="margin-top: 2rem;">
                  <el-col :span="7" style="margin-top: .6rem">审批意见：</el-col>
                  <el-col :span="17">
                    <!--<el-input placeholder="请填写审批意见（选填）"-->
                    <!--type="textarea"-->
                    <!--:rows="12"-->
                    <!--v-model="pass.opinion"></el-input>-->
                    <textarea class="textarea" type="text" v-model="pass.opinion" placeholder="请填写审批意见（选填）"></textarea>
                  </el-col>
                </el-col>
              </el-col>
              <el-col :span="9" :offset="1" class="Appset-right">
                <div class="Appset-right-title">待选人员（单选）</div>
                <div class="Appset-right-border"></div>
                <el-col :span="24">
                  <el-tree
                    class="filter-tree"
                    :data="approveData"
                    :props="defaultProps"
                    default-expand-all
                    @node-click="handleNodeClick"
                    ref="tree2">
                  </el-tree>
                </el-col>
              </el-col>
            </el-col>
            <el-col :span="24" style="margin: 3rem 0 2rem">
              <el-col :span="2" :offset="11">
                <el-button type="primary" @click="submitForward()" class="submit">提交</el-button>
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
  import req from './../../../../../assets/js/common'
  import formatdata from './../../../../../assets/js/date'
  import ElButton from "../../../../../../node_modules/element-ui/packages/button/src/button";
  export default{
    components: {ElButton},
    data(){
      return {
        key: '',
        changecolor: 0,
        isLoading: false,
        dialogTableVisible: false,
        Agree: false,
        Forward: false,
        Reject: false,
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
        dialogData: {},
        accessory:[],
        accessoryName:[],
        AppData: [],
        AppDataChild: [],
        approveData:[],
        defaultProps: {
          children: 'children',
          label: 'label'
        },
        approvalIndex: 0,
        link: '',
        person: '',
        tableData: [],
        currentPage: 1,
        pageALl: 10,
        total: 0,
        type: '',
        opinion: '',
        id: '',
        pass:{
          name:'',
          next:{},
          opinion:''
        },
        listname:'',
        nextApprove:'',
        docId:'',
        rejectId:'',
      }
    },
    created(){
      this.querryData(1);
    },
    methods: {
      operationData(type){
        let sAy = [], hdData = {
          title: '标题',
          name: '类型',
          proposer: '申请人',
          approver: '上一级审批人',
          Newresult: '审批结果',
          opinion: '审批意见',
          createTime: '创建日期',
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
          req.copyTableData('.Docnapprovel', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      Agreepass(row){
        this.nextApprove=row.nextApprove;
        this.docId=row.id;
        this.pass.name='';
        this.pass.opinion='';
        this.listname=row.name;
        this.getApproveList();
        this.Agree=true;
      },
      submitNext(){
        if(this.listname==='通用公文流转'){
          if(!this.pass.name){
            this.vmMsgWarning('请在右侧列表中选择下一审批人');
            return;
          }
          var param={
            whether:1,
            type:'pass',
            isCommon:'Y',
            docId:this.docId,
            next:this.pass.next,
            opinion:this.pass.opinion
          };
        }else{
          var param={
            whether:1,
            type:'pass',
            isCommon:'N',
            docId:this.docId,
            opinion:this.pass.opinion
          };
        }
        this.$confirm('是否确定保留该审批意见?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/WorkDemand/approveDoc','post',param,(res)=>{
            if(res.status===1){
              this.vmMsgSuccess(res.msg);
            }else {
              this.vmMsgError(res.msg);
            }
            this.querryData();
            this.Agree=false;
          });
        }).catch(() => {

        });
      },
      submitAndover(){
        if(this.listname==='通用公文流转'){
          var param={
            whether:1,
            type:'pass',
            isCommon:'Y',
            docId:this.docId,
            next:this.pass.next,
            opinion:this.pass.opinion
          };
        }else{
          var param={
            whether:1,
            type:'pass',
            isCommon:'N',
            docId:this.docId,
            opinion:this.pass.opinion
          };
        }
        this.$confirm('是否确定保留该审批意见?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/WorkDemand/approveDoc','post',param,(res)=>{
            if(res.status===1){
              this.vmMsgSuccess(res.msg);
            }else {
              this.vmMsgError(res.msg);
            }
            this.querryData();
            this.Agree=false;
          });
        }).catch(() => {

        });
      },
      submitReject(){
        let param={
          whether:1,
          type:'reject',
          docId:this.rejectId,
          opinion:this.pass.opinion
        };
        this.$confirm('是否确定保留该审批意见?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/WorkDemand/approveDoc','post',param,(res)=>{
            if(res.status===1){
              this.vmMsgSuccess(res.msg);
            }else {
              this.vmMsgError(res.msg);
            }
            this.querryData();
            this.Reject=false;
          });
        }).catch(() => {

        });
      },
      Rejectopinion(row){
        this.rejectId=row.id;
        this.Reject=true
      },
      Forwardpass(row){
        this.pass.opinion='';
        this.pass.name='';
        this.docId=row.id;
        this.getApproveList();
        this.Forward=true;
      },
      submitForward(){
        if(!this.pass.name){
          this.vmMsgWarning('请从右侧列表中选择下一审批人');
          return;
        }
        let param={
          whether:1,
          type:'transmit',
          docId:this.docId,
          isCommon:'Y',
          next:this.pass.next,
          opinion:this.pass.opinion
        };
        this.$confirm('是否确定保留该审批意见?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/WorkDemand/approveDoc','post',param,(res)=>{
            if(res.status===1){
              this.vmMsgSuccess(res.msg);
            }else {
              this.vmMsgError(res.msg);
            }
            this.querryData();
            this.Forward=false;
          });
        }).catch(() => {

        });
      },
      getApproveList(){
        this.isLoading=true;
        function suit2tree(data){
          var tree = [],
            final = [];
          if(isArray(data)){
            tree = data;
          }else{
            tree = obj2arr(data);
          }
          tree.forEach(function(val){
            var object = {};
            val.label = val.name || val.custom_id;
            if(val.id || val.custom_id)
              object.id = val.id || val.custom_id;
            if(val.custom_id)
              delete val.custom_id;
            if(val.name)
              delete val.name;
            val.children = deepCopy(val);
            if(val.label&&val.id){
              delete val.children;
            }else{
              val.children = suit2tree(val.children);
              object.children = val.children;
            }
            object.label = val.label;
            if(object.label&&object.label!=='children')
              final.push(object);
          });
          return final;
        }
        function obj2arr(data,key_name){
          if(!isArray(data)){
            var arr = [];
            for(var key in data){
              var object = data[key];
              if(typeof object === 'object'){
                object[key_name||'custom_id'] = key;
                arr.push(object);
              }
            }
            return arr;
          }else{
            return data;
          }
        }
        function isArray(data){
          return data&&!!data.join;
        }
        function deepCopy(data){
          return JSON.parse(JSON.stringify(data));
        }
        let param={
          isCommon:'Y',
          docId:this.docId,
        };
        req.ajaxSend('/school/WorkDemand/userLists','post',param,(res)=>{
          this.approveData=suit2tree(res.data);
          this.isLoading=false;
        });
      },
      handleNodeClick(data){
        if(data.children)return;
        this.pass.name=data.label;
        this.pass.next={
          id:data.id,
          name:data.label
        }
      },
      download(){
        req.downloadFile('.Docnapprovel', '/school/WorkDemand/approveDoc?export=ensure&whether=1', 'post');
      },
      downloadAcc(idx){
        req.downloadFile('.Docnapprovel','/school/WorkDemand/LogDoc?type=download&acc='
          +this.accessory[idx]+'&aNa='+this.accessoryName[idx],'post');
      },
      showDialogTable(row){
        this.link = '';
        this.person = '';
        let param = {
          type: 'detail',
          id: row.id
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
      toggleClass: function (index, list) {
        this.changecolor = index;
        this.dialogData.pass = list.text;
      },
      handleIconClick(){
        this.querryData(1);
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
      querryData(page){
        if(!(this.startvalue&&this.endvalue)){
          var param = {
            whether: 1,
            page: this.currentPage,
            count: 10,
            startTime:'',
            endTime:'',
            key: this.key
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
              whether: 1,
              page: this.currentPage,
              count: 10,
              startTime: startvalue,
              endTime: endvalue,
              key: this.key
            };
        }
        if (page !== this.currentPage) {
          this.currentPage = page;
        }
        this.isLoading = true;
        req.ajaxSend('/school/WorkDemand/approveDoc', 'post', param, (res) => {
          if (res.data === '') {
            this.tableData = [];
            this.isLoading = false;
            return;
          }
          this.tableData = res.data;
          this.total = res.total;
          this.isLoading = false;
        });
      },
      handleCurrentChange(val) {
        this.currentPage = val;
        this.querryData(val);
      },
      confirm(){
        if (this.dialogData.passReasonText === '' && this.dialogData.passReason === '') {
          this.vmMsgWarning('请先填写审批意见');
          return;
        }
        this.id = this.dialogData.id;
        this.type = this.dialogData.pass === true ? 'pass' : 'reject';
        this.opinion = this.dialogData.passReasonText === '' ? this.dialogData.passReason : this.dialogData.passReasonText;
        let param = {
          whether: 1,
          type: this.type,
          opinion: this.opinion,
          docId: this.id,
        };
        this.$confirm('是否确认保留该审批结果?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/WorkDemand/approveDoc', 'post', param, (res) => {
            if (res.status === 1) {
              this.vmMsgSuccess(res.msg);
              this.dialogTableVisible = false;
            } else {
              this.vmMsgError(res.msg);
            }
            this.querryData();
          });
        }).catch(() => {});
      }
    },
    filters: {
      getResultState(val){
        return val === '1' ? '同意' :
          val === '2' ? '审批过期' :
            val === '-1' ? '不同意' :
              val === '0' ? '未审批' :
                val === '5' ? '未审批' :
                  val === '4' ? '转发' : '无'
      }
    }
  }
</script>
<style lang="less" scoped>
  .LeaveRecord-title .active{
    color: #FFFFFF;
  }
  .agree-title{
    margin-left: -5rem;
    color:#A5A5A5;
  }
  .Docnapprovel{
    .Appset-right{
      height:27rem;
      margin-top: 2rem;
      border-radius: .4rem;
      overflow-y: auto;
      margin-bottom: 1rem;
      text-align: left;
    }
    .textarea{
      width: 100%;
      height:21.5rem;
      resize : none;
      border-radius: .4rem;
      padding: 1rem 0 0 1rem;
    }
    .submit{
      width: 8rem;
      padding-left: 1rem;
      padding-right: 1rem;
      border-radius: 1.1rem;
    }
    .Appset-right{
      border: 1px solid #d2d2d2;
    }
    .Appset-right-title{
      padding: .8rem 0 .8rem .8rem;
      font-weight: bold;
      font-size: 0.95rem;
      text-align: left;
    }
    .Appset-right-input1 .el-input__inner{
      height:28/16rem;
      border-radius:.8rem ;
    }
    .Appset-right-border{
      border: 1px solid #d2d2d2;
    }
    .el-tree{
      border: none;
      margin-bottom:3rem;
    }
  }
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
  .Docnapprovel .Infor-input-inner .el-input__inner{
    border-radius: 1.2rem;
  }
</style>
