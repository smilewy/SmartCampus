<template>
  <section class="gR-section">
    <el-form class="messageForm" ref="messageForm" :model="messageForm" label-width="112px">
      <!--Object.keys(dataType).includes('hasImg')判断是否有照片这一类，排版不一致-->
      <div class="g-messageContainer g-liOneWrapRow" v-if="Object.keys(dataType).includes('hasImg')">
        <el-form-item class="row" label="照片:">
          <div class="pictureContainer">
            <div class="imgContainer">
              <img :src="messageForm[dataType.hasImg.imgSrc]" />
            </div>
            <el-upload v-show="isHandlerMsg" class="personPicture upload-demo" :show-file-list="false" action="https://jsonplaceholder.typicode.com/posts/" :on-preview="handlePreview" :on-remove="handleRemove" list-type="picture">
              <div slot="tip" class="el-upload__tip">尺寸:150*200</div>
              <div slot="tip" class="el-upload__tip">大小:小于5M</div>
              <el-button size="small" type="success">上传图片</el-button>
            </el-upload>
          </div>
        </el-form-item>
        <div class="row">
          <el-form-item label="姓名:">
            <el-input v-model="messageForm[dataType.hasImg[1]]" v-show="isHandlerMsg"></el-input>
            <div v-text="messageData[dataType.hasImg[1]]" v-show="!isHandlerMsg"></div>
          </el-form-item>
          <el-form-item label="年级:">
            <el-select v-model="messageForm.gradNum" style="width:100%;" v-show="isHandlerMsg">
              <el-option v-for="(option,key) in messageData.selectArr.gradMsg" :key="option" :value="option"></el-option>
            </el-select>
            <div v-text="messageData.gradNum" v-show="!isHandlerMsg"></div>
          </el-form-item>
          <el-form-item label="班级:">
            <el-select v-model="messageForm.classNum" style="width:100%;" v-show="isHandlerMsg">
              <el-option v-for="(content,key) in messageData.selectArr.classMsg" :key="content" :value="content"></el-option>
            </el-select>
            <div v-text="messageData.classNum" v-show="!isHandlerMsg"></div>
          </el-form-item>
        </div>
      </div>
      <div class="g-messageContainer g-liOneWrapRow">
        <el-form-item class="row" label="班级序号:">
          <el-input v-model="messageForm.classOrdinal" v-show="isHandlerMsg"></el-input>
          <div v-text="messageData.classOrdinal" v-show="!isHandlerMsg"></div>
        </el-form-item>
        <el-form-item class="row" label="手机号码:">
          <el-input v-model="messageForm.tel" v-show="isHandlerMsg"></el-input>
          <div v-text="messageData.tel" v-show="!isHandlerMsg"></div>
        </el-form-item>
        <el-form-item class="row" label="性别:">
          <el-select v-model="messageForm.gender" style="width:100%;" v-show="isHandlerMsg">
            <el-option value="0" label="女"></el-option>
            <el-option value="1" label="男"></el-option>
          </el-select>
          <div v-text="messageData.gender" v-show="!isHandlerMsg"></div>
        </el-form-item>
        <el-form-item class="row" label="家庭住址:">
          <el-input v-model="messageForm.address" v-show="isHandlerMsg"></el-input>
          <div v-text="messageData.address" v-show="!isHandlerMsg"></div>
        </el-form-item>
        <el-form-item class="row" label="身高:">
          <el-input v-model="messageForm.personHeight" v-show="isHandlerMsg"></el-input>
          <div v-text="messageData.personHeight" v-show="!isHandlerMsg"></div>
        </el-form-item>
        <el-form-item class="row" label="家庭住址邮编:">
          <el-input v-model="messageForm.email" v-show="isHandlerMsg"></el-input>
          <div v-text="messageData.email" v-show="!isHandlerMsg"></div>
        </el-form-item>
        <el-form-item class="row" label="身份证号:">
          <el-input v-model="messageForm.IdCardNum" v-show="isHandlerMsg"></el-input>
          <div v-text="messageData.IdCardNum" v-show="!isHandlerMsg"></div>
        </el-form-item>
        <el-form-item class="row" label="现在住址:">
          <el-input v-model="messageForm.nowAddress" v-show="isHandlerMsg"></el-input>
          <div v-text="messageData.nowAddress" v-show="!isHandlerMsg"></div>
        </el-form-item>
        <el-form-item class="row" label="国籍:">
          <el-input v-model="messageForm.national" v-show="isHandlerMsg"></el-input>
          <div v-text="messageData.national" v-show="!isHandlerMsg"></div>
        </el-form-item>
        <el-form-item class="row" label="现在地址邮编:">
          <el-input v-model="messageForm.nowEmail" v-show="isHandlerMsg"></el-input>
          <div v-text="messageData.nowEmail" v-show="!isHandlerMsg"></div>
        </el-form-item>
        <el-form-item class="row" label="民族:">
          <el-select v-model="messageForm.local" placeholder="请选择民族" style="width: 100%;" v-show="isHandlerMsg">
            <el-option :label="national.value" :value="national.value" v-for="(national,ix) in nationalList"
                       :key="ix"></el-option>
          </el-select>
          <div v-text="messageData.local" v-show="!isHandlerMsg"></div>
        </el-form-item>
        <el-form-item class="row" label="宿舍栋号:">
          <el-input v-model="messageForm.buildingNum" v-show="isHandlerMsg"></el-input>
          <div v-text="messageData.buildingNum" v-show="!isHandlerMsg"></div>
        </el-form-item>
        <el-form-item class="row" label="政治面貌:">
          <el-select v-model="messageForm.plotical" placeholder="请选择政治面貌" style="width: 100%;" v-show="isHandlerMsg">
            <el-option :label="political.value" :value="political.value" v-for="(political,ix) in politicalData"
                       :key="ix"></el-option>
          </el-select>
          <div v-text="messageData.plotical" v-show="!isHandlerMsg"></div>
        </el-form-item>
        <el-form-item class="row" label="宿舍楼层:">
          <el-input v-model="messageForm.floors" v-show="isHandlerMsg"></el-input>
          <div v-text="messageData.floors" v-show="!isHandlerMsg"></div>
        </el-form-item>
        <el-form-item class="row" label="入团时间:">
          <el-date-picker type="date" style="width:100%;" :picker-options="dateOption" placeholder="选择时间" v-model="messageForm.ploticalTime" v-show="isHandlerMsg"></el-date-picker>
          <div v-text="messageData.ploticalTime" v-show="!isHandlerMsg"></div>
        </el-form-item>
        <el-form-item class="row" label="宿舍号:">
          <el-input v-model="messageForm.dormitoryNum" v-show="isHandlerMsg"></el-input>
          <div v-text="messageData.dormitoryNum" v-show="!isHandlerMsg"></div>
        </el-form-item>
        <el-form-item class="row" label="入学时间:">
          <el-date-picker type="date" style="width:100%;" :picker-options="dateOption" placeholder="选择时间" v-model="messageForm.schoolTime" v-show="isHandlerMsg"></el-date-picker>
          <div v-text="messageData.schoolTime" v-show="!isHandlerMsg"></div>
        </el-form-item>
        <el-form-item class="row" label="学生卡号:">
          <el-input v-model="messageForm.studentIdCard" v-show="isHandlerMsg"></el-input>
          <div v-text="messageData.studentIdCard" v-show="!isHandlerMsg"></div>
        </el-form-item>
        <el-form-item class="row" label="入学方式:">
          <el-select v-model="messageForm.schoolType" style="width:100%;" v-show="isHandlerMsg">
            <el-option :value="entranceType.value" :label="entranceType.value" v-for="(entranceType,ix) in entranceTypeData" :key="ix"></el-option>
          </el-select>
          <div v-text="messageData.schoolType" v-show="!isHandlerMsg"></div>
        </el-form-item>
      </div>
    </el-form>
    <footer class="gR-footer">
      <div class="gR-buttonContainer clear_fix">
        <el-button type="primary" class="msgButton" v-show="!isHandlerMsg" data-msg="handle" @click="buttonClick">编辑</el-button>
        <el-button class="msgButton" v-show="isHandlerMsg" @click="isHandlerMsg=false">取消</el-button>
        <el-button type="primary" class="msgButton" v-show="isHandlerMsg" data-msg="save" @click="buttonClick">保存</el-button>
      </div>
    </footer>
  </section>
