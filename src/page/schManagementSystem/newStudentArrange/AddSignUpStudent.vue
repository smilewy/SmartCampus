<template>
  <div class="g-container">
    <header class="g-textHeader g-flexStartRow">
      <el-button @click="goBackParent" class="g-gobackChart g-imgContainer RedButton">
        <img src="../../../assets/img/commonImg/icon_return.png" />
        返回
      </el-button>
      <span class="selfCenter" v-text="headerText[id]"></span>
    </header>
    <section>
      <el-form ref="signStudentForm" class="g-form_twoColumn" :rules="rules" :model="dataForm" label-width="120px">
        <div class="g-form-row g-liOneRow">
          <el-form-item label="姓名:" prop="name">
            <el-input v-model="dataForm.name"></el-input>
          </el-form-item>
          <el-form-item label="性别:" prop="sex">
            <el-radio-group v-model="dataForm.sex">
              <el-radio label="男" value="男"></el-radio>
              <el-radio label="女" value="女"></el-radio>
            </el-radio-group>
          </el-form-item>
        </div>
        <div class="g-form-row g-liOneRow">
          <el-form-item label="准考证号:" prop="regNumber">
            <el-input v-model="dataForm.regNumber"></el-input>
          </el-form-item>
          <el-form-item label="出生日期:" prop="birthday">
            <el-date-picker v-model="dataForm.birthday" type="date"></el-date-picker>
          </el-form-item>
        </div>
        <div class="g-form-row g-liOneRow">
          <el-form-item label="中学学校:" prop="secSchool">
            <el-input v-model="dataForm.secSchool"></el-input>
          </el-form-item>
          <el-form-item label="联系方式:" prop="phone">
            <el-input v-model="dataForm.phone" :addNewStudentmaxlength="11"></el-input>
          </el-form-item>
        </div>
        <div class="g-form-row g-liOneRow">
          <el-form-item label="签约承诺:" prop="promise">
            <el-select v-model="dataForm.promise"  style="width: 100%" placeholder="请选择">
              <el-option
                v-for="item in Leveloptions"
                :key="item.levelId"
                :label="item.level"
                :value="item.levelId">
              </el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="家庭地址:" prop="homePath1">
            <el-cascader v-model="dataForm.homePath1" placeholder="请选择省/市/县、区" :options="options2" :props="city_prop" @change="getCity_one"></el-cascader>
          </el-form-item>
        </div>
        <div class="g-form-row g-liOneRow">
          <el-form-item label="户口所在地:" prop="perAddress">
            <el-cascader v-model="dataForm.perAddress" placeholder="请选择省/市/县、区" :options="options2" :props="city_prop"></el-cascader>
          </el-form-item>
          <el-form-item prop="homePath2">
            <el-input v-model="dataForm.homePath2" placeholder="家庭详细地址"></el-input>
          </el-form-item>
        </div>
        <div class="g-form-row g-liOneRow">
          <el-form-item label="邮政编码:" prop="nowHomePostcode">
            <el-input v-model="dataForm.nowHomePostcode" type="number" :maxlength="6"></el-input>
          </el-form-item>
          <el-form-item label="现住地址:" prop="nowHomePath1">
            <el-cascader v-model="dataForm.nowHomePath1" placeholder="请选择省/市/县、区" :options="options2" :props="city_prop"></el-cascader>
          </el-form-item>
        </div>
        <div class="g-form-row g-liOneRow">
          <el-form-item></el-form-item>
          <el-form-item prop="nowHomePath2">
            <el-input v-model="dataForm.nowHomePath2" placeholder="现住详细地址"></el-input>
          </el-form-item>
        </div>
      </el-form>
      <div class="g-footer">
        <el-button @click="saveClick" type="primary" class="largeButton">保存</el-button>
      </div>
    </section>
  </div>
