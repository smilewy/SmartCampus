<template>
  <div class="transient">
    <el-row>
      <el-row class="into_row">
        <span class="into_subTitle">学生信息</span>
      </el-row>
      <el-row :gutter="100" type="flex" justify="center">
        <el-col :span="8">
          <el-form ref="studentMsgOne" :rules="studentMsgRules" :model="studentMsg" label-width="100px">
            <el-form-item label="姓名：" prop="name">
              <el-input placeholder="请输入姓名" v-model="studentMsg.name"></el-input>
            </el-form-item>
            <el-form-item label="性别：" prop="sex">
              <el-radio-group v-model="studentMsg.sex">
                <el-radio label="女">女</el-radio>
                <el-radio label="男">男</el-radio>
              </el-radio-group>
            </el-form-item>
            <el-form-item label="学籍号：">
              <el-input placeholder="请输入学籍号" v-model="studentMsg.studentCode"></el-input>
            </el-form-item>
          </el-form>
        </el-col>
        <el-col :span="8">
          <el-form ref="studentMsgTwo" :model="studentMsg" label-width="120px">
            <el-form-item label="身份证件类型：">
              <el-select v-model="studentMsg.certificate" placeholder="请选择身份证件类型" style="width:100%;">
                <el-option label="居民身份证" value="居民身份证"></el-option>
                <el-option label="士兵证" value="士兵证"></el-option>
                <el-option label="军官证" value="军官证"></el-option>
                <el-option label="警官证" value="警官证"></el-option>
                <el-option label="护照" value="护照"></el-option>
                <el-option label="其他" value="其他"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="身份证号：">
              <el-input placeholder="请输入身份证号" v-model="studentMsg.idCard"></el-input>
            </el-form-item>
          </el-form>
        </el-col>
      </el-row>
    </el-row>
    <el-row>
      <el-row class="into_row">
        <span class="into_subTitle">借读本校信息</span>
      </el-row>
      <el-row :gutter="100" type="flex" justify="center">
        <el-col :span="8">
          <el-form ref="intoMsgOne" :rules="studentMsgRules" :model="studentMsg" label-width="120px">
            <el-form-item label="借读年级：" prop="gradeid">
              <el-select v-model="studentMsg.gradeid" placeholder="请选择借读年级" style="width:100%;" @change="changeClass">
                <el-option :label="grade.name" :value="grade.gradeid" :key="grade.gradeid"
                           v-for="grade in gradeList"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="借读班级：" prop="classid">
              <el-select v-model="studentMsg.classid" placeholder="请选择借读班级" style="width:100%;">
                <el-option :label="classData.classname" :value="classData.classid" :key="classData.classid"
                           v-for="classData in classList"></el-option>
              </el-select>
            </el-form-item>
          </el-form>
        </el-col>
        <el-col :span="8">
          <el-form ref="intoMsgTwo" :model="studentMsg" label-width="120px">
            <el-form-item label="报道日期：">
              <el-date-picker
                v-model="studentMsg.reportdate"
                :editable="false"
                type="date"
                placeholder="选择日期时间" style="width: 100%">
              </el-date-picker>
            </el-form-item>
          </el-form>
        </el-col>
      </el-row>
    </el-row>
    <el-row>
      <el-row class="into_row">
        <span class="into_subTitle">原学校信息</span>
      </el-row>
      <el-row :gutter="100" type="flex" justify="center">
        <el-col :span="8">
          <el-form ref="outMsgOne" :model="studentMsg" label-width="120px">
            <el-form-item label="学校名称：">
              <el-input placeholder="请输入学校名称" v-model="studentMsg.outschoolname"></el-input>
            </el-form-item>
            <el-form-item label="学校标识编码：">
              <el-input placeholder="请输入学校标识编码" v-model="studentMsg.outschoolidentity"></el-input>
            </el-form-item>
          </el-form>
        </el-col>
        <el-col :span="8">
          <el-form ref="outMsgTwo" :model="studentMsg" label-width="120px">
            <el-form-item label="原年级：">
              <el-input v-model="studentMsg.nowgrade" placeholder="请输入原年级"></el-input>
            </el-form-item>
            <el-form-item label="原班级：">
              <el-input v-model="studentMsg.nowclass" placeholder="请输入原班级"></el-input>
            </el-form-item>
          </el-form>
        </el-col>
      </el-row>
      <el-row :gutter="100" type="flex" justify="center">
        <el-col :span="16">
          <el-form ref="outMsgThree" :model="studentMsg" label-width="120px">
            <el-form-item label="申请理由：">
              <el-input type="textarea" resize="none" placeholder="请输入申请理由" v-model="studentMsg.reason"></el-input>
            </el-form-item>
          </el-form>
        </el-col>
      </el-row>
    </el-row>
    <el-row class="into_row into_btn">
      <el-button type="primary" @click="save">提交</el-button>
      <el-button @click="resetData">重置</el-button>
    </el-row>
    <el-dialog
      title="提交成功"
      :visible.sync="dialogVisible"
      :modal="false"
      :before-close="handleClose">
      <el-row type="flex" justify="center" class="studentReturnMsg">
        <el-col :span="12">
          <h5>已生成</h5>
          <el-form :model="studentReturnMsg" label-width="120px">
            <el-form-item label="学生账号：">
              <span>{{studentReturnMsg.student.account}}</span>
            </el-form-item>
            <el-form-item label="密码：">
              <span>{{studentReturnMsg.student.InitialPassword}}</span>
            </el-form-item>
            <el-form-item label="家长账号：">
              <span>{{studentReturnMsg.parent.account}}</span>
            </el-form-item>
            <el-form-item label="密码：">
              <span>{{studentReturnMsg.parent.InitialPassword}}</span>
            </el-form-item>
          </el-form>
        </el-col>
      </el-row>
    </el-dialog>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  import moment from 'moment'
  export default{
    data(){
      return {
        gradeList: [],
        classList: [],
        dialogVisible: false,
        studentReturnMsg: {
          parent: {},
          student: {}
        },
        studentMsg: {
          name: '',
          sex: '女',
          certificate: '居民身份证',
          idCard: '',
          studentCode: '',
          hkAddress: '',
          gradeid: '',
          classid: '',
          reportdate: '',
          outschoolname: '',
          nowgrade: '',
          outschoolidentity: '',
          nowclass: '',
          outdate: '',
          reason: ''
        },
        toParam: {
          name: '',
          sex: '',
          certificate: '',
          idCard: '',
          studentCode: '',
          hkAddress: '',
          grade: {
            gradeid: '',
            name: ''
          },
          class: {
            classid: '',
            classname: ''
          },
          reportdate: '',
          outschoolname: '',
          nowgrade: '',
          outschoolidentity: '',
          nowclass: '',
          outdate: '',
          reason: '',
          type: 0
        },
        studentMsgRules: {
          name: [
            {required: true, message: '请输入姓名', trigger: 'blur'},
            {min: 1, max: 5, message: '长度在 1 到 5 个字符', trigger: 'blur'}
          ],
          sex: [
            {required: true, message: '请选择性别', trigger: 'change'}
          ],
          gradeid: [
            {required: true, message: '请选择借读年级', trigger: 'change'},
          ],
          classid: [
            {required: true, message: '请选择借读班级', trigger: 'change'}
          ]
        }
      }
    },
    created: function () {
      var self = this;
      req.ajaxSend('/school/Transaction/operation/type/getGrade', 'post', '', function (res) {
        self.gradeList = res;
      })
    },
    methods: {
      changeClass(){
        var self = this, data = {
          gradeid: self.studentMsg.gradeid
        };
        self.studentMsg.classid = '';
        req.ajaxSend('/school/Transaction/operation/type/getClass', 'post', data, function (res) {
          self.classList = res;
        })
      },
      handleClose(done) {
        done();
      },
      save(){
        var self = this;
        self.$refs['studentMsgOne'].validate((valid) => {
          if (valid) {
            self.$refs['intoMsgOne'].validate((valid) => {
              if (valid) {
                var reg=/\d{17}[\d|x]|\d{15}/;
                if(self.studentMsg.idCard&&!reg.test(self.studentMsg.idCard)){
                  self.vmMsgWarning('请检查身份证号格式是否正确！');
                  return false;
                }
                for (let name in self.studentMsg) {
                  if (name == 'gradeid') {
                    for (let obj of self.gradeList) {
                      if (obj.gradeid == self.studentMsg[name]) {
                        self.toParam.grade.gradeid = obj.gradeid;
                        self.toParam.grade.name = obj.name;
                      }
                    }
                  } else if (name == 'classid') {
                    for (let obj of self.classList) {
                      if (obj.classid == self.studentMsg[name]) {
                        self.toParam.class.classid = obj.classid;
                        self.toParam.class.classname = obj.classname;
                      }
                    }
                  } else if (name == 'reportdate' || name == 'outdate') {
                    self.toParam[name] = moment(self.studentMsg[name]).format('YYYY-MM-DD');
                  } else {
                    self.toParam[name] = self.studentMsg[name];
                  }
                }
                req.ajaxSend('/school/Transaction/operation/type/jiedu', 'post', self.toParam, function (res) {
                  if (res.return) {
                    if (res.return == 'error') {
                      self.$confirm('系统内检测出同名学生?', '提示', {
                        confirmButtonText: '新建',
                        cancelButtonText: '取消',
                        type: 'warning'
                      }).then(() => {
                        self.toParam.type = 1;
                        req.ajaxSend('/school/Transaction/operation/type/jiedu', 'post', self.toParam, function (res) {
                          if (res.return) {
                            self.dialogVisible = true;
                            self.studentReturnMsg = res.data;
                          } else {
                            self.vmMsgError('借读失败！');
                          }
                        })
                      }).catch(() => {
                      });
                    } else {
                      self.dialogVisible = true;
                      self.studentReturnMsg = res.data;
                    }
                  } else {
                    self.vmMsgError('借读失败！');
                  }
                })
              } else {
                return false;
              }
            });
          } else {
            return false;
          }
        });
      },
      resetData(){
        this.studentMsg = {
          name: '',
          sex: '女',
          studentCode: '',
          formerAddress: '',
          certificate: '居民身份证',
          idCard: '',
          hkAddress: '',
          nowHomePath: '',
          gradeid: '',
          classid: '',
          reportdate: '',
          outschoolname: '',
          outschoolidentity: '',
          outdate: '',
          nowgrade: '',
          nowclass: '',
          reason: ''
        }
      }
    }
  }
</script>
<style>
  .transient .into_row {
    margin: 3.125rem 0 2rem;
  }

  .transient .into_subTitle {
    display: inline-block;
    width: 7.5rem;
    height: 2rem;
    line-height: 2rem;
    padding: 0;
    border-radius: 0 15px 15px 0;
    -webkit-box-shadow: 0 5px 5px 0 #ddd;
    -moz-box-shadow: 0 5px 5px 0 #ddd;
    box-shadow: 0 5px 5px 0 #ddd;
    background-color: #89bcf5;
    border-color: #89bcf5;
    color: #fff;
    text-align: center;
  }

  .transient .el-textarea__inner {
    height: 9.375rem;
  }

  .transient .into_btn {
    text-align: center;
  }

  .transient .into_btn .el-button {
    padding: .5rem 2.1rem;
    border-radius: 20px;
  }

  .transient .studentReturnMsg h5 {
    text-align: center;
    margin-bottom: 1.5rem;
    font-size: 1.125rem;
  }
</style>
