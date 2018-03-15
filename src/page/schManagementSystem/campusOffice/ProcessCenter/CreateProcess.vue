<template>
  <div class="CreateProcess">
    <el-col :span="24">
      <el-col :span="22">
        <h3>创建流程</h3>
      </el-col>
      <el-col :span="2">
        <el-button type="primary" class="CreateProcess-top-btn" :loading="save===true" @click="saveProcess()">保存</el-button>
      </el-col>
    </el-col>
    <el-col :span="24">
      <el-col :span="17">
        <el-col :span="24" style="padding-top: 2rem;">
          <el-col :span="2" style="padding-top: .4rem;">流程类型：</el-col>
          <el-col :span="9">
            <el-select v-model="process_type" placeholder="请选择" style="width: 100%">
              <el-option
                v-for="item in types"
                :key="item.value"
                :label="item.label"
                :value="item.value">
              </el-option>
            </el-select>
          </el-col>
          <el-col :span="2" style="padding-top: .4rem;" :offset="2">审批环节：</el-col>
          <el-col :span="9">
            <el-select v-model="link_num" placeholder="请选择" style="width: 100%">
              <el-option
                v-for="item in links"
                :key="item.value"
                :label="item.label"
                :value="item.value">
              </el-option>
            </el-select>
          </el-col>
        </el-col>
        <el-col :span="24" style="padding-top:2rem;">
          <el-col :span="2" style="padding-top: .4rem;">流程名称：</el-col>
          <el-col :span="22">
            <el-input v-model="process_name" placeholder="请输入流程名称"></el-input>
          </el-col>
        </el-col>
        <el-col :span="24" style="padding-top:2rem;">
          <el-col :span="2" style="padding-top: .4rem;">环节设置：</el-col>
          <el-col :span="22">
            <el-row class="alertsList">
              <el-table
                :data="link_data"
                style="width: 100%"
                border>
                <el-table-column
                  prop="link"
                  label="环节"
                  align="center"
                  width="80">
                </el-table-column>
                <el-table-column
                  label="天数"
                  align="center"
                  width="240">
                  <template slot-scope="scope">
                    <el-col :span="24">
                      <el-col :span="16">
                        <el-select v-model="scope.row.limit" placeholder="请选择" style="width: 100%" @change="chooseLimit" @visible-change="lockIndex(scope.$index)">
                          <el-option
                            v-for="item in limits"
                            :key="item.value"
                            :label="item.label"
                            :value="item.value">
                          </el-option>
                        </el-select>
                      </el-col>
                      <el-col :span="7" :offset="1">
                        <el-input placeholder="天数" v-model="scope.row.days" :disabled="scope.row.limit===2"></el-input>
                      </el-col>
                    </el-col>
                  </template>
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
                      ref="saveTagInput"
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
        <el-col :span="24" class="Car-footer">
          <el-col :span="2">提示：</el-col>
          <el-col :span="22" style="margin-left: -1.6rem;">
            <el-col>1.审批人从右侧的列表中选择.</el-col>
            <el-col>2.直接输入审批人.</el-col>
            <el-col>3.在文档中复制审批人姓名粘贴到单元格内.</el-col>
          </el-col>
        </el-col>
      </el-col>
      <el-col :span="6" :offset="1" class="Process-name">
        <el-col :span="24">
          <div class="ProcessManagement-dialog-top">待选人员</div>
        </el-col>
        <el-col :span="24">
          <el-tree
            v-loading.body="isLoading"
            element-loading-text="拼命加载中..."
            :data="people2choose"
            :props="defaultProps"
            accordion
            @node-click="chosePeople">
          </el-tree>
        </el-col>
      </el-col>
    </el-col>
  </div>