</template>
<script>
  import {
    SignUpStudentManagementLoad,//操作
    newStudentGetGrade,//得到某个学生信息
  } from '@/api/http'
  import provinceList from '@/assets/js/city'
  import req from '@/assets/js/common'
  import formatdata from '@/assets/js/date'
  export default{
    data(){
      return{
        rules: {
          name: [
            { required: true, message: '请输入姓名', trigger: 'blur' }
          ],
          regNumber: [
            { required: true, message: '请输入准考证号', trigger: 'blur' }
          ],
          phone: [
            {  required: true, message: '请输入联系方式', trigger: 'blur' }
          ],
          promise: [
            {  required: true, message: '请选择签约承诺', trigger: 'change' }
          ]
        },
        Leveloptions:[],
        cacheData:{
          planId:'',
          cacheName:'',
        },
        /*form表单*/
        dataForm:{
          name:'',
          sex:'男',
          regNumber:'',//准考证号
          birthday:'',
          secSchool:'',//中学学校
          phone:'',
          promise:'',//签约生承诺
          levelId:'',//签约生承诺
          homePath1:[],//家庭住址
          perAddress:[],//户口所在地
          homePath2:'',//详细家庭住址
          nowHomePostcode:'',//邮政编码
          nowHomePath1:[],//现住地址
          nowHomePath2:'',//详细现住地址
        },
        /*确定是添加还是修改操作*/
        headerText:['添加签约生','编辑签约生信息'],
        /*路由参数*/
        id:'',
        gradeId:'',
        userId:'',
        /*地区选择*/
        options2: provinceList.data,
        city_prop:{
          label:'name',
          children:'cityList'
        },
        /*        startTimePOptions:{
         disabledDate(time){
         return time.getTime()<Date.now()-8.64e7;
         }
         },*/
      }
    },
    methods:{
      goBackParent(){
        this.$router.push('/SignUpStudentManagement');
      },
      getCity_one(city){
      },
      saveClick(){
        if(!/^[0-9a-zA_Z]+$/.test(this.dataForm.regNumber)){
          this.vmMsgWarning('请输入正确格式的准考证号');
          return;
        }
        if(!/^(13\d|14[579]|15[^4\D]|17[^49\D]|18\d)\d{8}$/.test(this.dataForm.phone)){
          this.vmMsgWarning('请输入正确格式的手机号码');
          return;
        }
        if(this.dataForm.name && this.dataForm.regNumber&&this.dataForm.phone&&this.dataForm.promise){
          /*处理参数*/
          if(this.id==0){
            /*为添加数据*/
//            this.dataForm.promise=this.dataForm.levelId;
            if(this.dataForm.birthday!=''){
              let birthday=new Date((this.dataForm.birthday));
              this.dataForm.birthday=formatdata.format(birthday,'yyyy-MM-dd');
            }
            this.dataForm.gradeId=this.gradeId;
            this.dataForm.type='add';
          }
          else if(this.id==1){
            /*修改数据*/
//            this.dataForm.promise=this.dataForm.levelId;
            if(this.dataForm.birthday!=''){
              let birthday=new Date((this.dataForm.birthday));
              this.dataForm.birthday=formatdata.format(birthday,'yyyy-MM-dd');
            }
            this.dataForm.userId=this.userId;
            this.dataForm.gradeId=this.gradeId;
            this.dataForm.type='edit';
          }
          this.saveAjax();
        }
        else{
          this.vmMsgWarning('请完善姓名、准考证号信息、联系方式及签约承诺！');
        }
      },
      /*send ajax*/
      saveAjax(){
        SignUpStudentManagementLoad(this.dataForm).then(data=>{
          if(this.id==0){
//            this.handlerData(data,'添加');
            if(data.status!=0){
              this.vmMsgSuccess(data.msg);
            }
            if(data.status==0){
              this.$confirm('准考证号重复，将用新记录覆盖旧记录，是否继续添加', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
              }).then(() => {
                this.cacheData.cacheName=data.cacheName;
                this.cacheData.planId=data.planId;
                req.ajaxSend('/school/StudentIni/uploadCache','post',this.cacheData,(res)=>{
                  this.vmMsgSuccess(res.msg);
                });
              }).catch(() => {});
            }
            if(data.status){
              this.$refs['signStudentForm'].resetFields();
            }
          }
          else if(this.id==1){
            this.handlerData(data,'修改');
            if(data.status){
              this.getPersonMsg();
            }
          }
        })
      },
      /*编辑时默认信息*/
      getPersonMsg(){
        newStudentGetGrade({func:'getOnePerson',param:{userId:this.userId}}).then(data=>{
            let object = {};
          Object.keys(this.dataForm).forEach((key)=>{
            if(key in data){
              object[key]=data[key]
            }
          });
          this.dataForm = object;
        });
      },
      handlerData(data,msg){
        if(data.status){
          this.vmMsgSuccess(msg+'成功！');
        }
        else{
          this.vmMsgError(msg+'失败！');
        }
      },
      GetLevel(){
        req.ajaxSend('/school/StudentIni/common','post',{func:'getLevel'},(res)=>{
          this.Leveloptions=res.data;
        });
      },
    },
    created(){
      this.GetLevel();
      this.id=this.$route.params.id;
      /*修改与添加新生信息所需参数*/
      this.userId=this.$route.params.userId;
      this.gradeId=this.$route.params.gradeId;
      if(this.id==1){
        /*修改数据*/
        this.getPersonMsg();
      }
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../style/style';
  .g-container{
    header.g-textHeader{border:none;padding-bottom:70/16rem;
      span{.fontSize(19);color:@HColor;.marginLeft(40,1582);}
    }
    section{/*1105*/
      .width(1105,1582);margin:0 auto;
    }
    .g-footer{width:100%;}
  }
</style>




