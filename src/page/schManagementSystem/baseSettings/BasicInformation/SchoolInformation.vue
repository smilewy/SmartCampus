<template>
  <div class="SchoolInformation">
    <h3>学校信息</h3>
    <el-row>
      <el-col :span="12" :offset="6" class="SchoolInformation-box">
        <el-col :span="24" class="SchoolInformation-top">
          <el-col :span="3">学校LOGO：</el-col>
          <el-col :span="6" class="UpLoad-head">
            <img id="logoImg" :src="paramData.logo" alt="">
          </el-col>
          <el-col :span="10" :offset="1" class="SchoolInformation-upload">
            <el-col :span="24">尺寸：200*200</el-col>
            <el-col :span="24" class="SchoolInformation-upload-1">大小：≤ 2M</el-col>
            <el-col :span="24">
              <!--<span  class="SchoolInformation-upload-2">上传图片</span>-->
              <div id="uploadFile">
                <el-button>上传图片</el-button>
                <input type="file" accept=".bmp,.png,.jpg,.jpeg" id="file_input" @change="sendFile">
              </div>
            </el-col>
          </el-col>
        </el-col>
        <el-col :span="24" class="SchoolInformation-top">
          <el-col :span="4"><span style="color: red;">*</span> 学校名称：</el-col>
          <el-col :span="20">
            <el-input placeholder="请输入学校名称" v-model="paramData.scName"></el-input>
          </el-col>
        </el-col>
        <el-col :span="24" class="SchoolInformation-top">
          <el-col :span="4">英文名：</el-col>
          <el-col :span="20">
            <el-input placeholder="请输入学校英文名称" v-model="paramData.enName"></el-input>
          </el-col>
        </el-col>
        <el-col :span="24" class="SchoolInformation-top">
          <el-col :span="4"><span style="color: red;">*</span>学校电话：</el-col>
          <el-col :span="20">
            <el-input placeholder="请输入学校电话" v-model="paramData.telephone"></el-input>
          </el-col>
        </el-col>
        <el-col :span="24" class="SchoolInformation-top">
          <el-col :span="4">学校邮箱：</el-col>
          <el-col :span="20">
            <el-input v-model="paramData.mail"></el-input>
          </el-col>
        </el-col>
        <el-col :span="24" class="SchoolInformation-top">
          <el-col :span="4"><span style="color: red;">*</span>所在地：</el-col>
          <el-col :span="5">
            <el-select v-model="paramData.province" @change="getCityList(paramData.province)" placeholder="请选择">
              <el-option
                v-for="item in provinceList"
                :key="item.name"
                :label="item.name"
                :value="item.name">
              </el-option>
            </el-select>
          </el-col>
          <el-col :span="5" :offset="1">
            <el-select v-model="paramData.city" @change="getAreaList(paramData.city)" placeholder="请选择">
              <el-option
                v-for="item in cityList"
                :key="item.name"
                :label="item.name"
                :value="item.name">
              </el-option>
            </el-select>
          </el-col>
          <el-col :span="5" :offset="1">
            <el-select v-model="paramData.area" placeholder="请选择">
              <el-option
                v-for="item in areaList"
                :key="item.name"
                :label="item.name"
                :value="item.name">
              </el-option>
            </el-select>
          </el-col>
        </el-col>
        <el-col :span="24" class="SchoolInformation-top">
          <el-col :span="4">上级主管部门：</el-col>
          <el-col :span="20">
            <el-input v-model="paramData.ministries"></el-input>
          </el-col>
        </el-col>
        <el-col :span="24" class="SchoolInformation-top">
          <el-col :span="4">学校类型：</el-col>
          <el-col :span="20">
            <el-radio class="radio" v-model="paramData.type" label='公办'>公办</el-radio>
            <el-radio class="radio" v-model="paramData.type" label='民办'>民办</el-radio>
          </el-col>
        </el-col>
        <el-col :span="24">
          <el-button type="primary" class="SchoolInformation-btn" @click="submit()">提交</el-button>
        </el-col>
      </el-col>
    </el-row>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  import formatdata from '@/assets/js/date'
  import {getFileUrl} from '@/assets/js/common'
  import provinceList from '@/assets/js/city'
  export default{
    data(){
      return{
        radio:0,
        options: [],
        paramData:{
          type:'公办'
        },
        provinceList:provinceList.data,
        cityList:[],
        areaList:[],
      }
    },
    created(){
      this.GetSchoolinfor();
    },
    methods:{
      GetSchoolinfor(){
        req.ajaxSend('/school/Bcinformation/schoolinformation/type/schoolfind','post',{},(res)=>{
          this.paramData=res;
        });
      },
      sendFile(){
        this.paramData.logo = getFileUrl('file_input');
      },
      getCityList(name){
        this.cityList=[];
        this.areaList=[];
        this.cityList = this.provinceList.filter(val=>name.indexOf(val.name)>-1)[0].cityList;
//          this.paramData.city = this.cityList[0].name;
        this.paramData.city = '';
        this.paramData.area = '';
      },
      getAreaList(name){
        this.areaList=[];
        this.areaList = this.cityList.filter(val=>val.name===name)[0].cityList;
//          this.paramData.area = this.areaList[0].name;
        this.paramData.area = '';
      },
      submit(){
        //在此限制图片的大小
        if($('#file_input').prop('files').length){
          var imgSize = $('#file_input').prop('files')[0].size;
          if(imgSize>2*1024*1024){
            this.vmMsgWarning('上传图片大小不能超过2M');
            return;
          }
        }
        if(!this.paramData.scName){
          this.vmMsgWarning('请输入学校名称');
          return;
        }
        if(!/^(\+[0-9]* |[0-9]*\-)?[0-9]+$/ .test(this.paramData.telephone)){
          this.vmMsgWarning('请正确输入学校电话');
          return;
        }
        if(!this.paramData.province){
          this.vmMsgWarning('请选择所在地');
          return;
        }
//          if(!/[\w!#$%&'*+/=?^_`{|}~-]+(?:\.[\w!#$%&'*+/=?^_`{|}~-]+)*@(?:[\w](?:[\w-]*[\w])?\.)+[\w](?:[\w-]*[\w])?/.test(this.paramData.mail)){
//            this.$message('请正确输入邮箱地址');
//            return;
//          }
        this.$confirm('是否确认修改学校信息?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/Bcinformation/schoolinformation/type/schoolupdate','post',this.paramData,(res)=>{
            if(!$('#file_input').prop('files').length){
              if(res.return===true){
                this.vmMsgSuccess('修改成功');
                this.GetSchoolinfor();
                return false;
              }
              if(res.return===false){
                this.vmMsgError('修改失败');
              }
            }
            let sendFormData=new FormData();
            sendFormData.append('photo', $('#file_input').prop('files')[0]);
            req.ajaxFile('/school/Bcinformation/schoolinformation/type/schoollogo','post',sendFormData,(res)=>{
              if(res.return===true){
                this.vmMsgSuccess('修改成功');
                this.GetSchoolinfor();
              }
              if(res.return===false){
                this.vmMsgError('修改失败');
              }
            });
          });
        }).catch(() => {});
      }
    }
  }
