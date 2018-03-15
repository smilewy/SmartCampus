<template>
  <div class="inSchoolProvePreview">
    <el-row type="flex" align="middle">
      <el-button type="primary" class="return_btn" @click="returnPrev"><img
        src="../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
        alt=""><span class="returnTxt">返回</span></el-button>
      <h3>在读证明预览</h3>
    </el-row>
    <el-row class="d_line inSchoolProvePreview_row"></el-row>
    <el-row class="alertsBtn">
      <!--<el-button-group>
        <el-button class="filt" title="复制" @click="operationData('copy')">
          <img class="filt_unactive"
               src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png"
               alt="">
          <img class="filt_active"
               src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png"
               alt="">
        </el-button>-->
      <el-button class="delete" title="打印" @click="operationData('print')">
        <img class="delete_unactive"
             src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png"
             alt="">
        <img class="delete_active"
             src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png"
             alt="">
      </el-button>
      <!--</el-button-group>-->
    </el-row>
    <el-row class="provePreviewContent">
      <el-col :span="8" :offset="8">
        <h6>在读证明</h6>
        <el-row class="prove_text">
          <el-row>
            <el-row>
              {{znMsg.name}} ， {{znMsg.sex}} ，出身于 {{znMsg.birthday}} ，是我校 {{znMsg.gradeName}} {{znMsg.className}} 的学生。
            </el-row>
            <el-row>
              特此证明。
            </el-row>
          </el-row>
          <el-row class="signed">
            <el-row>
              {{znMsg.schoolName}}
            </el-row>
            <el-row>
              {{znMsg.date}}
            </el-row>
          </el-row>
        </el-row>
        <h6>Current Study Certificate</h6>
        <el-row class="prove_text">
          <el-row>
            This is to certify that {{enMsg.name}}， {{enMsg.sex}} ，born on {{enMsg.birthday}}，is a student in Class
            {{enMsg.className}}，Grade {{enMsg.gradeName}} in our school .
          </el-row>
          <el-row class="signed">
            <el-row>
              {{enMsg.schoolName}}
            </el-row>
            <el-row>
              {{enMsg.date}}
            </el-row>
          </el-row>
        </el-row>
      </el-col>
    </el-row>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  export default{
    data(){
      return {
        enMsg: {
          birthday: "", //出身日期
          className: "", //班级
          name: "", //姓名
          date: "", //时间
          gradeName: "", //年级名
          schoolName: "" //学校
        },
        znMsg: {
          birthday: "", //出身日期
          className: "", //班级
          name: "", //姓名
          date: "", //时间
          gradeName: "", //年级名
          schoolName: "" //学校
        }
      }
    },
    created: function () {
      var self = this, data = {
        userId: self.$route.params.userId
      };
      req.ajaxSend('/school/Educational/zdPro?type=getUser', 'get', data, function (res) {
        self.enMsg = res.data.en;
        self.znMsg = res.data.zn;
      })
    },
    methods: {
      returnPrev(){
        this.$router.go(-1);
      },
      operationData(type){
        let sAy = $('.provePreviewContent').html();
        if (type == 'copy') {
          if ($("#bstableCopy").length < 1) {
            var element = "<div id='bstableCopy' style='opacity: 0;position: fixed;'>复制内容</div>";
            $('.inSchoolProvePreview').append(element);
          }
          $("#bstableCopy").html(sAy);
          $("#bstableCopy").select(); // 选择对象
          document.execCommand("Copy");
          alert('复制成功,请粘贴到word中！');
        } else {
          var newWin = window.open("");//新打开一个空窗口
          newWin.document.write(sAy);//将表格添加进新的窗口
          newWin.document.close();//在IE浏览器中使用必须添加这一句
          newWin.focus();//在IE浏览器中使用必须添加这一句

          newWin.print();//打印
          newWin.close();//关闭窗口
        }
      }
    }
  }
</script>
<style>
  .inSchoolProvePreview {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .inSchoolProvePreview h3 {
    font-size: 1.25rem;
    display: inline-block;
    margin-left: 2rem;
  }

  .inSchoolProvePreview .inSchoolProvePreview_row {
    margin-top: 2rem;
  }

  .inSchoolProvePreview .alertsBtn {
    margin: 1.25rem 0;
  }

  .inSchoolProvePreview .return_btn.el-button--primary {
    background-color: #ff8686;
    border-color: #ff8686;
    border-radius: 20px;
  }

  .inSchoolProvePreview .return_btn.el-button--primary .returnTxt {
    margin-left: 10px;
  }

  .inSchoolProvePreview h6 {
    font-size: 1.125rem;
    text-align: center;
    margin: 3.5rem 0;
  }

  .inSchoolProvePreview .prove_text {
    line-height: 2.5;
  }

  .inSchoolProvePreview .prove_text input {
    border-left: 0;
    border-right: 0;
    border-top: 0;
    border-bottom: 1px solid #4da1ff;
    font-size: 1rem;
  }

  .inSchoolProvePreview .prove_text input.right, .inSchoolProvePreview .signed {
    text-align: right;
  }

  .inSchoolProvePreview .signed {
    margin-top: 2rem;
  }
</style>
