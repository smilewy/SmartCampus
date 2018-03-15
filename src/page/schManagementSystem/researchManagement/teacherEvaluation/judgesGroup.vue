<template>
  <div class="g-importCourse judgesGroup">
    <header class="g-importCourseHeader g-liOneRow">
      <div class="g-flexStartRow">
        <el-button class="g-gobackChart g-imgContainer RedButton" @click="goBackChart">
          <img src="../../../../assets/img/schManagementSystem/teachingAdministration/arrangeClasses/icon_return.png" />
          返回流程图
        </el-button>
        <h2 class="selfCenter g-headerH">评委分组</h2>
      </div>
      <!--<el-button class="blueButton">保存</el-button>-->
    </header>
    <div class="g-container g-containerNoPadding">
      <section class="g-section">
        <div class="gs-header">
          <div class="gs-button alertsBtn">
            <el-button-group>
              <el-button @click="addDialogClick" class="filt buttonChild" title="添加">
                <img class="filt_unactive"  src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_add.png" />
                <img class="filt_active" src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_add_highlight.png" />
              </el-button>
              <!--              <el-button class="filt buttonChild" title="导出">
                              <img class="filt_unactive"  src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png" />
                              <img class="filt_active" src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png" />
                            </el-button>-->
            </el-button-group>
            <el-button-group class="elGroupButton_two">
              <el-button class="filt buttonChild" title="复制" @click="operationData('copy')">
                <img class="filt_unactive"  src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png" />
                <img class="filt_active" src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png" />
              </el-button>
              <el-button class="filt buttonChild" title="打印预览" @click="operationData('print')">
                <img class="filt_unactive"  src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png" />
                <img class="filt_active" src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png" />
              </el-button>
            </el-button-group>
          </div>
          <!--          <div class="gs-refresh g-fuzzyInput">
                      <el-input type="text" v-model="fuzzyInput" suffix-icon="el-icon-search" @change="fuzzyClick"></el-input>
                    </div>-->
        </div>
        <div class="gs-table alertsList">
          <el-table
            v-loading.body="isLoading"
            element-loading-text="拼命加载中..."
            class="g-NotHover" ref="studentMsgTable" :data="headerButtonData.studentBasicMsg" style="width:100%" @sort-change="sortChange" @selection-change="handleStudentTable">
            <!--show-overflow-tooltip 超出部分省略号显示-->
            <el-table-column label="序号" width="100" align="center" type="index"></el-table-column>
            <el-table-column label="评委分组" align="center" prop="name"></el-table-column>
            <el-table-column label="最高分去除（人）" align="center" prop="max"></el-table-column>
            <el-table-column label="最低分去除（人）" align="center" prop="min"></el-table-column>
            <el-table-column label="评委名单" align="center">
              <template slot-scope="prop">
                <span v-for="(content,index) in prop.row.judge">
                  <span v-if="index!=(prop.row.judge.length-1)" v-text="content.name+'，'"></span>
                  <span v-else v-text="content.name"></span>
                </span>
                <!--<span>{{prop.row.judge}}</span>-->
              </template>
            </el-table-column>
            <el-table-column label="操作" align="center" width="120px" fixed="right">
              <template slot-scope="props">
                <el-button @click="handlerClick(props.$index)" type="text">编辑</el-button>
                <el-button type="text" @click="mainTableDelete(props.row.id)" class="delete">删除</el-button>
              </template>
            </el-table-column>
          </el-table>
        </div>
      </section>
    </div>
    <el-dialog class="headerNotBackground" :title="dialogTitle" :modal="false" :visible.sync="isDialog" :before-close="handlerClose">
      <div style="height: 38rem;overflow-y: auto">
        <div class="dialogHeader g-flexStartRow">
          <el-button @click="dialogClearClick" class="redBgb defineHeight" type="primary">清空</el-button>
          <el-button @click="dialogSaveAjax" class="defineHeight" type="primary">保存</el-button>
        </div>
        <section class="dialogSection">
          <el-form :model="dialogForm" ref="dialogAddForm" :rules="dialogRule" label-width="85px" label-position="left">
            <el-form-item label="分组名称:" prop="name" required>
              <el-input class='NotAllWidth' v-model="dialogForm.name" placeholder="请输入分组名称"></el-input>
            </el-form-item>
            <el-form-item label="分数采样:">
              <span>本组最高分去除</span>
              <el-input @input="maxMinChange('max')" class="defineInputWidth" style="width:4.75rem;" v-model="dialogForm.max"></el-input>
              <span>人，本组最低分去除</span>
              <el-input @input="maxMinChange('min')" class="defineInputWidth" style="width:4.75rem;" v-model="dialogForm.min"></el-input>
              <span>人</span>
            </el-form-item>
          </el-form>
          <div class="g-tree_content g-dialog_content">
            <div class="g-sectionL">
              <header class="gL-header">
                <h2>候选人名单</h2>
                <el-input @input="dialogFuzzyClick" v-model="dialogFuzzyInput" class="fuzzyInput" placeholder="请输入候选人" suffix-icon="el-icon-search"></el-input>
              </header>
              <section class="gL-section">
                <el-tree
                  v-loading.body="isLoading"
                  element-loading-text="拼命加载中..."
                  :render-after-expand="false"
                  @check-change="checkChange" :highlight-current="true" :default-checked-keys="selectedArr" show-checkbox node-key="id" :data="treeData" :props="defaultProps" ref="allMsg" :filter-node-method="filterNode"></el-tree>
              </section>
            </div>
            <div class="g-sectionR alertsList centerTable" style="min-height: 10rem">
              <el-table :data="dialogForm.judge" class="g-NotHover g-timeSettingTable">
                <el-table-column label="姓名" prop="name"></el-table-column>
                <el-table-column label="操作">
                  <template slot-scope="props">
                    <el-button @click="dialogDeleteClick(props.$index)" class="deleteColor" type="text">删除</el-button>
                  </template>
                </el-table-column>
              </el-table>
            </div>
          </div>
        </section>
      </div>
    </el-dialog>
  </div>
