<template>
  <div class="g-evaluationGroup g-container">
    <header class="g-textHeader g-evaluationGroupHeader">
      <div class="g-liOneRow">
        <div class="g-flexStartRow">
          <el-button class="g-gobackChart g-imgContainer RedButton" @click="goBackChart">
            <img src="../../../../assets/img/commonImg/icon_return.png" />
            返回流程图
          </el-button>
          <h2 class="selfCenter g-headerH">分配考评人员</h2>
        </div>
        <el-button type="primary" class="defineHeight" @click="saveAjax">保存</el-button>
      </div>
      <div class="g-liOneRow g-sa_header_search">
        <div class="defineSelect g-js_selectPart">
          <span>被考评分组:</span>
          <el-select @change="evaluationChange(groupId)" v-model="groupId">
            <el-option v-for="(content,index) in IsEvaluationOption" :key="index" :label="content.name" :value="content.id"></el-option>
          </el-select>
        </div>
      </div>
    </header>
    <section class="g-threePart g-evaluationGroupSection">
      <div class="g-sectionL">
        <header class="gL-header">
          <h2>候选被评人员名单</h2>
        </header>
        <section class="gL-section">
          <el-tree
            v-loading.body="isLoading"
            element-loading-text="拼命加载中..."
            @check-change="AppraisedCheckChange"
            @node-click="setEditState"
            node-key="id"
            ref="AppraisedTree"
            :render-after-expand="false"
            :default-checked-keys="AppraisedTreeSelect"
            :highlight-current="true" show-checkbox
            :data="AppraisedTreeData"
            :props="AppraisedDefaultProps"></el-tree>
        </section>
      </div>
      <div class="g-sectionC">
        <header class="gL-header">
          <h2>已添加被评人员</h2>
        </header>
        <el-table
          v-loading.body="isLoading"
          element-loading-text="拼命加载中..."
          style="height:45.8rem;overflow-y: auto;" :data="classesTimeSetTable" class="g-NotHover g-timeSettingTable">
          <el-table-column label="姓名" prop="name"></el-table-column>
          <el-table-column label="操作">
            <template slot-scope="props">
              <el-button @click="AppraisedDelete(props.$index)" class="deleteColor" type="text">删除</el-button>
            </template>
          </el-table-column>
        </el-table>
      </div>
      <div class="g-sectionC">
        <header class="gL-header">
          <h2>已添加评委</h2>
        </header>
        <el-table
          v-loading.body="isLoading"
          element-loading-text="拼命加载中..."
          style="height:45.8rem;overflow-y: auto;" :data="JdgClassesTimeSetTable" class="g-NotHover g-timeSettingTable">
          <el-table-column label="姓名" prop="name"></el-table-column>
          <el-table-column label="操作">
            <template slot-scope="props">
              <el-button @click="JdgDelete(props.$index)" class="deleteColor" type="text">删除</el-button>
            </template>
          </el-table-column>
        </el-table>
      </div>
      <div class="g-sectionL centerTable">
        <header class="gL-header">
          <h2>候选评委名单</h2>
        </header>
        <section class="gL-section">
          <el-tree ref="judgeTree"
                   v-loading.body="isLoading"
                   element-loading-text="拼命加载中..."
                   @check-change="checkChange"
                   node-key="id"
                   :render-after-expand="false"
                   :default-checked-keys="treeSelect"
                   :highlight-current="true"
                   show-checkbox :data="treeData"
                   :props="defaultProps"></el-tree>
        </section>
      </div>
    </section>
  </div>
