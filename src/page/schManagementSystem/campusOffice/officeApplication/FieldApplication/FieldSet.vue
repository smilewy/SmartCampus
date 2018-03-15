<template>
  <div class="FieldSet">
    <h3>场地配置设置</h3>
    <el-row>
      <el-col :span="18" class="alertsBtn" style="margin-top:1.8rem;">
        <el-button type="primary" style="padding:0.5rem 0.9rem;" @click="addField()">
          <i class="el-icon-plus"></i>
          <span>添加归属</span>
        </el-button>
      </el-col>
    </el-row>
    <el-col :span="24">
      <el-row class="alertsList">
        <el-table
          v-loading.body="isLoading"
          element-loading-text="拼命上传中..."
          :data="tableData"
          style="width: 100%">
          <el-table-column
            prop="name"
            label="配置归属地"
            align="center">
          </el-table-column>
          <el-table-column
            prop="option"
            label="申请配置设置"
            align="center">
            <template slot-scope="scope">
              <el-tag
                :key="tag"
                v-for="(tag,idx) in scope.row.option"
                :closable="true"
                :close-transition="false"
                @close="handleClose(idx,scope.$index,scope.row)">
                {{tag}}
              </el-tag>
              <span class="NewfieldDetails-add" @click="addoptions(scope.$index ,scope.row)">+</span>
            </template>
          </el-table-column>
          <el-table-column
            label="操作"
            align="center">
            <template slot-scope="scope">
              <span style="color:#ff6a6a;cursor: pointer;" @click="Delectlist(scope.row)">删除</span>
            </template>
          </el-table-column>
        </el-table>
      </el-row>
      <el-dialog title="添加配置设置" v-if="dialogTableVisible" :modal="false" :visible.sync="dialogTableVisible">
        <el-row>
          <el-col style="height: 12rem;overflow-y: auto">
            <el-col :span="24">
              <el-col :span="5" :offset="2" style="padding-top: .5rem;">名称：</el-col>
              <el-col :span="15">
                <el-input
                  class="input-new-tag"
                  v-model="inputValue"
                  ref="saveTagInput"
                  @keyup.enter.native="addOption">
                </el-input>
              </el-col>
            </el-col>
            <el-col>
              <span v-if="Hideid">{{optionID.id}}</span>
            </el-col>
            <el-col :span="24">
              <el-col :span="2" :offset="11">
                <el-button type="primary" class="Field-save" @click="addOption">保存</el-button>
              </el-col>
            </el-col>
          </el-col>
        </el-row>
      </el-dialog>
      <el-dialog title="添加配置归属" v-if="dialogTableVisibleother" :modal="false" :visible.sync="dialogTableVisibleother">
        <el-row>
          <el-col style="height: 12rem;overflow-y: auto">
            <el-col :span="24">
              <el-col :span="5" :offset="2" style="padding-top: .5rem;">名称：</el-col>
              <el-col :span="15">
                <el-input
                  v-model="setting_name"
                  @keyup.enter.native="addSetting">
                </el-input>
              </el-col>
            </el-col>
            <el-col :span="24">
              <el-col :span="2" :offset="9">
                <el-button type="primary" class="Field-save" @click="addSetting()">保存</el-button>
              </el-col>
              <el-col :span="2" :offset="14">
                <el-button class="Field-save" @click="Offdialog()">取消</el-button>
              </el-col>
            </el-col>
          </el-col>
        </el-row>
      </el-dialog>
    </el-col>
  </div>
