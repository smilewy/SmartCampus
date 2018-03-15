<template>
  <div class="personalInfoWorker">
    <el-row type="flex" align="middle">
      <h3>个人信息</h3>
    </el-row>
    <el-row class="personalInfoWorker_row">
      <el-row type="flex" justify="center" :gutter="100">
        <el-col :span="10">
          <el-form ref="form1" :model="form" label-width="120px">
            <el-form-item label="姓名：" required>
              <el-input v-model="form.name" disabled placeholder="请输入姓名"></el-input>
            </el-form-item>
            <el-form-item label="性别：" required>
              <el-radio-group v-model="form.sex" disabled>
                <el-radio label="男">男</el-radio>
                <el-radio label="女">女</el-radio>
              </el-radio-group>
            </el-form-item>
            <el-form-item label="角色：" required v-show="!isTeacher">
              <el-input v-model="form.post" disabled placeholder="请输入角色"></el-input>
            </el-form-item>
            <el-form-item label="任教科目：" required v-show="isTeacher">
              <el-input v-model="form.teachingSubjects" disabled placeholder="请输入科目"></el-input>
            </el-form-item>
            <el-form-item label="部门：">
              <el-input v-model="form.department" disabled placeholder="请输入部门"></el-input>
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
            <el-form-item label="政治面貌：" required>
              <el-select v-model="form.politics" disabled placeholder="请选择政治面貌" style="width: 100%;">
                <el-option :label="political.value" :value="political.value" v-for="(political,ix) in politicalData" :key="ix"></el-option>
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
        isTeacher: true,
        politicalData:politicalStatus,
        idCardTypeData:idCardType,
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
          post: '',
          teachingSubjects: '',
          department: ''
        },
        birth: '',
        rules: {
          phone: [
            {required: true, validator: validatePhone, trigger: 'blur'}
          ]
        }
      }
    },
    created: function () {
      var self = this;
      req.ajaxSend('/school/User/userGl?type=getTeacherPersonData', 'get', '', function (res) {
        self.isTeacher = res.isTeacher == 1 ? true : false;
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
      });
      self.provinceDataList = cityData.data;
    },
    methods: {
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
        self.$refs['form2'].validate((valid) => {
          if (valid) {
            data = {
              userData: self.form
            };
            req.ajaxSend('/school/User/userGl?type=updataTeacherPersonData', 'post', data, function (res) {
              if (res.statu == 1) {
                self.vmMsgSuccess('修改成功！');
              } else {
                self.vmMsgError(res.message);
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
  .personalInfoWorker {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .personalInfoWorker h3 {
    font-size: 1.25rem;
    display: inline-block;
  }

  .personalInfoWorker .saveBtn {
    text-align: center;
    margin-top: 4rem;
  }

  .personalInfoWorker .saveBtn .el-button {
    width: 50%;
  }

  .personalInfoWorker .personalInfoWorker_row {
    padding: 6rem 0;
  }

  .personalInfoWorker .chAdr {
    width: 30%;
  }

  .personalInfoWorker .detailedAddress {
    margin-top: 22px;
  }
</style>