</template>
<script>
  import {
    addEvaluationPersonGroup,//被考评分组
    addEvaluationPersonIsDudges,//被考评候选人
    addEvaluationPersonIsTable,//被考评table
    addEvaluationPersonDudges,//候选人名单
    addEvaluationPersonTable,//候选人table
    addEvaluationPersonLoad,//操作
  } from '@/api/http'
  export default{
    data(){
      return{
        isLoading:false,
        /*被考评人分组*/
        groupId:'',
        IsEvaluationOption:[],
        /*el-tree*/
        /*被评人员*/
        AppraisedTreeData:[],
        AppraisedTreeSelect:[],
        AppraisedDefaultProps:{
          children:'child',
          label:'name'
        },
        /*评委分组*/
        treeData:[],
        treeSelect:[],
        defaultProps:{
          children:'judge',
          label:'name'
        },
        /*table框*/
        /*被评人员*/
        classesTimeSetTable:[],
        /*评委table*/
        JdgClassesTimeSetTable:[],
        /*send ajax params*/
        _id:'',
        editState:'init',
        oldGroupId:'',
        needWatchState:false
      }
    },
    methods:{
      evaluationChange(value){
        if(this.needWatchState&&this.editState==='edited'){
          this.groupId = this.oldGroupId;
          this.$confirm('是否保存当前设置?', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            this.saveAjax();
          }).catch(() => {
            this.needWatchState = false;
            setTimeout(()=>{
              this.needWatchState = true;
            },350);
            this.editState = 'init';
            this.groupId = value;
            this.getIsDudges();
            this.getIsTable();
            this.getDudges();
            this.getTable();
          });
          return;
        }
        this.needWatchState = false;
        setTimeout(()=>{
          this.needWatchState = true;
        },350);
        this.oldGroupId = value;
        this.getIsDudges();
        this.getIsTable();
        this.getDudges();
        this.getTable();
      },
      /*点击返回流程图按钮*/
      goBackChart(){
        this.$router.push({name:'evaluationManagement'});
      },
      /*被考评人分组*/
      chooseCourse(event){
        const e=$(event.currentTarget);
        e.addClass('activeLi');
        e.siblings().removeClass('activeLi');
        this.curriculumId=e.data('id');
        this.curriculumName=e.text();
      },
      /*el-tree*/
      /*el-tree复选框选中发生变化时回调*/
      /*被评人员---------*/
      /*table删除*/
      /*被评人员选择*/
      AppraisedCheckChange(data,value){
        if('child' in data){
          return false;
        }
        else{
          if(value){
            /*选中*/
            this.classesTimeSetTable.push(data);
          }
          else{
            /*取消选中*/
            for(let i=0;i<this.classesTimeSetTable.length;i++){
              if(this.classesTimeSetTable[i].id==data.id){
                /*删除该项*/
                this.classesTimeSetTable.splice(i,1);
                break;
              }
            }
          }
        }
      },
      AppraisedDelete(idx){
        this.$refs['AppraisedTree'].setChecked(this.classesTimeSetTable[idx].id,false);
        this.classesTimeSetTable.splice(idx,1);
      },
      /*候选评委-----------*/
      /*table删除*/
      JdgDelete(idx){
        this.$refs['judgeTree'].setChecked(this.JdgClassesTimeSetTable[idx].id,false);
        this.JdgClassesTimeSetTable.splice(idx,1);
      },
      /*评委选择*/
      checkChange(data,value){
        if('judge' in data){
          return false;
        }
        else{
          if(value){
            /*选中*/
            this.JdgClassesTimeSetTable.push(data);
          }
          else{
            /*取消选中*/
            for(let i=0;i<this.JdgClassesTimeSetTable.length;i++){
              if(this.JdgClassesTimeSetTable[i].id==data.id){
                /*删除该项*/
                this.JdgClassesTimeSetTable.splice(i,1);
                break;
              }
            }
          }
        }
      },
      /*send ajax*/
      /*被考评分组*/
      getIsGroup(){
        addEvaluationPersonGroup({id:this._id}).then(data=>{
          if(data.length>0){
            this.IsEvaluationOption=data;
            this.groupId=data[0].id;
            this.getIsDudges();
            this.getIsTable();
            this.getDudges();
            this.getTable();
          }
          else{
            this.IsEvaluationOption=[];
          }
        })
      },
      /*AppraisedTreeData、AppraisedTreeSelect、classesTimeSetTable、treeData、JdgClassesTimeSetTable、treeSelect*/
      /*被考评人员*/
      getIsDudges(){
        this.isLoading=true;
        this.AppraisedTreeData=[];
        this.AppraisedTreeSelect=[];
        addEvaluationPersonIsDudges({id:this._id,groupId:this.groupId}).then(data=>{
          if(data.status){
            this.AppraisedTreeData=data.data;
            this.AppraisedTreeSelect=data.selected;
          }
          else{
            this.AppraisedTreeData=[];
            this.AppraisedTreeSelect=[];
          }
          this.isLoading=false;
        });
      },
      /*被考评table*/
      getIsTable(){
        this.isLoading=true;
        this.classesTimeSetTable=[];
        addEvaluationPersonIsTable({groupId:this.groupId}).then(data=>{
          if(data.status&&data.data){
            this.classesTimeSetTable=data.data;
          }
          else{
            this.classesTimeSetTable=[];
          }
          this.isLoading=false;
        });
      },
      /*候选人人员*/
      getDudges(){
        this.isLoading=true;
        this.treeData=[];
        this.treeSelect=[];
        addEvaluationPersonDudges({id:this._id,groupId:this.groupId}).then(data=>{
          if(data.status){
            this.treeData=data.data;
            this.treeSelect=data.selected;
          }
          else{
            this.treeData=[];
            this.treeSelect=[];
          }
          this.isLoading=false;
        });
      },
      /*候选人table*/
      getTable(){
        this.isLoading=true;
        this.JdgClassesTimeSetTable=[];
        addEvaluationPersonTable({groupId:this.groupId}).then(data=>{
          if(data.status){
            this.JdgClassesTimeSetTable=data.data;
          }
          else{
            this.JdgClassesTimeSetTable=[];
          }
          this.isLoading=false;
        });
      },
      /*保存*/
      saveAjax(){
        let newArr = this.classesTimeSetTable.concat(this.JdgClassesTimeSetTable).map(val=>val.id);
        let finalArr = [...new Set(newArr)];
        if(finalArr.length<newArr.length){
          this.vmMsgWarning( '评委和被评人员不能是同一人' ); return;
        }
        let group_num = this.treeData.length,
          group = 0;
        this.treeData.forEach(val=>{
          if(val.judge.some((ssVal)=>{
              return this.JdgClassesTimeSetTable.some(subVal=>ssVal.id===subVal.id)
            })){
            group++;
          }
        });
        if(group_num!==group){
          this.vmMsgWarning( '需要选择所有的评委分组' ); return;
        }
        if(this.classesTimeSetTable.length>0){
          if(this.JdgClassesTimeSetTable.length>0){
            addEvaluationPersonLoad({id:this._id,groupId:this.groupId,type:'save',personnel:this.classesTimeSetTable,judge:this.JdgClassesTimeSetTable}).then(data=>{
              this.simplePrompt(data,'保存')
            });
          }
          else{
            this.vmMsgWarning( '请添加评委' );
          }
        }
        else{
          this.vmMsgWarning( '请添加被评人员' );
        }
      },
      simplePrompt(data,msg){
        if(data.status){
          this.vmMsgSuccess( msg+'成功！' );
          this.needWatchState = false;
          setTimeout(()=>{
            this.needWatchState = true;
          },350);
          this.editState='saved';
          this.getIsDudges();
          this.getIsTable();
          this.getDudges();
          this.getTable();
        }
        else{
          this.vmMsgError( msg+'失败！' );
        }
      },
      setEditState(){
        if(this.needWatchState){
          this.editState='edited';
        }
      }
    },
    created(){
      this._id=this.$route.params.id;
      this.getIsGroup();
    },
    watch:{
      'classesTimeSetTable.length':'setEditState',
      'JdgClassesTimeSetTable.length':'setEditState'
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/style';
  @import '../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.css';
  @import '../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.less';
  .g-sa_header_search{margin-top:20/16rem;}
  .g-threePart div.g-sectionL{width:17.5%;}
  .g-threePart div.g-sectionC{width:29.5%;}
  .g-threePart div.g-sectionL.centerTable{margin-left:20/16rem;}
</style>