</template>
<script>
  import {
    judgesGroupGetLoad,//操作
    judgesGroupGetJudge,//得到候选人
  } from '@/api/http'
  import req from './../../../../assets/js/common'
  export default{
    data(){
      return{
        isLoading:false,
        /*ajax data*/
        headerButtonData:{
          gradeloadData:[],
          classesLoadData:[],
          msgTypeLoadData:[],
          studentBasicMsg:[],
        },
        /*dialog*/
        dialogTitle:'新增评委分组',
        /*导出数据*/
        /*fuzzyFilter*/
        fuzzyInput:'',
        /*弹框---------*/
        isDialog:false,
        /*评委组名称form表单*/
        dialogForm:{
          name:'',
          max:0,
          min:0,
          judge:[],//table框
          judgeId:'',
        },
        /*左边tree树*/
        dialogFuzzyInput:'',
        treeData:[],
        defaultProps:{
          children:'child',
          label:'name'
        },
        /*弹框dialog*/
        classesTimeSetTable:[],
        dialogRule:{
          name:[{required:true,message:'请填写评委组名称',trigger:'blur'}],
        },
        /*send ajax param*/
        _id:'',
        ajaxType:'',
        /*弹框的el-tree*/
        /*默认选中项*/
        selectedArr:[],
      }
    },
    computed: {},
    methods:{
      operationData(type){
        let sAy = [], hdData = {
          name: '评委分组',
          max: '最高分去除（人）',
          min: '最低分去除（人）',
          Newjudge: '评委名单',
        };
        sAy.push(hdData);
        for (let obj of this.headerButtonData.studentBasicMsg) {
          this.headerButtonData.studentBasicMsg.forEach(val=>{
            if(val.judge){
              val.Newjudge = val.judge.map(subVal=>subVal.name).join('，');
            }
          });
          let d = {};
          for (let name in hdData) {
            d[name] = obj[name] || '';
          }
          sAy.push(d);
        }
        if (type === 'copy') {
          req.copyTableData('.judgesGroup', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      /*点击返回流程图按钮*/
      goBackChart(){
        this.$router.push({name:'evaluationManagement'});
      },
      /*table*/
      handleStudentTable(section){
        /*section为选择项行信息组成的数组*/
      },
      sortChange(column){
        /*table排序回调*/
      },
      deepCopy(data){return JSON.parse(JSON.stringify(data))},
      handlerClick(idx){
        this.isDialog=true;
        this.dialogTitle='修改评委分组';
        this.ajaxType='save';
        this.getTeacherAjax(this.headerButtonData.studentBasicMsg[idx].id);
        /*设置默认值*/
        this.dialogForm.name=this.deepCopy(this.headerButtonData.studentBasicMsg[idx].name);
        this.dialogForm.max=this.deepCopy(this.headerButtonData.studentBasicMsg[idx].max);
        this.dialogForm.min=this.deepCopy(this.headerButtonData.studentBasicMsg[idx].min);
        this.dialogForm.judge=this.deepCopy(this.headerButtonData.studentBasicMsg[idx].judge);
        this.dialogForm.judgeId=this.deepCopy(this.headerButtonData.studentBasicMsg[idx].id);
      },
      /*header的button群*/
      addDialogClick(){
        this.isDialog=true;
        this.dialogTitle='新增评委分组';
        this.ajaxType='add';
        this.dialogForm={name:'',max:0,min:0,judge:[],judgeId:''};
        this.getTeacherAjax();
      },
      /*模糊查询*/
      fuzzyClick(){
        /*点击search按钮*/
      },
      /*导出数据*/
      exportAjax(){},
      /*弹框*/
      /*关闭按钮点击*/
      handlerClose(done){
        done();
      },
      /*tree点击事件*/
      handleNodeClick(data,node){
        /*点击节点回调*/
        /*点击名字发送请求，返回数据绑定在右边部分*/
        /*给每一级赋值一个唯一的id即可辨认点击的是几级*/
      },
      /*tree模糊查询*/
      dialogFuzzyClick(){
        /*input框输入值改变触发的函数*/
        this.$refs['allMsg'].filter(this.dialogFuzzyInput);
      },
      filterNode(value, data) {
        /*筛选节点*/
        if (!value) return true;
        return data.name.indexOf(value) !== -1;
      },
      /*common-------------*/
      /*页面删除*/
      dialogDeleteClick(idx){
        this.$refs['allMsg'].setChecked(this.dialogForm.judge[idx]);
        this.dialogForm.judge.splice(idx,1);
      },
      /*el-tree复选框选中发生变化时回调*/
      checkChange(data,value,child){
        if('child' in data){
          return false;
        }
        else{
          if(value){
            /*选中*/
            this.dialogForm.judge.push(data);
          }
          else{
            /*取消选中*/
            for(let i=0;i<this.dialogForm.judge.length;i++){
              if(this.dialogForm.judge[i].id==data.id){
                /*删除该项*/
                this.dialogForm.judge.splice(i,1);
                break;
              }
            }
          }
        }
      },
      /*最低分最高分输入限制*/
      maxMinChange(msg){
        if(parseInt(this.dialogForm.judge.length)<(parseInt(this.dialogForm.max)+parseInt(this.dialogForm.min))){
          this.vmMsgWarning( '最高分与最低分的人数和必须小于总人数！' );
          this.$nextTick(() => {
            this.dialogForm[msg]=0;
          });
        }
      },
      /*清空*/
      dialogClearClick(){
        this.$confirm('是否清空数据？','提示',{
          confirmButtonText:'确定',
          cancelButtonText:'取消',
          type:'warning'
        }).then(()=>{
          this.dialogForm.judge=[];
        }).catch(()=>{});
      },
      /*send ajax*/
      getLoadAjax(){
        this.isLoading=true;
        judgesGroupGetLoad({id:this._id}).then(data=>{
          if(data.status){
            this.headerButtonData.studentBasicMsg=data.data;
          }
          else{
            this.headerButtonData.studentBasicMsg=[];
          }
          this.isLoading=false;
        });
      },
      /*删除*/
      mainTableDelete(id){
        this.$confirm('是否删除数据？','提示',{
          confirmButtonText:'确定',
          cancelButtonText:'取消',
          type:'warning'
        }).then(()=>{
          judgesGroupGetLoad({id:this._id,type:'del',judgeId:id}).then(data=>{
            this.simplePrompt(data,'删除');
          });
        }).catch(()=>{});
      },
      /*弹框*/
      getTeacherAjax(judgeId){
        this.isLoading=true;
        judgesGroupGetJudge({judgeId:judgeId,id:this._id}).then(data=>{
          if(data.status){
            this.treeData=data.data;
            this.selectedArr=data.selected;
          }
          else{
            this.treeData=[];
            this.selectedArr=[];
          }
          this.isLoading=false;
        });
      },
      /*保存*/
      dialogSaveAjax(){
        this.$refs['dialogAddForm'].validate((valid)=>{
          if(valid){
            if(this.dialogForm.judge.length>0){
              judgesGroupGetLoad({id:this._id,type:this.ajaxType,...this.dialogForm}).then(data=>{
                this.isDialog=false;
                if(this.ajaxType=='add'){
                  this.simplePrompt(data,'添加');
                }
                else{
                  this.simplePrompt(data,'修改');
                }
              });
            }
            this.getLoadAjax();
          }
        });
      },
      simplePrompt(data,msg){
        if(data.status){
          this.vmMsgSuccess( msg+'成功！' );
          this.getLoadAjax();
        }
        else{
          this.vmMsgError( msg+'失败！' );
        }
      },
    },
    created(){
      this._id=this.$route.params.id;
      this.getLoadAjax();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/style';
  @import '../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.css';
  @import '../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.less';
  @import '../../../../style/arrangeClasses/importCourse.less';
  .g-importCourse .g-container .g-section{margin:1.25rem 0;width:100%;}
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
  .g-tree_content .g-sectionL .gL-section{
    height: auto;
  }
</style>








