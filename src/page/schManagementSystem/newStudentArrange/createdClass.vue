<template>
  <div class="g-container">
    <header class="g-importCourseHeader">
      <div class="g-textHeader g-flexStartRow">
        <el-button class="g-gobackChart g-imgContainer RedButton" @click="goBackChart">
          <img src="../../../assets/img/schManagementSystem/teachingAdministration/arrangeClasses/icon_return.png" />
          返回流程图
        </el-button>
        <h2 class="selfCenter">创建班级</h2>
      </div>
      <div class="g-text--container">
        <div class="g-flexStartRow">
          <p>新生人数:<span v-text="newStudentNum"></span>人</p>
          <p>参与分班人数:<span v-text="attend"></span>人</p>
        </div>
        <div class="g-prompt">注：如果班级专业划分特长班，则特长生将自动划分到特长班而不参与分班计算，如果班级不分专业，则特长生参与分班计算。</div>
      </div>
    </header>
    <div class="g-container g-containerNoPadding">
      <section class="g-section">
        <div class="gs-header g-liOneRow">
          <div class="gs-button alertsBtn">
            <el-button-group>
              <el-button @click="addClick" data-msg="add" class="filt buttonChild" title="添加">
                <img class="filt_unactive"  src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_add.png" />
                <img class="filt_active" src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_add_highlight.png" />
              </el-button>
              <el-button @click="deleteClick" data-msg="delete" class="filt buttonChild" title="删除">
                <img class="filt_unactive"  src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_delete.png" />
                <img class="filt_active" src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_delete_highlight.png" />
              </el-button>
            </el-button-group>
          </div>
        </div>
        <div class="gs-table alertsList">
          <el-table
            v-loading.body="isLoading"
            element-loading-text="拼命加载中..."
            class="g-NotHover" ref="studentMsgTable" :data="headerButtonData.studentBasicMsg" style="width:100%" @sort-change="sortChange" @selection-change="handleStudentTable">
            <!--show-overflow-tooltip 超出部分省略号显示-->
            <el-table-column type="selection" width="55px"></el-table-column>
            <el-table-column label="班级名称" prop="className"></el-table-column>
            <el-table-column label="容纳人数" prop="number"></el-table-column>
            <el-table-column label="当前班级人数" prop="realNumber"></el-table-column>
            <el-table-column label="科类" prop="branch"></el-table-column>
            <el-table-column label="班级专业" prop="major"></el-table-column>
            <el-table-column label="班级级别" prop="level"></el-table-column>
            <el-table-column label="操作" width="75px" fixed="right">
              <template slot-scope="props">
                <el-button @click="changeClick(props.row,props.$index,props.row.classId)" type="text">编辑</el-button>
              </template>
            </el-table-column>
          </el-table>
        </div>
      </section>
    </div>
    <el-dialog class="headerNotBackground scheduleDialog" :title="dialogTitle" :modal="false" :visible.sync="isDialog" :before-close="handlerClose">
      <el-form :model="dialogForm" ref="dialogForm" :rules="rules" label-width="100px" label-position="center">
        <el-form-item label="班级名称:" prop="className" :required="true">
          <el-input :disabled="realNumber>0" v-model="dialogForm.className"></el-input>
        </el-form-item>
        <el-form-item label="容纳人数:" prop="number" :required="true">
          <el-input v-model="dialogForm.number"></el-input>
        </el-form-item>
        <el-form-item label="班级科类:" prop="branchId">
          <el-select  :disabled="realNumber>0" v-model="dialogForm.branchId" @change="branchChange">
            <el-option v-for="(content,index) in branchArr" :key="index" :value="content.branchId" :label="content.branch"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="班级专业:" prop="majorId">
          <el-select :disabled="realNumber>0" v-model="dialogForm.majorId">
            <el-option v-for="(content,index) in majorArr" :key="index" :value="content.majorId" :label="content.major"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="班级级别:" prop="levelId" :required="true">
          <el-select :disabled="realNumber>0" v-model="dialogForm.levelId">
            <el-option v-for="(content,index) in levelArr" :key="index" :value="content.levelId" :label="content.level"></el-option>
          </el-select>
        </el-form-item>
      </el-form>
      <div class="g-button">
        <el-button @click="saveClick" type="primary">保存</el-button>
        <el-button @click="confirmClick">取消</el-button>
      </div>
    </el-dialog>
  </div>
