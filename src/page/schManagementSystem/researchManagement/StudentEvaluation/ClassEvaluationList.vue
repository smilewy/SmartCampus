<template>
    <div class="ClassEvaluationList">
      <h3>班级评教名单</h3>
      <el-row>
       <el-col :span="24" class="Infor-head">
         <el-col :span="22">
           <el-form :inline="true" :model="form" class="demo-form-inline Infor-title clear_fix">
             <el-form-item label="评教名称：">
               <el-select v-model="form.planId" placeholder="请选择评教名称" @change="changegrade()">
                 <el-option
                   v-for="item in Planoptions"
                   :key="item.id"
                   :label="item.name"
                   :value="item.id">
                 </el-option>
               </el-select>
             </el-form-item>
             <el-form-item label="年级：" class="Infor-item">
               <el-select v-model="form.gradeId" placeholder="请选择年级" @change="changeclass()">
                 <el-option
                   v-for="item in Gradeoptions"
                   :key="item.gradeId"
                   :label="item.grade"
                   :value="item.gradeId">
                 </el-option>
               </el-select>
             </el-form-item>
             <el-form-item label="班级：" class="Infor-item">
               <el-select v-model="form.classId" placeholder="请选择班级">
                 <el-option
                   v-for="item in Classoptions"
                   :key="item.classId"
                   :label="item.class"
                   :value="item.classId">
                 </el-option>
               </el-select>
             </el-form-item>
           </el-form>
         </el-col>
         <el-col :span="2">
           <el-button type="primary" icon="el-icon-search" @click="getList()">查询</el-button>
         </el-col>
       </el-col>
        <el-col :span="24" style="margin-top: 2rem">
          <el-col :span="17" class="alertsBtn" style="margin-top: 0">
            <el-button class="delete" title="导出" @click="download()">
              <img class="delete_unactive"
                   src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png"
                   alt="">
              <img class="delete_active"
                   src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png"
                   alt="">
            </el-button>
            <el-button-group style="margin-left: 2.1rem">
              <el-button class="filt" title="复制" @click="operationData('copy')">
                <img class="filt_unactive"
                     src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png"
                     alt="">
                <img class="filt_active"
                     src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png"
                     alt="">
              </el-button>
              <el-button class="delete" title="打印" @click="operationData('print')">
                <img class="delete_unactive"
                     src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png"
                     alt="">
                <img class="delete_active"
                     src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png"
                     alt="">
              </el-button>
            </el-button-group>
          </el-col>
          <el-col :span="5" :offset="2" class="Infor-input-inner">
            <el-input style="border-radius:1rem" placeholder="请输入搜索内容" suffix-icon="el-icon-search" v-model="key" @change="handleIconClick"></el-input>
          </el-col>
        </el-col>
      </el-row>
      <el-row class="alertsList">
        <el-table
          :data="tableData"
          style="width: 100%"
          v-loading.body="isLoading"
          element-loading-text="拼命加载中...">
          <el-table-column
            prop="serialNumber"
            label="班级序号"
            align="center">
          </el-table-column>
          <el-table-column
            prop="name"
            label="姓名"
            align="center">
          </el-table-column>
          <el-table-column
            prop=""
            label="评教状态"
            align="center">
            <template  slot-scope="scope">
              <span class="Notteaching" v-if="scope.row.joined==='0'">未评教</span>
              <span class="teaching"  v-if="scope.row.joined==='1'">已评教</span>
            </template>
          </el-table-column>
        </el-table>
      </el-row>
      <el-row class="pageAlerts">
        <el-pagination
          @current-change="handleCurrentChange"
          :current-page.sync="currentPage"
          :page-size="10"
          layout="prev, pager, next, jumper"
          :total="total">
        </el-pagination>
      </el-row>
    </div>
</template>
<script>
  import req from '@/assets/js/common'
  import formatdata from '@/assets/js/date'
    export default{
        data(){
            return {
              form: {
                planId:'',
                gradeId:'',
                classId:'',
              },
              key: '',
              tableData:[],
              isLoading:false,
              currentPage: 1,
              pageALl: 10,
              total:0,
              Status:'',
              Planoptions:[],
              Gradeoptions:[],
              Classoptions:[],
            }
        },
      created(){
        this.getclass();
      },
      methods:{
        operationData(type){
          if(!this.tableData.length||this.Status==8){
            this.vmMsgWarning( '暂无数据' ); return;
          }
          let sAy = [], hdData = {
            serialNumber: '班级序号',
            name: '姓名',
            Newjoined: '评教状态'
          };
          sAy.push(hdData);
          for (let obj of this.tableData) {
            let d = {};
            for (let name in hdData) {
              if(name==='Newjoined'){
                if(obj.joined==0){
                  obj[name]='未评教'
                }else if(obj.joined==1){
                  obj[name]='已评教'
                }
              }
              d[name] = obj[name] || ''||0;
            }
            sAy.push(d);
          }
          if (type === 'copy') {
            req.copyTableData('.ClassEvaluationList', sAy);
          } else {
            req.lodop(sAy);
          }
        },
        download(){
          if(!this.tableData.length||this.Status==8){
            this.vmMsgWarning( '暂无数据' ); return;
          }
          let classId=this.form.classId,
              evaluateId=this.form.planId;
          req.downloadFile('.ClassEvaluationList','/school/StudentEvaluate/teachEvaluate?export=ensure&classId='+classId+'&evaluateId='+evaluateId,'post');
        },
        getclass(){
          let param={
            func:'getClass'
          };
          req.ajaxSend('/school/StudentEvaluate/common','post',param,(res)=>{
            this.Status=res.statu;
            if(res.statu==8){
              this.vmMsgWarning( '不是班主任或年级主任' ); return;
            }
            this.Planoptions=res;
          });
        },
        changegrade(){
          for(let obj of this.Planoptions){
            if(obj.id===this.form.planId){
              this.Gradeoptions=obj.child;
            }
          }
          this.form.gradeId='';
          this.form.classId='';
        },
        changeclass(){
          for(let obj of this.Gradeoptions){
            if(obj.gradeId===this.form.gradeId){
              this.Classoptions=obj.child;
            }
          }
          this.form.classId='';
        },
        getList(page){
          if(this.form.planId===''){
            this.vmMsgWarning( '请选择评教名称' ); return;
          }
          if(this.form.gradeId===''){
            this.vmMsgWarning( '请选择年级' ); return;
          }
          if(this.form.classId===''){
            this.vmMsgWarning( '请选择班级' ); return;
          }
          if(page!==this.currentPage){
            this.currentPage=page;
          }
          this.isLoading=true;
          let param={
            page:this.currentPage,
            count:10,
            key:this.key,
            classId:this.form.classId,
            evaluateId:this.form.planId
          };
          req.ajaxSend('/school/StudentEvaluate/teachEvaluate','post',param,(res)=>{
            if (res.status === -1) {
              this.tableData = [];
              this.isLoading = false;
              return;
            }
            this.tableData=res.data;
            this.total = res.total;
            this.isLoading=false;
          });
        },
        handleCurrentChange(val) {
          this.currentPage = val;
          this.getList(val);
        },
        handleIconClick(){
          this.getList();
        },
      }
    }
</script>
<style lang="less" scoped>
  .ClassEvaluationList{
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
    .Notteaching{
      color:#ff6a6a;
    }
    .teaching{
      color:#4da1ff;
    }
    .Infor-input-inner .el-input__inner{
      border-radius: 1.1rem;
    }
    .Infor-head{
      margin-top: 2rem;
      border-bottom: 1px solid #d2d2d2;
    }
  }
</style>
