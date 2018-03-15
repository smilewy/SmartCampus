<template>
  <div class="SubjectGradeStatistics">
    <h3>科目年级统计</h3>
    <el-row>
      <el-col :span="24" class="Infor-head">
        <el-col :span="22">
          <el-form :inline="true" :model="form" class="demo-form-inline Infor-title clear_fix">
            <el-form-item label="评教名称：">
              <el-select v-model="form.planId" placeholder="请选择评教名称" @change="getgrade()">
                <el-option
                  v-for="item in Planoptions"
                  :key="item.id"
                  :label="item.name"
                  :value="item.id">
                </el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="科目：" class="Infor-item">
              <el-select v-model="form.subjectId" placeholder="请选择科目">
                <el-option
                  v-for="item in Subjectoptions"
                  :key="item.id"
                  :label="item.name"
                  :value="item.id">
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
        <el-col :span="5" :offset="2" class="Infor-input-inner">
          <el-input style="border-radius:1rem" placeholder="请输入搜索内容" suffix-icon="el-icon-search" v-model="key" @change="handleIconClick"></el-input>
        </el-col>
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
            prop="grade"
            label="年级"
            align="center">
          </el-table-column>
          <el-table-column
            prop="total"
            label="参评人数"
            align="center">
          </el-table-column>
          <el-table-column
            v-for="colume in columes"
            :key="colume.prop"
            :label="colume.label"
            align="center">
            <template slot-scope="scope">
              <span>{{scope.row[colume.prop]||0}}</span>
            </template>
          </el-table-column>
        </el-table>
      </el-row>
    </el-col>
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
          subjectId:'',
        },
        key: '',
        tableData:[],
        Planoptions:[],
        Subjectoptions:[],
        columes:[],
        isLoading:false,
        currentPage: 1,
        pageALl: 10,
        total:0,
      }
    },
    created(){
      this.getclass();
    },
    methods:{
      operationData(type){
        if(!this.tableData.length){
          this.vmMsgWarning( '暂无数据' ); return;
        }
        let sAy = [], hdData = {
          grade: '年级',
          total: '参评人数'
        };
        this.columes.forEach((val)=>{
          hdData[val.prop]=val.label;
        });
        sAy.push(hdData);
        for (let obj of this.tableData) {
          let d = {};
          for (let name in hdData) {
            d[name] = obj[name] || ''||0;
          }
          sAy.push(d);
        }
        if (type === 'copy') {
          req.copyTableData('.SubjectGradeStatistics', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      download(){
        if(!this.tableData.length){
          this.vmMsgWarning( '暂无数据' ); return;
        }
        let subjectId=this.form.subjectId,
          evaId=this.form.planId;
        req.downloadFile('.SubjectGradeStatistics','/school/StudentEvaluate/statisticsEvaluate?export=ensure&option=subjectGrade&subjectId='+subjectId+'&evaId='+evaId,'post');
      },
      getclass(){
        let param={
          func:'getAllEva'
        };
        req.ajaxSend('/school/StudentEvaluate/common','post',param,(res)=>{
          this.Planoptions=res.data;
        });
      },
      getgrade(){
        let param={
          func:'getCheckBox',
          param:{
            evaId:this.form.planId,
            option:'allSubject'
          }
        };
        req.ajaxSend('/school/StudentEvaluate/common','post',param,(res)=>{
          this.Subjectoptions=res.data;
        });
        this.form.subjectId='';
      },
      getList(page){
        if(this.form.planId===''){
          this.vmMsgWarning( '请选择评教名称' ); return;
        }
        if(this.form.subjectId===''){
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
          option:'subjectGrade',
          evaId:this.form.planId,
          subjectId:this.form.subjectId,
        };
        req.ajaxSend('/school/StudentEvaluate/statisticsEvaluate','post',param,(res)=>{
          this.columes = res.field.map(val=>{
            return {
              label:val.name,
              prop:val.id
            }
          });
          if (res.status === -1) {
            this.tableData = [];
            this.isLoading = false;
            return;
          }
          if(this.key){
            this.tableData = res.data.filter(val=>val.grade.indexOf(this.key)>-1);
          }else{
            this.tableData = res.data;
          }
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
  .SubjectGradeStatistics{
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
    .Infor-head{
      margin-top: 2rem;
    }
  }
</style>
