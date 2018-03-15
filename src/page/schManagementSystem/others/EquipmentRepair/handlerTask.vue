<template>
  <div class="g-container">
    <header class="g-textHeader g-flexStartRow">
      <el-button @click="goBackParent" class="g-gobackChart g-imgContainer RedButton">
        <img src="../../../../assets/img/commonImg/icon_return.png"/>
        返回
      </el-button>
      <span class="selfCenter">处理任务</span>
    </header>
    <section>
      <div class="g-repairDetail">
        <div class="g-liOneWrapRow">
          <div class="g-detailRow">
            <span class="g-detailLable">报修单号:</span>
            <span v-text="detailForm.repairNumber"></span>
          </div>
          <div class="g-detailRow">
            <span class="g-detailLable">维修地点:</span>
            <span v-text="detailForm.repairAddress"></span>
          </div>
          <div class="g-detailRow">
            <span class="g-detailLable">报修物品:</span>
            <span v-text="detailForm.repairName"></span>
          </div>
          <div class="g-detailRow">
            <span class="g-detailLable">联系方式:</span>
            <span v-text="detailForm.phone"></span>
          </div>
          <div class="g-detailRow">
            <span class="g-detailLable">资产编号:</span>
            <span v-text="detailForm.assetsNumber"></span>
          </div>
          <div class="g-detailRow">
            <span class="g-detailLable">报修类别:</span>
            <span v-text="detailForm.repairType"></span>
          </div>
        </div>
        <div class="g-detailRow">
          <span class="g-detailLable">报修内容:</span>
          <span v-text="detailForm.repairContent"></span>
        </div>
        <div class="g-detailRow g-repairPerson g-liOneRow">
          <span class="g-detailLable">图片:</span>
          <ul class="g-flexStartWrapRow">
            <li v-for="url in detailForm.logo">
              <img :src="url" alt=""/>
            </li>
          </ul>
        </div>
      </div>
      <div class="g-spaceDetail">维修中...</div>
      <div>
        <div class="g-contentOne_header">维修结果</div>
        <el-form :model="handlerForm" ref="handlerForm" :rules="rules" label-width="110px" label-position="right">
          <div class="g-liOneWrapRow">
            <el-form-item label="维修结果:" prop="state">
              <el-select v-model="handlerForm.state">
                <el-option value="0" label="维修失败"></el-option>
                <el-option value="1" label="维修成功"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="维修方法:" prop="method">
              <el-input v-model="handlerForm.method"></el-input>
            </el-form-item>
            <el-form-item label="故障原因:" prop="reason">
              <el-input v-model="handlerForm.reason"></el-input>
            </el-form-item>
            <el-form-item label="更换备件类型:" prop="replaceType">
              <el-input v-model="handlerForm.replaceType"></el-input>
            </el-form-item>
            <el-form-item label="到场时间:" prop="arriveTime">
              <el-date-picker
                v-model="handlerForm.arriveTime"
                type="datetime"
                placeholder="选择到场时间">
              </el-date-picker>
            </el-form-item>
          </div>
          <el-form-item label="维修反馈:" prop="feedback">
            <el-input type="textarea" v-model="handlerForm.feedback"></el-input>
          </el-form-item>
          <el-form-item label="上传图片:">
            <div class="g-detailRow g-repairPerson">
              <ul class="g-flexStartWrapRow">
                <li v-for="url in handlerForm.logo">
                  <img :src="url" alt=""/>
                </li>
              </ul>
              <div class="button_row">
                <button type="button" class="fileButtonShow headerButton">
                  选择上传图片
                </button>
                <input type="file" @change="chooseFile" class="chooseFile" title="选择上传图片"/>
              </div>
            </div>
          </el-form-item>
        </el-form>
      </div>
      <div class="g-footer">
        <el-button type="primary" @click="submitClick" class="largeButton">提交</el-button>
      </div>
    </section>
  </div>