</template>
<script>
  import {
    newStudentGetGrade,//弹框select下的信息
    createdClassLoad,//操作
  } from '@/api/http'
  export default{
    data(){
      return{
        isLoading:false,
        /*ajax data*/
        headerButtonData:{
          studentBasicMsg:[],
        },
        /*fuzzyFilter*/
        fuzzyInput:'',
        /*------------------------*/
        /*删除所需参数*/
        deleteParams:[],
        /*弹框---------*/
        isDialog:false,
        realNumber:0,
        dialogTitle:'添加信息',
        needInit:true,
        /*新生总人数*/
        newStudentNum:0,
        attend:0,//参与分班人数
        /*科类级别下拉框*/
        branchArr:[],
        /*班级专业下拉框*/
        majorArr:[],
        /*班级级别下拉框*/
        levelArr:[],
        /*form*/
        dialogForm:{
          className:'',
          number:'',
          branchId:'',//科类类别
          majorId:'',//班级专业
          levelId:''//班级级别
        },
        /*send param*/
        classId:'',
        ajaxType:'',
        gradeId:'',
        rules:{
          className:[{required:true,message:'请输入班级名称'}],
          number:[{required:true,message:'请输入班级人数'}],
        //   branchId:[{required:true,message:'请选择班级科类'}],
        //   majorId:[{required:true,message:'请选择班级专业'}],
          levelId:[{required:true,message:'请选择班级级别'}],
        },
      }
    },
    computed: {},
    methods:{
      /*点击返回流程图按钮*/
      goBackChart(){
        this.$router.push({name:'newStudentClass'});
      },
      /*table*/
      sortChange(column){
        /*table排序回调*/
      },
      /*模糊查询*/
      fuzzyClick(){
        /*点击search按钮*/
      },
      /*科类发生变化*/
      branchChange(){
        if(this.dialogForm.branchId){
          if(this.needInit){
            this.dialogForm.majorId='';
          }
          this.dialogAjax('getMajor',{branchId:this.dialogForm.branchId},'暂无数据！');
        }
      },
      /*---------------------------------------*/
      /*table复选*/
      handleStudentTable(section){
        /*section为选择项行信息组成的数组*/
        this.deleteParams=[];
        section.forEach((value)=>{
          this.deleteParams.push(value.classId);
        });
      },
      /*删除*/
      deleteClick(){
        if(this.deleteParams.length>0){
          this.sendDeleteAjax();
        }
        else{
          this.vmMsgWarning('请选择需要删除的信息！');
        }
      },
      /*编辑*/
      changeClick(row,index,classId){
        this.isDialog=true;
        this.realNumber=row.realNumber;
        this.dialogTitle='编辑信息';
        this.ajaxType='edit';
        this.needInit=false;
        this.classId=classId;
        /*班级水平*/
        this.dialogAjax('getLevel','','暂无数据');
        /*科类*/
        this.dialogAjax('getBranch','','暂无数据');
        if(this.dialogForm.branchId){
          this.dialogAjax('getMajor',{branchId:this.dialogForm.branchId},'暂无数据');
        }
        Object.keys(this.dialogForm).forEach((value)=>{
          if(value in this.headerButtonData.studentBasicMsg[index]){
            this.dialogForm[value]=this.headerButtonData.studentBasicMsg[index][value];
          }
        });
        setTimeout(()=>{
          this.needInit=true;
        },250)
      },
      /*header的button群*/
      addClick(){
        this.isDialog=true;
        this.ajaxType='add';
        this.realNumber=0;
        this.dialogTitle='添加信息';
        /*班级水平*/
        this.dialogAjax('getLevel','','暂无数据');
        /*科类*/
        this.dialogAjax('getBranch','','暂无数据');
        this.dialogForm={
          className:'',
          number:'',
          branchId:'',//科类类别
          majorId:'',//班级专业
          levelId:''//班级级别
        };
      },
      /*弹框*/
      /*关闭按钮点击*/
      handlerClose(done){
        this.$refs['dialogForm'].resetFields();
        done();
      },
      /*退出*/
      confirmClick(){
        this.$confirm('确定退出？','提示',{
          confirmButtonText:'确定',
          cancelButtonText:'取消',
          type:'warning'
        }).then(()=>{
          this.isDialog=false;
          this.$refs['dialogForm'].resetFields();
        }).catch(()=>{});
      },
      /*send ajax*/
      /*table信息*/
      getLoadAjax(){
        this.isLoading=true;
        createdClassLoad({gradeId:this.gradeId}).then(data=>{
          this.newStudentNum=data.total;
          this.attend=data.attend;
          if(data.status){
            this.headerButtonData.studentBasicMsg=data.data;
          }
          else{
            this.vmMsgError('暂无数据');
            this.headerButtonData.studentBasicMsg=[];
          }
          this.isLoading=false;
        });
      },
      saveClick(){
        if(!/^[1-9]\d*$/.test(this.dialogForm.className)){
          this.vmMsgWarning('班级名称格式不正确');
          return;
        }
        if(!/^[1-9]\d*$/.test(this.dialogForm.number)){
          this.vmMsgWarning('班级人数格式不正确');
          return;
        }
        // if(!this.dialogForm.branchId){
        //   this.$message({
        //     type:'info',
        //     showClose:true,
        //     message:'请选择班级科类'
        //   });
        //   return;
        // }
        // if(!this.dialogForm.majorId){
        //   this.$message({
        //     type:'info',
        //     showClose:true,
        //     message:'请选择班级专业'
        //   });
        //   return;
        // }
        if(!this.dialogForm.levelId){
          this.vmMsgWarning('请选择班级级别');
          return;
        }
        this.$refs['dialogForm'].validate(valid=>{
          if(valid){
            if(this.ajaxType=='add'){
              /*添加*/
              createdClassLoad({gradeId:this.gradeId,type:this.ajaxType,...this.dialogForm}).then(data=>{
                this.getLoadAjax();
                if(data.status){
                  this.isDialog=false;
                  this.vmMsgSuccess(data.msg);
                  this.$refs['dialogForm'].resetFields();
                }
                else{
                  this.vmMsgError(data.msg);
                }
              });
            }
            else{
              createdClassLoad({gradeId:this.gradeId,classId:this.classId,type:this.ajaxType,...this.dialogForm}).then(data=>{
                if(data.status){
                  this.isDialog=false;
                  this.vmMsgSuccess(data.msg);
                }
                else{
                  this.vmMsgError(data.msg);
                }
                this.getLoadAjax();
              });
            }
          }
        });
      },
      /*删除数据*/
      sendDeleteAjax(){
        createdClassLoad({type:'del',gradeId:this.gradeId,ids:this.deleteParams}).then(data=>{
          if(data.status){
            this.vmMsgSuccess(data.msg);
            this.getLoadAjax();
          }
          else{
            this.vmMsgError(data.msg);
          }
        });
      },
      /*-------------------------------*/
      /*弹框加载信息*/
      dialogAjax(params1,params2,msg){
        newStudentGetGrade({func:params1,param:params2}).then(data=>{
          if(data.status){
            if(params1=='getLevel'){
              this.levelArr=data.data;
            }
            else if(params1=='getBranch'){
              this.branchArr=data.data;
            }
            else if(params1=='getMajor'){
              this.majorArr=data.data;

            }
          }
          else{
            this.vmMsgError(msg);
          }
        });
      },
    },
    created(){
      this.gradeId=this.$route.params.gradeId;
      this.getLoadAjax();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../style/style';
  .g-importCourse .g-container .g-section{margin:1.25rem 0;width:100%;}
  .g-container.g-containerNoPadding{width:100%;}
  /*弹框*/
  .headerNotBackground{
    .dialogHeader{position:absolute;right:20px;top:0.625rem;
      button{.border-radius(1rem);}
    }
    .dialogSection{
      .NotAllWidth{width:auto;}
      .defineInputWidth{.widthRem(60);}
    }
  }
  .g-textHeader{
    h2{.marginLeft(40,1582);}
  }
  .g-text--container{
    .marginTop(30);
  }
  .g-flexStartRow{
    p{color:#666;.fontSize(14);
      span{color:#4da1ff;}
    }
  }
  .g-prompt{text-align:left;padding-top:5/16rem;}
</style>








