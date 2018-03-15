<template>
  <div class="g-container">
    <header>
      <div class="g-textHeader g-liOneRow">
        <h2>新生信息管理</h2>
        <el-button @click="createGrade" class="g-gobackChart g-imgContainer blueButton">
          <img src="../../../assets/img/commonImg/icon_add_01.png" />
          创建年级
        </el-button>
      </div>
      <div class="g-selection">
        <el-form ref="studentMessge" lable-position="left" :model="dataHeader" label-width="55px">
          <el-form-item label="年级：">
            <el-select @change="getTableData" v-model="dataHeader.gradeId" placeholder="请选择年级">
              <el-option v-for="(content,index) in gradeAjaxData" :key="index" :label="content.znName" :value="content.id"></el-option>
            </el-select>
          </el-form-item>
        </el-form>
      </div>
    </header>
    <section class="g-section">
      <div class="gs-header g-liOneRow">
        <div class="gs-button alertsBtn">
          <el-button-group>
            <el-button @click="addClick" data-msg="add" class="filt" title="添加">
              <img class="filt_unactive" src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_add.png" />
              <img class="filt_active" src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_add_highlight.png" />
            </el-button>
            <el-button @click="deleteClick" data-msg="delete" class="filt" title="删除">
              <img class="filt_unactive" src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_delete.png" />
              <img class="filt_active" src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_delete_highlight.png" />
            </el-button>
            <el-button @click="importNewStudent" data-msg="delete" class="filt" title="批量导入">
              <img class="filt_unactive" src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_leading-in_n.png" />
              <img class="filt_active" src="../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_leading-in_highlight.png" />
            </el-button>
          </el-button-group>
        </div>
        <div class="g-fuzzyInput selfCenter">
          <el-input placeholder="准考证号/姓名" v-model="fuzzyInput" suffix-icon="el-icon-search" @change="getTableData"></el-input>
        </div>
      </div>
      <div class="centerTable alertsList">
        <el-table
          v-loading.body="isLoading"
          element-loading-text="拼命加载中..."
          class="g-NotHover" ref="studentMsgTable" :data="headerButtonData.studentBasicMsg" style="width:100%" @sort-change="sortChange" @selection-change="handleStudentTable">
          <!--show-overflow-tooltip 超出部分省略号显示-->
          <el-table-column type="selection" width="55"></el-table-column>
          <el-table-column label="序号" type="index" width="110"></el-table-column>
          <el-table-column label="姓名" prop="name"></el-table-column>
          <el-table-column label="准考证号" prop="regNumber"></el-table-column>
          <el-table-column label="性别" prop="sex"></el-table-column>
          <el-table-column label="出生日期" prop="birthday"></el-table-column>
          <el-table-column label="填报志愿所在地" prop="voluntPath"></el-table-column>
          <el-table-column label="联系方式" prop="phone"></el-table-column>
          <el-table-column label="户口所在地" prop="perAddress"></el-table-column>
          <el-table-column label="家庭地址" prop="homePath"></el-table-column>
          <el-table-column label="民族" prop="nation"></el-table-column>
          <el-table-column label="政治面貌" prop="politics"></el-table-column>
          <el-table-column label="现住地址" prop="nowHomePath"></el-table-column>
          <el-table-column label="考生类别" prop="exaCategory"></el-table-column>
          <el-table-column label="毕业学校" prop="secSchool"></el-table-column>
          <el-table-column label="邮政编码" prop="nowHomePostcode"></el-table-column>
          <el-table-column label="考试分数" prop="midExam"></el-table-column>
          <el-table-column label="指标生出档" prop="isTarget">
            <template slot-scope="scope">
              <span v-if="scope.row.isTarget=='0'">否</span>
              <span v-if="scope.row.isTarget=='1'">是</span>
            </template>
          </el-table-column>
          <el-table-column label="操作" fixed="right">
            <template slot-scope="props">
              <el-button type="text" @click="changeMsg(props.$index)">编辑</el-button>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </section>
    <footer class="g-footer">
      <el-row class="pageAlerts">
        <el-pagination
          @current-change="handleCurrentChange"
          :current-page.sync="currentPage"
          layout="prev, pager, next, jumper"
          :page-count="pageAll">
        </el-pagination>
      </el-row>
    </footer>
    <el-dialog class="headerNotBackground" title="新增年级" :modal="false" :visible.sync="isDialog"  :before-close="handlerClose">
      <el-row>
        <el-col style="min-height: 26rem;">
          <el-col :span="24">
            <el-col :span="3" :offset="1" style="padding-top: .4rem;"><span style="color: red;">*</span> 年级代码：</el-col>
            <el-col :span="7">
              <el-input v-model="gradeDialogForm.gradeCode" placeholder="请输入年级代码,如:C2016"></el-input>
            </el-col>
            <!--<el-col :span="3" :offset="2" style="padding-top: .4rem;"><span style="color: red;">*</span> 年级名称：</el-col>-->
            <!--<el-col :span="7">-->
            <!--<el-input v-model="gradeDialogForm.gradeName" placeholder="请输入年级名称,如:高一"></el-input>-->
            <!--</el-col>-->
          </el-col>
          <el-col :span="24" style="margin-top: 2rem;">
            <el-col :span="5" :offset="1">所有年级自动升级：</el-col>
            <el-col :span="5">
              <el-switch v-model="gradeDialogForm.update" active-text="是" inactive-text="否" active-color="#13b5b1"></el-switch>
            </el-col>
            <el-col :span="5" :offset="2">自动毕业最高年级：</el-col>
            <el-col :span="5">
              <el-switch v-model="gradeDialogForm.maxGrade" active-text="是" inactive-text="否" active-color="#13b5b1"></el-switch>
            </el-col>
          </el-col>
          <el-col :span="24" :offset="1" style="margin-top:4rem;line-height:2rem;color: #B4B4B4">
            <p>说明：</p>
            <p>1.年级代码为代号+入学年份，高中请填写如：G2015，初中请填写如：C2015，小学请填写如：X2015；</p>
            <p>2.所有年级自动升级：即当前以创建的年级会自动升一级，如当前G2015高一，会自动升级成G2015高二；</p>
            <p>3.自动毕业最高年级：分别将X、C、G三个字母为首的年级代码为依据，自动毕业最高年级。</p>
          </el-col>
        </el-col>
      </el-row>
      <div class="g-button">
        <el-button type="primary" @click="confirmCreateGrade">保存</el-button>
        <el-button @click="cancelCreateGrade">取消</el-button>
      </div>
    </el-dialog>
  </div>
