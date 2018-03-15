<template>
  <div class="reviseSubClassPlan">
    <el-row type="flex" align="middle" class="subClassDivision_title">
      <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
        src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
        alt=""><span class="returnTxt">返回流程图</span></el-button>
      <h3>修改分班方案</h3>
    </el-row>
    <el-row class="reviseSubClassPlan_body">
      <el-form ref="form" :model="form" :rules="rules" label-width="180px" class="subMsg">
        <el-form-item label="分班方案名称：" prop="name">
          <el-input v-model="form.name"></el-input>
        </el-form-item>
        <el-form-item label="学生填报志愿时间：" required>
          <el-col :span="8">
            <el-form-item prop="fillStart">
              <el-date-picker type="datetime" format="yyyy-MM-dd HH:mm" placeholder="选择时间" :editable="false" v-model="form.fillStart"
                              :picker-options="pickerBeginDateBefore"
                              style="width: 100%;"></el-date-picker>
            </el-form-item>
          </el-col>
          <el-col class="line" :span="1">-</el-col>
          <el-col :span="8">
            <el-form-item prop="fillEnd">
              <el-date-picker type="datetime" format="yyyy-MM-dd HH:mm" placeholder="选择时间" :editable="false" v-model="form.fillEnd"
                              :picker-options="pickerBeginDateAfter"
                              style="width: 100%;"></el-date-picker>
            </el-form-item>
          </el-col>
        </el-form-item>
        <el-form-item label="调整志愿时间：" required>
          <el-col :span="8">
            <el-form-item prop="changeStart">
              <el-date-picker type="datetime" format="yyyy-MM-dd HH:mm" placeholder="选择时间" :editable="false" v-model="form.changeStart"
                              :picker-options="pickerBeginDateBefore1"
                              style="width: 100%;"></el-date-picker>
            </el-form-item>
          </el-col>
          <el-col class="line" :span="1">-</el-col>
          <el-col :span="8">
            <el-form-item prop="changeEnd">
              <el-date-picker type="datetime" format="yyyy-MM-dd HH:mm" placeholder="选择时间" :editable="false" v-model="form.changeEnd"
                              :picker-options="pickerBeginDateAfter1"
                              style="width: 100%;"></el-date-picker>
            </el-form-item>
          </el-col>
        </el-form-item>
        <el-form-item label="允许学生查询成绩：">
          <el-switch active-text="是" inactive-text="否" active-color="#09baa7"
                     inactive-color="#ff8686" v-model="form.stuSearch"></el-switch>
        </el-form-item>
        <el-form-item label="允许学生反复修改志愿：">
          <el-switch active-text="是" inactive-text="否" active-color="#09baa7"
                     inactive-color="#ff8686" v-model="form.stuChange"></el-switch>
        </el-form-item>
        <el-form-item label="允许班主任调整学生志愿：">
          <el-switch active-text="是" inactive-text="否" active-color="#09baa7"
                     inactive-color="#ff8686" v-model="form.teaChange"></el-switch>
        </el-form-item>
        <el-form-item label="分科分班公告：" class="vuEditor" required>
          <div class="vuEditorContent">
            <quill-editor style="height: 80%" v-model="form.notice"></quill-editor>
          </div>
        </el-form-item>
        <el-form-item class="submitBtn">
          <el-button type="primary" :disabled="btnState" @click="save">保存</el-button>
          <el-button @click="reset">重置</el-button>
        </el-form-item>
      </el-form>
    </el-row>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  import moment from 'moment'

  export default{
    data(){
      return {
        form: {
          planId: '',
          type: 'update',
          name: '',
          fillStart: '',
          fillEnd: '',
          changeStart: '',
          changeEnd: '',
          stuSearch: true,
          stuChange: true,
          teaChange: true,
          notice: ''
        },
        editor:'',
        btnState:false,
        pickerBeginDateBefore: {
          disabledDate: (time) => {
            let beginDateVal = this.form.fillEnd;
            if (beginDateVal) {
              return time.getTime() > beginDateVal;
            }
          }
        },
        pickerBeginDateAfter: {
          disabledDate: (time) => {
            let beginDateVal = this.form.fillStart;
            if (beginDateVal) {
              return time.getTime() < beginDateVal;
            }
          }
        },
        pickerBeginDateBefore1: {
          disabledDate: (time) => {
            let beginDateVal = this.form.changeEnd;
            if (beginDateVal) {
              return time.getTime() > beginDateVal;
            }
          }
        },
        pickerBeginDateAfter1: {
          disabledDate: (time) => {
            let beginDateVal = this.form.changeStart;
            if (beginDateVal) {
              return time.getTime() < beginDateVal;
            }
          }
        },
        rules: {
          name: [
            {required: true, message: '请输入分班方案名称', trigger: 'blur'},
            {min: 1, max: 20, message: '长度在 1 到 20 个字符', trigger: 'blur'}
          ],
          fillStart: [
            {type: 'date', required: true, message: '请选择时间', trigger: 'change'}
          ],
          fillEnd: [
            {type: 'date', required: true, message: '请选择时间', trigger: 'change'}
          ],
          changeStart: [
            {type: 'date', required: true, message: '请选择时间', trigger: 'change'}
          ],
          changeEnd: [
            {type: 'date', required: true, message: '请选择时间', trigger: 'change'}
          ]
        }
      }
    },
    mounted: function () {
      var planId = this.$route.params.planId, data = {
        planId: planId
      };
      this.form.planId = planId;
      this.loadData(data);
    },
    methods: {
      returnFlowchart(){
        this.$router.push('/subClassDivisionHome');
      },
      save(){
        var self = this, data = {};
        for (let name in self.form) {
          data[name] = self.form[name];
        }
        data.stuSearch = self.form.stuSearch ? 1 : 0;
        data.stuChange = self.form.stuChange ? 1 : 0;
        data.teaChange = self.form.teaChange ? 1 : 0;
        data.fillStart = moment(data.fillStart).format('YYYY-MM-DD HH:mm');
        data.fillEnd = moment(data.fillEnd).format('YYYY-MM-DD HH:mm');
        data.changeStart = moment(data.changeStart).format('YYYY-MM-DD HH:mm');
        data.changeEnd = moment(data.changeEnd).format('YYYY-MM-DD HH:mm');
        self.$refs['form'].validate((valid) => {
          if (valid) {
            if (!self.form.notice) {
              self.vmMsgWarning('请输入公告内容！');
              return false;
            }
            self.btnState=true;
            req.ajaxSend('/school/DivideBranch/updatePlan', 'post', data, function (res) {
              self.btnState=false;
              if (res.status == 1) {
                self.vmMsgSuccess('修改成功！');
              } else {
                self.vmMsgError(res.msg);
              }
            })
          } else {
            return false;
          }
        });
      },
      reset(){
        this.$confirm('此次修改未保存，确定重置?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          var data = {
            planId: this.form.planId
          };
          this.loadData(data);
        }).catch(() => {
        });
      },
      loadData(data){
        var self = this;
        req.ajaxSend('/school/DivideBranch/updatePlan', 'post', data, function (res) {
          for (let name in res.data) {
            self.form[name] = res.data[name];
          }
          self.form.stuSearch = res.data.stuSearch == '1' ? true : false;
          self.form.stuChange = res.data.stuChange == '1' ? true : false;
          self.form.teaChange = res.data.teaChange == '1' ? true : false;
          self.form.fillStart = new Date(Number.parseInt(self.form.fillStart) * 1000);
          self.form.fillEnd = new Date(Number.parseInt(self.form.fillEnd) * 1000);
          self.form.changeStart = new Date(Number.parseInt(self.form.changeStart) * 1000);
          self.form.changeEnd = new Date(Number.parseInt(self.form.changeEnd) * 1000);
        })
      }
    }
  }
</script>
<style>
  .reviseSubClassPlan .reviseSubClassPlan_body {
    margin-top: 3.43rem;
  }

  .reviseSubClassPlan .subMsg {
    width: 75%;
    margin: auto;
  }

  .reviseSubClassPlan .line {
    text-align: center;
  }

  .reviseSubClassPlan .submitBtn {
    text-align: right;
  }

  .reviseSubClassPlan .submitBtn .el-button {
    width: 7.5rem;
    padding: 10px 0;
    border-radius: 20px;
    border: 1px solid #4da1ff;
    color: #4da1ff;
  }

  .reviseSubClassPlan .submitBtn .el-button--primary {
    color: #fff;
  }

  .reviseSubClassPlan .vuEditorContent{
    height:25rem;
  }
  .reviseSubClassPlan .vuEditor .el-form-item__content {
    line-height: 1;
  }
</style>
