<template>
  <div class="g-container">
    <header class="g-importCourseHeader g-liOneRow">
      <div class="g-textHeader g-flexStartRow selfSpace">
        <el-button class="g-gobackChart g-imgContainer RedButton" @click="goBackChart">
          <img src="../../../assets/img/schManagementSystem/teachingAdministration/arrangeClasses/icon_return.png" />
          返回流程图
        </el-button>
        <h2 class="selfCenter">分班成绩合成设置</h2>
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
            </el-button-group>
          </div>
          <div>
            <el-button type="primary" @click="SaveAll()" style="padding:.76rem 1.6rem;">保存</el-button>
          </div>
        </div>
        <div class="gs-table centerTable alertsList">
          <el-table
            v-loading.body="isLoading"
            element-loading-text="拼命加载中..."
            class="g-NotHover" ref="studentMsgTable" :data="headerButtonData.studentBasicMsg" style="width:100%" @sort-change="sortChange" @selection-change="handleStudentTable">
            <!--show-overflow-tooltip 超出部分省略号显示-->
            <el-table-column label="序号" type="index" width="80px"></el-table-column>
            <el-table-column label="考试名称" prop="examination"></el-table-column>
            <el-table-column label="计入总分分数">
              <template slot-scope="props">
                <div v-text="Number(props.row.examRatio)"></div>
              </template>
            </el-table-column>
            <el-table-column label="依据科目">
              <template slot-scope="props">
                <div class="g-flexStartRow">
                  <div class="g-tagContainer">
                    <sl-tag class="g-redTag"
                            @click="changeSubject(content,props.$index,n)"
                            v-for="(content,n) in props.row.sub"
                            :key="n" closable :disable-transitions="false"
                            @close="handleClose(props.row.sub,n)">{{content.subject}}({{numMulti(100,content.subRatio)}}%)
                    </sl-tag>
                  </div>
                  <div class="el-icon-plus selfCenter" @click="addSubject(props.$index)"><!--添加按钮--></div>
                </div>
              </template>
            </el-table-column>
            <el-table-column label="操作" width="180px" fixed="right">
              <template slot-scope="props">
                <!--:disabled="editState==='edited'||getTotalRatio(props.row.sub)!==1"-->
                <el-button @click="importScore(props.row.examId)"  type="text">导入成绩</el-button>
                <el-button @click="changeClick(props.$index,props.row.examId)" type="text">编辑</el-button>
                <el-button @click="deleteExam(props.row.examId)" class="deleteColor" type="text">删除</el-button>
              </template>
            </el-table-column>
          </el-table>
        </div>
      </section>
    </div>
    <el-dialog class="headerNotBackground" :title="ExamDialog" :modal="false" :visible.sync="isExamDialog" :before-close="handlerExamClose">
      <el-form ref="examForm" :rules="rulesExam" :model="dialogExamForm" label-width="125px" label-position="right">
        <el-form-item label="依据考试名称:" prop="exam">
          <el-input placeholder="请输入考试名称" v-model="dialogExamForm.exam"></el-input>
        </el-form-item>
        <el-form-item label="计入总分分数:" prop="ratio">
          <el-input placeholder="请输入计入总分分数" type="number" v-model="dialogExamForm.ratio"></el-input>
        </el-form-item>
      </el-form>
      <div class="g-button">
        <el-button @click="saveExamClick" type="primary">保存</el-button>
        <el-button @click="cancelExam">取消</el-button>
      </div>
    </el-dialog>
    <el-dialog class="headerNotBackground" :title="subjectDialog" :modal="false" :visible.sync="isSubjectDialog" :before-close="handlerSubjectClose">
      <el-form ref="subjectForm" label-width="110px" label-position="right">
        <el-form-item label="科目名称:" prop="subject">
          <el-input placeholder="如：语文" v-model="dialogSubjectForm.subject"></el-input>
        </el-form-item>
        <el-form-item label="科目满分:" prop="maxPoint">
          <el-input placeholder="如：100" type="number" v-model="dialogSubjectForm.maxPoint"></el-input>
        </el-form-item>
        <el-form-item label="统计比例(%):" prop="ratio">
          <el-input placeholder="如：20" type="number" v-model="dialogSubjectForm.ratio"></el-input>
        </el-form-item>
      </el-form>
      <div class="g-button">
        <el-button @click="addSubjectSave" type="primary">确定</el-button>
        <el-button @click="cancelSubject">取消</el-button>
      </div>
    </el-dialog>
  </div>
