<template>
  <div class="SchoolYear">
    <el-col :span="24">
      <el-col :span="22">
        <h3>学年学期</h3>
      </el-col>
      <el-col :span="2">
        <!--<el-button type="primary" class="CreateProcess-top-btn">保存</el-button>-->
      </el-col>
    </el-col>
    <el-row>
      <el-col :span="18" class="alertsBtn" style="margin-top:1.8rem;">
        <el-button type="primary" style="padding:0.5rem 0.9rem;" @click="addField()">
          <!--<img src="./../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_add_highlight.png">-->
          <i class="el-icon-plus"></i>
          <span>添加学年学期</span>
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
            prop="yearname"
            label="学年"
            align="center"
            sortable="custom">
          </el-table-column>
          <el-table-column
            prop="term"
            label="学期"
            align="center"
            sortable="custom">
          </el-table-column>
          <el-table-column
            prop=""
            label="使用日期"
            align="center"
            sortable="custom">
            <template slot-scope="scope">
              <span>{{scope.row.startTime}}至{{scope.row.endTime}}</span>
            </template>
          </el-table-column>
          <el-table-column
            prop="lastRecordTime"
            label="更新时间"
            align="center"
            sortable="custom">
          </el-table-column>
          <el-table-column
            prop="atPresent"
            label="当前学年学期"
            align="center">
            <template slot-scope="scope">
              <span style="color:#13B5B1;font-weight: bold" v-if="scope.row.atPresent===true">
                <img src="./../../../../assets/img/schManagementSystem/baseSettings/BasicInformaton/icon_right.png" alt="">
              </span>
            </template>
          </el-table-column>
          <el-table-column
            label="操作"
            align="center">
            <template slot-scope="scope">
              <span style="color:#4da1ff;cursor: pointer;border-right:1px solid #d2d2d2;padding-right:.6rem;" @click="Editdialog(scope.row)">编辑</span>
              <span style="color:#ff6a6a;cursor: pointer;padding-left:.6rem;" @click="Delectlist(scope.row)">删除</span>
            </template>
          </el-table-column>
        </el-table>
      </el-row>
      <el-dialog title="添加学年学期" v-if="dialogTableVisibleother" :modal="false" :visible.sync="dialogTableVisibleother">
        <el-row>
          <el-form ref="form" label-position="right" label-width="75px" :model="param">
            <el-form-item label="学年:" class="red-span">
              <el-input placeholder="如：2016-2017学年" v-model="param.yearname"></el-input>
            </el-form-item>
            <el-form-item label="学期:" class="red-span">
              <el-input placeholder="如：第一学期" v-model="param.term"></el-input>
            </el-form-item>
            <el-form-item label="日期:" class="red-span">
              <el-col :span="11">
                <el-date-picker type="date" :picker-options="pickerOptions0" placeholder="yyyy-mm-dd" v-model="param.startTime" style="width: 100%;"></el-date-picker>
              </el-col>
              <el-col class="line" :span="1" :offset="1">-</el-col>
              <el-col :span="11">
                <el-date-picker type="date" :picker-options="pickerOptions1" placeholder="yyyy-mm-dd" v-model="param.endTime" style="width: 100%;"></el-date-picker>
              </el-col>
            </el-form-item>
          </el-form>
          <el-col :span="1" :offset="10">
            <el-button type="primary" class="field-save" @click="SaveaddSchoolYear()">保存</el-button>
          </el-col>
        </el-row>
      </el-dialog>
      <el-dialog title="编辑学年学期" v-if="dialogTableVisible" :modal="false" :visible.sync="dialogTableVisible">
       <el-row>
         <el-form ref="form" label-position="right" label-width="75px" :model="editData">
           <el-form-item label="学年:" class="red-span">
             <el-input placeholder="如：2016-2017学年" v-model="editData.yearname"></el-input>
           </el-form-item>
           <el-form-item label="学期:" class="red-span">
             <el-input placeholder="如：第一学期" v-model="editData.term"></el-input>
           </el-form-item>
           <el-form-item label="日期:" class="red-span">
             <el-col :span="11">
               <el-date-picker type="date" :picker-options="pickerOptions0" placeholder="yyyy-mm-dd" v-model="editData.startTime" style="width: 100%;"></el-date-picker>
             </el-col>
             <el-col class="line" :span="1" :offset="1">-</el-col>
             <el-col :span="11">
               <el-date-picker type="date" :picker-options="pickerOptions1" placeholder="yyyy-mm-dd" v-model="editData.endTime" style="width: 100%;"></el-date-picker>
             </el-col>
           </el-form-item>
         </el-form>
         <el-col :span="1" :offset="10">
           <el-button type="primary" class="field-save" @click="SaveEditSchoolYear()">保存</el-button>
         </el-col>
       </el-row>
      </el-dialog>
    </el-col>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  import formatdata from '@/assets/js/date'
  export default{
    data(){
      return{
        tableData:[],
        dialogTableVisibleother:false,
        dialogTableVisible:false,
        pickerOptions0: {
          disabledDate:(time)=> {
//            if(this.param.endTime){
//              return time.getTime() > this.endvalue;
//            }
          }
        },
        pickerOptions1: {
          disabledDate:(time)=> {
//            if(this.param.startTime){
//              return time.getTime() < this.startvalue;
//            }
          }
        },
        param:{
          yearid:'',
          yearname:'',
          startTime:'',
          endTime:'',
          term:'',
          order:'',
          field:''
        },
        editData:{},
      }
    },
    created(){
      this.GetSchoolYearList();
    },
    methods:{
      GetSchoolYearList(){
        this.isLoading=true;
        req.ajaxSend('/school/Bcinformation/schoolyear/type/schoolyearselect','post',{order:this.param.order,field:this.param.field},(res)=>{
          if (res.status === -1) {
            this.tableData = [];
            this.isLoading = false;
            return;
          }
          this.pickerOptions0.disabledDate = function (time) {
            let disabled = false;
            res.forEach(val=>{
              let timeStr = formatdata.format(time,'yyyy-MM-dd');
              if(timeStr>=val.startTime&&timeStr<=val.endTime){
                disabled = true;
              }
            });
            return disabled;
          };
          this.pickerOptions1.disabledDate = function (time) {
            let disabled = false;
            res.forEach(val=>{
              let timeStr = formatdata.format(time,'yyyy-MM-dd');
              if(timeStr>=val.startTime&&timeStr<=val.endTime){
                disabled = true;
              }
            });
            return disabled;
          };
          this.tableData=res;
          this.isLoading=false;
        });
      },
      addField(){
        this.param.yearname='';
        this.param.term='';
        this.param.startTime='';
        this.param.endTime='';
        this.dialogTableVisibleother=true;
      },
      SaveaddSchoolYear(){
        if(this.param.yearname===''){
          this.vmMsgWarning('请填写学年');
          return;
        }
        if(this.param.term===''){
          this.vmMsgWarning('请填写学期');
          return;
        }
        if(this.param.startTime===''){
          this.vmMsgWarning('请填写开始日期');
          return;
        }
        if(this.param.endTime===''){
          this.vmMsgWarning('请填写结束日期');
          return;
        }
        if(this.param.startTime >= this.param.endTime){
          this.vmMsgWarning('结束日期必须大于开始日期');
          return;
        }
        this.param.startTime = formatdata.format(this.param.startTime,'yyyy-MM-dd HH:mm:ss');
        this.param.endTime = formatdata.format(this.param.endTime,'yyyy-MM-dd HH:mm:ss');
        this.param.yearid='';
        req.ajaxSend('/school/Bcinformation/schoolyear/type/yearupdate','post',this.param,(res)=>{
          if(res.return===true){
            this.vmMsgSuccess('添加成功');
            this.dialogTableVisibleother=false;
          }
          if(res.return===false){
            this.vmMsgError('添加失败');
          }
          if(res.return==='error'){
            this.vmMsgError('时间重叠');
          }
          this.GetSchoolYearList();
        });
      },
      Editdialog(row){
        this.editData=this.deepCopy(row);
        this.editData.startTime=new Date(row.startTime);
        this.editData.endTime=new Date(row.endTime);
        this.param.yearid=row.yearid;
        this.dialogTableVisible=true;
      },
      deepCopy(data){return JSON.parse(JSON.stringify(data))},
      SaveEditSchoolYear(){
        if(this.editData.yearname===''){
          this.vmMsgWarning('请填写学年');
          return;
        }
        if(this.editData.term===''){
          this.vmMsgWarning('请填写学期');
          return;
        }
        if(this.editData.startTime===''){
          this.vmMsgWarning('请填写开始日期');
          return;
        }
        if(this.editData.endTime===''){
          this.vmMsgWarning('请填写结束日期');
          return;
        }
        if(this.editData.startTime >= this.editData.endTime){
          this.vmMsgWarning('结束日期必须大于开始日期');
          return;
        }
        this.editData.startTime = formatdata.format(this.editData.startTime,'yyyy-MM-dd HH:mm:ss');
        this.editData.endTime = formatdata.format(this.editData.endTime,'yyyy-MM-dd HH:mm:ss');
        let param={
          yearid:this.editData.yearid,
          yearname:this.editData.yearname,
          endTime:this.editData.endTime,
          startTime:this.editData.startTime,
          term:this.editData.term,
        };
        req.ajaxSend('/school/Bcinformation/schoolyear/type/yearupdate','post',param,(res)=>{
          if(res.return===true){
            this.vmMsgSuccess('修改成功');
            this.dialogTableVisible=false;
          }
          if(res.return===false){
            this.vmMsgError('修改失败');
          }
          if(res.return==='error'){
            this.vmMsgError('时间重叠');
          }
          this.GetSchoolYearList();
        });
      },
      Delectlist(row){
        this.$confirm('确定删除该学年学期吗?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/Bcinformation/schoolyear/type/yeardel','post',{yearid:row.yearid},(res)=>{
            if(res.return===true){
              this.vmMsgSuccess('删除成功');
            }
            if(res.return===false){
              this.vmMsgError('删除失败');

            }
            this.GetSchoolYearList();
          });
        }).catch(() => {});
      },
      sort(column){
        this.param.field=column.prop;
        this.param.order =column.order?column.order==='ascending'?'asc':'desc':'';
        this.GetSchoolYearList();
      },
    }
  }
</script>
<style lang="less" scoped>
  .SchoolYear{
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
    .red-span:before{
      position: relative;
      content: '*';
      color: red;
      top:1.8rem;
      left:1.4rem;
    }
  }
  .SchoolYear  .CreateProcess-top-btn{
    padding:.5rem 2.8rem ;
    border-radius: 1.1rem;
  }
  .SchoolYear .field-save{
    margin: 2rem 0 1.4rem 0;
    padding: .5rem 2.1rem;
    border-radius: 1.1rem;
  }
</style>
