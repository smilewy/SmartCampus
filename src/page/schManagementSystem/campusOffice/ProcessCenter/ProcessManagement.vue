<template>
  <div class="ProcessManagement">
    <h3>流程管理</h3>
    <el-col :span="24" class="ProcessManagement-top">
      <el-row class="alertsList">
        <el-table
          v-loading.body="isLoading"
          element-loading-text="拼命加载中..."
          :data="process_list"
          style="width: 100%">
          <el-table-column
            type="index"
            label="序号"
            align="center"
            width="80">
          </el-table-column>
          <el-table-column
            label="流程类型"
            align="center">
            <template slot-scope="scope">
              {{getkindName(scope.row.kind)}}
            </template>
          </el-table-column>
          <el-table-column
            prop="name"
            label="流程名称"
            align="center">
          </el-table-column>
          <el-table-column
            prop="step"
            label="审批需要环节"
            align="center">
          </el-table-column>
          <el-table-column
            label="操作"
            align="center">
            <template slot-scope="scope">
              <span style="color:#4da1ff;cursor: pointer;border-right:1px solid #d2d2d2;padding-right: .6rem" @click="ShowDatils(scope.$index,scope.row)">编辑</span>
              <span style="color:#ff6a6a;cursor: pointer;padding-left: .6rem" @click="Delectlist(scope.$index,scope.row)">删除</span>
            </template>
          </el-table-column>
        </el-table>
      </el-row>
      <el-dialog title="流程编辑" v-if="dialogTableVisible" :modal="false" :visible.sync="dialogTableVisible">
        <el-row style="height:42rem;">
          <el-col :span="24">
            <el-col :span="3" style="padding-top: .4rem;">流程类型：</el-col>
            <el-col :span="5">
              <el-select v-model="process_type">
                <el-option
                  v-for="item in types"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value">
                </el-option>
              </el-select>
            </el-col>
            <el-col :span="3" style="padding-top: .4rem;" :offset="1">审批环节：</el-col>
            <el-col :span="5">
              <el-select v-model="link_num" placeholder="请选择">
                <el-option
                  v-for="item in links"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value">
                </el-option>
              </el-select>
            </el-col>
          </el-col>
          <el-col :span="24" style="padding-top:1.4rem;">
            <el-col :span="3" style="padding-top: .4rem;">流程名称：</el-col>
            <el-col :span="14">
              <el-input placeholder="请输入" v-model="process_name"></el-input>
            </el-col>
          </el-col>
          <el-col :span="24" style="padding-top:1.4rem;">
            <el-col :span="7" class="ProcessManagement-name">
              <el-col :span="24">
                <div class="ProcessManagement-dialog-top">待选人员</div>
              </el-col>
              <el-col :span="24">
                <el-tree
                  :data="people2choose"
                  :props="defaultProps"
                  accordion
                  @node-click="choosePeople">
                </el-tree>
              </el-col>
            </el-col>
            <el-col :span="16" :offset="1" class="ProcessManagement-link">
              <el-col :span="24">
                <div class="ProcessManagement-dialog-top" style="border-bottom: none">环节设置</div>
              </el-col>
              <el-col>
                <el-row class="alertsList" style="min-height: 10rem">
                  <el-table
                    :data="link_data"
                    style="width: 100%"
                    border>
                    <el-table-column
                      type="index"
                      label="环节"
                      align="center"
                      width="80">
                    </el-table-column>
                    <el-table-column
                      prop=""
                      label="审批人"
                      align="center">
                      <template slot-scope="scope">
                        <el-tag
                          :key="tag"
                          v-for="(tag,idx) in scope.row.approver"
                          :closable="true"
                          :close-transition="false"
                          @close="delApprover(scope.$index,idx)">
                          {{tag}}
                        </el-tag>
                        <el-input
                          class="input-new-tag"
                          v-if="scope.row.inputVisible"
                          v-model="inputValue"
                          size="mini"
                          style="width: 36%;"
                          @keyup.enter.native="handleInputConfirm"
                          @blur="handleInputConfirm">
                        </el-input>
                        <el-button v-else class="button-new-tag" style="border:none;" size="small" @click="showInput(scope.$index)">添加审批人</el-button>
                      </template>
                    </el-table-column>
                  </el-table>
                </el-row>
              </el-col>
            </el-col>
          </el-col>
          <el-col :span="2" :offset="11">
            <el-button type="primary" class="process-save" :loading="save===true" @click="saveProcess()">保存</el-button>
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
  import {
    createProcess,
    manageProcess
  } from '@/api/http'
  import req from '@/assets/js/common'
  import formatdata from '@/assets/js/date'
  export default{
    data(){
      return{
        isLoading:false,
        save:false,
        total:0,
        currentPage:1,
        process_list:[],
        cur_process_id:'',
        index2edit:0,
        canChoosePeople:false,
        process_limit:'',
        limits:[
          {
            value: 2,
            label:'无天数限制'
          },
          {
            value: 1,
            label:'小于等于'
          }
        ],
        link_num:10,
        init_link_data:[],
        approvers:[],
        dialogTableVisible:false,
        days:'',
        process_type:'',
        process_link:0,
        process_name:'',
        types: [
          {
            value: '1',
            label:'教师请假'
          },
          {
            value: '2',
            label:'用车申请'
          },
          {
            value: '3',
            label:'场地申请'
          },
          {
            value: '4',
            label:'公文管理'
          },
//          {
//            value: '5',
//            label:'订餐申请'
//          }
        ],
        people2choose: [],
        defaultProps: {
          children: 'children',
          label: 'label'
        },
        inputValue: ''
      }
    },
    computed:{
      links(){
        let links = [];
        for(let i = 0;i<10;i++){
          links.push({
            value:i+1,
            label:`共${i+1}环节`
          })
        }
        return links;
      },
      link_data(){
        let link_data = [];
        for(let i = 0;i<this.link_num;i++){
          if(this.init_link_data[i]){
            link_data[i]={
              link:i+1,
              ...this.init_link_data[i],
              inputVisible:false
            }
          }else{
            link_data.push({
              link:i+1,
              time:0,
              days:'',
              approveId:[],
              approver:[],
              limit:2,
              inputVisible:false
            })
          }
        }
        return link_data;
      }
    },
    created(){
      this.isLoading=true;
      req.ajaxSend('/school/Process/userLists','post',{},(res)=>{
        let people2choose = [];
        for(let worker in res.data){
          let tempWorker = {
            label:worker
          };
          let workers = res.data[worker];
          tempWorker.children=[];
          for(let dept in workers){
            let tempDept = workers[dept];
            tempDept.label=dept;
            tempDept.children=[];
            tempDept.forEach(val=>{
              tempDept.children.push({
                id:val.id,
                label:val.name
              });
              this.approvers.push({
                id:val.id,
                label:val.name
              });
            });
            tempWorker.children.push(tempDept)
          }
          people2choose.push(tempWorker);
        }

        this.people2choose = people2choose;
        this.isLoading=false;
      });
      this.GetprocessList(1);
    },
    methods:{
      GetprocessList(page){
        if(page!==this.currentPage){
          this.currentPage=page;
        }
        this.isLoading=true;
        let param={
          page:this.currentPage,
          count:10,
        };
        req.ajaxSend('/school/Process/manage','post',param,(res)=>{
          this.process_list = res.data;
          this.total = res.total;
          this.isLoading=false;
        });
      },
      handleCurrentChange(val) {
        this.currentPage = val;
        this.GetprocessList(val);
      },
      getkindName(id){
        let kind_map = {
          '1':'教师请假',
          '2':'用车申请',
          '3':'场地申请',
          '4':'公文管理',
          '5':'订餐申请'
        };
        return kind_map[id];
      },
      ShowDatils(idx,row){
        this.cur_process_id=row.id;
        this.days=row.time/8.64e4;
        this.process_type=row.kind;
        this.process_link=0;
        this.link_num=row.step;
        this.process_name=row.name;
        this.init_link_data=row.process.map(val=>({
          time:val.time,
          approveId:val.approveId,
          approver:val.approver,
          limit:val.limit
        }));
        this.dialogTableVisible=true;
      },
      Delectlist(idx,row){
        this.$confirm('确认删除该流程?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          let params = {
            processId:row.id,
            type:'del'
          };
          req.ajaxSend('/school/Process/manage','post',params,(res)=>{
            if(res.status===1) {
              this.vmMsgSuccess(res.msg);
              this.GetprocessList();
            }else{
              this.vmMsgError(res.msg);
            }
          })
        }).catch(() => {

        });
      },
      choosePeople(data) {
        if(data.id){
          if(!this.canChoosePeople){
            this.vmMsgWarning('请先点击添加审批人');
            return;
          }
          if(this.link_data[this.index2edit].approveId.some(val=>val===data.id)){
            this.vmMsgWarning('该审批人已存在');
            return;
          }
          if(this.process_type=='4'&&this.link_data[this.index2edit].approveId.length===1){
            this.vmMsgWarning('该流程只能添加一个审批人');
            return;
          }
          this.link_data[this.index2edit].approveId.push(data.id);
          this.link_data[this.index2edit].approver.push(data.label);
          this.link_data[this.index2edit].inputVisible = false;
        }else{
          if(!this.canChoosePeople){
            return;
          }
          this.link_data[this.index2edit].inputVisible = true;
        }
      },
      delApprover(rootIdx,idx) {
        this.link_data[rootIdx].approveId.splice(idx,1);
        this.link_data[rootIdx].approver.splice(idx,1);
      },
      showInput(idx) {
        this.index2edit=idx;
        this.canChoosePeople = true;
        this.link_data[idx].inputVisible = true;
      },
      handleInputConfirm() {
        let inputValue = this.inputValue;
        if (inputValue) {
          if(this.approvers.every(val=>val.label!==inputValue)){
            this.vmMsgWarning('该审批人不存在');
            return;
          }
          if(this.link_data[this.index2edit].approver.some(val=>val===inputValue)){
            this.vmMsgWarning('该审批人已存在');
            return;
          }
          this.link_data[this.index2edit].approveId.push(this.approvers.filter(val=>val.label===inputValue)[0].id);
          this.link_data[this.index2edit].approver.push(inputValue);
        }
        this.inputValue = '';
        this.link_data[this.index2edit].inputVisible = false;
        setTimeout(()=>{
          this.canChoosePeople=false;
        },75)
      },
      saveProcess(){
        if(!this.process_name){
          this.vmMsgWarning('请填写流程名称');
          return;
        }
        if(this.link_data.some(val=>!val.approveId.length)){
          this.vmMsgWarning('全部流程都需要选择审批人');
          return;
        }
        this.$confirm('是否确认修改该流程?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          this.save=true;
          let params = {
            type:'edit',
            processId:this.cur_process_id,
            kind:this.process_type,
            step:this.link_num,
            name:this.process_name,
            process:JSON.stringify(this.link_data.map(val=>({
              time:val.time,
              approveId:val.approveId,
              approver:val.approver,
              limit:val.limit
            })))
          };
          req.ajaxSend('/school/Process/manage','post',params,(res)=>{
            this.save=false;
            if(res.status===1){
              this.vmMsgSuccess(res.msg);
              this.dialogTableVisible=false;
              this.process_list=res.data;
              this.currentPage=1;
            }else {
              this.vmMsgError(res.msg);
            }
            this.GetprocessList(1);
          });
        }).catch(() => {});
      }
    }
  }
</script>
<style lang="less">
  .ProcessManagement .el-tag{
    background-color:#F08BC5 ;
    margin-left: .3rem;
    color: #ffffff;
    .el-icon-close{
      color: #ffffff;
    }
  }
</style>
<style lang="less" scoped>
  .ProcessManagement{
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }
  .ProcessManagement .el-tree{
    border: none;
  }
  .ProcessManagement-top{
    padding-top:46/16rem;
  }
  .process-save{
    padding: .52rem 2.8rem;
    border-radius: 1.1rem;
    margin: 2.1rem 0 2rem 0;
  }
  .ProcessManagement-name,.ProcessManagement-link{
    height:450/16rem;
    border: 1px solid #d2d2d2;
    overflow-y: auto;
    border-radius: .3rem;
  }
  .ProcessManagement-dialog-top{
    padding:.5rem .3rem;
    border-bottom: 1px solid #E7E7E7;
    font-size: 1rem;
  }
  .ProcessManagement-dialog-input{
    display: inline-block;
    width:6rem;
    margin-right: 0;
  }
</style>
