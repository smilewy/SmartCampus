<template>
  <div class="ProfessionalInformation">
    <el-col :span="24">
      <el-col :span="22">
        <h3>专业信息</h3>
      </el-col>
      <el-col :span="2">
        <!--<el-button type="primary" class="CreateProcess-top-btn">保存</el-button>-->
      </el-col>
    </el-col>
    <el-row>
      <el-col :span="18" class="alertsBtn" style="margin-top:1.8rem;">
        <el-button type="primary" style="padding:0.5rem 0.9rem;" @click="addmajor()">
          <i class="el-icon-plus"></i>
          <span>添加专业</span>
        </el-button>
      </el-col>
    </el-row>
    <el-col :span="24">
      <el-row class="alertsList">
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
            width="100">
          </el-table-column>
          <el-table-column
            prop="branch"
            label="科类"
            align="center"
            sortable="custom">
          </el-table-column>
          <el-table-column
            prop="majorname"
            label="专业名称"
            align="center"
            sortable="custom">
          </el-table-column>
          <el-table-column
            label="操作"
            align="center">
            <template slot-scope="scope">
              <span style="color:#4da1ff;cursor: pointer;border-right:1px solid #d2d2d2;padding-right:.6rem;" v-if="scope.row.state===true" @click="Editdialog(scope.row)">编辑</span>
              <span style="color:#ff6a6a;cursor: pointer;padding-left:.6rem;" v-if="scope.row.state===true" @click="Delectlist(scope.row)">删除</span>
            </template>
          </el-table-column>
        </el-table>
      </el-row>
      <el-dialog title="添加专业" v-if="dialogTableVisibleother" :modal="false" :visible.sync="dialogTableVisibleother">
        <el-row>
          <el-col style="min-height: 20rem;">
            <el-col :span="24">
              <el-col :span="5" :offset="2" style="padding-top: .4rem;"><span style="color: red;">*</span> 科类：</el-col>
              <el-col :span="15">
                <el-select v-model="param.branchid" style="width: 100%;" placeholder="请选择">
                  <el-option
                    v-for="item in options"
                    :key="item.branchid"
                    :label="item.branch"
                    :value="item.branchid">
                  </el-option>
                </el-select>
              </el-col>
            </el-col>
            <el-col :span="24" style="margin-top: 2rem;">
              <el-col :span="5" :offset="2" style="padding-top: .4rem;"><span style="color: red;">*</span> 专业名称：</el-col>
              <el-col :span="15">
                <el-input placeholder="请输入" v-model="param.majorname"></el-input>
              </el-col>
            </el-col>
            <el-col :span="2" :offset="11">
              <el-button type="primary" class="Field-save" @click="SaveaddMajor()">保存</el-button>
            </el-col>
          </el-col>
        </el-row>
      </el-dialog>
      <el-dialog title="编辑专业" v-if="dialogTableVisible" :modal="false" :visible.sync="dialogTableVisible">
        <el-row>
          <el-col style="min-height: 20rem;">
            <el-col :span="24">
              <el-col :span="5" :offset="2" style="padding-top: .4rem;"><span style="color: red;">*</span> 科类：</el-col>
              <el-col :span="15">
                <el-select v-model="editData.branchid" style="width: 100%;" placeholder="请选择">
                  <el-option
                    v-for="item in options"
                    :key="item.branchid"
                    :label="item.branch"
                    :value="item.branchid">
                  </el-option>
                </el-select>
              </el-col>
            </el-col>
            <el-col :span="24" style="margin-top: 2rem;">
              <el-col :span="5" :offset="2" style="padding-top: .4rem;"><span style="color: red;">*</span> 专业名称：</el-col>
              <el-col :span="15">
                <el-input placeholder="请输入" v-model="editData.majorname"></el-input>
              </el-col>
            </el-col>
            <el-col :span="2" :offset="11">
              <el-button type="primary" class="Field-save" @click="SaveEditMajor()">保存</el-button>
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
        dialogTableVisible:false,
        editData:{},
        param:{
          majorid:'',
          branchid:'',
          majorname:'',
          order:'',
          field:''
        },
        options: [],
        state:''
      }
    },
    created(){
      this.GetmajorList();
      req.ajaxSend('/school/Bcinformation/major/type/branchfind','post',{},(res)=>{
        this.options=res;
      });
    },
    methods:{
      Default(row){
        if(row.state===false){
          this.vmMsgWarning('该专业信息不可修改');
        }
      },
      GetmajorList(){
        this.isLoading=true;
        req.ajaxSend('/school/Bcinformation/major/type/majorselect','post',{order:this.param.order,field:this.param.field},(res)=>{
          if (res.status === -1) {
            this.tableData = [];
            this.isLoading = false;
            return;
          }
          this.tableData=res.data;
          this.isLoading=false;
        });
      },
      addmajor(){
        this.param.branchid='';
        this.param.majorname='';
        this.dialogTableVisibleother=true;
      },
      SaveaddMajor(){
        if(this.param.branchid===''){
          this.vmMsgWarning('请选择科类');
          return;
        }
        if(this.param.majorname===''){
          this.vmMsgWarning('请填写专业名称');
          return;
        }
        let param={
          majorid:'',
          majorname:this.param.majorname,
          branchid:this.param.branchid
        };
        req.ajaxSend('/school/Bcinformation/major/type/majorupdate','post',param,(res)=>{
          if(res.return===true){
            this.vmMsgSuccess('添加成功');
            this.dialogTableVisibleother=false;
          }
          if(res.return===false){
            this.vmMsgError('添加失败');
          }
          if(res.return==='error'){
            this.vmMsgError('该专业已存在');
          }
          this.GetmajorList();
        });
      },
      Editdialog(row){
        this.state=row.state;
        if(row.state===false){
          this.vmMsgWarning('该专业信息不可修改');
          return;
        }
        this.editData=this.deepCopy(row);
        this.$set(this.editData,'branchid',this.getIdfromName(row.branch));
        this.param.majorid=row.majorid;
        this.dialogTableVisible=true;
      },
      deepCopy(data){return JSON.parse(JSON.stringify(data))},
      SaveEditMajor(){
        if(this.editData.branchid===''){
          this.vmMsgWarning('请选择科类');
          return;
        }
        if(this.editData.majorname===''){
          this.vmMsgWarning('请填写专业名称');
          return;
        }
        let param={
          majorid:this.editData.majorid,
          majorname:this.editData.majorname,
          branchid:this.editData.branchid
        };
        req.ajaxSend('/school/Bcinformation/major/type/majorupdate','post',param,(res)=>{
          if(res.return===true){
            this.vmMsgSuccess('修改成功');
            this.dialogTableVisible=false;
          }
          if(res.return===false){
            this.vmMsgError('修改失败');
          }
          if(res.return==='error'){
            this.vmMsgError('该专业已存在');
          }
          this.GetmajorList();
        });
      },
      Delectlist(row){
        this.state=row.state;
        this.$confirm('确定删除该专业吗?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/Bcinformation/major/type/majordelete','post',{majorid:row.majorid},(res)=>{
            if(res.return===true){
              this.vmMsgSuccess('删除成功');
            }
            if(res.return===false){
              this.vmMsgError('删除失败');

            }
            this.GetmajorList();
          });
        }).catch(() => {});
      },
      sort(column){
        this.param.field=column.prop;
        this.param.order =column.order?column.order==='ascending'?'asc':'desc':'';
        this.GetmajorList();
      },
      getIdfromName(name){
        return this.options.filter(val=>val.branch===name)[0].branchid;
      }
    }
  }
</script>
<style lang="less" scoped>
  .ProfessionalInformation{
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }
  .ProfessionalInformation  .CreateProcess-top-btn{
    padding:.5rem 2.8rem ;
    border-radius: 1.1rem;
  }
  .ProfessionalInformation  .Field-save{
    position: absolute;
    bottom: 1.4rem;
    padding: .5rem 2.1rem;
    border-radius: 1.1rem;
  }
</style>