</script>
<style lang="less" scoped>
  .SchoolInformation{
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }
  .SchoolInformation-box{
    margin-top:1rem;
  }
  .SchoolInformation-top{
    margin-top: 1.3rem;
  }
  .UpLoad-head{
    height: 200px;
    width: 200px;
    border: 1px solid #DFDFDF;
    img{
      width: 100%;
      height: 100%;
    }
  }
  .SchoolInformation-upload{
    font-size: 0.875rem;
    color: #999999;
  }
  .SchoolInformation-upload-1{
    margin: 1rem 0 2rem 0;
  }
  .SchoolInformation-btn{
    width: 100%;
    margin: 4rem 0 3rem 0;
  }
  #uploadFile {
    display: inline-block;
    position: relative;
    #file_input {
      position: absolute;
      right: 0;
      top: 0;
      z-index: 1;
      -moz-opacity: 0;
      -ms-opacity: 0;
      -webkit-opacity: 0;
      opacity: 0; /*css属性——opcity不透明度，取值0-1*/
      filter: alpha(opacity=0); /*兼容IE8及以下--filter属性是IE特有的，它还有很多其它滤镜效果，而filter: alpha(opacity=0); 兼容IE8及以下的IE浏览器(如果你的电脑IE是8以下的版本，使用某些效果是可能会有一个允许ActiveX的提示,注意点一下就ok啦)*/
      cursor: pointer;
    }
    button{
      border: none;
      color: #4DA1FF;
      font-size: 1rem;
      text-align: left;
      padding-left: 0;
      margin-left: 0;
    }
  }
</style>
