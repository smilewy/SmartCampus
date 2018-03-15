<template>
  <div class="createDistribution">
    <el-row type="flex" align="middle">
      <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
        src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
        alt=""><span class="returnTxt">返回</span></el-button>
      <h3>创建宿舍分配方案</h3>
    </el-row>
    <el-row class="createDistribution_body">
      <el-form ref="form" :rules="rules" :model="form" label-width="150px" class="subMsg">
        <el-form-item label="宿舍分配方案名称：" prop="name">
          <el-input v-model="form.name" placeholder="请输入宿舍分配方案名称"></el-input>
        </el-form-item>
        <el-form-item label="宿舍分配年级：" prop="gradeId">
          <el-select v-model="form.gradeId" placeholder="请选择" style="width: 100%;">
            <el-option value="all" label="全部年级"></el-option>
            <el-option
              v-for="item in gradeList"
              :key="item.id"
              :label="item.znName"
              :value="item.id">
            </el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="宿舍分配公告：" class="vuEditor" required>
          <el-row class="noticeUeditor">
            <quill-editor style="height: 80%" v-model="form.notice"></quill-editor>
          </el-row>
        </el-form-item>
        <el-form-item class="submitBtn">
          <el-button type="primary" @click="save">创建</el-button>
          <el-button @click="resetData">重置</el-button>
        </el-form-item>
      </el-form>
    </el-row>
  </div>
</template>
<script>
  import req from '@/assets/js/common'

  export default{
    data(){
      return {
        form: {
          type: 'add',
          name: '',
          gradeId: '',
          notice: ''
        },
        gradeList: [],
        rules: {
          name: [
            {required: true, message: '请输入宿舍分配方案名称', trigger: 'blur'},
            {min: 1, max: 20, message: '长度在 1 到 20 个字符', trigger: 'blur'}
          ],
          gradeId: [
            {required: true, message: '请选择宿舍分配年级', trigger: 'change'}
          ]
        }
      }
    },
    created: function () {
      var self = this, data = {
        func: 'getGrade'
      };
      req.ajaxSend('/school/StudentDorm/common', 'post', data, function (res) {
        self.gradeList = res.data;
      })
    },
    methods: {
      returnFlowchart(){
        this.$router.go(-1);
      },
      save(){
        var self = this, data = {};
        self.$refs['form'].validate((valid) => {
          if (valid) {
            if (!self.form.notice) {
              self.vmMsgWarning('请填写宿舍分配公告！');
              return false;
            }
            for (let name in self.form) {
              if (name == 'gradeId') {
                if (self.form[name] == 'all') {
                  data[name] = 0;
                } else {
                  data[name] = self.form[name];
                }
              } else {
                data[name] = self.form[name];
              }
            }
            req.ajaxSend('/school/StudentDorm/dormPlan', 'post', data, function (res) {
              if (res.status == 1) {
                self.vmMsgSuccess('创建方案成功！');
              } else {
                self.vmMsgError(res.msg);
              }
            })
          } else {
            return false;
          }
        });
      },
      resetData(){
        this.$refs['form'].resetFields();
        this.form.notice = '';
      }
    }
  }
</script>
<style>
  .createDistribution .createDistribution_body {
    margin: 4.375rem 0;
  }

  .createDistribution .subMsg {
    width: 75%;
    margin: auto;
  }

  .createDistribution .line {
    text-align: center;
  }

  .createDistribution .noticeUeditor {
    height: 25rem;
  }

  .createDistribution .submitBtn {
    text-align: right;
    margin-top: 2rem;
  }

  .createDistribution .submitBtn .el-button {
    width: 7.5rem;
    padding: 10px 0;
    border-radius: 20px;
    border: 1px solid #4da1ff;
    color: #4da1ff;
  }

  .createDistribution .submitBtn .el-button--primary {
    color: #fff;
  }

  .createDistribution .vuEditor .el-form-item__content {
    line-height: 1;
  }
</style>
