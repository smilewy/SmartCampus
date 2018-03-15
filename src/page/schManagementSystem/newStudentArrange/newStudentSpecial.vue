<template>
  <div class="specifiedStudentClass g-container">
    <header class="g-textHeader g-importCourseHeader">
      <div class="g-flexStartRow">
        <el-button class="g-gobackChart g-imgContainer RedButton" @click="goBackChart">
          <img src="../../../assets/img/schManagementSystem/teachingAdministration/arrangeClasses/icon_return.png" />
          返回流程图
        </el-button>
        <h2 class="selfCenter">指定学生到班</h2>
      </div>
    </header>
    <el-row :gutter="20" class="subClassDivision_row">
      <el-col :span="5">
        <el-row class="treeList">
          <el-row class="treeList_title">
            <el-row>
              <h5>待选班级</h5>
            </el-row>
          </el-row>
          <el-row class="d_line"></el-row>
          <el-row class="treeList_body">
            <el-tree
              v-loading.body="isLoading"
              element-loading-text="拼命加载中..."
              :data="treeData"
              node-key="id"
              ref="tree"
              @node-click="chooseStudent"
              :props="defaultProps">
            </el-tree>
          </el-row>
        </el-row>
      </el-col>
      <el-col :span="5">
        <el-row class="treeList">
          <el-row class="treeList_title">
            <el-row>
              <h5>{{classHeader}} <span class="classNum">（<span v-text="currentPerson">0</span>/<span v-text="totalPerson">80</span>人）</span></h5>
            </el-row>
          </el-row>
          <el-row class="d_line"></el-row>
          <el-row class="nameLists">
            <el-row v-for="(content,n) in studentShowData" v-text="" :key="n" class="nameList">
              {{content}}
              <i class="el-icon-close" @click="deleteData(n)"></i>
            </el-row>
          </el-row>
