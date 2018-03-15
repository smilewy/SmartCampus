<template>
  <div class="g-container g-studentRecord">
    <header class="g-header">
      <div class="gh-header">学生补录</div>
    </header>
    <section class="g-section">
      <el-form class="g-form" ref="studentForm" :rules="rules" :model="studentMsgForm" label-position="right"
               label-width="75px">
        <el-form-item label="年级:" prop="grade">
          <el-select v-model="studentMsgForm.grade" @change="gradeChange" placeholder="请选择年级">
            <el-option v-for="(content,index) in gradeAjaxData" :key="index" :value="content.gradeid"
                       :label="content.znGradeName"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="班级:" prop="className">
          <el-select v-model="studentMsgForm.className" placeholder="请选择班级">
            <el-option v-for="(content,index) in classesAjaxData" :key="index" :value="content.classid"
                       :label="content.classname+'班'"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="姓名:" class="g-floatLeft" prop="name">
          <el-input v-model="studentMsgForm.name"></el-input>
        </el-form-item>
        <el-form-item label="手机号:" class="g-floatLeft" prop="phone">
          <el-input v-model="studentMsgForm.phone"></el-input>
        </el-form-item>
        <el-form-item label="性别:" class="g-floatLeft" prop="sex">
          <el-radio-group v-model="studentMsgForm.sex">
            <el-radio label="女" value="女"></el-radio>
            <el-radio label="男" value="男"></el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="是否借读:" class="g-floatLeft" prop="ifExtern">
          <el-switch active-text="是" inactive-text="否" v-model="studentMsgForm.ifExtern"></el-switch>
        </el-form-item>
      </el-form>
      <div class="g-footer">
        <el-button type="primary" @click="submitClick">提交</el-button>
      </div>
    </section>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  import {
    studentRecordMsg,//提交
    studentMessageGradeLoad,//得到年级
    studentMessageClassLoad,//得到班级
  } from '@/api/http'

  export default {
    data() {
      return {
        /*form表单的双向绑定数据*/
        studentMsgForm: {
          grade: '',
          className: '',
          name: '',
          phone: '',
          sex: '女',
          ifExtern: '',//借读,值为true/false
        },
        /*年级*/
        gradeAjaxData: [],
        classesAjaxData: [],
        /**/
        rules: {
          grade: [{required: true, message: '请选择年级'}],
          className: [{required: true, message: '请选择班级'}],
          name: [{required: true, message: '请输入姓名'}],
          phone: [{required: true, message: '请输入手机号'}],
        },
      }
    },
    methods: {
      /*年级变化*/
      gradeChange() {
        this.studentMsgForm.className='';
        this.classesAjaxData=[];
        this.getClassesAjax();
      },
      /*send ajax*/
      submitClick() {
        var self = this, data;
        self.$refs.studentForm.validate((valid) => {
          if (valid) {
            data = {
              name: self.studentMsgForm.name,
              roleId: -1
            };
            req.ajaxSend('/school/user/userGl?type=checkUserMore', 'post', data, function (res) {
              if (res.statu == 1) {
                studentRecordMsg(self.studentMsgForm).then(data => {
                  if (data.statu) {
                    self.vmMsgSuccess('补录成功!');
                    self.$refs['studentForm'].resetFields();
                  } else {
                    self.vmMsgError(data.message);
                  }
                });
              } else {
                self.$confirm(res.message, '提示', {
                  confirmButtonText: '确定',
                  cancelButtonText: '取消',
                  type: 'warning'
                }).then(() => {
                  studentRecordMsg(self.studentMsgForm).then(data => {
                    if (data.statu) {
                      self.vmMsgSuccess('补录成功!');
                      self.$refs['studentForm'].resetFields();
                    } else {
                      self.vmMsgError(data.message);
                    }
                  });
                }).catch(() => {
                });
              }
            })
          }
        });
      },
      getGradeAjax() {
        studentMessageGradeLoad().then(data => {
          this.gradeAjaxData = data;
        })
      },
      getClassesAjax() {
        studentMessageClassLoad({gradeid: this.studentMsgForm.grade}).then(data => {
          this.classesAjaxData = data;
        })
      },
    },
    created() {
      this.getGradeAjax();
    }
  }
</script>
<style lang="less" scoped>
  /*@import '../../../../../style/userManager/student/studentMessage.less';*/
  @import '../../../../../style/userManager/student/studentManager.css';
  @import '../../../../../style/common';

  .g-header {
    .width(1646-64, 1646);
    margin: 20/16rem 32/1646*100% 0 32/1646*100%;
    .gh-header {
      width: 100%;
      color: @HColor;
      font-weight: bold;
      margin-bottom: 30/16rem;
      font-size: 1.25rem;
    }
  }

  .g-section { /*1582*/
    .width(1646-64, 1646);
    margin: 140/16rem 32/1646*100% 0 32/1646*100%;
    .g-form { /*852*/
      .width(852, 1582);
      margin: 0 365/1582*100%;
      .g-floatLeft {
        float: left;
      }
      .g-floatLeft:nth-of-type(even) {
        float: left;
        margin-left: 14.3%;
      }
    }
    .g-footer {
      .width(852, 1582);
      margin: 0 365/1582*100% 95/16rem;
    }
  }
</style>