</template>
<script>
  import {
    createProcess,
    manageProcess,
    ProcessuserList
  } from '@/api/http'
  import req from '@/assets/js/common'
  import formatdata from '@/assets/js/date'
  export default{
    data(){
      return{
        isLoading:false,
        save:false,
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
        link_num:2,
        approvers:[],
        dialogTableVisible:false,
        days:'',
        process_type:'',
        process_link:0,
        process_name:'',
        types: [
          {
            value: 1,
            label:'教师请假'
          },
          {
            value: 2,
            label:'用车申请'
          },
          {
            value: 3,
            label:'场地申请'
          },
          {
            value: 4,
            label:'公文管理'
          },
//          {
//            value: 5,
//            label:'订餐申请'
//          }
        ],
        people2choose: [],
        defaultProps: {
          children: 'children',
          label: 'label'
        },
        inputValue: '',
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
    },
    methods:{
      lockIndex(idx){
//        console.log('asd');
        this.index2edit=idx;
      },
      chooseLimit(val){
        if(val===1){
          this.link_data[this.index2edit].days='';
        }
      },
      chosePeople(data) {
        if(data.id){
          if(!this.canChoosePeople){
            this.vmMsgWarning('请先点击添加审批人');
            return;
          }
          if(this.link_data[this.index2edit].approveId.some(val=>val===data.id)){
            this.vmMsgWarning('该审批人已存在');
            return;
          }
          if(this.process_type===4&&this.link_data[this.index2edit].approveId.length===1){
            this.vmMsgWarning('该流程只能添加一个审批人');
            return;
          }
          this.link_data[this.index2edit].approveId.push(data.id);
          this.link_data[this.index2edit].approver.push(data.label);

          this.link_data[this.index2edit].inputVisible = false;
        }
        else{
          if(!this.canChoosePeople){
            return;
          };
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
        this.$nextTick(()=> {
          this.$refs.saveTagInput.$refs.input.focus();
        });
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
        this.link_data[this.index2edit].inputVisible = false;
        this.inputValue = '';
        setTimeout(()=>{
          this.canChoosePeople=false;
        },125)
      },
      saveProcess(){
        if(!this.process_type){
          this.vmMsgWarning('请选择流程类型');
          return;
        }
        if(!this.process_name){
          this.vmMsgWarning('请填写流程名称');
          return;
        }
        if(this.link_data.some(val=>!val.approveId.length)){
          this.vmMsgWarning('全部流程都需要选择审批人');
          return;
        }
        if(this.link_data.some(val=>val.limit===1&&!val.days)){
          this.vmMsgWarning('有天数限制的环节需要填写天数');
          return;
        }
        this.$confirm('是否确认创建该流程?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          this.save=true;
          let params = {
            type:'create',
            kind:this.process_type,
            step:this.link_num,
            name:this.process_name,
            process:JSON.stringify(this.link_data.map(val=>({
              time:val.days*8.64e4,
              approveId:val.approveId,
              approver:val.approver,
              limit:val.limit
            })))
          };
          req.ajaxSend('/school/Process/create','post',params,(res)=>{
            this.save=false;
            if(res.status===1){
              this.vmMsgSuccess(res.msg);
            }else {
              this.vmMsgError(res.msg);
            }
          });
        }).catch(() => {});
      }
    }
  }
</script>
<style lang="less" scoped>
  .CreateProcess{
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }
  .CreateProcess-top-btn{
    padding:.5rem 2.8rem ;
    border-radius: 1.1rem;
  }
  .ProcessManagement .el-tree{
    border: none;
  }
  .ProcessManagement-top{
    padding-top:46/16rem;
  }
  .Field-save{
    padding: .52rem 2.8rem;
    border-radius: 1.1rem;
    margin: 2rem 0;
  }
  .Process-name{
    height:760/16rem;
    border: 1px solid #d2d2d2;
    overflow-y: auto;
    border-radius: .3rem;
    margin-top: 2rem;
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
  .Car-footer{
    font-size:14/16rem ;
    color: #999999;
    letter-spacing:.1rem;
  }
  .CreateProcess .el-tree{
    border: none;
  }
</style>
<style>
  .CreateProcess .el-tag{
    background-color:#F08BC5 ;
    margin-left: .3rem;
    color: #ffffff;
  }
  .CreateProcess .el-tag .el-icon-close{
    color: #ffffff;
  }
</style>
