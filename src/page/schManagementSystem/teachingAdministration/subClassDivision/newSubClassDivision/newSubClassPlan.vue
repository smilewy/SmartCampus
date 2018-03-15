<template>
  <div class="newSubClassPlan">
    <el-row type="flex" align="middle" class="subClassDivision_title">
      <h3>创建分班方案</h3>
    </el-row>
    <el-row class="newSubClassPlan_body">
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
          <el-row class="noticeUeditor">
            <quill-editor style="height: 80%" v-model="form.notice"></quill-editor>
          </el-row>
        </el-form-item>
        <el-form-item class="submitBtn">
          <el-button type="primary" @click="save">创建</el-button>
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
          type: 'create',
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
        pickerBeginDateBefore: {
          disabledDate: (time) => {
            let beginDateVal = this.form.fillEnd;
            if (beginDateVal) {
              return new Date(moment(time).format('YYYY-MM-DD')).getTime() > new Date(moment(beginDateVal).format('YYYY-MM-DD')).getTime();
            }
          }
        },
        pickerBeginDateAfter: {
          disabledDate: (time) => {
            let beginDateVal = this.form.fillStart;
            if (beginDateVal) {
              return new Date(moment(time).format('YYYY-MM-DD')).getTime() < new Date(moment(beginDateVal).format('YYYY-MM-DD')).getTime();
            }
          }
        },
        pickerBeginDateBefore1: {
          disabledDate: (time) => {
            let beginDateVal = this.form.changeEnd;
            if (beginDateVal) {
              return new Date(moment(time).format('YYYY-MM-DD')).getTime() > new Date(moment(beginDateVal).format('YYYY-MM-DD')).getTime();
            }
          }
        },
        pickerBeginDateAfter1: {
          disabledDate: (time) => {
            let beginDateVal = this.form.changeStart;
            if (beginDateVal) {
              return new Date(moment(time).format('YYYY-MM-DD')).getTime() < new Date(moment(beginDateVal).format('YYYY-MM-DD')).getTime();
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
    methods: {
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
            if(new Date(data.fillStart).getTime()>new Date(data.fillEnd).getTime()){
              self.vmMsgWarning('学生填报志愿时间的开始时间不能大于结束时间！');
              return false;
            }
            if(new Date(data.changeStart).getTime()>new Date(data.changeEnd).getTime()){
              self.vmMsgWarning('调整志愿时间的开始时间不能大于结束时间！');
              return false;
            }
            req.ajaxSend('/school/DivideBranch/createPlan', 'post', data, function (res) {
              if (res.status == 1) {
                self.vmMsgSuccess('创建成功！');
              } else {
                self.vmMsgError(res.msg);
              }
            })
          } else {
            return false;
          }
        });
      }
    }
  }
</script>
<style>
  .newSubClassPlan {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .newSubClassPlan h3 {
    font-size: 1.25rem;
  }

  .newSubClassPlan .newSubClassPlan_body {
    margin-top: 3.43rem;
  }

  .newSubClassPlan .subMsg {
    width: 75%;
    margin: auto;
  }

  .newSubClassPlan .line {
    text-align: center;
  }

  .newSubClassPlan .noticeUeditor {
    height: 25rem;
  }

  .newSubClassPlan .submitBtn {
    text-align: right;
  }

  .newSubClassPlan .submitBtn .el-button {
    width: 7.5rem;
    padding: 10px 0;
    border-radius: 20px;
    border: 1px solid #4da1ff;
    color: #4da1ff;
  }

  .newSubClassPlan .submitBtn .el-button--primary {
    color: #fff;
  }

  .newSubClassPlan .vuEditor .el-form-item__content {
    line-height: 1;
  }
</style>
