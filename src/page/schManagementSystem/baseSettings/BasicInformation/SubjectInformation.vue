<template>
  <div class="SubjectInformation">
    <el-col :span="24">
      <el-col :span="22">
        <h3>科目信息</h3>
      </el-col>
      <el-col :span="2">
        <!--<el-button type="primary" class="CreateProcess-top-btn">保存</el-button>-->
      </el-col>
    </el-col>
    <el-row>
      <el-col :span="18" class="alertsBtn" style="margin-top:1.8rem;">
        <el-button type="primary" style="padding:0.5rem 0.9rem;" @click="addSubject()">
          <i class="el-icon-plus"></i>
          <span>添加科目</span>
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
          @sort-change="sort">
          <el-table-column
            type="index"
            label="序号"
            align="center"
            width="100">
          </el-table-column>
          <el-table-column
            prop="subjectname"
            label="科目"
            align="center"
            sortable="custom">
          </el-table-column>
          <el-table-column
            prop="fullcredit"
            label="满分分数"
            align="center"
            sortable="custom">
          </el-table-column>
          <el-table-column
            label="操作"
            align="center">
            <template slot-scope="scope">
              <span style="color:#4da1ff;cursor: pointer;border-right:1px solid #d2d2d2;padding-right:.6rem" @click="editSubject(scope.row)">编辑</span>
              <span style="color:#ff6a6a;cursor: pointer;" @click="Delectlist(scope.row)">删除</span>
            </template>
          </el-table-column>
        </el-table>
      </el-row>
      <el-dialog title="添加科目" v-if="dialogaddSubject" :modal="false" :visible.sync="dialogaddSubject">
        <el-row>
          <el-col style="min-height: 20rem;">
            <el-col :span="24">
              <el-col :span="5" :offset="2" style="padding-top: .4rem;"><span style="color: red;">*</span> 科目：</el-col>
              <el-col :span="15">
                <el-input placeholder="请输入科目" v-model="param.subjectname"></el-input>
              </el-col>
            </el-col>
            <el-col :span="24" style="margin-top: 2rem;">
              <el-col :span="5" :offset="2" style="padding-top: .4rem;"><span style="color: red;">*</span> 满分分数：</el-col>
              <el-col :span="15">
                <el-input placeholder="请输入数字" type="number" v-model="param.fullcredit"></el-input>
              </el-col>
            </el-col>
            <el-col :span="2" :offset="11">
              <el-button type="primary" class="Field-save" @click="SaveAddSubject()">保存</el-button>
            </el-col>
          </el-col>
        </el-row>
      </el-dialog>
      <el-dialog title="编辑科目" v-if="dialogeditSubject" :modal="false" :visible.sync="dialogeditSubject">
        <el-row>
          <el-col style="min-height: 20rem;">
            <el-col :span="24">
              <el-col :span="5" :offset="2" style="padding-top: .4rem;"><span style="color: red;">*</span> 科目：</el-col>
              <el-col :span="15">
                <el-input placeholder="请输入科目" v-model="editData.subjectname"></el-input>
              </el-col>
            </el-col>
            <el-col :span="24" style="margin-top: 2rem;">
              <el-col :span="5" :offset="2" style="padding-top: .4rem;"><span style="color: red;">*</span> 满分分数：</el-col>
              <el-col :span="15">
                <el-input placeholder="请输入数字" type="number" v-model="editData.fullcredit"></el-input>
              </el-col>
            </el-col>
            <el-col :span="2" :offset="11">
              <el-button type="primary" class="Field-save" @click="SaveEditSubject()">保存</el-button>
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
        dialogaddSubject:false,
        dialogeditSubject:false,
        editData:{},
        param:{
          subjectid:'',
          subjectname:'',
          fullcredit:'',
          order:'',
          field:''
        }

      }
    },
    created(){
      this.GetSubjectList();
    },
    methods:{
      GetSubjectList(){
        this.isLoading=true;
        req.ajaxSend('/school/Bcinformation/subject/type/subjectfind','post',{order:this.param.order,field:this.param.field},(res)=>{
          if (res.status === -1) {
            this.tableData = [];
            this.isLoading = false;
            return;
          }
          this.tableData=res;
          this.isLoading=false;
        });
      },
      addSubject(){
        this.param.subjectname='';
        this.param.fullcredit='';
        this.dialogaddSubject=true;
      },
      editSubject(row){
        this.editData=this.deepCopy(row);
        this.param.subjectid=row.id;
        this.dialogeditSubject=true;
      },
      deepCopy(data){return JSON.parse(JSON.stringify(data))},
      SaveAddSubject(){
        if(this.param.subjectname===''){
          this.vmMsgWarning('请填写科目名');
          return;
        }
        if(!this.param.fullcredit || Number(this.param.fullcredit)<0){
          this.vmMsgWarning('请填写科目满分');
          return;
        }
        let param={
          gradeid:'',
          subjectname:this.param.subjectname,
          fullcredit:this.param.fullcredit,
        };
        req.ajaxSend('/school/Bcinformation/subject/type/subjectupdate','post',param,(res)=>{
          if(res.return===true){
            this.vmMsgSuccess('添加成功');
            this.dialogaddSubject=false;
          }
          if(res.return===false){
            this.vmMsgError('添加失败');
          }
          if(res.return==='error'){
            this.vmMsgError('该科目已存在');
          }
          this.GetSubjectList();
        });
      },
      SaveEditSubject(){
        if(this.editData.subjectname===''){
          this.vmMsgWarning('请填写科目名');
          return;
        }
        if(!this.editData.fullcredit || Number(this.editData.fullcredit)<0){
          this.vmMsgWarning('请填写科目满分');
          return;
        }
        let param={
          subjectid:this.editData.subjectid,
          subjectname:this.editData.subjectname,
          fullcredit:this.editData.fullcredit,
        };
        req.ajaxSend('/school/Bcinformation/subject/type/subjectupdate','post',param,(res)=>{
          if(res.return===true){
            this.vmMsgSuccess('修改成功');
            this.dialogeditSubject=false;
          }
          if(res.return===false){
            this.vmMsgError('修改失败');
          }
          if(res.return==='error'){
            this.vmMsgError('该科目已存在');
          }
          this.GetSubjectList();
        });
      },
      Delectlist(row){
        this.$confirm('确定删除该科目吗?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/Bcinformation/subject/type/subjectdel','post',{subjectid:row.subjectid},(res)=>{
            if(res.return===true){
              this.vmMsgSuccess('删除成功');
            }
            if(res.return===false){
              this.vmMsgError('删除失败');

            }
            this.GetSubjectList();
          });
        }).catch(() => {});
      },
      sort(column){
        this.param.field=column.prop;
        this.param.order = column.order?column.order==='ascending'?'asc':'desc':'';
        this.GetSubjectList();
      },
    }
  }
</script>
<style lang="less" scoped>
  .SubjectInformation{
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }
  .SubjectInformation  .CreateProcess-top-btn{
    padding:.5rem 2.8rem ;
    border-radius: 1.1rem;
  }
  .SubjectInformation  .Field-save{
    margin: 4.6rem 0 1.6rem 0;
    padding: .5rem 2.1rem;
    border-radius: 1.1rem;
  }
</style>
