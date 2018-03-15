<template>
  <div class="g-container programManagement">
    <header class="g-textHeader g-flexStartRow">
      <div class="g-headerButtonGroup">
        <h2>方案管理</h2>
      </div>
    </header>
    <section>
      <header class="g-textHeader">
        <div class="g-liOneRow g-sa_header_search">
          <div class="gs-button alertsBtn">
            <el-button type="primary" @click="totalApproval"><i class="el-icon-plus"></i>新建方案</el-button>
          </div>
          <!--          <div class="gs-refresh g-fuzzyInput">
                      <el-input type="text" v-model="fuzzyInput" suffix-icon="el-icon-search" placeholder="班级/姓名" @change="fuzzyClick"></el-input>
                    </div>-->
        </div>
      </header>
      <section class="centerTable alertsList">
        <el-table
          v-loading.body="isLoading"
          element-loading-text="拼命加载中..."
          class="g-NotHover g-fixedColumn" :data="classesTimeSetTable" @selection-change="handleSelectionChange" @sort-change="sortChange">
          <el-table-column label="方案名称" prop="programmeName"></el-table-column>
          <el-table-column label="考核方向" prop="directionName"></el-table-column>
          <el-table-column label="考核进度">
            <template slot-scope="props">
              <div class="g-dialogDiv" @click="detailDialogShow(props.row.programmeId)">
                <span class="g-processPrompt" v-text="props.row.schedule"></span>
                <el-progress :text-inside="true" :show-text="false" :stroke-width="20" :percentage="props.row.percentage"></el-progress>
              </div>
            </template>
          </el-table-column>
          <el-table-column label="开始时间" prop="startTime"></el-table-column>
          <el-table-column label="结束时间" prop="endTime"></el-table-column>
          <el-table-column label="创建时间" prop="createTime"></el-table-column>
          <el-table-column label="操作" width="150" fixed="right">
            <template slot-scope="props">
              <el-button type="text" @click="handleClick(props.$index)">编辑</el-button>
              <el-button @click="deleteClick(props.row.programmeId)" class="deleteColor" type="text">删除</el-button>
            </template>
          </el-table-column>
        </el-table>
      </section>
      <el-dialog class="g-tree_content g-defineDialog headerNotBackground" :title="dialogTitle" :modal="false" :visible.sync="isDialog" :before-close="handlerClose">
        <el-form :model="gradeDialogForm" :rules="dialogRules" ref="dialogForm" label-width="90px" label-position="right">
          <el-form-item label="方案名称:" prop="programmeName" required>
            <el-input v-model="gradeDialogForm.programmeName" placeholder="请输入方案名称"></el-input>
          </el-form-item>
          <el-form-item label="考核范围:" prop="gradeid" required>
            <el-select :disabled="ajaxType=='handler'" v-model="gradeDialogForm.gradeid" style="width:100%;">
              <el-option v-for="(content,index) in rangeData" :key='index' :label="content.name" :value="content.gradeid"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="起始时间:" prop="startTime" required>
            <el-date-picker type="date" :editable="false" :picker-options="startPickerOption" placeholder="选择日期" v-model="gradeDialogForm.startTime" style="width: 100%;"></el-date-picker>
          </el-form-item>
          <el-form-item label="结束时间:" prop="endTime" required>
            <el-date-picker type="date" :editable="false" :picker-options="endPickerOption" placeholder="选择日期" v-model="gradeDialogForm.endTime" style="width: 100%;"></el-date-picker>
          </el-form-item>
          <el-form-item label="考核方向:" prop="directionId" required>
            <el-select :disabled="ajaxType=='handler'" v-model="gradeDialogForm.directionId" style="width:100%;">
              <el-option v-for="(content,index) in directData" :key='index' :label="content.directionName" :value="content.directionId"></el-option>
            </el-select>
          </el-form-item>
          <div class="g-dialogOther g-flexStartRow">
            <span>评分权重:</span>
            <!-- v-if="gradeDialogForm.isPersonScore"-->
            <div class="g-dialogOther g-flexStartRow">
              <div>
                <span>班主任:</span>
                <el-input :disabled="ajaxType=='handler'" @change="dialogInputChange" class="dialogInput defineInputWidth" v-model="gradeDialogForm.headmaster"></el-input>
                <span>%</span>
              </div>
              <div>
                <span>任课老师:</span>
                <el-input :disabled="ajaxType=='handler'" @change="dialogInputChange" class="dialogInput defineInputWidth" v-model="gradeDialogForm.teacher"></el-input>
                <span>%</span>
              </div>
              <div>
                <span>学生本人:</span>
                <el-input :disabled="ajaxType=='handler'" @change="dialogInputChange" class="dialogInput defineInputWidth" v-model="gradeDialogForm.student"></el-input>
                <span>%</span>
              </div>
            </div>
          </div>
        </el-form>
        <div class="g-button">
          <el-button @click="confirmClick" type="primary">确定</el-button>
          <el-button @click="cancelClick">取消</el-button>
        </div>
      </el-dialog>
      <el-dialog class="g-tree_content g-defineDialog headerNotBackground" title="考核进度" :modal="false" :visible.sync="isDetailDialog" :before-close="handlerDetailClose">
        <div class="g-dialogContent alertsList">
          <el-radio-group @change="detailDialogChange" class="g-dialogRadio" v-model="detailRadioV">
            <el-radio label="headmaster">班主任</el-radio>
            <el-radio label="teacher">任课教师</el-radio>
            <el-radio label="student">学生</el-radio>
          </el-radio-group>
          <el-table class="g-fixedColumn" :data="dialogTable">
            <el-table-column v-for="(col,colI) in columnData" :key="colI" :label="col.titleName" :prop="col.props"></el-table-column>
          </el-table>
        </div>
        <footer class="g-footer">
          <el-row class="pageAlerts">
            <el-pagination
              @current-change="handleCurrentChange"
              :current-page.sync="currentPage"
              :page-size="pageCount"
              layout="prev, pager, next, jumper"
              :total="pageALl">
            </el-pagination>
          </el-row>
        </footer>
      </el-dialog>
    </section>
  </div>