</template>
<script>
  import {mapState,mapActions} from 'vuex'
  import {
    newStudentGetGrade,//得到年级
    newStudentManagement,//新生管理页面接口
  } from '@/api/http'
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
        /*年级数组*/
        gradeAjaxData:[],
        /*form表单双向绑定数据*/
        dataHeader:{
          gradeId:'',
        },
        /*删除所需参数*/
        deleteParams:[],
        /*table*/
        isFilter: false,
        tableData: [
          {
            serialNum:'1',
            classes:'1',
            seatNum:'1',//座位号
            name: 'hhh',
            sex: '女',
            sexNum: 0,
            tel: '123565566',
            section:'文科'//科类
          }
        ],
        sortList: {   //排序按钮
          serialNum:{
            filterText:''
          },
          classes:{
            filterText:''
          },
          seatNum:{
            filterText:''
          },
          name: {
            filterText: ''
          },
          sex: {
            filterText: ''
          },
          tel: {
            filterText: ''
          },
          section:{
            filterText:''
          }
        },
        /*fuzzyFilter*/
        fuzzyInput:'',
        /*footer*/
        pageAll:1,
        currentPage:1,
        pageCount:10,
        /*弹框*/
        isDialog:false,
        gradeDialogForm:{
          gradeCode:'',
          gradeName:'',
          update:true,
          maxGrade:false
        },
      }
    },
    computed: {},
    methods:{
      /*编辑时的默认信息*/
      ...mapActions(['newStudentManLoad']),
      /*footer*/
      handleCurrentChange(val) {
        this.currentPage = val;
        this.getTableData();
      },
      /*弹框*/
      handlerClose(done){
        done();
      },
      /*创建年级按钮*/
      createGrade(){
        this.isDialog=true;
      },
      confirmCreateGrade(){
        if(!/^[G|X|C]\d{4}$/.test(this.gradeDialogForm.gradeCode)){
          this.vmMsgWarning('请填写正确年级代码');
          return;
        }
//        if(!this.gradeDialogForm.gradeName){
//            this.$message('请填写年级名称');
//            return;
//        }
        this.createGradeAjax();
        this.initCreateGrade();
      },
      cancelCreateGrade(){
        this.$confirm('确定退出？', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(()=>{
          this.initCreateGrade();
          this.isDialog=false;
        }).catch(()=>{});
      },
      initCreateGrade(){
        this.gradeDialogForm={
          gradeCode:'',
          gradeName:'',
          update:true,
          maxGrade:false
        }
      },
      /*table*/
      handleStudentTable(section){
        /*section为选择项行信息组成的数组*/
        this.deleteParams=[];
        section.forEach((value)=>{
          this.deleteParams.push(value.userId);
        });
      },
      sortChange(column){
        /*table排序回调*/
      },
      /*编辑*/
      changeMsg(index){
        this.$router.push({name:'addNewStudent',params:{id:1,gradeId:this.dataHeader.gradeId,userId:this.headerButtonData.studentBasicMsg[index]['userId']}});
      },
      /*添加*/
      addClick(){
        /*编辑1，添加0*/
        if(this.dataHeader.gradeId){
          this.$router.push({name:'addNewStudent',params:{id:0,gradeId:this.dataHeader.gradeId,userId:0}});
        }
        else{
          this.vmMsgWarning('请选择需要添加的年级！');
        }
      },
      /*删除*/
      deleteClick(){
        if(this.deleteParams.length>0){
          this.sendDeleteAjax();
        }
        else{
          this.vmMsgWarning('请选择需要删除信息！');
        }
      },
      /*批量导入*/
      importNewStudent(){
        if(this.dataHeader.gradeId){
          this.$router.push({name:'ImportNewStudent',params:{param:this.dataHeader.gradeId}});
        }
        else{
          this.vmMsgWarning('请选择需要导入数据年级！');
        }
      },
      /*send ajax*/
      /*得到年级*/
      getLoadAjax(){
        newStudentGetGrade({func:'getGrade'}).then(data=>{
          this.gradeAjaxData=this.handlerAjaxData(data,'年级数据加载失败！');
          if(sessionStorage.getItem('gradeId')){
            this.dataHeader.gradeId = sessionStorage.getItem('gradeId');
            sessionStorage.removeItem('gradeId');
          }
        });
      },
      getTableData(){
        this.isLoading=true;
        sessionStorage.setItem('gradeId',this.dataHeader.gradeId);
        newStudentManagement({gradeId:this.dataHeader.gradeId,page:this.currentPage,count:this.pageCount,key:this.fuzzyInput}).then(data=>{
          /*加载table框数据及模糊查询接口*/
          this.headerButtonData.studentBasicMsg=this.handlerAjaxData(data);
          if(data.status){
            this.pageAll=data.maxPage;
            this.headerButtonData.studentBasicMsg.forEach((value,i)=>{
              value.homePath=value.homePath1.join('')+value.homePath2;
              value.nowHomePath=value.nowHomePath1.join('')+value.nowHomePath2;
              value.perAddress=value.perAddress.join('');
              value.voluntPath=value.voluntPath.join('');
            });
          }
          else{
            this.pageAll=1;
            this.headerButtonData.studentBasicMsg=[];
          }
        });
        this.isLoading=false;
      },
      /*创建年级*/
      createGradeAjax(){
        newStudentManagement({type:'createGrade',code:this.gradeDialogForm.gradeCode,znName:this.gradeDialogForm.gradeName,highestgrade:Number(this.gradeDialogForm.maxGrade),autoupdate:Number(this.gradeDialogForm.update)}).then(data=>{
          this.getLoadAjax();
          if(data.status==0){
            this.vmMsgError('添加失败');
          }else if(data.status==1){
            this.vmMsgSuccess('添加成功');
            this.isDialog=false;
          }else{
            this.vmMsgError(data.message);
          }
        });
      },
      /*删除数据*/
      sendDeleteAjax(){
        this.$confirm('是否删除该学生?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          newStudentManagement({type:'del',gradeId:this.dataHeader.gradeId,ids:this.deleteParams}).then(data=>{
            if(data.status){
              this.vmMsgSuccess('删除成功！');
              this.getTableData();
            }
            else{
              this.vmMsgError('删除失败！');
            }
          });
        }).catch(() => {});
      },
      /*处理数据*/
      handlerAjaxData(data,msg){
        if(data.status){
          return data.data;
        }
        else{
          (msg || data.message) && this.vmMsgError(msg);
        }
      },
    },
    created(){
      this.getLoadAjax();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../style/style';
</style>


