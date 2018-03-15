<template>
  <div class="InformationService ReferenceConfirmation">
    <h3>学生参考确认</h3>
    <el-row>
      <el-col :span="24" class="Infor-head">
        <el-form :inline="true" class="demo-form-inline Infor-title clear_fix">
          <el-form-item label="年级：">
            <el-select v-model="gradeVal" placeholder="请选择年级" @change="getClassList(gradeVal)">
              <el-option
                v-for="item in gradeOption"
                :key="item.gradeid"
                :value="item.gradeid"
                :label="item.name">
              </el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="班级：" class="Infor-item">
            <el-select v-model="classVal" placeholder="请选择班级" @change="getExamList(gradeVal,classVal)">
              <el-option label="全选" value="all"></el-option>
              <el-option
                v-for="item in classOption"
                :key="item.classid"
                :value="item.classid"
                :label="item.classname">
              </el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="考试：" class="Infor-item">
            <el-select v-model="examVal" placeholder="请选择考试">
              <el-option
                v-for="item in examOption"
                :key="item.examinationid"
                :value="item.examinationid"
                :label="item.examination">
              </el-option>
            </el-select>
          </el-form-item>
        </el-form>
        <el-col :span="4" :offset="19">
          <el-col :span="2">
            <el-button type="primary" class="Infor-search" @click="queryData()">
              <i class="el-icon-search"></i>
              <span>查询</span>
            </el-button>
          </el-col>
          <el-col :span="2" :offset="12">
            <el-button type="primary" class="Infor-search-1" @click="SaveChange()">
              <span>保存</span>
            </el-button>
          </el-col>
        </el-col>
      </el-col>
      <el-col :span="17" class="alertsBtn" style="margin-top: 0">
        <el-button-group style="margin-top:1.8rem">
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
      <el-col :span="5" :offset="2" class="Infor-input-inner" style="margin-top:1.8rem;">
        <el-input style="border-radius:1rem" placeholder="请输入搜索内容" suffix-icon="el-icon-search" v-model="key" @change="handleIconClick"></el-input>
      </el-col>
    </el-row>
    <el-row class="alertsList">
      <el-table
        :data="tableData"
        style="width: 100%"
        @sort-change="sort"
        v-loading.body="isLoading"
        element-loading-text="拼命加载中...">
        <el-table-column
          prop="className"
          label="班级"
          align="center"
          sortable="custom">
        </el-table-column>
        <el-table-column
          prop="serialNumber"
          label="班级序号"
          align="center"
          sortable="custom">
        </el-table-column>
        <el-table-column
          prop="name"
          label="姓名"
          align="center"
          sortable="custom">
        </el-table-column>
        <el-table-column
          prop="sex"
          label="性别"
          align="center"
          sortable="custom">
        </el-table-column>
        <el-table-column
          prop="participate"
          label="是否参加考试"
          align="center">
          <template  slot-scope="scope">
            <el-switch
              v-model="scope.row.participate"
              active-text="是"
              inactive-text="否"
              active-color="#09BAA6"
              inactive-color="#B4B4B4">
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column
          prop="reported"
          label="是否上报数据"
          align="center">
          <template  slot-scope="scope">
            <el-switch
              v-model="scope.row.reported"
              active-text="是"
              inactive-text="否"
              active-color="#09BAA6"
              inactive-color="#B4B4B4">
            </el-switch>
          </template>
        </el-table-column>
      </el-table>
    </el-row>
    <el-col :span="24">
      <el-col :span="22">
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
    </el-col>

  </div>
