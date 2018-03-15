<template>
  <div class="VoluntaryAdjustment">
    <h3>志愿调整（班）</h3>
    <el-row style="padding: 2rem 0 1.2rem 0;border-bottom: 1px solid #d2d2d2;">
      <el-form :inline="true" class="demo-form-inline">
        <el-form-item label="分班方案：">
          <el-select v-model="planVal" placeholder="请选择分班方案" @change="choosePlan">
            <el-option
              v-for="item in planOption"
              :key="item.id"
              :value="item.id"
              :label="item.name">
            </el-option>
          </el-select>
        </el-form-item>
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
          <el-select v-model="classVal" placeholder="请选择班级" @change="getPlan()">
            <el-option
              v-for="item in classOption"
              :key="item.classid"
              :value="item.classname"
              :label="item.classname">
            </el-option>
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="queryData()"><i class="el-icon-search"></i>查询</el-button>
        </el-form-item>
      </el-form>
      <el-col  v-if="activePlan.changeStart">
        志愿调整时间：{{activePlan.changeStart | formatDate}}  -  {{activePlan.changeEnd | formatDate}}
      </el-col>
    </el-row>
    <el-row>
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
        v-loading.body="isLoading"
        element-loading-text="拼命加载中...">
        <el-table-column
          prop="preGrade"
          label="年级"
          align="center">
        </el-table-column>
        <el-table-column
          prop="preClass"
          label="班级"
          align="center">
        </el-table-column>
        <el-table-column
          prop="name"
          label="姓名"
          align="center">
        </el-table-column>
        <el-table-column
          prop="branch"
          label="科类"
          align="center">
          <template  slot-scope="scope">
            <span>{{scope.row.branch}}</span>
          </template>
        </el-table-column>
        <el-table-column
          prop="major"
          label="专业"
          align="center">
          <template  slot-scope="scope">
            <span>{{scope.row.major}}</span>
          </template>
        </el-table-column>
        <el-table-column
          label="操作"
          align="center">
          <template  slot-scope="scope">
            <span style="color:#4da1ff;cursor: pointer;" @click="edit(scope.row)">编辑</span>
          </template>
        </el-table-column>
      </el-table>
    </el-row>
    <el-row>
      <el-dialog title="编辑志愿" v-if="dialogTableVisible" :modal="false" :visible.sync="dialogTableVisible">
        <el-row>
          <el-col style="min-height:15rem;">
            <el-col :span="24">
              <el-col :span="5" :offset="2" style="padding-top: .4rem;">科类：</el-col>
              <el-col :span="15">
                <el-select style="width: 100%;" v-model="branchval"  @change="changemajor" placeholder="请选择">
                  <el-option
                    v-for="item in branchOption"
                    :key="item.branchId"
                    :label="item.name"
                    :value="item.name">
                  </el-option>
                </el-select>
              </el-col>
            </el-col>
            <el-col :span="24" style="margin-top: 1.2rem;">
              <el-col :span="5" :offset="2" style="padding-top: .4rem;">专业：</el-col>
              <el-col :span="15">
                <el-select style="width: 100%;" v-model="majorsval" placeholder="请选择">
                  <el-option
                    v-for="item in majorsOption"
                    :key="item.wishId"
                    :label="item.name"
                    :value="item.name">
                  </el-option>
                </el-select>
              </el-col>
            </el-col>
            <el-col :span="24">
              <el-col :span="2" :offset="9">
                <el-button type="primary" class="Field-save" @click="saveDialog()">保存</el-button>
              </el-col>
              <el-col :span="2" :offset="14">
                <el-button class="Field-save" @click="cancelDialog()">取消</el-button>
              </el-col>
            </el-col>
          </el-col>
        </el-row>
      </el-dialog>
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
  import req from './../../../../assets/js/common'
  import formatdata from './../../../../assets/js/date'
  export default{
    data(){
      return {
        branchOption:[],
        majorsOption:[],
        branchval:'',
        majorsval:'',
        gradeVal: '',
        classVal:'',
        planVal:'',
        activePlan: {},
        studentid:'',
        changeStart:'',
        changeEnd:'',
        gradeOption:[],
        classOption:[],
        planOption:[],
        key:'',
        tableData:[],
        dialogTableVisible:false,
        isLoading:false,
        currentPage: 1,
        total:0,
      }
    },
    created(){
      this.getPlan();
      this.getGradeList(()=>{
        this.getClassList(this.gradeVal,()=>{})
      });
    },
    methods: {
      operationData(type){
        if(!this.tableData.length){
          this.vmMsgWarning('暂无数据');
          return;
        }
        let sAy = [], hdData = {
          preGrade: '年级',
          preClass: '班级',
          name: '姓名',
          branch: '科类',
          major: '专业',
        };
        sAy.push(hdData);
        for (let obj of this.tableData) {
          let d = {};
          for (let name in hdData) {
            d[name] = obj[name] || '';
          }
          sAy.push(d);
        }
        if (type === 'copy') {
          req.copyTableData('.VoluntaryAdjustment', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      getGradeList(cb){
        req.ajaxSend('/school/ClassManage/common','post',{func:'getGrade',param:{source:'class'}}, (res)=> {
          this.gradeOption=res;
          if(cb)cb(res);
        })
      },
      getClassList(gradeId,cb){
        this.classVal='';
        req.ajaxSend('/school/ClassManage/common','post',{func:'gradeClass',param:{gradeId,source:'class'}}, (res)=> {
          this.classOption=res;
          if(cb)cb(res);
        })
      },
      getPlan(){
        req.ajaxSend('/school/ClassManage/common','post',{func:'getAllPlan'}, (res)=> {
            res.forEach(val=>{
              this.changeStart=val.changeStart;
              this.changeEnd=val.changeEnd;
            });
          this.planOption=res;
        })
      },
      choosePlan(){
        this.gradeVal='';
        this.classVal='';
        for (let obj of this.planOption) {
          if (obj.id === this.planVal) {
            this.activePlan = obj;
          }
        }
      },
      queryData(page){
        if(this.planVal===''){
          this.vmMsgWarning('请选择分班方案');
          return;
        }else if(this.gradeVal===''){
          this.vmMsgWarning('请选择年级');
          return;
        } else if(this.classVal===''){
          this.vmMsgWarning('请选择班级');
          return;
        }
        if(page!==this.currentPage){
          this.currentPage=page;
        }
        this.isLoading=true;
        let param={
          page:page,
          count:10,
          planId:parseInt(this.planVal),
          gradeId:parseInt(this.gradeVal),
          className:this.classVal,
          key:this.key,
        };
        req.ajaxSend('/school/ClassManage/adjustWish','post',param, (res)=> {
//          console.log(res);
          if (res.data.length===0) {
            this.tableData = [];
            this.isLoading = false;
            return;
          }
          this.tableData =res.data;
          this.total = res.total;
          this.isLoading=false;
        });
      },
      getWish(name){
        let wish={
          func:'getWish',
          param:{
            planId:this.planVal,
          }
        };
        this.branchOption=[];
        this.majorsOption=[];
        req.ajaxSend('/school/ClassManage/common','post',wish, (res)=> {
          this.branchOption=res.data;
          res.data.forEach(val=>{
            if(val.name===name){
              this.majorsOption = val.majors;
            }
          });
        });
      },
      changemajor(){
        for(let obj of this.branchOption){
          if(obj.name === this.branchval){
            this.majorsOption=obj.majors;
            this.majorsval='';
          }
        }
      },
      edit(row){
        this.getWish(row.branch);
        this.changemajor();
        this.branchval=row.branch;
        this.majorsval=row.major;
        this.studentid=row.id;
        this.dialogTableVisible=true;
      },
      saveDialog(){
        if(this.branchval===null) {
          this.vmMsgWarning('请选择科类');
          return;
        }else if(this.majorsval===''){
          this.vmMsgWarning('请选择专业');
          return;
        }
        this.$confirm('是否确定修改?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          let time = new Date().getTime();
          if (time < Number.parseInt(this.activePlan.changeStart) * 1000) {
            this.vmMsgWarning('还未到该志愿修改时间！');
            return false;
          }
          if (time > Number.parseInt(this.activePlan.changeEnd) * 1000) {
            this.vmMsgWarning('该志愿修改时间已过！');
            return false;
          }
          let param={
            type:'adjust',
            planId:parseInt(this.planVal),
            branch:this.branchval,
            major:this.majorsval,
            id:this.studentid,
//          wishId:this.majorsval
          };
          req.ajaxSend('/school/ClassManage/adjustWish','post',param, (res)=> {
//            console.log(res);
            if(res.status===1){
              this.vmMsgSuccess(res.msg);
              this.queryData(1);
            }else {
              this.vmMsgError(res.msg);
            }
          });
        }).catch(() => {});
        this.dialogTableVisible=false;
      },
      cancelDialog(){
        this.dialogTableVisible=false;
      },
      handleIconClick(){
        this.queryData(1)
      },
      handleCurrentChange(val) {
        this.currentPage = val;
        this.queryData(val);
      },
    }
  }
</script>
<style lang="less" scoped>
  .VoluntaryAdjustment{
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
    .Field-save{
      position: absolute;
      bottom: 1rem;
      padding: .5rem 2.1rem;
      border-radius: 1.1rem;
    }
  }
</style>
<style>
  .Infor-input-inner .el-input__inner{
    border-radius: 1.2rem;
  }
</style>