</template>
<script>
  import {ethnics,politicalStatus,entranceType} from '@/assets/js/common-const-data'

  export default{
    data(){
      return{
        /*wqq*/
        /*按钮区域*/
        politicalData:politicalStatus,
        nationalList:ethnics,
        entranceTypeData:entranceType,
        isHandlerMsg:false,
        /*时间选择器*/
        dateOption:{
            disabledDate(time){
              return time.getTime()>Date.now();
            }
        }
      }
    },
    props:['messageData','dataType'],
    computed:{
      messageForm(){
        return JSON.parse(JSON.stringify(this.messageData));
      },
    },
    methods:{
      /*未完。。。*/
      /*头像部分*/
      handleRemove(file, fileList) {
        console.log(file, fileList);
      },
      handlePreview(file) {
        console.log(file);
      },
      /*按钮点击事件*/
      buttonClick(event){
        const e=$(event.currentTarget);
        this.isHandlerMsg=true;
        if(e.data('msg')==='handle'){
          this.$emit('handlerMsg','点击编辑按钮')
        }
        else if(e.data('msg')==='save'){
          this.$emit('confirmChange','保存修改');
        }
      },
    },
    created(){
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../../../style/style';
  @import '../../../../../../style/userManager/student/studentManager.less';
  @import '../../../../../../style/userManager/student/studentManager.css';
</style>