</template>
<script>
  import req from './../../../../assets/js/common'
  export default{
    data() {
      return {
        gradeVal: '',
        classVal:'',
        examVal:'',
        classid:[],
        classidArr:[],
        gradeOption:[],
        classOption:[],
        examOption:[],
        key: '',
        tableData:[],
        isLoading:false,
        currentPage: 1,
        dialogTableVisible: false,
        total:0,
        order:'createTime desc',
        field:'',
        alldata:[]
      }
    },
    created(){
      this.getGradeList(()=>{
//        this.getClassList(this.gradeVal,()=>{
//          this.getExamList(this.gradeVal,this.classVal,()=>{})
//        })
      });
    },
    methods: {
      operationData(type){
        if(!this.tableData.length){
          this.vmMsgWarning('暂无数据');
          return;
        }
        let sAy = [], hdData = {
          className: '班级',
          serialNumber: '班级序号',
          name: '姓名',
          sex: '性别',
          Nparticipate: '是否参加考试',
          Nreported: '是否上报数据',
        };
        sAy.push(hdData);
        for (let obj of this.tableData) {
          let d = {};
          for (let name in hdData) {
            if(name==='Nparticipate'){
              if(obj.participate==true){
                obj[name]='是'
              }else if(obj.participate==false){
                obj[name]='否'
              }
            }
            if(name==='Nreported'){
              if(obj.reported==true){
                obj[name]='是'
              }else if(obj.reported==false){
                obj[name]='否'
              }
            }
            d[name] = obj[name] || '';
          }
          sAy.push(d);
        }
        if (type === 'copy') {
          req.copyTableData('.ReferenceConfirmation', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      getGradeList(cb){
        req.ajaxSend('/school/ClassManage/common','post',{func:'getGrade',param:{source:'class'}}, (res)=> {
          this.gradeOption=res;
          if(cb)cb(res);
        });
      },
      getClassList(gradeId,cb){
        this.classVal='';
        this.examVal='';
        req.ajaxSend('/school/ClassManage/common','post',{func:'gradeClass',param:{gradeId,source:'class'}}, (res)=> {
          this.classOption=res;
          if(cb)cb(res);
        });
      },
      getExamList(gradeId,classId,cb){
        this.examVal='';
        this.classidArr=[];
        if(this.classVal==='all'){
          this.classOption.forEach(val=>{
            this.classidArr.push(val.classid);
            classId=this.classidArr;
          })
        }
        else{
          this.classid=[];
          this.classid.push(this.classVal);
          classId=this.classid;
        }
        req.ajaxSend('/school/ClassManage/common','post',{func:'getExam',param:{gradeId, classId}}, (res)=> {
          if(res.return===false){
             this.vmMsgWarning(res.msg);
          }
          this.examOption=res;
          if(cb)cb(res);
        })
      },
      queryData(page){
        if(!this.gradeVal){
          this.vmMsgWarning('请选择年级');
          return;
        }
        if(!this.classVal){
          this.vmMsgWarning('请选择班级');
          return;
        }
        if(!this.examVal){
          this.vmMsgWarning('请选择考试');
          return;
        }
        if(page!==this.currentPage){
          this.currentPage=page;
        }
        this.isLoading=true;
        let param={
          examinationid:parseInt(this.examVal),
          classid:this.classVal==='all'?this.classidArr:this.classid,
          page:this.currentPage,
          limit:10,
          screen:this.key,
          order:this.order,
          field:this.field
        };
        req.ajaxSend('/school/ClassManage/stuAttend','post',param, (res)=> {
          if (res.data === null) {
            this.tableData = [];
            this.isLoading = false;
            return;
          }
          this.tableData =res.data;
          this.total = parseInt(res.page.count);
          this.isLoading=false;
        });
      },
      SaveChange(){
        this.alldata=[];
        if(!this.gradeVal){
          this.vmMsgWarning('请选择年级');
          return;
        }
        if(!this.classVal){
          this.vmMsgWarning('请选择班级');
          return;
        }
        if(!this.examVal){
          this.vmMsgWarning('请选择考试');
          return;
        }
        for(let k of this.tableData){
          let data={
            id:parseInt(k.id),
            participate: k.participate ? true : false,
            reported: k.reported ? true : false
          };
          this.alldata.push(data)
        }
        let param={
          source:'class',
          data:this.alldata
        };
        req.ajaxSend('/school/ClassManage/save','post',param, (res)=> {
          if (res.return) {
            this.vmMsgSuccess('设置成功！');
            this.queryData(1);
          } else {
            this.vmMsgError('设置失败！');
          }
        });
      },
      handleIconClick(){
        this.queryData(1)
      },
      sort(column){
        this.field=column.prop;
        this.order = column.order?column.order==='ascending'?'asc':'desc':'';
        this.queryData(1);
      },
      handleCurrentChange(val) {
        this.currentPage = val;
        this.queryData(val);
      },
    }
  }
</script>
<style scoped>
  .InformationService{
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }
  .Infor-item{
    margin-left: 2.8rem;
  }
  .Infor-search{
    /*width: 7rem;*/
    padding:.5rem 2rem;
    border-radius: 1.1rem;
    margin-top: -1rem;
  }
  .Infor-search-1{
    padding:.5rem 2.5rem;
    border-radius: 1.1rem;
    margin-top: -1rem;
  }
  .Infor-confirm{
    background: #4da1ff;
    padding:.5rem 2rem;
    border-radius: 1.1rem;
    margin-top: 4rem;
  }
  .Infor-cancel{
    border: 1px solid #d2d2d2;
    padding:.5rem 2rem;
    border-radius: 1.1rem;
    color: #888888;
    margin-top: 4rem;
  }
  .Infor-head{
    margin-top: 2rem;
    padding-bottom: 1.1rem;
    border-bottom: 1px solid #d2d2d2;
  }
</style>
<style>
  .Infor-input-inner .el-input__inner{
    border-radius: 1.2rem;
  }
</style>