</template>
<script>
  import {
    programManagementLoad,//加载
    programManagementRange,//考核范围
    programManagementDirect,//考核方向
    programManagementNew,//新建
    programManagementDel,//删除
    programManagementUpdate,//修改
    programManagementDetail,//进度详情
  } from '@/api/http'
  import moment from 'moment'
  export default{
    data(){
      let _self=this;
      return{
        isLoading:false,
        returnD:'',
        /*模糊查询*/
        fuzzyInput:'',
        evaluationName:'',
        /*table*/
        classesTimeSetTable:[],
        /*考核进度弹框*/
        isDetailDialog:false,
        detailRadioV:'headmaster',
        dialogTable:[],
        dialogTotalTable:[],
        detailData:[],
        columnData:[],
        /*footer*/
        pageALl:1,
        currentPage:1,
        pageCount:10,//每页数据条数
        /*弹框*/
        isDialog:false,
        dialogTitle:'添加考核方向',
        gradeDialogForm:{
          programmeName:'',
          programmeId:'',
          gradeid:'',
          startTime:'',
          endTime:'',
          directionId:'',
          headmaster:'',
          teacher:'',
          student:'',
        },
        rangeData:[],
        directData:[],
        dialogRules:{
          programmeName:[{required:true,message:'请输入方案名称',trigger: 'blur'}],
          gradeid:[{required:true,message:'请选择考核范围',trigger: 'blur'}],
          startTime:[{required:true,message:'请选择起始时间',trigger: 'blur'}],
          endTime:[{required:true,message:'请选择结束时间',trigger: 'blur'}],
          directionId:[{required:true,message:'请选择考核方向',trigger: 'blur'}],
        },
        startPickerOption:{
          disabledDate(time){
            if(_self.gradeDialogForm.endTime){
              return time.getTime()>Date.parse(_self.gradeDialogForm.endTime);
            }else{
              return time.getTime()<Date.now()-8.64e7;
            }
          }
        },
        endPickerOption:{
          disabledDate(time){
            if(_self.gradeDialogForm.startTime){
              return time.getTime()<Date.parse(_self.gradeDialogForm.startTime);
            }else{
              return time.getTime()<Date.now()-8.64e7;
            }
          }
        },
        /*tree*/
        treeData:[],
        defaultProps: {
          children: 'data',
          label:'techerName',
        },
        /*send ajax param*/
        ajaxType:'',
      }
    },
    methods:{
      /*点击返回流程图按钮*/
      goBackChart(){
        this.$router.push({name:'evaluationManagement'});
      },
      /*模糊查询*/
      fuzzyClick(){
        /*模糊查询执行回调*/
      },
      /*考核进度弹框*/
      handlerDetailClose(done){
        done();
      },
      /*radio的change事件*/
      detailDialogChange(){
        this.dialogTotalTable=this.detailData[this.detailRadioV].data;
        this.currentPage=1;
        this.columnData=this.detailData[this.detailRadioV].title;
        /*总条数*/
        if(this.detailData[this.detailRadioV].data){
          if(this.detailData[this.detailRadioV].data.length>0){
            this.pageALl=this.detailData[this.detailRadioV].data.length;
          }
          else{
            this.pageALl=1;
          }
        }
        this.changePageMsg();
      },
      /*footer*/
      handleCurrentChange(val) {
        this.currentPage = val;
        this.changePageMsg();
      },
      /*更改页面展示信息*/
      changePageMsg(){
        this.dialogTable=[];
        if(this.currentPage*this.pageCount>this.dialogTotalTable.length){
          /*最后一页*/
          for(let i=((this.currentPage-1)*this.pageCount);i<this.dialogTotalTable.length;i++){
            this.dialogTable.push(this.dialogTotalTable[i]);
          }
        }
        else{
          for(let i=((this.currentPage-1)*this.pageCount);i<this.currentPage*this.pageCount;i++){
            this.dialogTable.push(this.dialogTotalTable[i]);
          }
        }
      },
      /*弹框*/
      handlerClose(done){
//        this.$confirm('确定退出？','提示',{
//          confirmButtonText:'确定',
//          cancelButtonText:'取消',
//          type:'warning'
//        }).then(()=>{
        done();
//        }).catch(()=>{});
      },
      /*取消*/
      cancelClick(){
//        this.$confirm('确定退出？','提示',{
//          confirmButtonText:'确定',
//          cancelButtonText:'取消',
//          type:'warning'
//        }).then(()=>{
        this.isDialog=false;
//        }).catch(()=>{});
      },
      /*权重处input框change事件，判断输入是否合法*/
      dialogInputChange(newVal){
        if(isNaN(Number(newVal))){
          this.vmMsgWarning( '请输入数值！' );
        }
      },
      /*确定*/
      confirmClick(){
        if(!this.gradeDialogForm.startTime){
          this.vmMsgWarning( '请选择起始时间' );
          return;
        }
        if(!this.gradeDialogForm.endTime){
          this.vmMsgWarning("请选择结束时间");
          return;
        }
        let _total=0;
        _total=Number(this.gradeDialogForm.headmaster)+Number(this.gradeDialogForm.teacher)+Number(this.gradeDialogForm.student);
        if(_total!=100){
          this.vmMsgWarning('评分权重综合必须为100%');
          return false;
        }
        this.$refs.dialogForm.validate((valid)=>{
          if(valid){
            if(this.ajaxType=='handler'){
              this.handlerAjax();
            }
            else{
              this.addAjax();
            }
          }
        });
      },
      /*table*/
      fuzzyTableClick(){},
      handleSelectionChange(val){
      },
      sortChange(column,prop,order){},
      handleClick(idx){
        this.isDialog=true;
        this.ajaxType='handler';
        this.dialogTitle='修改考核方案';
        this.gradeDialogForm.programmeName=this.classesTimeSetTable[idx].programmeName;
        this.gradeDialogForm.programmeId=this.classesTimeSetTable[idx].programmeId;
        this.gradeDialogForm.startTime=this.classesTimeSetTable[idx].startTime;
        this.gradeDialogForm.endTime=this.classesTimeSetTable[idx].endTime;
        this.gradeDialogForm.directionId=this.classesTimeSetTable[idx].directionId;
        this.gradeDialogForm.gradeid=this.classesTimeSetTable[idx].gradeid;
        this.gradeDialogForm.headmaster=this.classesTimeSetTable[idx].headmaster;
        this.gradeDialogForm.teacher=this.classesTimeSetTable[idx].teacher;
        this.gradeDialogForm.student=this.classesTimeSetTable[idx].student;
      },
      /*批量审批*/
      totalApproval(){
        if(!this.returnD){
          this.vmMsgWarning('不能新建方案');
          return;
        }
        this.isDialog=true;
        this.dialogTitle='创建考核方案';
        this.ajaxType='add';
        this.gradeDialogForm={
          programmeName:'',
          programmeId:'',
          gradeid:'',
          startTime:'',
          endTime:'',
          directionId:'',
          headmaster:'',
          teacher:'',
          student:'',
        };
      },
      /*send ajax*/
      getLoadAjax(){
        this.isLoading=true;
        programManagementLoad().then(data=>{
          this.returnD=data.return;
          if(Array.isArray(data.data)){
            this.classesTimeSetTable=data.data;
          }
          else{
            this.classesTimeSetTable=[];
          }
          this.isLoading=false;
        });
      },
      getRangeAjax(){
        programManagementRange().then(data=>{
          if(Array.isArray(data)){
            this.rangeData=data;
          }
          else{
            this.rangeData=[];
          }
        });
      },
      getDirectAjax(){
        programManagementDirect().then(data=>{
          if(Array.isArray(data)){
            this.directData=data;
          }
          else{
            this.directData=[];
          }
        });
      },
      /*删除*/
      deleteClick(programmeId){
        this.$confirm('确定删除此方案吗？','提示',{
          confirmButtonText:'确定',
          cancelButtonText:'取消',
          type:'warning'
        }).then(()=>{
          programManagementDel({programmeId:programmeId}).then(data=>{
            this.simplePrompt(data,'删除成功！','删除失败，请重试！');
            this.getLoadAjax();
          });
        }).catch(()=>{});
      },
      /*添加*/
      addAjax(){
        let startTime = this.gradeDialogForm.startTime,endTime = this.gradeDialogForm.endTime;
        if(typeof startTime !== 'string'){
          startTime=moment(startTime).format('YYYY-MM-DD');
          endTime=moment(endTime).format('YYYY-MM-DD');
        }
        let param={
          programmeName:this.gradeDialogForm.programmeName,
          programmeId:this.gradeDialogForm.programmeId,
          gradeid:this.gradeDialogForm.gradeid,
          startTime:startTime,
          endTime:endTime,
          directionId:this.gradeDialogForm.directionId,
          headmaster:this.gradeDialogForm.headmaster,
          teacher:this.gradeDialogForm.teacher,
          student:this.gradeDialogForm.student,
        };
        programManagementNew(param).then(data=>{
          if(data.return==true){
            this.vmMsgSuccess( '添加成功' );
            this.isDialog=false;
          }
          if(data.return==false){
            this.vmMsgError( data.msg );
          }
          this.getLoadAjax();
        });
      },
      /*修改*/
      handlerAjax(){
        let startTime = this.gradeDialogForm.startTime,endTime = this.gradeDialogForm.endTime;
        if(typeof startTime !== 'string'){
          startTime=moment(startTime).format('YYYY-MM-DD');
          endTime=moment(endTime).format('YYYY-MM-DD');
        }
        let param={
          programmeName:this.gradeDialogForm.programmeName,
          programmeId:this.gradeDialogForm.programmeId,
          gradeid:this.gradeDialogForm.gradeid,
          startTime:startTime,
          endTime:endTime,
          directionId:this.gradeDialogForm.directionId,
          headmaster:this.gradeDialogForm.headmaster,
          teacher:this.gradeDialogForm.teacher,
          student:this.gradeDialogForm.student,
        };
        programManagementUpdate(param).then(data=>{
          if(data.return==true){
            this.isDialog=false;
          }
          this.simplePrompt(data,'修改成功！','修改失败！');
          this.getLoadAjax();
        })
      },
      detailDialogShow(programmeId){
        this.detailRadioV='headmaster';
        this.isDetailDialog=true;
        programManagementDetail({programmeId:programmeId}).then(data=>{
          /*所有总数据*/
          this.detailData=data;
          /*当前radio的总数据*/
          this.dialogTotalTable=data[this.detailRadioV].data;
          this.currentPage=1;
          this.columnData=data[this.detailRadioV].title;
          /*总条数*/
          if(data[this.detailRadioV].data.length>0){
            this.pageALl=data[this.detailRadioV].data.length;
          }
          else{
            this.pageALl=1;
          }
          this.changePageMsg();
        });
      },
      simplePrompt(data,suceessmsg,errMsg){
        if(data.return){
          this.vmMsgSuccess( suceessmsg );
          this.getLoadAjax();
        }
        else{
          this.vmMsgError( errMsg );
        }
      },
    },
    created(){
      this.getLoadAjax();
      this.getRangeAjax();
      this.getDirectAjax();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/test';
  @import '../../../../style/style';
  .g-sa_header_search{.marginTop(32);.marginBottom(20);}
  .headerNotBackground.g-tree_content{margin-top:0;
    .g-sectionR{.width(585,940);margin:0;}
    .g-sectionL{.width(330,940);.NotLineheight(541);}
  }
  /*进度条上的文字*/
  .g-processPrompt{position:absolute;z-index:10;}
  /*弹框*/
  .g-tree_content h2{text-align:center;.fontSize(12);color:@HColor;padding-bottom:20/16rem;}
  .g-tree_content .el-dialog--tiny{.NotLineheight(560);}
  /*容器*/
  .gs-button button{
    i{.fontSize(14);margin-right:10/16rem;}
  }
  .g-tree_content .g-flexStartRow>div:not(:first-of-type){margin-left:1rem;}
  .g-dialogOther{padding-left:15/16rem;padding-bottom:20/16rem;}
  .g-dialogDiv:hover{cursor:pointer;}
  /*考核进度*/
  .g-dialogRadio{margin-bottom:20/16rem;}
</style>
<style>
  .programManagement .el-input__inner{
    width: 100%;
    padding: 0 5px;
    text-align: center;
  }
</style>

