<template>
  <div class="GradeInformation g-classSchedule">
      <header class="g-timeHeader g-flexStartRow">
        <div class="g-headerButtonGroup">
            <h2>年级信息</h2>
        </div>
        <ul class="changeRouter selfCenter clear_fix">
            <li class="activeCss" data-msg="echartsStatistic" @click="changeRouterClick">在校年级</li>
            <li class="normalCss" data-msg="replyDetail" @click="changeRouterClick">非在校年级</li>
        </ul>
      </header>
    <el-row>
      <el-col :span="3" class="alertsBtn" style="margin-top:1.8rem;">
        <el-button type="primary" style="padding:0.5rem 0.9rem;" @click="addGrade()">
          <i class="el-icon-plus"></i>
          <span>添加年级</span>
        </el-button>
      </el-col>
      <el-col :span="18" style="margin-top:2.8rem;">
        注：年级代码为+入学年份，例如：高中请填写【G2015】，初中请填写【C2015】，小学请填写【X2015】。
      </el-col>
    </el-row>
    <el-row class="alertsList GradeInformation-top">
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
          prop="code"
          label="年级代码"
          align="center"
          sortable="custom">
        </el-table-column>
        <el-table-column
          prop="znName"
          label="年级名称"
          align="center"
          sortable="custom">
        </el-table-column>
        <el-table-column
          label="操作"
          align="center">
          <template slot-scope="scope">
            <span style="color:#4da1ff;cursor: pointer;border-right:1px solid #d2d2d2;padding-right:.6rem;" v-show="!isSchoolGrade" @click="editGrade(scope.row)">编辑</span>
            <span style="color:#ff6a6a;cursor: pointer;padding-left: .6rem;" @click="Delectlist(scope.row)">删除</span>
          </template>
        </el-table-column>
      </el-table>
    </el-row>
    <el-dialog title="添加年级" v-if="dialogGrade" :modal="false" :visible.sync="dialogGrade">
      <el-row>
        <el-col style="min-height: 26rem;">
          <el-col :span="24">
            <el-col :span="3" :offset="1" style="padding-top: .4rem;"><span style="color: red;">*</span> 年级代码：</el-col>
            <el-col :span="7">
              <el-input placeholder="如：X2018" v-model="param.code"></el-input>
            </el-col>
            <!--<el-col :span="3" :offset="2" style="padding-top: .4rem;"><span style="color: red;">*</span> 年级名称：</el-col>-->
            <!--<el-col :span="7">-->
            <!--<el-input placeholder="请输入" v-model="param.znName"></el-input>-->
            <!--</el-col>-->
          </el-col>
          <el-col :span="24" style="margin-top: 2rem;">
            <el-col :span="5" :offset="1">所有年级自动升级：</el-col>
            <el-col :span="5">
              <el-radio v-model="param.Gradeup" :label="1">是</el-radio>
              <el-radio v-model="param.Gradeup" :label="0">否</el-radio>
            </el-col>
            <el-col :span="5" :offset="2">自动毕业最高年级：</el-col>
            <el-col :span="5">
              <el-radio v-model="param.Grademax" :label="1">是</el-radio>
              <el-radio v-model="param.Grademax" :label="0">否</el-radio>
            </el-col>
          </el-col>
          <el-col :span="24" :offset="1" style="margin-top:4rem;line-height:2rem;color: #B4B4B4">
            <p>说明：</p>
            <p>1.年级代码为代号+入学年份，高中请填写如：G2015，初中请填写如：C2015，小学请填写如：X2015；</p>
            <p>2.所有年级自动升级：即当前以创建的年级会自动升一级，如当前G2015高一，会自动升级成G2015高二；</p>
            <p>3.自动毕业最高年级：分别将X、C、G三个字母为首的年级代码为依据，自动毕业最高年级。</p>
          </el-col>
          <el-col :span="2" :offset="11">
            <el-button type="primary" class="Field-save" @click="SaveaddGrade">保存</el-button>
          </el-col>
        </el-col>
      </el-row>
    </el-dialog>
    <el-dialog title="修改年级信息" v-if="dialogeditGrade" :modal="false" :visible.sync="dialogeditGrade">
      <el-row>
        <el-col style="min-height: 26rem;">
          <el-col :span="24">
            <el-col :span="3" :offset="1" style="padding-top: .4rem;"><span style="color: red;">*</span> 年级代码：</el-col>
            <el-col :span="7">
              <el-input placeholder="如：X2018" v-model="editData.code"></el-input>
            </el-col>
            <!--<el-col :span="3" :offset="2" style="padding-top: .4rem;"><span style="color: red;">*</span> 年级名称：</el-col>-->
            <!--<el-col :span="7">-->
            <!--<el-input placeholder="请输入" v-model="editData.znName"></el-input>-->
            <!--</el-col>-->
          </el-col>
          <el-col :span="24" style="margin-top: 2rem;">
            <el-col :span="5" :offset="1">所有年级自动升级：</el-col>
            <el-col :span="5">
              <el-radio-group v-model="editData.autoupdate">
                <el-radio :label="1" disabled>是</el-radio>
                <el-radio :label="0" disabled>否</el-radio>
              </el-radio-group>
            </el-col>
            <el-col :span="5" :offset="2">自动毕业最高年级：</el-col>
            <el-col :span="5">
              <el-radio-group v-model="editData.highestgrade">
                <el-radio :label="1" disabled>是</el-radio>
                <el-radio :label="0" disabled>否</el-radio>
              </el-radio-group>
            </el-col>
          </el-col>
          <el-col :span="24" :offset="1" style="margin-top:4rem;line-height:2rem;color: #B4B4B4">
            <p>说明：</p>
            <p>1.年级代码为代号+入学年份，高中请填写如：G2015，初中请填写如：C2015，小学请填写如：X2015；</p>
            <p>2.所有年级自动升级：即当前以创建的年级会自动升一级，如当前G2015高一，会自动升级成G2015高二；</p>
            <p>3.自动毕业最高年级：分别将X、C、G三个字母为首的年级代码为依据，自动毕业最高年级。</p>
          </el-col>
          <el-col :span="2" :offset="11">
            <el-button type="primary" class="Field-save" @click="SaveeditGrade">保存</el-button>
          </el-col>
        </el-col>
      </el-row>
    </el-dialog>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  export default{
    data(){
      return{
        tableData:[],
        dialogeditGrade:false,
        dialogGrade:false,
        editData:{},
        gradeid:'',
        param:{
          code:'',
          znName:'',
          Gradeup:1,
          Grademax:0,
          order:'',
          field:''
        },
        state:'',
        isSchoolGrade: false
      }
    },
    created(){
      this.GetGradeList();
    },
    methods:{
      Default(row){
//        if(row.state===false){
//          this.$message('该年级信息不可修改');
//        }
      },
      changeRouterClick(event){
            const e=$(event.currentTarget);
            e.addClass('activeCss');
            e.removeClass('normalCss');
            e.siblings().addClass('normalCss');
            e.siblings().removeClass('activeCss');
            this.isSchoolGrade = !this.isSchoolGrade;
            this.GetGradeList();
      },
      GetGradeList(){
        this.isLoading=true;
        req.ajaxSend('/school/Bcinformation/grade/type/gradefind','post',{order:this.param.order,field:this.param.field, typename: !this.isSchoolGrade ? 'on' : 'off'},(res)=>{
          if (res.status === -1) {
            this.tableData = [];
            this.isLoading = false;
            return;
          }
          this.tableData=res;
          this.isLoading=false;
        });
      },
      SaveaddGrade(){
        if(!/^[G|X|C]\d{4}$/.test(this.param.code)){
          this.vmMsgWarning('请填写正确年级代码');
          return;
        }
//        if(!this.param.znName){
//          this.$message({
//            type:'info',
//            message:'请填写年级名称',
//            showClose: true,
//          });
//          return;
//        }
        let param={
          gradeid:'',
          code:this.param.code,
          znName:this.param.znName,
          autoupdate:this.param.Gradeup===1?1:0,
          highestgrade:this.param.Grademax===1?1:0,
        };
        req.ajaxSend('/school/Bcinformation/grade/type/gradeupin','post',param,(res)=>{
          if(res.return===true){
            this.dialogGrade=false;
            this.vmMsgSuccess('添加成功');
          }
          if(res.return===false){
            this.vmMsgError(res.msg);
          }
          if(res.return=='error'){
            this.vmMsgError('年级代码已存在');
          }
          this.GetGradeList();
        });
      },
      SaveeditGrade(){
        if(!/^[G|X|C]\d{4}$/.test(this.editData.code)){
          this.vmMsgWarning('请填写正确年级代码');
          return;
        }
//        if(!this.editData.znName){
//          this.$message({
//            type:'info',
//            message:'请填写年级名称',
//            showClose: true,
//          });
//          return;
//        }
        let param={
          gradeid:this.gradeid,
          code:this.editData.code,
          znName:this.editData.znName,
        };
        req.ajaxSend('/school/Bcinformation/grade/type/gradeupin','post',param,(res)=>{
          if(res.return===true){
            this.dialogeditGrade=false;
            this.vmMsgSuccess('修改成功');
          }
          if(res.return===false){
            this.vmMsgError(res.msg);
          }
          if(res.return==='error'){
            this.vmMsgError('年级代码已存在');
          }
          this.GetGradeList();
        });
      },
      Delectlist(row){
        this.state=row.state;
        if(row.state===false){
          this.vmMsgWarning('该年级信息不可删除');
          return;
        }
        this.$confirm('确定删除年级吗?（年级为基础数据，删除后将影响整个系统关于该年级的数据，请确认清楚后再进行删除）', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/Bcinformation/grade/type/gradedelete','post',{gradeid:row.gradeid, typename: !this.isSchoolGrade ? 'on' : 'off'},(res)=>{
            if(res.return===true){
              this.vmMsgSuccess('删除成功');
            }
            if(res.return===false){
              this.vmMsgError('删除失败，该年级下有学生，不能删除');
            }
            this.GetGradeList();
          });
        }).catch(() => {});
      },
      sort(column){
        this.param.field=column.prop;
        this.param.order =column.order?column.order==='ascending'?'asc':'desc':'';
        this.GetGradeList();
      },
      addGrade(){
        this.param.code='';
        this.param.znName='';
        this.param.Gradeup=1;
        this.param.Grademax=0;
        this.dialogGrade=true;
      },
      editGrade(row){
        this.state=row.state;
        if(row.state===false){
          this.vmMsgWarning('该年级信息不可修改');
          return;
        }
        this.editData=this.deepCopy(row);
        this.editData.autoupdate=this.editData.autoupdate==='1'?1:0;
        this.editData.highestgrade=this.editData.highestgrade==='1'?1:0;
        this.gradeid=row.gradeid;
        this.dialogeditGrade=true;
      },
      deepCopy(data){return JSON.parse(JSON.stringify(data))},
    }
  }
</script>
<style lang="less" scoped>
    @import '../../../../style/style';
    @import '../../../../style/arrangeClasses/classSchedule';
    @import '../../../../style/arrangeClasses/arrangeClasses.css';

  .GradeInformation{
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
    .Field-save{
      margin: 3.6rem 0 1.6rem 0;
      padding: .5rem 2.1rem;
      border-radius: 1.1rem;
    }
  }
  .GradeInformation .el-row{
      margin-bottom: 2.8rem
  }
  .alertsBtn{
      width: 12.5% !important;
  }
</style>
<style>
  .GradeInformation .el-radio__input.is-checked .el-radio__inner{
    border-color: #20a0ff;
    background: #20a0ff;
  }
</style>
