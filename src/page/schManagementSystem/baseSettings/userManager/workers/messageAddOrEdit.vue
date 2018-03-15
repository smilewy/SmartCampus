<template>
  <div class="teacherMessageAddOrEdit">
    <el-row type="flex" align="middle">
      <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
        src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
        alt=""><span class="returnTxt">返回</span></el-button>
      <h3>{{titleTxt}}</h3>
    </el-row>
    <el-row class="teacherMessageAddOrEdit_row">
      <el-row type="flex" justify="center" :gutter="100">
        <el-col :span="10">
          <el-form ref="form1" :model="form" :rules="rules" label-width="120px">
            <el-form-item label="姓名：" prop="name">
              <el-input v-model="form.name" placeholder="请输入姓名"></el-input>
            </el-form-item>
            <el-form-item label="性别：" required>
              <el-radio-group v-model="form.sex">
                <el-radio label="男">男</el-radio>
                <el-radio label="女">女</el-radio>
              </el-radio-group>
            </el-form-item>
            <el-form-item label="角色：" prop="roleId">
              <el-select v-model="form.roleId" placeholder="请选择角色" style="width: 100%;">
                <el-option :label="subject.roleName" :value="subject.roleId" :key="subject.subjectid"
                           v-for="subject in postLists"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="部门：">
              <el-select v-model="form.departmentId" placeholder="请选择部门" style="width: 100%;">
                <el-option :label="department.departmentName" :value="department.departmentId"
                           :key="department.departmentId"
                           v-for="department in departmentList"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="民族：">
              <el-select v-model="form.nation" placeholder="请选择民族" style="width: 100%;">
                <el-option :label="national.value" :value="national.value" v-for="(national,ix) in nationalList"
                           :key="ix"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="出生日期：">
              <el-date-picker type="date" :editable="false" placeholder="请选择出生日期" v-model="birth"
                              style="width: 100%;"></el-date-picker>
            </el-form-item>
            <el-form-item label="籍贯：">
              <el-select v-model="form.origin.province" placeholder="请选择省份" class="chAdr" @change="chooseOrigin">
                <el-option :label="cityData.name" :value="cityData.name" v-for="(cityData,ix) in provinceDataList"
                           :key="ix"></el-option>
              </el-select>
              <el-select v-model="form.origin.city" placeholder="请选择市" class="chAdr">
                <el-option :label="origin.name" :value="origin.name" v-for="(origin,ix) in originCityDataList"
                           :key="ix"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="身份证类型：">
              <el-select v-model="form.idCardType" placeholder="请选择身份证类型" style="width: 100%;">
                <el-option :label="idCardType.value" :value="idCardType.value" v-for="(idCardType,ix) in idCardTypeData" :key="ix"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="身份证号：">
              <el-input v-model="form.idCard" placeholder="请输入身份证号"></el-input>
            </el-form-item>
          </el-form>
        </el-col>
        <el-col :span="10">
          <el-form ref="form2" :model="form" :rules="rules" label-width="120px">
            <el-form-item label="手机号码：" prop="phone">
              <el-input v-model="form.phone" placeholder="请输入手机号码"></el-input>
            </el-form-item>
            <el-form-item label="政治面貌：" prop="politics">
              <el-select v-model="form.politics" placeholder="请选择政治面貌" style="width: 100%;">
                <el-option :label="political.value" :value="political.value" v-for="(political,ix) in politicalData"
                           :key="ix"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="家庭住址：">
              <el-row>
                <el-select v-model="form.homeAddress.province" placeholder="请选择省份" class="chAdr"
                           @change="chooseHomeAddressCity">
                  <el-option :label="cityData.name" :value="cityData.name" v-for="(cityData,ix) in provinceDataList"
                             :key="ix"></el-option>
                </el-select>
                <el-select v-model="form.homeAddress.city" placeholder="请选择市" class="chAdr"
                           @change="chooseHomeAddressCounty">
                  <el-option :label="homeAddressCity.name" :value="homeAddressCity.name"
                             v-for="(homeAddressCity,ix) in homeAddressCityDataList" :key="ix"></el-option>
                </el-select>
                <el-select v-model="form.homeAddress.area" placeholder="区/县级" class="chAdr">
                  <el-option :label="homeAddressCounty.name" :value="homeAddressCounty.name"
                             v-for="(homeAddressCounty,ix) in homeAddressCountyDataList" :key="ix"></el-option>
                </el-select>
              </el-row>
              <el-row class="detailedAddress">
                <el-input v-model="form.homeAddress.detail" placeholder="请输入详细地址"></el-input>
              </el-row>
            </el-form-item>
            <el-form-item label="现住地址：">
              <el-row>
                <el-select v-model="form.nowAddress.province" placeholder="请选择省份" class="chAdr"
                           @change="chooseLiveAddressCity">
                  <el-option :label="cityData.name" :value="cityData.name" v-for="(cityData,ix) in provinceDataList"
                             :key="ix"></el-option>
                </el-select>
                <el-select v-model="form.nowAddress.city" placeholder="请选择市" class="chAdr"
                           @change="chooseLiveAddressCounty">
                  <el-option :label="liveAddressCity.name" :value="liveAddressCity.name"
                             v-for="(liveAddressCity,ix) in liveAddressCityDataList" :key="ix"></el-option>
                </el-select>
                <el-select v-model="form.nowAddress.area" placeholder="区/县级" class="chAdr">
                  <el-option :label="liveAddressCounty.name" :value="liveAddressCounty.name"
                             v-for="(liveAddressCounty,ix) in liveAddressCountyDataList" :key="ix"></el-option>
                </el-select>
              </el-row>
              <el-row class="detailedAddress">
                <el-input v-model="form.nowAddress.detail" placeholder="请输入详细地址"></el-input>
              </el-row>
            </el-form-item>
            <el-form-item label="户口所在地：">
              <el-row>
                <el-select v-model="form.registerAddress.province" placeholder="请选择省份" class="chAdr"
                           @change="chooseAccountLocationCity">
                  <el-option :label="cityData.name" :value="cityData.name" v-for="(cityData,ix) in provinceDataList"
                             :key="ix"></el-option>
                </el-select>
                <el-select v-model="form.registerAddress.city" placeholder="请选择市" class="chAdr"
                           @change="chooseAccountLocationCounty">
                  <el-option :label="accountLocationCity.name" :value="accountLocationCity.name"
                             v-for="(accountLocationCity,ix) in accountLocationCityDataList" :key="ix"></el-option>
                </el-select>
                <el-select v-model="form.registerAddress.area" placeholder="区/县级" class="chAdr">
                  <el-option :label="accountLocationCounty.name" :value="accountLocationCounty.name"
                             v-for="(accountLocationCounty,ix) in accountLocationCountyDataList" :key="ix"></el-option>
                </el-select>
              </el-row>
              <el-row class="detailedAddress">
                <el-input v-model="form.registerAddress.detail" placeholder="请输入详细地址"></el-input>
              </el-row>
            </el-form-item>
          </el-form>
        </el-col>
      </el-row>
      <el-row class="saveBtn">
        <el-button type="primary" @click="save">提交</el-button>
      </el-row>
    </el-row>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  import moment from 'moment'
  import cityData from '@/assets/js/city'
  import {ethnics, politicalStatus,idCardType} from '@/assets/js/common-const-data'

  export default {
    data() {
      var reg = /0?(13|14|15|18)[0-9]{9}/;
      var validatePhone = (rule, value, callback) => {
        if (!reg.test(value)) {
          callback(new Error('请输入正确的手机号格式'));
        } else {
          callback();
        }
      };
      return {
        politicalData: politicalStatus,
        idCardTypeData: idCardType,
        postLists: [],
        departmentList: [],
        provinceDataList: [],
        originCityDataList: [],
        homeAddressCityDataList: [],
        homeAddressCountyDataList: [],
        liveAddressCityDataList: [],
        liveAddressCountyDataList: [],
        accountLocationCityDataList: [],
        accountLocationCountyDataList: [],
        nationalList: ethnics,
        titleTxt: '',
        form: {
          departmentId: '',
          name: '',
          phone: '',
          sex: '男',
          politics: '',
          idCardType: '',
          origin: {
            province: '',
            city: ''
          },
          homeAddress: {
            province: '',
            city: '',
            area: '',
            detail: ''
          },
          registerAddress: {
            province: '',
            city: '',
            area: '',
            detail: ''
          },
          nowAddress: {
            province: '',
            city: '',
            area: '',
            detail: ''
          },
          nation: '',
          birth: '',
          idCard: '',
          roleId: ''
        },
        birth: '',
        rules: {
          name: [
            {required: true, message: '请输入姓名', trigger: 'blur'}
          ],
          roleId: [
            {required: true, message: '请选择角色', trigger: 'change'}
          ],
          phone: [
            {required: true, validator: validatePhone, trigger: 'blur'}
          ],
          politics: [
            {required: true, message: '请选择政治面貌', trigger: 'change'}
          ]
        }
      }
    },
    created: function () {
      var self = this, type = this.$route.params.type, data;
      self.provinceDataList = cityData.data;
      if (type == 0) {
        self.titleTxt = '职工补录';
      } else {
        self.titleTxt = '职工信息修改';
        data = {
          id: this.$route.params.id
        };
        req.ajaxSend('/school/User/userGl?type=getZgById', 'get', data, function (res) {
          self.form = res.data;
          self.birth = res.data.birth ? new Date(res.data.birth) : res.data.birth;
          for (let obj of self.provinceDataList) {
            if (obj.name == self.form.origin.province) {
              self.originCityDataList = obj.cityList;
            }
            if (obj.name == self.form.homeAddress.province) {
              self.homeAddressCityDataList = obj.cityList;
            }
            if (obj.name == self.form.nowAddress.province) {
              self.liveAddressCityDataList = obj.cityList;
            }
            if (obj.name == self.form.registerAddress.province) {
              self.accountLocationCityDataList = obj.cityList;
            }
          }
          if (typeof self.form.homeAddress.city) {
            for (let obj of self.homeAddressCityDataList) {
              if (obj.name == self.form.homeAddress.city) {
                self.homeAddressCountyDataList = obj.cityList;
              }
            }
          }
          if (typeof self.form.nowAddress.city) {
            for (let obj of self.liveAddressCityDataList) {
              if (obj.name == self.form.nowAddress.city) {
                self.liveAddressCountyDataList = obj.cityList;
              }
            }
          }
          if (typeof self.form.registerAddress.city) {
            for (let obj of self.accountLocationCityDataList) {
              if (obj.name == self.form.registerAddress.city) {
                self.accountLocationCountyDataList = obj.cityList;
              }
            }
          }
        })
      }
      //查询角色
      req.ajaxSend('/school/user/getRoleListUser?type=list', 'get', '', function (res) {
        self.postLists = res.data;
      });
      //查询部门
      req.ajaxSend('/school/user/userGl?type=getBmList', 'get', '', function (res) {
        self.departmentList = res.data;
      })
    },
    methods: {
      returnFlowchart() {
        this.$router.go(-1);
      },
      chooseOrigin() {
        this.form.origin.city = '';
        for (let obj of this.provinceDataList) {
          if (obj.name == this.form.origin.province) {
            this.originCityDataList = obj.cityList;
          }
        }
      },
      chooseHomeAddressCity() {
        this.form.homeAddress.city = '';
        this.form.homeAddress.area = '';
        this.homeAddressCountyDataList = [];
        for (let obj of this.provinceDataList) {
          if (obj.name == this.form.homeAddress.province) {
            this.homeAddressCityDataList = obj.cityList;
          }
        }
      },
      chooseHomeAddressCounty() {
        this.form.homeAddress.area = '';
        if (typeof this.form.homeAddress.city) {
          for (let obj of this.homeAddressCityDataList) {
            if (obj.name == this.form.homeAddress.city) {
              this.homeAddressCountyDataList = obj.cityList;
            }
          }
        }
      },
      chooseLiveAddressCity() {
        this.form.nowAddress.city = '';
        this.form.nowAddress.area = '';
        this.liveAddressCountyDataList = [];
        for (let obj of this.provinceDataList) {
          if (obj.name == this.form.nowAddress.province) {
            this.liveAddressCityDataList = obj.cityList;
          }
        }
      },
      chooseLiveAddressCounty() {
        this.form.nowAddress.area = '';
        if (typeof this.form.nowAddress.city) {
          for (let obj of this.liveAddressCityDataList) {
            if (obj.name == this.form.nowAddress.city) {
              this.liveAddressCountyDataList = obj.cityList;
            }
          }
        }
      },
      chooseAccountLocationCity() {
        this.form.registerAddress.city = '';
        this.form.registerAddress.area = '';
        this.accountLocationCountyDataList = [];
        for (let obj of this.provinceDataList) {
          if (obj.name == this.form.registerAddress.province) {
            this.accountLocationCityDataList = obj.cityList;
          }
        }
      },
      chooseAccountLocationCounty() {
        this.form.registerAddress.area = '';
        if (typeof this.form.registerAddress.city) {
          for (let obj of this.accountLocationCityDataList) {
            if (obj.name == this.form.registerAddress.city) {
              this.accountLocationCountyDataList = obj.cityList;
            }
          }
        }
      },
      save() {
        var self = this, data;
        self.form.birth = self.birth ? moment(self.birth).format('YYYY-MM-DD') : self.birth;
        self.$refs['form1'].validate((valid) => {
          if (valid) {
            self.$refs['form2'].validate((valid) => {
              if (valid) {
                if (self.titleTxt == '职工补录') {
                  data = {
                    name: self.form.name,
                    roleId: self.form.roleId
                  };
                  req.ajaxSend('/school/user/userGl?type=checkUserMore', 'post', data, function (res) {
                    if (res.statu == 1) {
                      req.ajaxSend('/school/user/userGl?type=addStaff', 'post', self.form, function (res) {
                        if (res.statu == 1) {
                          self.vmMsgSuccess('添加成功！');
                        } else {
                          self.vmMsgError(res.message);
                        }
                      })
                    } else {
                      self.$confirm(res.message, '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                      }).then(() => {
                        req.ajaxSend('/school/user/userGl?type=addStaff', 'post', self.form, function (res) {
                          if (res.statu == 1) {
                            self.vmMsgSuccess('添加成功！');
                          } else {
                            self.vmMsgError(res.message);
                          }
                        })
                      }).catch(() => {
                      });
                    }
                  })
                } else {
                  data = {
                    userData: self.form
                  };
                  req.ajaxSend('/school/user/userGl?type=updataZg', 'post', data, function (res) {
                    if (res.statu == 1) {
                      self.vmMsgSuccess('修改成功！');
                    } else {
                      self.vmMsgError(res.message);
                    }
                  })
                }
              } else {
                return false;
              }
            });
          } else {
            return false;
          }
        });
      }
    }
  }
</script>
<style>
  .teacherMessageAddOrEdit {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .teacherMessageAddOrEdit .return_btn.el-button--primary {
    background-color: #ff8686;
    border-color: #ff8686;
    border-radius: 20px;
  }

  .teacherMessageAddOrEdit .return_btn.el-button--primary .returnTxt {
    margin-left: 10px;
  }

  .teacherMessageAddOrEdit .return_btn + h3 {
    font-size: 1.25rem;
    display: inline-block;
    margin-left: 2rem;
  }

  .teacherMessageAddOrEdit .saveBtn {
    text-align: center;
    margin-top: 4rem;
  }

  .teacherMessageAddOrEdit .saveBtn .el-button {
    width: 50%;
  }

  .teacherMessageAddOrEdit .teacherMessageAddOrEdit_row {
    padding: 6rem 0;
  }

  .teacherMessageAddOrEdit .chAdr {
    width: 30%;
  }

  .teacherMessageAddOrEdit .detailedAddress {
    margin-top: 22px;
  }
</style>