<!--          <el-row type="flex" justify="center">
            <el-col :span="22" class="search">
              <el-input placeholder="请输入添加学生名字" v-model="studentName">
              </el-input>
              <span class="searchBtn" @click="goSearchStudent">
                  <i class="el-icon-plus"></i>
              </span>
            </el-col>
          </el-row>-->
          <el-row class="btns">
            <el-button @click="save('clear')">清空</el-button>
            <el-button type="primary" @click="save">保存</el-button>
          </el-row>
        </el-row>
      </el-col>
      <el-col :span="14">
        <el-row class="studentsList">
          <div class="g-tableH centerTable alertsList">
            <header class="g-liOneRow">
              <h2>待选学生</h2>
              <div class="gs-refresh selfCenter g-fuzzyInput">
                <el-input type="text" v-model="fuzzyInput" placeholder="姓名" suffix-icon="el-icon-search" @change="getChooseStudent"></el-input>
              </div>
            </header>
            <el-table
              v-loading.body="isLoading"
              element-loading-text="拼命加载中..."
              :data="tableData"
              :max-height="600"
              style="width: 100%"
              border
              class="g-NotHover g-trHover"
              @row-click="tableRowClick"
              @sort-change="sort"
            >
              <el-table-column
                type="index"
                width="80"
                label="序号">
              </el-table-column>
              <el-table-column
                prop="name"
                label="姓名">
              </el-table-column>
              <el-table-column
                prop="sex"
                label="性别" sortable>
              </el-table-column>
              <el-table-column
                prop="publisher"
                label="合成成绩" sortable>
              </el-table-column>
              <el-table-column
                prop="class"
                label="班级" sortable>
              </el-table-column>
              <el-table-column
                label="是否为签约生">
                <template slot-scope="prop">
                  <span v-if="Number(prop.row.isSigRaw)">是</span>
                  <span v-else>否</span>
                </template>
              </el-table-column>
              <el-table-column
                prop="promise"
                label="签约承诺">
              </el-table-column>
            </el-table>
          </div>
        </el-row>
      </el-col>
    </el-row>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  import {
    newStudentSpecialSet,//操作
    newStudentGetGrade,//得到待选信息
  } from '@/api/http'
  export default{
    data(){
      return {
        isLoading:false,
        tableData: [],
        currentPage: 1,
        pageALl: 100,
        treeData:[],
        defaultProps: {
          children: 'child',
          label: 'name',
        },
        /*中间表格*/
        studentAjaxData:{
          stuIds:[],
        },
        studentShowData:[],
        classId:'',
        className:'',
        /*header处信息*/
        classHeader:'',
        totalPerson:0,
        currentPerson:0,
        oldArr:[],
        /*学生名字输入*/
        studentName:'',
        /*模糊查询*/
        fuzzyInput:'',
        /*send ajax*/
        gradeId:'',
      }
    },
    methods: {
      /*点击返回流程图按钮*/
      goBackChart(){
        this.$router.push({name:'newStudentClass'});
      },
      /*模糊查询*/
      fuzzyClick(){
        /*点击search按钮*/
      },
      goSearchStudent(){//查询
      },
      sort(column){
      },
      handleCurrentChange(val) {
        this.currentPage = val;
      },
      deleteData(idx){
        this.studentAjaxData.stuIds.splice(idx,1);
        this.studentShowData.splice(idx,1);
        this.currentPerson=this.studentAjaxData.stuIds.length;
      },
      /*table*/
      tableRowClick(row,event,column){
        if(this.classId){
          this.increasePerson(row);
        }
        else{
          this.vmMsgWarning('请选择指定到的班级！');
        }
      },
      /*添加人到中间表格*/
      increasePerson(row){
        /*row是增加的单条数据*/
        if(this.studentAjaxData.stuIds.length>0){
          for(let i=0;i<this.studentAjaxData.stuIds.length;i++){
            if(this.studentAjaxData.stuIds[i]==row.id){
              this.vmMsgWarning('该学生已经添加到分班！');
              break;
            }
            else{
              if(i==(this.studentAjaxData.stuIds.length-1)){
                this.studentAjaxData.stuIds.push(row.id);
                this.studentShowData.push(row.name);
                break;
              }
            }
          }
        }
        else{
          this.studentAjaxData.stuIds.push(row.id);
          this.studentShowData.push(row.name);
        }
        this.currentPerson=this.studentAjaxData.stuIds.length;
      },
      /*点击el-tree，删除上次点击的人*/
      deletePerson(row){
        /*row是删除的单条数据*/
        if(this.studentAjaxData.stuIds.length>0){
          for(let i=0;i<this.studentAjaxData.stuIds.length;i++){
            if(this.studentAjaxData.stuIds[i]==row.id){
              this.studentAjaxData.stuIds.splice(i,1);
              this.studentShowData.splice(i,1);
            }
          }
          this.currentPerson=this.studentAjaxData.stuIds.length;
        }
        else{
          return false;
        }
      },
      /*展开班级*/
      chooseStudent(value){
        if('stuId' in value){
          this.vmMsgWarning('请选择学生所在的班级,重新确定班级人员！');
        }
        else if('classId' in value){
          /*删除上次点击的数据*/
          this.oldArr.forEach((row)=>{
            this.deletePerson(row);
          });
          /*添加当前点击数据*/
          if(value.child instanceof Array){
            value.child.forEach((row)=>{
              this.increasePerson(row);
            });
            /*上一次点击el-tree添加的数组*/
            this.oldArr=value.child;
          }
          else{
            this.oldArr=[];
          }
          this.classHeader=value.name;
          this.classId=value.classId;
          this.className=value.name;
          this.totalPerson=value.total;
          this.currentPerson=value.number;
        }
      },
      /*send ajax*/
      getLoadAjax(){
        this.isLoading=true;
        newStudentGetGrade({func:'getClass',param:{gradeId:this.gradeId}}).then(data=>{
          if(data.status){
            this.treeData=data.data;
          }
          else{
            this.treeData=[];
          }
          this.isLoading=false;
        });
        this.getChooseStudent();
      },
      /*得到待选学生*/
      getChooseStudent(){
        this.isLoading=true;
        newStudentGetGrade({func:'getStu',param:{gradeId:this.gradeId,key:this.fuzzyInput}}).then(data=>{
          if(data.status){
            this.tableData=data.data;
          }
          else{
            this.tableData=[];
          }
          this.isLoading=false;
        });
      },
      save(type){
        if(this.classId){
          if(type=='clear'){  //清空
            this.$confirm('该操作不能退回，确定清空数据？','提示',{
              confirmButtonText:'确定',
              cancelButtonText:'取消',
              type:'warning'
            }).then(()=>{
              /*发送请求*/
              newStudentSpecialSet({gradeId:this.gradeId,type:'clean',classId:this.classId}).then(data=>{
                if(data.status){
                  this.studentAjaxData={stuIds:[]};
                  this.studentShowData=[];
                  this.getLoadAjax();
                  this.vmMsgSuccess('清空数据成功！');
                }
                else{
                  this.vmMsgError('清空数据失败，请重试！');
                }
              });
            }).catch(()=>{});
          }
          else{   //保存
            if(this.studentAjaxData.stuIds.length>0){
              newStudentSpecialSet({gradeId:this.gradeId,type:'save',...this.studentAjaxData,allot:{classId:this.classId,class:this.className}}).then(data=>{
                if(data.status){
                  this.studentAjaxData={stuIds:[]};
                  this.studentShowData=[];
                  this.getLoadAjax();
                  this.vmMsgSuccess('保存成功！');
                }
                else{
                  this.vmMsgError('保存失败，请重试！');
                }
              });
            }
            else{
              this.vmMsgWarning('没有保存数据！');
            }
          }
        }
        else{
          this.vmMsgWarning('请选择操作班级');
        }
      }
    },
    created(){
      this.gradeId=this.$route.params.gradeId;
      this.getLoadAjax();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../style/style';
  .g-textHeader{
    h2{.marginLeft(40,1582);}
  }
  .g-tableH{width:100%;}
  .specifiedStudentClass .treeList{
    border: 1px solid #d2d2d2;
    border-radius: 5px;
    height:52.25rem;
  }
  .specifiedStudentClass .treeList_body{
    padding:.875rem;
  }
  .specifiedStudentClass .treeList_title{
    padding:.875rem;
  }
  .specifiedStudentClass h5{
    font-size:1rem;
  }
  .specifiedStudentClass h5 .classNum{
    float: right;
    font-size:14px;
    font-weight:normal;
  }
  .specifiedStudentClass .classNum>span{
    color: #4da1ff;
  }
  .specifiedStudentClass .treeList .el-tree{
    border:none;
  }
  .specifiedStudentClass .alertsBtn{
    margin:0 0 1.25rem 0;
  }
  .specifiedStudentClass .nameList{
    padding:.625rem 1rem;
    font-size:.875rem;
    position: relative;
  }
  .specifiedStudentClass .nameList:hover{
    background-color: #deeefe;
  }
  .specifiedStudentClass .nameLists{
    height:40rem;
    overflow: auto;
  }
  .specifiedStudentClass .nameList i{
    font-size:12px;
    color: #ff5b5a;
    position: absolute;
    right:1rem;
    top:.625rem;
    cursor: pointer;
  }
  .specifiedStudentClass .nameList:hover>i{
    display: inline-block;
  }
  .specifiedStudentClass .btns{
    text-align: center;
    margin-top:1.25rem;
  }
  .specifiedStudentClass .btns .el-button{
    border-radius: 20px;
    width:6.25rem;
    padding:10px 0;
  }
  .specifiedStudentClass .search{
    position: relative;
  }
  .specifiedStudentClass .search .el-input__inner{
    border-radius: 18px;
    padding:3px 40px 3px 10px;
    border:1px solid #4da1ff;
  }
  .specifiedStudentClass .searchBtn{
    display: block;
    width:36px;
    height:36px;
    position: absolute;
    text-align: center;
    line-height:36px;
    font-size:14px;
    right:0;
    top:0;
    background-color: #4da1ff;
    color: #fff;
    border-radius: 100%;
    cursor: pointer;
  }
  .specifiedStudentClass .listHeader{
    height:3.375rem;
    background-color: #89bcf5;
    color: #fff;
    font-size:.875rem;
  }
  .specifiedStudentClass .studentsList .el-table th{
    background-color: #deeefe;
    height:3rem;
  }
  .specifiedStudentClass .studentsList .el-table td{
    height:3rem;
    font-size: .875rem;
  }
  .specifiedStudentClass .studentsList .el-table__footer-wrapper thead div, .specifiedStudentClass .studentsList .el-table__header-wrapper thead div{
    background-color: #deeefe;
  }
</style>
