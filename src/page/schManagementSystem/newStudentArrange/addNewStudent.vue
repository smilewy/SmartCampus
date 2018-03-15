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
      <el-form ref="messageForm" class="g-form_twoColumn" :rules="rules" :model="dataForm" label-width="120px">
        <div class="g-form-row g-liOneRow">
          <el-form-item label="姓名:" prop="name">
            <el-input v-model="dataForm.name"></el-input>
          </el-form-item>
          <el-form-item label="性别:" prop="sex">
            <el-radio-group v-model="dataForm.sex">
              <el-radio label="男"></el-radio>
              <el-radio label="女"></el-radio>
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
          <el-form-item label="填报志愿所在地:" prop="voluntPath">
            <el-cascader v-model="dataForm.voluntPath" placeholder="请选择省/市/县、区" :options="options2" :props="city_prop" @change="getCity_one"></el-cascader>
          </el-form-item>
          <el-form-item label="联系方式:" prop="phone">
            <el-input v-model="dataForm.phone" :maxlength="11"></el-input>
          </el-form-item>
        </div>
        <!--户口所在地及家庭地址及现住地址等待提交时再拼接-->
        <div class="g-form-row g-liOneRow">
          <el-form-item label="户口所在地:" prop="perAddress">
            <el-cascader  v-model="dataForm.perAddress" placeholder="请选择省/市/县、区" :options="options2" :props="city_prop" @change="getCity_two"></el-cascader>
          </el-form-item>
          <el-form-item label="家庭地址:" prop="homePath1">
            <el-cascader v-model="dataForm.homePath1" placeholder="请选择省/市/县、区" :options="options2" :props="city_prop" @change="getCity_three"></el-cascader>
          </el-form-item>
        </div>
        <div class="g-form-row g-liOneRow">
          <el-form-item label="民族:" prop="nation">
            <el-select style="width: 100%;" v-model="dataForm.nation" placeholder="请选择">
              <el-option
                v-for="item in NationOption"
                :key="item.value"
                :value="item.value">
              </el-option>
            </el-select>
            <!--<el-input v-model="dataForm.nation"></el-input>-->
          </el-form-item>
          <el-form-item prop="homePath2">
            <el-input v-model="dataForm.homePath2" placeholder="家庭详细地址"></el-input>
          </el-form-item>
        </div>
        <div class="g-form-row g-liOneRow">
          <el-form-item label="政治面貌:" prop="politics">
            <el-radio-group v-model="dataForm.politics">
              <el-radio :label="political.value" v-for="(political,ix) in politicalData" :key="ix"></el-radio>
            </el-radio-group>
          </el-form-item>
          <el-form-item label="现住地址:" prop="nowHomePath1">
            <el-cascader v-model="dataForm.nowHomePath1" :options="options2" :props="city_prop" @change="getCity_four"></el-cascader>
          </el-form-item>
        </div>
        <div class="g-form-row g-liOneRow">
          <el-form-item label="考生类型:" prop="exaCategory">
            <el-radio-group v-model="dataForm.exaCategory">
              <el-radio label="应届生"></el-radio>
              <el-radio label="复读生"></el-radio>
              <el-radio label="其他"></el-radio>
            </el-radio-group>
          </el-form-item>
          <el-form-item prop="nowHomePath2">
            <el-input v-model="dataForm.nowHomePath2" placeholder="现住详细地址"></el-input>
          </el-form-item>
        </div>
        <div class="g-form-row g-liOneRow">
          <el-form-item label="毕业学校:" prop="secSchool">
            <el-input v-model="dataForm.secSchool"></el-input>
          </el-form-item>
          <el-form-item label="邮政编码:" prop="nowHomePostcode">
            <el-input v-model="dataForm.nowHomePostcode" type="number" :maxlength="6"></el-input>
          </el-form-item>
        </div>
        <div class="g-form-row g-liOneRow">
          <el-form-item label="中考分数:" prop="midExam">
            <el-input v-model="dataForm.midExam" type="number"></el-input>
          </el-form-item>
          <el-form-item label="指标生出档:" prop="isTarget">
            <el-switch v-model="dataForm.isTarget" active-value="1" inactive-value="0" active-text="是" inactive-text="否"></el-switch>
          </el-form-item>
        </div>
      </el-form>
      <div class="g-footer">
        <el-button type="primary" @click="saveClick" class="largeButton">保存</el-button>
      </div>
    </section>
  </div>
</template>
<script>
  import {mapState,mapActions} from 'vuex'
  import {
    newStudentManagement,//操作
    newStudentGetGrade,//得到某个学生信息
  } from '@/api/http'
  import provinceList from '@/assets/js/city'
  import req from '@/assets/js/common'
  import formatdata from '@/assets/js/date'
  import {ethnics, politicalStatus} from '@/assets/js/common-const-data'
  export default{
    data(){
      return{
        politicalData:politicalStatus,
        rules: {
          name: [
            { required: true, message: '请输入姓名', trigger: 'blur' }
          ],
          regNumber: [
            { required: true, message: '请输入准考证号', trigger: 'blur' }
          ],
          phone: [
            { required: true, message: '输入联系方式', trigger: 'blur' }
          ],
        },
        NationOption:ethnics,
        /*form表单*/
        cacheData:{
          planId:'',
          cacheName:'',
        },
        dataForm:{
          name:'',
          sex:'男',
          politics:'',//政治面貌
          birthday:'',
          phone:'',
          homePath1:[],//家庭住址
          homePath2:'',//详细家庭住址
          nation:'',//民族
          exaCategory:'',//考生类别
          nowHomePath1:[],//现住地址
          nowHomePath2:'',//详细现住地址
          nowHomePostcode:'',//邮政编码
          regNumber:'',//准考证号
          perAddress:[],//户口所在地
          voluntPath:[],//填报志愿所在地
          secSchool:'',//中学学校
          midExam:'',//中考分数
          isTarget:'0',//指标生出档
        },
        otherAddress:{
          /*家庭地址*/
          homePathBig:[],
          homePathDetail:'',
          /*现住地址*/
          nowHomePathBig:[],
          nowHomePathDetail:'',
        },
        /*确定是添加还是修改操作*/
        headerText:['添加新生','编辑新生信息'],
        /*路由参数*/
        id:'',
        userId:'',
        gradeId:'',
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
    computed:{
      ...mapState(['newStudentManHanlder']),
    },
    methods:{
      goBackParent(){
        this.$router.push('/newStudentmanagement');
      },
      getCity_one(city){
//        console.log(city);
      },
      getCity_two(city){
//        console.log('选中的城市：'+city);
      },
      getCity_three(city){
//        console.log('选中的城市：'+city);
      },
      getCity_four(city){
//        console.log('选中的城市：'+city);
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
        if(this.dataForm.name && this.dataForm.regNumber&&this.dataForm.phone){
          /*处理参数*/
          if(this.id==0){
            /*为添加数据*/
           if(this.dataForm.birthday!=''){
             let birthday=new Date((this.dataForm.birthday));
             this.dataForm.birthday=formatdata.format(birthday,'yyyy-MM-dd');
           }
            this.dataForm.gradeId=this.gradeId;
            this.dataForm.type='add';
          }
          else if(this.id==1){
            /*修改数据*/
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
          this.vmMsgWarning('请完善姓名、准考证号信息及联系方式！');
        }
      },
      /*send ajax*/
      saveAjax(){
        newStudentManagement(this.dataForm).then(data=>{
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
              this.$refs['messageForm'].resetFields();
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
                object[key] = data[key];
            }
          });
          this.dataForm=object;
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
    },
    created(){
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




