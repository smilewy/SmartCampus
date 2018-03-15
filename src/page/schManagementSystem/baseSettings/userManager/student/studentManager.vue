<template>
  <div class="g-container sManager">
    <header class="g-header g-textHeader">学生个人信息</header>
    <section class="g-section clear_fix">
      <div class="g-sectionL">
        <header class="gL-header">
          <h2>选择学生</h2>
          <el-input v-model="fuzzyInput" class="fuzzyInput" placeholder="请输入查询分类或姓名"
                    suffix-icon="el-icon-search"></el-input>
        </header>
        <section class="gL-section">
          <el-tree :highlight-current="true" :data="treeData" :props="defaultProps" ref="tree"
                   :filter-node-method="filterNode" @node-click="handleNodeClick"
                   v-loading="loading"
                   element-loading-text="拼命加载中">
          </el-tree>
        </section>
      </div>
      <div class="g-sectionR">
        <header class="gR-header">
          <ul>
            <li @click="changeMsg('student_info')"
                :style="{color:headerShow=='student_info'?'#4da1ff':''}">
              基本信息
            </li>
            <li @click="changeMsg('school_rollinfo')"
                :style="{color:headerShow=='school_rollinfo'?'#4da1ff':''}">
              学籍信息
            </li>
            <li @click="changeMsg('cen_reg_info')"
                :style="{color:headerShow=='cen_reg_info'?'#4da1ff':''}">
              户籍信息
            </li>
            <li @click="changeMsg('parents_info')"
                :style="{color:headerShow=='parents_info'?'#4da1ff':''}">
              家长信息
            </li>
            <li @click="changeMsg('tuition_info')"
                :style="{color:headerShow=='tuition_info'?'#4da1ff':''}">
              学费信息
            </li>
            <li @click="changeMsg('other_info')"
                :style="{color:headerShow=='other_info'?'#4da1ff':''}">
              其他信息
            </li>
          </ul>
        </header>
        <section class="gR-section" v-show="userId">
          <el-row class="gR-sectionBody">
            <el-form class="messageForm" ref="student_info" :rules="messageFormRules.student_info"
                     :model="messageForm.student_info" label-width="112px"
                     v-show="headerShow=='student_info'">
              <div class="g-messageContainer g-liOneWrapRow">
                <el-form-item class="row" label="照片:">
                  <div class="pictureContainer">
                    <div class="clear_fix" v-show="isHandlerMsg">
                      <div class="imgContainer">
                        <img :src="messageForm.student_info.logo"/>
                      </div>
                      <div style="font-size:12px;">
                        <p>尺寸：150*200</p>
                        <p>大小：小于5M</p>
                        <el-button type="primary" class="uploadImg">
                          <span>上传照片</span>
                          <input type="file" accept="image/jpeg,image/jpg,image/png" class="file_input"
                                 @change="sendFile">
                        </el-button>
                      </div>
                    </div>
                    <div v-show="!isHandlerMsg" class="imgContainer">
                      <img :src="messageData.logo"/>
                    </div>
                  </div>
                </el-form-item>
                <div class="row">
                  <el-form-item label="姓名:">
                    <div v-text="messageData.name"></div>
                  </el-form-item>
                  <el-form-item label="年级:">
                    <div v-text="messageData.grade"></div>
                  </el-form-item>
                  <el-form-item label="班级:">
                    <div v-text="messageData.className"></div>
                  </el-form-item>
                </div>
              </div>
              <div class="g-messageContainer g-liOneWrapRow">
                <el-form-item class="row" label="班级座号:">
                  <el-input v-model="messageForm.student_info.serialNumber" v-show="isHandlerMsg"
                            placeholder="请输入班级座号"></el-input>
                  <div v-text="messageData.serialNumber" v-show="!isHandlerMsg"></div>
                </el-form-item>
                <el-form-item class="row" label="手机号码:" prop="phone">
                  <el-input v-model="messageForm.student_info.phone" v-show="isHandlerMsg"
                            placeholder="请输入手机号码"></el-input>
                  <div v-text="messageData.phone" v-show="!isHandlerMsg"></div>
                </el-form-item>
                <el-form-item class="row" label="性别:">
                  <el-select v-model="messageForm.student_info.sex" style="width:100%;" v-show="isHandlerMsg"
                             placeholder="请选择性别">
                    <el-option value="女" label="女"></el-option>
                    <el-option value="男" label="男"></el-option>
                  </el-select>
                  <div v-text="messageData.sex" v-show="!isHandlerMsg"></div>
                </el-form-item>
                <el-form-item class="row" label="家庭住址:">
                  <el-input v-model="messageForm.student_info.homePath" v-show="isHandlerMsg"
                            placeholder="请输入家庭住址"></el-input>
                  <div v-text="messageData.homePath" v-show="!isHandlerMsg"></div>
                </el-form-item>
                <el-form-item class="row" label="身高:">
                  <el-input v-model="messageForm.student_info.height" v-show="isHandlerMsg"
                            placeholder="请输入身高"></el-input>
                  <div v-text="messageData.height" v-show="!isHandlerMsg"></div>
                </el-form-item>
                <el-form-item class="row" label="家庭住址邮编:">
                  <el-input v-model="messageForm.student_info.homePostcode" v-show="isHandlerMsg"
                            placeholder="请输入家庭住址邮编"></el-input>
                  <div v-text="messageData.homePostcode" v-show="!isHandlerMsg"></div>
                </el-form-item>
                <el-form-item class="row" label="身份证号:">
                  <el-input v-model="messageForm.student_info.idCard" v-show="isHandlerMsg"
                            placeholder="请输入身份证号"></el-input>
                  <div v-text="messageData.idCard" v-show="!isHandlerMsg"></div>
                </el-form-item>
                <el-form-item class="row" label="现住地址:">
                  <el-input v-model="messageForm.student_info.nowHomePath" v-show="isHandlerMsg"
                            placeholder="请输入现住地址"></el-input>
                  <div v-text="messageData.nowHomePath" v-show="!isHandlerMsg"></div>
                </el-form-item>
                <el-form-item class="row" label="国籍:">
                  <el-input v-model="messageForm.student_info.country" v-show="isHandlerMsg"
                            placeholder="请输入国籍"></el-input>
                  <div v-text="messageData.country" v-show="!isHandlerMsg"></div>
                </el-form-item>
                <el-form-item class="row" label="现住地址邮编:">
                  <el-input v-model="messageForm.student_info.nowHomePostcode" v-show="isHandlerMsg"
                            placeholder="请输入现住地址邮编"></el-input>
                  <div v-text="messageData.nowHomePostcode" v-show="!isHandlerMsg"></div>
                </el-form-item>
                <el-form-item class="row" label="民族:">
                  <el-select v-model="messageForm.student_info.nation" placeholder="请选择民族" style="width: 100%;" v-show="isHandlerMsg">
                    <el-option :label="national.value" :value="national.value" v-for="(national,ix) in nationalList"
                               :key="ix"></el-option>
                  </el-select>
                  <div v-text="messageData.nation" v-show="!isHandlerMsg"></div>
                </el-form-item>
                <el-form-item class="row" label="宿舍栋号:">
                  <el-input v-model="messageForm.student_info.dorStore" v-show="isHandlerMsg"
                            placeholder="请输入宿舍栋号"></el-input>
                  <div v-text="messageData.dorStore" v-show="!isHandlerMsg"></div>
                </el-form-item>
                <el-form-item class="row" label="政治面貌:">
                  <el-select v-model="messageForm.student_info.political" v-show="isHandlerMsg" placeholder="请选择政治面貌" style="width: 100%;">
                    <el-option :label="political.value" :value="political.value" v-for="(political,ix) in politicalData" :key="ix"></el-option>
                  </el-select>
                  <div v-text="messageData.political" v-show="!isHandlerMsg"></div>
                </el-form-item>
                <el-form-item class="row" label="宿舍楼层:">
                  <el-input v-model="messageForm.student_info.dorStorey" v-show="isHandlerMsg"
                            placeholder="请输入宿舍楼层"></el-input>
                  <div v-text="messageData.dorStorey" v-show="!isHandlerMsg"></div>
                </el-form-item>
                <el-form-item class="row" label="入团时间:">
                  <el-date-picker type="date" :editable="false" style="width:100%;" placeholder="选择时间"
                                  v-model="student_infoTime1"
                                  v-show="isHandlerMsg"></el-date-picker>
                  <div v-text="messageData.leagueTime" v-show="!isHandlerMsg"></div>
                </el-form-item>
                <el-form-item class="row" label="宿舍号:">
                  <el-input v-model="messageForm.student_info.dorNumber" v-show="isHandlerMsg"
                            placeholder="请输入宿舍号"></el-input>
                  <div v-text="messageData.dorNumber" v-show="!isHandlerMsg"></div>
                </el-form-item>
                <el-form-item class="row" label="入学时间:">
                  <el-date-picker type="date" :editable="false" style="width:100%;" placeholder="选择时间"
                                  v-model="student_infoTime2"
                                  v-show="isHandlerMsg"></el-date-picker>
                  <div v-text="messageData.enrolTime" v-show="!isHandlerMsg"></div>
                </el-form-item>
                <el-form-item class="row" label="学生卡号:">
                  <el-input v-model="messageForm.student_info.studentCard" v-show="isHandlerMsg"
                            placeholder="请输入学生卡号"></el-input>
                  <div v-text="messageData.studentCard" v-show="!isHandlerMsg"></div>
                </el-form-item>
                <el-form-item class="row" label="入学方式:">
                  <el-select v-model="messageForm.student_info.enrolWay" style="width:100%;" v-show="isHandlerMsg"
                             placeholder="请选择入学方式">
                    <el-option :value="entranceType.value" :label="entranceType.value" v-for="(entranceType,ix) in entranceTypeData" :key="ix"></el-option>
                  </el-select>
                  <div v-text="messageData.enrolWay" v-show="!isHandlerMsg"></div>
                </el-form-item>
                <el-form-item class="row" label="是否住校:">
                  <el-radio-group v-model="messageForm.student_info.isResSchool" v-show="isHandlerMsg">
                    <el-radio label="是">是</el-radio>
                    <el-radio label="否">否</el-radio>
                  </el-radio-group>
                  <el-radio-group v-model="messageData.isResSchool" v-show="!isHandlerMsg">
                    <el-radio disabled label="是">是</el-radio>
                    <el-radio disabled label="否">否</el-radio>
                  </el-radio-group>
                </el-form-item>
              </div>
            </el-form>
            <el-form class="messageForm" ref="school_rollinfo" :model="messageForm.school_rollinfo" label-width="112px"
                     v-show="headerShow=='school_rollinfo'">
              <el-row :gutter="100">
                <el-col :span="11">
                  <el-form-item label="是否在校：">
                    <el-radio-group v-model="messageForm.school_rollinfo.isAtSchool" v-show="isHandlerMsg">
                      <el-radio label="是">是</el-radio>
                      <el-radio label="否">否</el-radio>
                    </el-radio-group>
                    <el-radio-group v-model="messageData.isAtSchool" v-show="!isHandlerMsg">
                      <el-radio disabled label="是">是</el-radio>
                      <el-radio disabled label="否">否</el-radio>
                    </el-radio-group>
                  </el-form-item>
                  <el-form-item label="是否休学：">
                    <el-radio-group v-model="messageForm.school_rollinfo.isLeave" v-show="isHandlerMsg">
                      <el-radio label="1">是</el-radio>
                      <el-radio label="0">否</el-radio>
                    </el-radio-group>
                    <el-radio-group v-model="messageData.isLeave" v-show="!isHandlerMsg">
                      <el-radio disabled label="1">是</el-radio>
                      <el-radio disabled label="0">否</el-radio>
                    </el-radio-group>
                  </el-form-item>
                  <el-form-item label="科类：">
                    <el-select v-model="messageForm.school_rollinfo.stream" style="width: 100%;" placeholder="请选择科类"
                               @change="chooseMajor"
                               v-show="isHandlerMsg">
                      <el-option :label="branch.branch" :value="branch.branch" v-for="branch in branchList"
                                 :key="branch.branchid"></el-option>
                    </el-select>
                    <div v-show="!isHandlerMsg">{{messageData.stream}}</div>
                  </el-form-item>
                  <el-form-item label="国家学号：">
                    <el-input v-show="isHandlerMsg" v-model="messageForm.school_rollinfo.nationalNumber"
                              placeholder="请输入国家学号"></el-input>
                    <div v-show="!isHandlerMsg">{{messageData.nationalNumber}}</div>
                  </el-form-item>
                  <el-form-item label="省统编学号：">
                    <el-input v-show="isHandlerMsg" v-model="messageForm.school_rollinfo.provinceNumber"
                              placeholder="请输入省统编学号"></el-input>
                    <div v-show="!isHandlerMsg">{{messageData.provinceNumber}}</div>
                  </el-form-item>
                  <el-form-item label="市统编学号：">
                    <el-input v-show="isHandlerMsg" v-model="messageForm.school_rollinfo.cityNumber"
                              placeholder="请输入市统编学号"></el-input>
                    <div v-show="!isHandlerMsg">{{messageData.cityNumber}}</div>
                  </el-form-item>
                  <el-form-item label="录取类别：">
                    <el-select v-show="isHandlerMsg" v-model="messageForm.school_rollinfo.admCategory"
                               style="width: 100%;"
                               placeholder="请选择录取类别">
                      <el-option :label="admittedType.value" :value="admittedType.value" v-for="(admittedType,ix) in admittedTypeData" :key="ix"></el-option>
                    </el-select>
                    <div v-show="!isHandlerMsg">{{messageData.admCategory}}</div>
                  </el-form-item>
                </el-col>
                <el-col :span="11">
                  <el-form-item label="是否挂靠：">
                    <el-radio-group v-model="messageForm.school_rollinfo.isSubor" v-show="isHandlerMsg">
                      <el-radio label="1">是</el-radio>
                      <el-radio label="0">否</el-radio>
                    </el-radio-group>
                    <el-radio-group v-model="messageData.isSubor" v-show="!isHandlerMsg">
                      <el-radio disabled label="1">是</el-radio>
                      <el-radio disabled label="0">否</el-radio>
                    </el-radio-group>
                  </el-form-item>
                  <el-form-item label="是否借读：">
                    <el-radio-group v-model="messageForm.school_rollinfo.isTempStudy" v-show="isHandlerMsg">
                      <el-radio label="1">是</el-radio>
                      <el-radio label="0">否</el-radio>
                    </el-radio-group>
                    <el-radio-group v-model="messageData.isTempStudy" v-show="!isHandlerMsg">
                      <el-radio disabled label="1">是</el-radio>
                      <el-radio disabled label="0">否</el-radio>
                    </el-radio-group>
                  </el-form-item>
                  <el-form-item label="专业：">
                    <el-select v-show="isHandlerMsg" v-model="messageForm.school_rollinfo.major" style="width: 100%;"
                               placeholder="请选择专业">
                      <el-option :label="major.majorname" :value="major.majorname" v-for="major in majorList"
                                 :key="major.majorid"></el-option>
                    </el-select>
                    <div v-show="!isHandlerMsg">{{messageData.major}}</div>
                  </el-form-item>
                  <el-form-item label="小学学校：">
                    <el-input v-show="isHandlerMsg" v-model="messageForm.school_rollinfo.eleSchool"
                              placeholder="请输入小学学校"></el-input>
                    <div v-show="!isHandlerMsg">{{messageData.eleSchool}}</div>
                  </el-form-item>
                  <el-form-item label="中学学校：">
                    <el-input v-show="isHandlerMsg" v-model="messageForm.school_rollinfo.secSchool"
                              placeholder="请输入中学学校"></el-input>
                    <div v-show="!isHandlerMsg">{{messageData.secSchool}}</div>
                  </el-form-item>
                  <el-form-item label="中考分数：">
                    <el-input v-show="isHandlerMsg" v-model="messageForm.school_rollinfo.midExam"
                              placeholder="请输入中考分数"></el-input>
                    <div v-show="!isHandlerMsg">{{messageData.midExam}}</div>
                  </el-form-item>
                </el-col>
              </el-row>
            </el-form>
            <el-form class="messageForm" ref="cen_reg_info" :model="messageForm.cen_reg_info" label-width="130px"
                     v-show="headerShow=='cen_reg_info'">
              <el-row :gutter="100">
                <el-col :span="11">
                  <el-form-item label="户口所在地：">
                    <el-input v-show="isHandlerMsg" v-model="messageForm.cen_reg_info.perAddress"
                              placeholder="请输入户口所在地"></el-input>
                    <div v-show="!isHandlerMsg">{{messageData.perAddress}}</div>
                  </el-form-item>
                  <el-form-item label="户籍类型：">
                    <el-select v-show="isHandlerMsg" v-model="messageForm.cen_reg_info.cenRegType" style="width: 100%;"
                               placeholder="请选择户籍类型">
                      <el-option :label="tenanciesType.value" :value="tenanciesType.value" v-for="(tenanciesType,ix) in tenanciesTypeData" :key="ix"></el-option>
                    </el-select>
                    <div v-show="!isHandlerMsg">{{messageData.cenRegType}}</div>
                  </el-form-item>
                  <el-form-item label="户口性质：">
                    <el-input v-show="isHandlerMsg" v-model="messageForm.cen_reg_info.cenRegNature"
                              placeholder="请输入户口性质"></el-input>
                    <div v-show="!isHandlerMsg">{{messageData.cenRegNature}}</div>
                  </el-form-item>
                </el-col>
                <el-col :span="11">
                  <el-form-item label="户口所在地代码：">
                    <el-input v-show="isHandlerMsg" v-model="messageForm.cen_reg_info.perAddressCode"
                              placeholder="请输入户口所在地代码"></el-input>
                    <div v-show="!isHandlerMsg">{{messageData.perAddressCode}}</div>
                  </el-form-item>
                  <el-form-item label="生源地代码：">
                    <el-input v-show="isHandlerMsg" v-model="messageForm.cen_reg_info.originCode"
                              placeholder="请输入生源地代码"></el-input>
                    <div v-show="!isHandlerMsg">{{messageData.originCode}}</div>
                  </el-form-item>
                </el-col>
              </el-row>
            </el-form>
            <el-form class="messageForm" ref="parents_info" :rules="messageFormRules.parents_info"
                     :model="messageForm.parents_info" label-width="200px"
                     v-show="headerShow=='parents_info'">
              <el-row :gutter="50">
                <el-col :span="12">
                  <el-form-item label="家长1姓名：">
                    <el-input v-show="isHandlerMsg" v-model="messageForm.parents_info.jzName1"
                              placeholder="请输入家长1姓名"></el-input>
                    <div v-show="!isHandlerMsg">{{messageData.jzName1}}</div>
                  </el-form-item>
                  <el-form-item label="家长1联系电话：" prop="jzPhone1">
                    <el-input v-show="isHandlerMsg" v-model="messageForm.parents_info.jzPhone1"
                              placeholder="请输入家长1联系电话"></el-input>
                    <div v-show="!isHandlerMsg">{{messageData.jzPhone1}}</div>
                  </el-form-item>
                  <el-form-item label="家长1工作单位：">
                    <el-input v-show="isHandlerMsg" v-model="messageForm.parents_info.jzWorkUnit1"
                              placeholder="请输入家长1工作单位"></el-input>
                    <div v-show="!isHandlerMsg">{{messageData.jzWorkUnit1}}</div>
                  </el-form-item>
                  <el-form-item label="家长1居住地址：">
                    <el-input v-show="isHandlerMsg" v-model="messageForm.parents_info.resAddress1"
                              placeholder="请输入家长1居住地址"></el-input>
                    <div v-show="!isHandlerMsg">{{messageData.resAddress1}}</div>
                  </el-form-item>
                  <el-form-item label="家长1身份证类型：">
                    <el-select v-show="isHandlerMsg" v-model="messageForm.parents_info.jzCardType1" style="width:100%;"
                               placeholder="请选择家长1身份证类型">
                      <el-option :label="idCardType.value" :value="idCardType.value" v-for="(idCardType,ix) in idCardTypeData" :key="ix"></el-option>
                    </el-select>
                    <div v-show="!isHandlerMsg">{{messageData.jzCardType1}}</div>
                  </el-form-item>
                </el-col>
                <el-col :span="12">
                  <el-form-item label="家长1关系说明：">
                    <el-input v-show="isHandlerMsg" v-model="messageForm.parents_info.relation1"
                              placeholder="请输入关系"></el-input>
                    <div v-show="!isHandlerMsg">{{messageData.relation1}}</div>
                  </el-form-item>
                  <el-form-item label="家长1是否监护人：">
                    <el-radio-group v-show="isHandlerMsg" v-model="messageForm.parents_info.isGuardian1">
                      <el-radio label="是">是</el-radio>
                      <el-radio label="否">否</el-radio>
                    </el-radio-group>
                    <el-radio-group v-show="!isHandlerMsg" v-model="messageForm.parents_info.isGuardian1">
                      <el-radio disabled label="是">是</el-radio>
                      <el-radio disabled label="否">否</el-radio>
                    </el-radio-group>
                  </el-form-item>
                  <el-form-item label="家长1职务：">
                    <el-input v-show="isHandlerMsg" v-model="messageForm.parents_info.jzPost1"
                              placeholder="请输入家长1职务"></el-input>
                    <div v-show="!isHandlerMsg">{{messageData.jzPost1}}</div>
                  </el-form-item>
                  <el-form-item label="家长1民族：">
                    <el-select v-model="messageForm.parents_info.jzNation1" placeholder="请选择民族" style="width: 100%;" v-show="isHandlerMsg">
                      <el-option :label="national.value" :value="national.value" v-for="(national,ix) in nationalList"
                                 :key="ix"></el-option>
                    </el-select>
                    <div v-show="!isHandlerMsg">{{messageData.jzNation1}}</div>
                  </el-form-item>
                  <el-form-item label="家长1身份证号：">
                    <el-input v-show="isHandlerMsg" v-model="messageForm.parents_info.jzIdCard1"
                              placeholder="请输入家长1身份证号"></el-input>
                    <div v-show="!isHandlerMsg">{{messageData.jzIdCard1}}</div>
                  </el-form-item>
                </el-col>
              </el-row>
              <el-row :gutter="50">
                <el-col :span="12">
                  <el-form-item label="家长2姓名：">
                    <el-input v-show="isHandlerMsg" v-model="messageForm.parents_info.jzName2"
                              placeholder="请输入家长2姓名"></el-input>
                    <div v-show="!isHandlerMsg">{{messageData.jzName2}}</div>
                  </el-form-item>
                  <el-form-item label="家长2联系电话：" prop="jzPhone2">
                    <el-input v-show="isHandlerMsg" v-model="messageForm.parents_info.jzPhone2"
                              placeholder="请输入家长2联系电话"></el-input>
                    <div v-show="!isHandlerMsg">{{messageData.jzPhone2}}</div>
                  </el-form-item>
                  <el-form-item label="家长2工作单位：">
                    <el-input v-show="isHandlerMsg" v-model="messageForm.parents_info.jzWorkUnit2"
                              placeholder="请输入家长2工作单位"></el-input>
                    <div v-show="!isHandlerMsg">{{messageData.jzWorkUnit2}}</div>
                  </el-form-item>
                  <el-form-item label="家长2居住地址：">
                    <el-input v-show="isHandlerMsg" v-model="messageForm.parents_info.resAddress2"
                              placeholder="请输入家长2居住地址"></el-input>
                    <div v-show="!isHandlerMsg">{{messageData.resAddress2}}</div>
                  </el-form-item>
                  <el-form-item label="家长2身份证类型：">
                    <el-select v-show="isHandlerMsg" v-model="messageForm.parents_info.jzCardType2" style="width: 100%;"
                               placeholder="请选择家长2身份证类型">
                      <el-option :label="idCardType.value" :value="idCardType.value" v-for="(idCardType,ix) in idCardTypeData" :key="ix"></el-option>
                    </el-select>
                    <div v-show="!isHandlerMsg">{{messageData.jzCardType2}}</div>
                  </el-form-item>
                </el-col>
                <el-col :span="12">
                  <el-form-item label="家长2关系说明：">
                    <el-input v-show="isHandlerMsg" v-model="messageForm.parents_info.relation2"
                              placeholder="请输入关系"></el-input>
                    <div v-show="!isHandlerMsg">{{messageData.relation2}}</div>
                  </el-form-item>
                  <el-form-item label="家长2是否监护人：">
                    <el-radio-group v-show="isHandlerMsg" v-model="messageForm.parents_info.isGuardian2">
                      <el-radio label="是">是</el-radio>
                      <el-radio label="否">否</el-radio>
                    </el-radio-group>
                    <el-radio-group v-show="!isHandlerMsg" v-model="messageForm.parents_info.isGuardian2">
                      <el-radio disabled label="是">是</el-radio>
                      <el-radio disabled label="否">否</el-radio>
                    </el-radio-group>
                  </el-form-item>
                  <el-form-item label="家长2职务：">
                    <el-input v-show="isHandlerMsg" v-model="messageForm.parents_info.jzPost2"
                              placeholder="请输入家长2职务"></el-input>
                    <div v-show="!isHandlerMsg">{{messageData.jzPost2}}</div>
                  </el-form-item>
                  <el-form-item label="家长2民族：">
                    <el-select v-model="messageForm.parents_info.jzNation2" placeholder="请选择民族" style="width: 100%;" v-show="isHandlerMsg">
                      <el-option :label="national.value" :value="national.value" v-for="(national,ix) in nationalList"
                                 :key="ix"></el-option>
                    </el-select>
                    <div v-show="!isHandlerMsg">{{messageData.jzNation2}}</div>
                  </el-form-item>
                  <el-form-item label="家长2身份证号：">
                    <el-input v-show="isHandlerMsg" v-model="messageForm.parents_info.jzIdCard2"
                              placeholder="请输入家长2身份证号"></el-input>
                    <div v-show="!isHandlerMsg">{{messageData.jzIdCard2}}</div>
                  </el-form-item>
                </el-col>
              </el-row>
            </el-form>
            <el-form class="messageForm" ref="tuition_info" :model="messageForm.tuition_info" label-width="130px"
                     v-show="headerShow=='tuition_info'">
              <el-row :gutter="100">
                <el-col :span="11">
                  <el-form-item label="开户银行：">
                    <el-input v-show="isHandlerMsg" v-model="messageForm.tuition_info.openBank"
                              placeholder="请输入开户银行"></el-input>
                    <div v-show="!isHandlerMsg">{{messageData.openBank}}</div>
                  </el-form-item>
                </el-col>
                <el-col :span="11">
                  <el-form-item label="开户银行账号：">
                    <el-input v-show="isHandlerMsg" v-model="messageForm.tuition_info.bankAccount"
                              placeholder="请输入开户银行账号"></el-input>
                    <div v-show="!isHandlerMsg">{{messageData.bankAccount}}</div>
                  </el-form-item>
                </el-col>
              </el-row>
            </el-form>
            <el-form class="messageForm" ref="other_info" :rules="messageFormRules.other_info"
                     :model="messageForm.other_info" label-width="200px"
                     v-show="headerShow=='other_info'">
              <el-row :gutter="50">
                <el-col :span="12">
                  <el-form-item label="是否独生子女：">
                    <el-radio-group v-model="messageForm.other_info.isSingleton" v-show="isHandlerMsg">
                      <el-radio label="1">是</el-radio>
                      <el-radio label="0">否</el-radio>
                    </el-radio-group>
                    <el-radio-group v-model="messageData.isSingleton" v-show="!isHandlerMsg">
                      <el-radio disabled label="1">是</el-radio>
                      <el-radio disabled label="0">否</el-radio>
                    </el-radio-group>
                  </el-form-item>
                  <el-form-item label="是否留守儿童：">
                    <el-radio-group v-model="messageForm.other_info.isBehChild" v-show="isHandlerMsg">
                      <el-radio label="1">是</el-radio>
                      <el-radio label="0">否</el-radio>
                    </el-radio-group>
                    <el-radio-group v-model="messageData.isBehChild" v-show="!isHandlerMsg">
                      <el-radio disabled label="1">是</el-radio>
                      <el-radio disabled label="0">否</el-radio>
                    </el-radio-group>
                  </el-form-item>
                  <el-form-item label="是否孤儿：">
                    <el-radio-group v-model="messageForm.other_info.isOrphan" v-show="isHandlerMsg">
                      <el-radio label="1">是</el-radio>
                      <el-radio label="0">否</el-radio>
                    </el-radio-group>
                    <el-radio-group v-model="messageData.isOrphan" v-show="!isHandlerMsg">
                      <el-radio disabled label="1">是</el-radio>
                      <el-radio disabled label="0">否</el-radio>
                    </el-radio-group>
                  </el-form-item>
                  <el-form-item label="是否烈士或优抚子女：">
                    <el-radio-group v-model="messageForm.other_info.isMartyrChild" v-show="isHandlerMsg">
                      <el-radio label="1">是</el-radio>
                      <el-radio label="0">否</el-radio>
                    </el-radio-group>
                    <el-radio-group v-model="messageData.isMartyrChild" v-show="!isHandlerMsg">
                      <el-radio disabled label="1">是</el-radio>
                      <el-radio disabled label="0">否</el-radio>
                    </el-radio-group>
                  </el-form-item>
                  <el-form-item label="是否需要申请资助：">
                    <el-radio-group v-model="messageForm.other_info.isApplyFund" v-show="isHandlerMsg">
                      <el-radio label="1">是</el-radio>
                      <el-radio label="0">否</el-radio>
                    </el-radio-group>
                    <el-radio-group v-model="messageData.isApplyFund" v-show="!isHandlerMsg">
                      <el-radio disabled label="1">是</el-radio>
                      <el-radio disabled label="0">否</el-radio>
                    </el-radio-group>
                  </el-form-item>
                  <el-form-item label="残疾人类型：">
                    <el-select v-show="isHandlerMsg" v-model="messageForm.other_info.disPerType" style="width: 100%;"
                               placeholder="请选择残疾人类型">
                      <el-option :label="disabledType.value" :value="disabledType.value" v-for="(disabledType,ix) in disabledTypeData" :key="ix"></el-option>
                    </el-select>
                    <div v-show="!isHandlerMsg">{{messageData.disPerType}}</div>
                  </el-form-item>
                  <el-form-item label="上下学交通距离：">
                    <el-input v-show="isHandlerMsg" v-model="messageForm.other_info.distance"
                              placeholder="请输入上下学交通距离"></el-input>
                    <div v-show="!isHandlerMsg">{{messageData.distance}}</div>
                  </el-form-item>
                  <el-form-item label="社工证号：">
                    <el-input v-show="isHandlerMsg" v-model="messageForm.other_info.workerNumber"
                              placeholder="请输入社工证号"></el-input>
                    <div v-show="!isHandlerMsg">{{messageData.workerNumber}}</div>
                  </el-form-item>
                  <el-form-item label="通信地址：">
                    <el-input v-show="isHandlerMsg" v-model="messageForm.other_info.mailAddress"
                              placeholder="请输入通信地址"></el-input>
                    <div v-show="!isHandlerMsg">{{messageData.mailAddress}}</div>
                  </el-form-item>
                  <el-form-item label="血型：">
                    <el-select v-show="isHandlerMsg" v-model="messageForm.other_info.bloodType" style="width: 100%;"
                               placeholder="请选择血型">
                      <el-option :label="bloodType.value" :value="bloodType.value" v-for="(bloodType,ix) in bloodTypeData" :key="ix"></el-option>
                    </el-select>
                    <div v-show="!isHandlerMsg">{{messageData.bloodType}}</div>
                  </el-form-item>
                  <el-form-item label="电子邮箱：" prop="email">
                    <el-input v-show="isHandlerMsg" v-model="messageForm.other_info.email"
                              placeholder="请输入电子邮箱"></el-input>
                    <div v-show="!isHandlerMsg">{{messageData.email}}</div>
                  </el-form-item>
                </el-col>
                <el-col :span="12">
                  <el-form-item label="是否受过学前教育：">
                    <el-radio-group v-model="messageForm.other_info.isPreschool" v-show="isHandlerMsg">
                      <el-radio label="1">是</el-radio>
                      <el-radio label="0">否</el-radio>
                    </el-radio-group>
                    <el-radio-group v-model="messageData.isPreschool" v-show="!isHandlerMsg">
                      <el-radio disabled label="1">是</el-radio>
                      <el-radio disabled label="0">否</el-radio>
                    </el-radio-group>
                  </el-form-item>
                  <el-form-item label="是否进城务工人员随迁子女：">
                    <el-radio-group v-model="messageForm.other_info.isTraChild" v-show="isHandlerMsg">
                      <el-radio label="1">是</el-radio>
                      <el-radio label="0">否</el-radio>
                    </el-radio-group>
                    <el-radio-group v-model="messageData.isTraChild" v-show="!isHandlerMsg">
                      <el-radio disabled label="1">是</el-radio>
                      <el-radio disabled label="0">否</el-radio>
                    </el-radio-group>
                  </el-form-item>
                  <el-form-item label="是否需要乘坐校车：">
                    <el-radio-group v-model="messageForm.other_info.isTakeSchoolBus" v-show="isHandlerMsg">
                      <el-radio label="1">是</el-radio>
                      <el-radio label="0">否</el-radio>
                    </el-radio-group>
                    <el-radio-group v-model="messageData.isTakeSchoolBus" v-show="!isHandlerMsg">
                      <el-radio disabled label="1">是</el-radio>
                      <el-radio disabled label="0">否</el-radio>
                    </el-radio-group>
                  </el-form-item>
                  <el-form-item label="是否享受一补：">
                    <el-radio-group v-model="messageForm.other_info.isSubsidy" v-show="isHandlerMsg">
                      <el-radio label="1">是</el-radio>
                      <el-radio label="0">否</el-radio>
                    </el-radio-group>
                    <el-radio-group v-model="messageData.isSubsidy" v-show="!isHandlerMsg">
                      <el-radio disabled label="1">是</el-radio>
                      <el-radio disabled label="0">否</el-radio>
                    </el-radio-group>
                  </el-form-item>
                  <el-form-item label="是否由政府购买学位：">
                    <el-radio-group v-model="messageForm.other_info.isGovBuyDegree" v-show="isHandlerMsg">
                      <el-radio label="1">是</el-radio>
                      <el-radio label="0">否</el-radio>
                    </el-radio-group>
                    <el-radio-group v-model="messageData.isGovBuyDegree" v-show="!isHandlerMsg">
                      <el-radio disabled label="1">是</el-radio>
                      <el-radio disabled label="0">否</el-radio>
                    </el-radio-group>
                  </el-form-item>
                  <el-form-item label="上学交通方式：">
                    <el-input v-show="isHandlerMsg" v-model="messageForm.other_info.trafficMode"
                              placeholder="请输入上学交通方式"></el-input>
                    <div v-show="!isHandlerMsg">{{messageData.trafficMode}}</div>
                  </el-form-item>
                  <el-form-item label="健康状况：">
                    <el-select v-show="isHandlerMsg" v-model="messageForm.other_info.healthStatus" style="width:100%;"
                               placeholder="请选择健康状况">
                      <el-option value="2" label="差"></el-option>
                      <el-option value="1" label="良好"></el-option>
                      <el-option value="0" label="健康"></el-option>
                    </el-select>
                    <div v-show="!isHandlerMsg">
                      <span v-if="messageData.healthStatus=='0'">健康</span>
                      <span v-if="messageData.healthStatus=='1'">良好</span>
                      <span v-if="messageData.healthStatus=='2'">差</span>
                    </div>
                  </el-form-item>
                  <el-form-item label="学籍辅号：">
                    <el-input v-show="isHandlerMsg" v-model="messageForm.other_info.auxNumber"
                              placeholder="请输入学籍辅号"></el-input>
                    <div v-show="!isHandlerMsg">{{messageData.auxNumber}}</div>
                  </el-form-item>
                  <el-form-item label="学生来源：">
                    <el-input v-show="isHandlerMsg" v-model="messageForm.other_info.studentSource"
                              placeholder="请输入学生来源"></el-input>
                    <div v-show="!isHandlerMsg">{{messageData.studentSource}}</div>
                  </el-form-item>
                  <el-form-item label="主页地址：">
                    <el-input v-show="isHandlerMsg" v-model="messageForm.other_info.businessPage"
                              placeholder="请输入主页地址"></el-input>
                    <div v-show="!isHandlerMsg">{{messageData.businessPage}}</div>
                  </el-form-item>
                </el-col>
              </el-row>
            </el-form>
          </el-row>
          <!--<footer class="gR-footer">
            <el-button type="primary" class="msgButton" v-show="!isHandlerMsg"
                       @click="buttonClick('edit')">
              编辑
            </el-button>
            <el-button class="msgButton" v-show="isHandlerMsg" @click="isHandlerMsg=false">取消</el-button>
            <el-button type="primary" class="msgButton" v-show="isHandlerMsg"
                       @click="buttonClick('save')">
              保存
            </el-button>
          </footer>-->
        </section>
        </el-row>
      </div>
    </section>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  import {ethnics,politicalStatus,idCardType,disabledType,bloodType,entranceType,admittedType,tenanciesType} from '@/assets/js/common-const-data'
  import moment from 'moment'
  import {
    studentManagerGetGrade,//得到左边年级及姓名
  } from '@/api/http'
  export default{
    data(){
      var regPhone = /0?(13|14|15|18)[0-9]{9}$/,
        regEmail = /\w+((-w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+/;
      var validatePhone = (rule, value, callback) => {
        if (value && !regPhone.test(value)) {
          callback(new Error('请输入正确手机号'));
        } else {
          callback();
        }
      };
      var validateEmail = (rule, value, callback) => {
        if (value && !regEmail.test(value)) {
          callback(new Error('请输入正确邮箱格式'));
        } else {
          callback();
        }
      };
      return {
        politicalData:politicalStatus,
        idCardTypeData:idCardType,
        nationalList:ethnics,
        disabledTypeData:disabledType,
        bloodTypeData:bloodType,
        entranceTypeData:entranceType,
        admittedTypeData:admittedType,
        tenanciesTypeData:tenanciesType,
        treeData: [],
        defaultProps: {
          children: 'data',
          label: 'name'
        },
        userId: '',
        fuzzyInput: '',
        headerShow: 'student_info',
        messageAllData: {
          student_info: {},
          school_rollinfo: {},
          cen_reg_info: {},
          parents_info: {},
          tuition_info: {},
          other_info: {}
        },
        messageData: {},
        student_infoTime1: '',
        student_infoTime2: '',
        isHandlerMsg: false,
        branchList: [],
        majorList: [],
        messageForm: {
          /*基本信息*/
          student_info: {
            logo: '',
            sex: '',
            birthday: '',
            serialNumber: '',
            height: '',
            idCard: '',
            country: '',
            nation: '',
            leagueTime: '',
            enrolTime: '',
            enrolWay: '',
            isResSchool: '',
            phone: '',
            homePath: '',
            homePostcode: '',
            nowHomePath: '',
            nowHomePostcode: '',
            dorStore: '',
            dorStorey: '',
            dorNumber: '',
            studentCard: ''
          },
          /*学籍信息*/
          school_rollinfo: {
            isAtSchool: '是',
            isLeave: '0',
            isSubor: '0',
            isTempStudy: '0',
            stream: '',
            major: '',
            nationalNumber: '',
            provinceNumber: '',
            cityNumber: '',
            admCategory: '',
            eleSchool: '',
            secSchool: '',
            midExam: ''
          },
          /*户籍信息*/
          cen_reg_info: {
            perAddress: '',
            perAddressCode: '',
            cenRegType: '',
            originCode: '',
            cenRegNature: ''
          },
          /*家长信息*/
          parents_info: {
            jzName1: '',
            relation1: '',
            jzPhone1: '',
            resAddress1: '',
            jzWorkUnit1: '',
            jzPost1: '',
            jzCardType1: '',
            jzIdCard1: '',
            jzNation1: '',
            jzName2: '',
            relation2: '',
            jzPhone2: '',
            jzWorkUnit2: '',
            jzPost2: '',
            jzCardType2: '',
            resAddress2: '',
            jzIdCard2: '',
            jzNation2: '',
            isGuardian1: '是',
            isGuardian2: '是'
          },
          /*学费信息*/
          tuition_info: {
            openBank: '',
            bankAccount: ''
          },
          /*其他信息*/
          other_info: {
            disPerType: '',
            trafficMode: '',
            distance: '',
            workerNumber: '',
            healthStatus: '',
            mailAddress: '',
            bloodType: '',
            studentSource: '',
            email: '',
            businessPage: '',
            isSingleton: '0',
            isBehChild: '0',
            isOrphan: '0',
            isMartyrChild: '0',
            isApplyFund: '0',
            isPreschool: '0',
            isTraChild: '0',
            isTakeSchoolBus: '0',
            isSubsidy: '0',
            isGovBuyDegree: '0',
            auxNumber: ''
          }
        },
        messageFormRules: {
          student_info: {
            phone: [
              {validator: validatePhone, trigger: 'blur'}
            ]
          },
          parents_info: {
            jzPhone1: [
              {validator: validatePhone, trigger: 'blur'}
            ],
            jzPhone2: [
              {validator: validatePhone, trigger: 'blur'}
            ]
          },
          other_info: {
            email: [
              {validator: validateEmail, trigger: 'blur'}
            ]
          }
        },
        loading:false
      }
    },
    watch: {
      fuzzyInput(val){
        this.$refs.tree.filter(val);
      }
    },
    created(){
      var self = this;
      self.getGradeAjax();
      //得到科类
      req.ajaxSend('/school/user/userGl?type=getBranch', 'get', '', function (res) {
        self.branchList = res.data;
      });
    },
    methods: {
      filterNode(value, data)
      {
        if (!value) return true;
        if (data.name) {
          data.name = data.name.toString();
          return data.name.indexOf(value) !== -1;
        }
      },
      handleNodeClick(node){
        if (!node.data && node.userId) {
          var self = this, data = {
            userId: node.userId
          };
          this.userId = node.userId;
          req.ajaxSend('/school/user/userGl?type=studentPersonalData', 'get', data, function (res) {
            self.messageAllData = res;
            self.messageData = self.messageAllData[self.headerShow];
            if (self.headerShow == 'student_info') {
              self.student_infoTime1 = self.messageData.leagueTime ? new Date(self.messageData.leagueTime) : self.messageData.leagueTime;
              self.student_infoTime2 = self.messageData.enrolTime ? new Date(self.messageData.enrolTime) : self.messageData.enrolTime;
            }
            $.extend(self.messageForm[self.headerShow], self.messageData);
          })
        }
      },
      changeMsg(name){
        this.headerShow = name;
        this.messageData = this.messageAllData[this.headerShow];
        if (this.headerShow == 'student_info') {
          this.student_infoTime1 = this.messageData.leagueTime ? new Date(this.messageData.leagueTime) : this.messageData.leagueTime;
          this.student_infoTime2 = this.messageData.enrolTime ? new Date(this.messageData.enrolTime) : this.messageData.enrolTime;
        }
        $.extend(this.messageForm[this.headerShow], this.messageData);
      },
      sendFile(){   //上传文件
        // 实例化一个表单数据对象
        var self = this, file = $('.file_input').prop('files')[0], suffix,sAy,len, data = new FormData();
        if (!file) {
          return false;
        }
        $('.file_input').val('');
        sAy = file.name.split('.');
        len = sAy.length-1;
        suffix = sAy[len];
        if (file) {
          if (suffix != 'jpeg' && suffix != 'jpg' && suffix != 'png') {
            self.vmMsgWarning('只能上传jpeg、jpg、png格式图片！');
            return false;
          }
          data.append('userFile', file);
          req.ajaxFile('/school/User/userGl?type=uploadStudentLogo', 'post', data, function (res) {
            if (res.statu == 1) {
              self.vmMsgSuccess('上传成功！');
              self.messageForm.student_info.logo = res.logo;
            } else {
              self.vmMsgError('上传失败！');
            }
          })
        }
      },
      chooseMajor(){
        var self = this, data = {
          branchid: ''
        };
        for (let obj of self.branchList) {
          if (obj.branch == self.messageForm.school_rollinfo.stream) {
            data.branchid = obj.branchid;
          }
        }
        self.messageForm.school_rollinfo.major = '';
        req.ajaxSend('/school/user/userGl?type=getMajor', 'get', data, function (res) {
          self.majorList = res.data;
        })
      },
      getGradeAjax(){
        this.loading=true;
        studentManagerGetGrade().then((data) => {
          this.loading=false;
          this.treeData = this.handleData(data);
        });
      },
      /*数据处理*/
      handleData(data){
        if (data.statu) {
          return data.data;
        } else {
          this.vmMsgError('请求失败，请重试!');
        }
      },
      /*按钮点击事件*/
      buttonClick(type){
        var self = this, data = {
          ListType: self.headerShow,
          updataData: self.messageForm[self.headerShow]
        };
        if (type == 'edit') {
          self.isHandlerMsg = true;
          if (self.headerShow == 'student_info') {
            self.student_infoTime1 = self.messageData.leagueTime ? new Date(self.messageData.leagueTime) : self.messageData.leagueTime;
            self.student_infoTime2 = self.messageData.enrolTime ? new Date(self.messageData.enrolTime) : self.messageData.enrolTime;
          }
          $.extend(self.messageForm[self.headerShow], self.messageData);
        } else {
          if (self.headerShow == 'student_info') {
            self.messageForm[self.headerShow].leagueTime = self.student_infoTime1 ? moment(self.student_infoTime1).format('YYYY-MM-DD') : self.student_infoTime1;
            self.messageForm[self.headerShow].enrolTime = self.student_infoTime2 ? moment(self.student_infoTime2).format('YYYY-MM-DD') : self.student_infoTime2;
          }
          self.$refs[self.headerShow].validate((valid) => {
            if (valid) {
              req.ajaxSend('/school/user/userGl?type=studentPersonalUpdata', 'post', data, function (res) {
                if (res.statu == '1') {
                  let datas = {
                    userId: self.userId
                  };
                  self.vmMsgSuccess('修改成功！');
                  req.ajaxSend('/school/user/userGl?type=studentPersonalData', 'get', datas, function (res) {
                    self.messageAllData = res;
                    self.messageData = self.messageAllData[self.headerShow];
                    $.extend(self.messageForm[self.headerShow], self.messageData);
                  })
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
  }
</script>
<style lang="less" scoped>
  @import '../../../../../style/style';
  @import '../../../../../style/userManager/student/studentManager.less';
  @import '../../../../../style/userManager/student/studentManager.css';
</style>