</template>
<script>
  import slTag from '@/components/tag.vue';
  import {
    organizeResultsSet,//操作
  } from '@/api/http'
  export default{
    components:{
      slTag
    },
    data(){
      return{
        isLoading:false,
        /*ajax data*/
        editState:'init',
        headerButtonData:{
          studentBasicMsg:[],
        },
        /*fuzzyFilter*/
        fuzzyInput:'',
        rootIndex2edit:0,
        index2edit:0,
        /*弹框---------*/
        /*添加依据考试*/
        isExamDialog:false,
        ExamDialog:'添加依据考试',
        /*form*/
        dialogExamForm:{
          exam:'',
          ratio:'',
        },
        /*添加依据科目*/
        isSubjectDialog:false,
        subjectDialog:'添加依据科目',
        /*form*/
        dialogSubjectForm:{
          subject:'',
          maxPoint:'',
          ratio:'',
        },
        /*send ajax params*/
        gradeId:'',
        examId:'',
        ajaxType:'',
        /*rule*/
        rulesExam:{
          exam:[{required:true,message:'请输入依据考试名称'}],
          ratio:[
            {required:true,message:'请输入总分比例'},
//            { type: 'number', message: '总分比例必须为数字值',trigger:'change'}
          ],
        },
        rulesSub:{
          subject:[{required:true,message:'请输入科目名称'}],
          maxPoint:[{required:true,message:'请输入科目满分'}],
          ratio:[
            {required:true,message:'请输入统计比例'},
//            { type: 'number', message: '总分比例必须为数字值',trigger:'change'}
          ],
        },
      }
    },
    computed: {},
    methods:{
      SaveAll(){
        let Subjects = this.headerButtonData.studentBasicMsg;
        let param={
          type:'save',
          gradeId:this.gradeId,
          subjects:Subjects
        };
        if(Subjects.some(val=>{
            return val.sub.reduce((prev,cur)=>{
                return {subRatio:Number(prev.subRatio) + Number(cur.subRatio)}
              },{subRatio:0}).subRatio!==1;
          })){
          this.vmMsgWarning('依据科目占比和必须等于100％');
          return;
        }
        this.$confirm('是否保存依据科目？','提示',{
          confirmButtonText:'确定',
          cancelButtonText:'取消',
          type:'warning'
        }).then(()=>{
          organizeResultsSet(param).then(data=>{
            if(data.status){
              this.vmMsgSuccess(data.msg);
              this.editState='saved';
              this.getLoadAjax();
            }
            else{
              this.vmMsgWarning(data.msg);
            }
          });
        }).catch(()=>{});
      },
      /*点击返回流程图按钮*/
      goBackChart(){
        this.$router.push({name:'newStudentClass'});
      },
      /*table*/
      handleStudentTable(section){
        /*section为选择项行信息组成的数组*/
      },
      sortChange(column){
        /*table排序回调*/

      },
      /*导入成绩*/
      importScore(examId){
        if(this.editState==='edited'){
          this.vmMsgWarning('请先保存修改科目');
          return;
        }
        this.$router.push({name:'importExam',params:{gradeId:this.gradeId,examId:examId}});
      },
      /*考试依据*/
      /*header的button群*/
      /*添加*/
      addClick(){
        this.isExamDialog=true;
        this.ExamDialog='添加依据考试';
        this.ajaxType='addExam';
        this.examId='';
      },
      /*编辑*/
      changeClick(index,examId){
        this.examId=examId;
        this.ajaxType='editExam';
        this.isExamDialog=true;
        this.ExamDialog='修改依据考试';
        this.dialogExamForm.exam=this.headerButtonData.studentBasicMsg[index]['examination'];
        this.dialogExamForm.ratio=this.headerButtonData.studentBasicMsg[index]['examRatio'];
      },
      /*取消*/
      handlerExamClose(done){
        this.$refs.examForm.resetFields();
        done();
      },
      cancelExam(){
        this.$confirm('确定退出？','提示',{
          confirmButtonText:'确定',
          cancelButtonText:'取消',
          type:'warning'
        }).then(()=>{
          this.isExamDialog=false;
          this.$refs.examForm.resetFields();
        }).catch(()=>{});
      },
      /*考试科目*/
      /*添加依据科目*/
      addSubject(rootIndex){
        this.rootIndex2edit = rootIndex;
        this.dialogSubjectForm = {
          subject:'',
          maxPoint:'',
          ratio:'',
        };
        this.subjectDialog='添加依据科目';
//        this.examId=examId;
        this.isSubjectDialog=true;
      },
      cancelSubject(){
//        this.$confirm('确定退出？','提示',{
//          confirmButtonText:'确定',
//          cancelButtonText:'取消',
//          type:'warning'
//        }).then(()=>{
        this.isSubjectDialog=false;
//          this.$refs['subjectForm'].resetFields();
//        }).catch(()=>{});
      },
      /*修改依据科目*/
      changeSubject(row,rootIndex,index){
        this.subjectDialog='修改依据科目';
        this.rootIndex2edit = rootIndex;
        this.index2edit = index;
        this.dialogSubjectForm = {
          subId:row.subId,
          ratio:row.subRatio*100,
          maxPoint:row.maxPoint||'',
          subject:row.subject,
        };
        this.isSubjectDialog=true;
      },
      /*弹框*/
      /*关闭按钮点击*/
      /*添加依据考试*/
      /*添加依据科目*/
      handlerSubjectClose(done){
        this.$refs['subjectForm'].resetFields();
        done();
      },
      /*send ajax*/
      getLoadAjax(){
        this.isLoading=true;
        organizeResultsSet({gradeId:this.gradeId}).then(data=>{
          if(data.status){
            this.headerButtonData.studentBasicMsg=data.data;
          }
          else{
            this.vmMsgWarning('暂无数据');
            this.headerButtonData.studentBasicMsg=[];
          }
          this.isLoading=false;
        })
      },
      /*考试依据*/
      saveExamClick(){
        this.$refs['examForm'].validate(valid=>{
          if(valid){
            this.isExamDialog=false;
            this.dialogExamForm.ratio=this.dialogExamForm.ratio;
            organizeResultsSet({gradeId:this.gradeId,examId:this.examId,type:this.ajaxType,...this.dialogExamForm}).then(data=>{
              this.getLoadAjax();
              if(data.status){
                if(this.ajaxType=='addExam'){
                  this.vmMsgSuccess(data.msg);
                }
                else{
                  this.vmMsgSuccess(data.msg);
                }
              }
              else{
                if(this.ajaxType=='addExam'){
                  this.vmMsgError(data.msg);
                }
                else{
                  this.vmMsgError(data.msg);
                }
              }
            });
          }
          this.$refs.examForm.resetFields();
        });
      },
      /*考试科目*/
      addSubjectSave(){
//        this.$refs['subjectForm'].validate(valid=>{
//          if(valid){
        if(!this.dialogSubjectForm.subject){
          this.vmMsgWarning('请输入科目名称');
          return;
        }
        if(!/^[1-9]\d*$/.test(this.dialogSubjectForm.maxPoint)){
          this.vmMsgWarning('科目满分格式不正确');
          return;
        }
        if(!this.dialogSubjectForm.ratio){
          this.vmMsgWarning('请输入统计比例');
          return;
        }
        if(this.subjectDialog.indexOf('添加')>-1){
          this.headerButtonData.studentBasicMsg[this.rootIndex2edit].sub.push({
            subId:'',
            subject:this.dialogSubjectForm.subject,//科目名
            subRatio:this.dialogSubjectForm.ratio/100,//比例 传小数
            maxPoint:this.dialogSubjectForm.maxPoint
          })
        }else{
          let sub = this.headerButtonData.studentBasicMsg[this.rootIndex2edit].sub[this.index2edit];
//          sub.subId=this.dialogSubjectForm.subId;//科目名
          sub.subject=this.dialogSubjectForm.subject;//科目名
          sub.subRatio=this.dialogSubjectForm.ratio/100;//比例 传小数
          sub.maxPoint=this.dialogSubjectForm.maxPoint;
        }
        this.editState='edited';
        this.isSubjectDialog=false;
      },
      /*tag标签*/
      handleClose(list,index){
        this.$confirm('是否删除依据科目？','提示',{
          confirmButtonText:'确定',
          cancelButtonText:'取消',
          type:'warning'
        }).then(()=>{
//          organizeResultsSet({type:'delSubject',gradeId:this.gradeId,subId:subId}).then(data=>{
//            if(data.status){
//              this.$message({
//                message:data.msg,
//                type:'success'
//              });
          list.splice(index, 1);
          this.editState='edited';
//              this.getLoadAjax();
//            }
//            else{
//              this.$message({
//                message:data.msg,
//                type:'error'
//              });
//            }
//          });
        }).catch(()=>{});
      },
      deleteExam(examId){
        this.$confirm('是否删除依据考试？','提示',{
          confirmButtonText:'确定',
          cancelButtonText:'取消',
          type:'warning'
        }).then(()=>{
          organizeResultsSet({type:'delExam',gradeId:this.gradeId,examId:examId}).then(data=>{
            if(data.status){
              this.vmMsgSuccess(data.msg);
              this.getLoadAjax();
            }
            else{
              this.vmMsgError(data.msg);
            }
          });
        }).catch(()=>{});
      },
      getTotalRatio(list){
        return list.reduce((prev,cur)=>{
          return Number(prev.subRatio) + Number(cur.subRatio);
        },{subRatio:0});
      },
      numMulti(num1, num2) {
        var baseNum = 0;
        try {
          baseNum += num1.toString().split(".")[1].length;
        } catch (e) {
        }
        try {
          baseNum += num2.toString().split(".")[1].length;
        } catch (e) {
        }
        return Number(num1.toString().replace(".", "")) * Number(num2.toString().replace(".", "")) / Math.pow(10, baseNum);
      }
    },
    created(){
      this.gradeId=this.$route.params.gradeId;
      this.getLoadAjax();
    }
  }
</script>
<style>
  .el-tag .el-icon-close{color: #ffffff}
</style>
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
  .gs-table li:hover{cursor:pointer;}
  .gs-table li:not(:first-of-type){margin-left:5/16rem;}
  .gs-table li:last-of-type{.widthRem(24);.height(24);border:1px solid @liColor;.box-sizing();text-align:center;color:@liColor;.fontSize(14);}
  .gs-table li:not(:last-of-type){.widthRem(60);.height(24);background:@liColor;color:#fff;.fontSize(14);.border-radius(4/16rem);}
  /*el-tag*/
  .g-redTag.el-tag{margin-right:10/16rem;color: #ffffff}
  .g-redTag.el-tag:hover{cursor:pointer;}
  /*加号图标*/
  .el-icon-plus{width:10%;.widthRem(24);.height(24);border:1px solid @liColor;.box-sizing();text-align:center;color:@liColor;.fontSize(14);}
  .el-icon-plus:hover{cursor:pointer;}
  .g-tagContainer{width:90%;}
</style>