</template>
<script>
  import req from './../../../../../assets/js/common'
  import formatdata from './../../../../../assets/js/date'
  export default{
    data(){
      return{
        dialogTableVisible: false,
        isLoading: false,
        dialogTableVisibleother:false,
        tableData: [],
        dialogData:[],
        setting_name:'',
        index2edit:0,
        inputVisible: false,
        inputValue: '',
        optionID:'',
        Hideid:false
      }
    },
    created(){
      this.getSettingList();
    },
    methods:{
      getSettingList(){
        let param={};
        this.isLoading=true;
        req.ajaxSend('/school/WorkDemand/placeOutfit','post',param,(res)=>{
          if(res.data.length){
            this.tableData=res.data.map(val=>{
              val.option = val.option.filter(subVal=>!!subVal);
              return val;
            });
          }
          this.isLoading=false;
        });
      },
      addSetting(){
        let name=this.setting_name;
        if(name===''){
          this.vmMsgWarning('请填写名称');
          return;
        }
        if(this.tableData.some(val=>val.name===name)){
          this.vmMsgWarning('配置归属地已存在');
          return;
        }
        else{
          let param={
            type:'create',
            name:name
          };
          req.ajaxSend('/school/WorkDemand/placeOutfit','post',param,(res)=>{
            if(res.status===1){
              this.vmMsgSuccess(res.msg);
            }else {
              this.vmMsgError(res.msg);
            }
            this.getSettingList();
          });
        }
        this.dialogTableVisibleother=false;
      },
      handleClose(idx,rootIdx,row) {
        if(row.option.length===1){
          this.vmMsgWarning('请保留一个选项');
          return;
        }
        let options=row.option,
          param={
            type:'option',
            id:row.id,
            option:options
          };
        options.splice(idx,1);
        req.ajaxSend('/school/WorkDemand/placeOutfit','post',param, (res)=>{});
      },
      addOption() {
        let option=this.inputValue;
        if(option==='' || option===null){
          this.vmMsgWarning('请填写名称');
          return;
        }
        else{
          let optionArray=this.tableData[this.index2edit].option;
          let tagArray=this.tableData[this.index2edit].tag;
          if(optionArray.some(val=>val===option)){
            this.vmMsgWarning('选项已存在');
            return;
          }
          let param={
            type:'option',
            option:optionArray,
            id:this.optionID
          };
          optionArray.push(option);
          req.ajaxSend('/school/WorkDemand/placeOutfit','post',param,(res)=>{
            if(res.status===1){
              this.vmMsgSuccess(res.msg);
            }else {
              this.vmMsgError(res.msg);
            }
          });
        }
        this.inputVisible = false;
        this.inputValue = '';
        this.dialogTableVisible=false;
      },
      addField(){
        this.setting_name='';
        this.dialogTableVisibleother=true;
      },
      addoptions(idx,row){
        this.optionID=row.id;
        this.index2edit = idx;
        this.dialogTableVisible=true;
      },
      Offdialog(){
        this.dialogTableVisibleother=false;
      },
      Delectlist(row){
        let that=this,
          idsarray=[],
          param={
            type:'del',
            ids:idsarray,
          };
        idsarray.push(row.id);
        that.$confirm('是否确定删除该归属?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/WorkDemand/placeOutfit','post',param,function (res){
            that.getSettingList();
            if(res.status===1){
              that.vmMsgSuccess(res.msg);
            }else {
              that.vmMsgError(res.msg);
            }
          })
        }).catch(() => {

        });
      },
    }
  }
</script>
<style lang="less" scoped>
  .FieldAdministration .el-button.FieldAdmin-titlebtn {
    background-color: #13b5b1;
    border-color: #13b5b1;
    height: 36px;
    padding: 0 .9rem;
  }
  .Field-save{
    position: absolute;
    bottom: 1.4rem;
    padding: .5rem 2.1rem;
    border-radius: 1.1rem;
  }
  .NewfieldDetails-add{
    border: 1.48px solid #F08BC5;
    display: inline-block;
    color: #F08BC5;
    cursor: pointer;
    font-weight: bold;
    margin-left: .5rem;
    width:1.6rem;
    height:1.6rem;
    line-height:1.6rem;
    text-align: center;
    vertical-align: middle;
    font-size: 1rem;
    border-radius: .3rem;
  }
  .FieldSet{
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
    padding-bottom: 1.3rem;
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
    height: 2.625rem;
    border-top: 1px solid #d2d2d2;
    text-align: center;
    line-height: 2.625rem;
    box-sizing:border-box;
  }
  .LeaveRecord-table-div-final{
    border-bottom: 1px solid #d2d2d2;
  }
  .LeaveRecord-table-div-1{
    border-right: 1px solid #d2d2d2;
  }
  .LeaveRecord-dialog-title{
    display: inline-block;
    margin: auto;
    font-weight: bold;
    font-size: 16px;
    padding-bottom: 1.2rem;
  }
</style>
<style>
  .FieldSet .el-tag{
    background-color:#F08BC5 ;
    margin-left: .3rem;
    color: #ffffff;
  }
  .FieldSet .el-tag .el-icon-close{
    color: #ffffff;
  }
</style>
