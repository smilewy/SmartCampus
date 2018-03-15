<template>
  <div class="KeleiInformation">
    <el-col :span="24">
      <el-col :span="22">
        <h3>角色信息</h3>
      </el-col>
      <el-col :span="2">
        <!--<el-button type="primary" class="CreateProcess-top-btn">保存</el-button>-->
      </el-col>
    </el-col>
    <el-row>
      <el-col :span="18" class="alertsBtn" style="margin-top:1.8rem;">
        <el-button type="primary" style="padding:0.5rem 0.9rem;" @click="addKelei()">
          <i class="el-icon-plus"></i>
          <span>添加角色</span>
        </el-button>
      </el-col>
    </el-row>
    <el-col :span="24">
      <el-row class="alertsList KeleiInformation-input">
        <el-table
          v-loading.body="isLoading"
          element-loading-text="拼命加载中..."
          :data="tableData"
          style="width: 100%"
          @sort-change="sort"
          @row-click="Default">
          <el-table-column
            type="index"
            label="序号"
            align="center"
            width="400">
          </el-table-column>
          <el-table-column
            prop="roleName"
            label="角色"
            align="center"
            sortable="custom">
          </el-table-column>
          <el-table-column
            label="操作"
            align="center">
            <template slot-scope="scope">
              <span style="color:#4da1ff;cursor: pointer;border-right:1px solid #d2d2d2;padding-right:.6rem"  v-if="scope.row.state===true"  @click="editKelei(scope.row)">编辑</span>
              <span style="color:#ff6a6a;cursor: pointer;padding-left:.6rem"  v-if="scope.row.state===true"  @click="Delectlist(scope.row)">删除</span>
            </template>
          </el-table-column>
        </el-table>
      </el-row>
      <el-dialog title="添加角色" v-if="dialogTableVisibleother" :modal="false" :visible.sync="dialogTableVisibleother">
        <el-row>
          <el-col style="min-height:13rem;">
            <el-col :span="24">
              <el-col :span="5" :offset="2" style="padding-top: .4rem;"><span style="color: red;">*</span> 角色：</el-col>
              <el-col :span="15">
                <el-input placeholder="请输入" v-model="param.roleName"></el-input>
              </el-col>
            </el-col>
            <el-col :span="2" :offset="11">
              <el-button type="primary" class="Field-save" @click="SaveaddKeilei()">保存</el-button>
            </el-col>
          </el-col>
        </el-row>
      </el-dialog>
      <el-dialog title="编辑角色" v-if="editKeilei" :modal="false" :visible.sync="editKeilei">
        <el-row>
          <el-col style="min-height:13rem;">
            <el-col :span="24">
              <el-col :span="5" :offset="2" style="padding-top: .4rem;"><span style="color: red;">*</span> 角色：</el-col>
              <el-col :span="15">
                <el-input placeholder="请输入" v-model="editData.roleName"></el-input>
              </el-col>
            </el-col>
            <el-col :span="2" :offset="11">
              <el-button type="primary" class="Field-save" @click="SaveeditKeilei()">保存</el-button>
            </el-col>
          </el-col>
        </el-row>
      </el-dialog>
    </el-col>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  export default{
    data(){
      return{
        tableData:[],
        dialogTableVisibleother:false,
        editKeilei:false,
        editData:{},
        param:{
          roleId:'',
          roleName:'',
          order:'',
          field:''
        },
        state:'',

      }
    },
    created(){
      this.GetKeleiList();
    },
    methods:{
      GetKeleiList(){
        this.isLoading=true;
        req.ajaxSend('/school/Bcinformation/role/type/find','post',{order:this.param.order,field:this.param.field},(res)=>{
          if (res.status === -1) {
            this.tableData = [];
            this.isLoading = false;
            return;
          }
          this.tableData=res;
          this.isLoading=false;
        });
      },
      addKelei(){
        this.param.roleName='';
        this.dialogTableVisibleother=true;
      },
      SaveaddKeilei(){
        if(this.param.roleName===''){
          this.vmMsgWarning('请填写角色');
          return;
        }
        let param={
          roleId:'',
          roleName:this.param.roleName,
        };
        req.ajaxSend('/school/Bcinformation/role/type/upin','post',param,(res)=>{
          if(res.return===true){
            this.vmMsgSuccess('添加成功');
            this.dialogTableVisibleother=false;
          }
          if(res.return===false){
            this.vmMsgError('添加失败');
          }
          if(res.return==='error'){
            this.vmMsgError('该角色已存在');
          }
          this.GetKeleiList();
        });
      },
      Default(row){
        if(row.state===false){
          this.vmMsgWarning('系统默认，该角色信息不可修改');
        }
      },
      editKelei(row){
        this.state=row.state;
        if(row.state===false){
          this.vmMsgWarning('该角色信息不可修改');
          return;
        }
        this.editData=this.deepCopy(row);
        this.param.roleId=row.roleId;
        this.editKeilei=true;
      },
      deepCopy(data){return JSON.parse(JSON.stringify(data))},
      SaveeditKeilei(){
        if(this.editData.roleName===''){
          this.vmMsgWarning('请填写角色');
          return;
        }
        let param={
          roleId:this.editData.roleId,
          roleName:this.editData.roleName,
        };
        req.ajaxSend('/school/Bcinformation/role/type/upin','post',param,(res)=>{
          if(res.return===true){
            this.vmMsgSuccess('修改成功');
            this.editKeilei=false;
          }
          if(res.return===false){
            this.vmMsgError('修改失败');
          }
          if(res.return==='error'){
            this.vmMsgError('该角色已存在');
          }
          this.GetKeleiList();
        });
      },
      Delectlist(row){
        this.state=row.state;
        if(row.state===false){
          this.vmMsgWarning('该角色信息不可删除');
          return;
        }
        this.$confirm('确定删除该角色吗?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/Bcinformation/role/type/del','post',{roleId:row.roleId},(res)=>{
            if(res.return===true){
              this.vmMsgSuccess('删除成功');
            }
            if(res.return===false){
              this.vmMsgError('删除失败');

            }
            this.GetKeleiList();
          });
        }).catch(() => {});
      },
      sort(column){
        this.param.field=column.prop;
        this.param.order =column.order?column.order==='ascending'?'asc':'desc':'';
        this.GetKeleiList();
      },
    }
  }
</script>
<style lang="less" scoped>
  .KeleiInformation{
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }
  .KeleiInformation  .CreateProcess-top-btn{
    padding:.5rem 2.8rem ;
    border-radius: 1.1rem;
  }
  .KeleiInformation  .Field-save{
    position: absolute;
    bottom: 1rem;
    padding: .5rem 2.1rem;
    border-radius: 1.1rem;
  }
  .KeleiInformation-input .el-input__inner{
    border: none;
  }
  .KeleiInformation-input .el-input__inner:focus{
    outline:1px solid #96C3F9;
  }
</style>