</template>
<script>
  import {
    serviceTaskHanlderMsg,//得到信息
    serviceTaskHanlder,//提交
    serviceTaskUploadImg,//上传图片
  } from '@/api/http'
  import moment from 'moment'
  import {fileTypeCheck} from '@/assets/js/common'
  export default{
    data(){
      return {
        /*form表单——提交param*/
        handlerForm: {
          state: '',
          method: '',
          reason: '',
          replaceType: '',
          uploadImg: '',
          arriveTime: '',
          logo: []
        },
        rules: {
          state: [{required: true, message: '请选择维修结果'}],
          method: [{required: true, message: '请输入维修方法'}],
          reason: [{required: true, message: '请输入故障原因'}],
          replaceType: [{required: true, message: '请输入替换设备型号'}],
          feedback: [{required: true, message: '请输入反馈内容'}],
          arriveTime: [{required: true, type: 'date', message: '请选择到场时间'}]
        },
        /*页面展示信息*/
        detailForm: {
          repairNumber: '',
          repairAddress: '',
          repairName: '',
          phone: '',
          assetsNumber: '',
          repairType: '',
          repairContent: '',
          logo: []
        },
        /*图片信息*/
        detailImgUpload: [
          {
            name: 'food.jpeg',
            url: 'https://fuss10.elemecdn.com/3/63/4e7f3a15429bfda99bce42a18cdd1jpeg.jpeg?imageMogr2/thumbnail/360x360/format/webp/quality/100'
          }, {
            name: 'food2.jpeg',
            url: 'https://fuss10.elemecdn.com/3/63/4e7f3a15429bfda99bce42a18cdd1jpeg.jpeg?imageMogr2/thumbnail/360x360/format/webp/quality/100'
          },
        ],
        /*send ajax param*/
        idParam: '',
      }
    },
    methods: {
      goBackParent(){
        this.$router.push('/serviceTask');
      },
      /*form表单*/
      /*图片上传*/
      handleAvatarSuccess(res, file) {
        this.imageUrl = URL.createObjectURL(file.raw);
      },
      beforeAvatarUpload(file) {
        const isJPG = file.type === 'image/jpeg' || file.type === 'image/png';
        const isLt2M = file.size / 1024 / 1024 < 2;

        if (!isJPG) {
          this.vmMsgError( '上传头像图片只能是 JPG或PNG 格式!' );
        }
        if (!isLt2M) {
          this.vmMsgError( '上传头像图片大小不能超过 2MB!' );
        }
        return isJPG && isLt2M;
      },
      handlerAvatarRemove(file, fileList){
      },
      /*选择文件change事件*/
      chooseFile(event){
        if (!fileTypeCheck(this.extractFilename($(event.currentTarget).val()), ['.png', '.jpeg', '.jpg'])) {
          this.$alert('上传图片只能是 JPG或PNG 格式!', '提示', {
            confirmButtonText: '确定',
            type: 'warning',
            callback: action => {
            }
          });
        } else {
          this.uploadImgAjax();//上传图片
        }
      },
      extractFilename(path){
        let x;
        x = path.lastIndexOf('\\');
        if (x >= 0) // 基于Windows的路径
          return path.substr(x + 1);
        x = path.lastIndexOf('/');
        if (x >= 0) // 基于Unix的路径
          return path.substr(x + 1);
        return path; // 仅包含文件名
      },
      /*send ajax*/
      getLoadAjax(){
        serviceTaskHanlderMsg({id: this.idParam}).then(data => {
          if (data.statu) {
            this.detailForm = data.data;
          } else {
            this.detailForm = {
              repairNumber: '',
              repairAddress: '',
              repairName: '',
              phone: '',
              assetsNumber: '',
              repairType: '',
              repairContent: '',
              logo: [],
            };
            this.vmMsgError( '数据加载失败，请重试！' );
          }
        });
      },
      /*提交*/
      submitClick(){
        this.$refs.handlerForm.validate((valid) => {
          if (valid) {
            var tData = {};
            for (let name in this.handlerForm) {
              if (name == 'arriveTime') {
                tData[name] = moment(this.handlerForm[name]).format('YYYY-MM-DD HH:mm:ss');
              } else {
                tData[name] = this.handlerForm[name];
              }
            }
            serviceTaskHanlder({id: this.idParam, ...tData}).then(data => {
              this.vmMsgSuccess( '提交成功！' );
              this.$router.push('/serviceTask');
            })
          }
        });
      },
      /*上传图片*/
      uploadImgAjax(){
        let formData = new FormData(), _self = this;
        formData.append('userFile', $('.chooseFile')[0].files[0]);
        serviceTaskUploadImg(formData).then(data => {
          if (data.statu) {
            this.vmMsgSuccess( '上传成功！' );
            _self.handlerForm.logo.push(data.url);
          }
          else {
            this.vmMsgError( data.message );
          }
        });
      },
    },
    created(){
      this.idParam = this.$route.params.id;
      this.getLoadAjax();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/test';
  @import '../../../../style/style';
  .g-container{
  header.g-textHeader{border:none;padding-bottom:70/16rem;
  span{.fontSize(19);color:@HColor;.marginLeft(40,1582);}
  }
  section{/*1105*/
  .width(1105,1582);margin:0 auto;
  }
  .g-footer{width:100%;}
  }
  /*添加与编辑弹框*/
  .g-liOneWrapRow .el-form-item{.widthRem(385);}
  .g-liOneWrapRow .el-select{width:100%;}
  /*维修详情弹框*/
  .g-repairDetail{}
  .g-liOneWrapRow .g-detailRow{.widthRem(385);}
  .g-detailRow{width:100%;.marginBottom(36);
  .g-detailLable{display:inline-block;.widthRem(80);text-align:right;margin-right:10/16rem;}
  span{.fontSize(14);color:@normalColor;}
  }
  .g-repairPerson{
  ul{width:100%;}
  li{width:7.5rem;height:7.5rem;margin-right:10/16rem;.marginBottom(10);
  img{width:100%;}
  }
  }
  .g-spaceDetail{.marginBottom(30);border:1px dashed @backgroundBlue;width:100%;.height(42);text-align:center;color:@buttonActive;background-color:@backgroundBlueOpacity;.box-sizing();}
  /*header button*/
  .button_row{position:relative;}
  button.headerButton{.widthRem(120);height:36px;line-height:36px;border:none;.border-radius(4px);text-align:center;background:#13b5b1;color:#fff;
  &:focus{outline:none;}
  &:hover{cursor:pointer;}
  }
  .fileButtonShow{position:relative;z-index:1;}
  .chooseFile{z-index:10;opacity:0;position:absolute;left:0;top:0;.widthRem(120);height:36px;.box-sizing();
  &:hover{cursor:pointer;}
  }
  .g-contentOne_header{.widthRem(80);height:1.875rem;line-height:1.875rem;
    font-size:0.875rem;color:#fff;background:#89bcf5;text-align:center;
  .box-shadow(0 0.25rem 0.375rem 0 rgba(0, 0, 0, 0.2));.marginBottom(20);
  .border-bottom-right-radius(15/16rem);.border-top-right-radius(15/16rem);
  }
</style>




